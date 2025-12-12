<?php
session_start();
require 'db_connection.php';

// --- CONFIGURAÇÃO DA URL ---
define('SITE_URL', 'https://www.legacystyle.com.br'); 

$url_sucesso  = SITE_URL . "/index.php?agendamento=sucesso&mensagem=Pagamento confirmado!";
$url_falha    = SITE_URL . "/index.php?agendamento=erro&mensagem=O pagamento falhou. Tente novamente.";
$url_pendente = SITE_URL . "/index.php?agendamento=sucesso&mensagem=Estamos processando seu pagamento.";
$url_webhook  = SITE_URL . '/notificacao.php';

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // VARIÁVEL ESSENCIAL PARA O NOVO RECURSO DE EXPIRAÇÃO (10 minutos)
    $data_criacao = date('Y-m-d H:i:s'); 
    
    // 1. Receber Dados do Formulário
    $barbeiro_id    = filter_input(INPUT_POST, 'barbeiro_id', FILTER_VALIDATE_INT);
    $servicos_ids   = filter_input(INPUT_POST, 'servicos', FILTER_SANITIZE_STRING);
    $data           = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $hora           = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $nome_cliente   = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $telefone       = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $pagamento      = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING) ?? 'presencial';
    $valor_final    = (float) ($_POST['valor_total'] ?? 0);
    $cpf_limpo      = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $id_cliente     = $_SESSION['cliente_id'] ?? null;

    // --- NOVO: LÓGICA DE CADASTRO RÁPIDO PARA CLIENTES NÃO LOGADOS ---
    $senha_cadastro = $_POST['senha_cadastro'] ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';
    $consentimento  = isset($_POST['consentimento_cadastro']); 

    if (!$id_cliente && !empty($senha_cadastro)) {
        // Cliente não está logado, mas forneceu senhas para cadastro rápido
        
        if ($senha_cadastro !== $senha_confirma) {
            header("Location: index.php?agendamento=erro&mensagem=As senhas de cadastro não conferem.");
            exit;
        }
        
        if (!$consentimento) {
            header("Location: index.php?agendamento=erro&mensagem=Você deve dar consentimento para criar a conta.");
            exit;
        }

        try {
            // 1. Verificar se o e-mail já existe
            $stmt_check = $pdo->prepare("SELECT id, senha, nome, telefone FROM clientes WHERE email = ?");
            $stmt_check->execute([$email]);
            $cliente_existente = $stmt_check->fetch();

            if ($cliente_existente) {
                 // Se o cliente existe, verifica a senha (pode ser uma tentativa de login)
                 if (password_verify($senha_cadastro, $cliente_existente['senha'])) {
                    $id_cliente = $cliente_existente['id'];
                    // Loga o cliente
                    $_SESSION['cliente_id'] = $id_cliente; 
                    $_SESSION['cliente_nome'] = $cliente_existente['nome'];
                    $_SESSION['cliente_email'] = $email;
                    $_SESSION['cliente_telefone'] = $cliente_existente['telefone'];
                 } else {
                     header("Location: index.php?agendamento=erro&mensagem=Este e-mail já está cadastrado com outra senha.");
                     exit;
                 }
            } else {
                // 2. Inserir novo cliente (Cadastro)
                $senhaHash = password_hash($senha_cadastro, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
                $stmt_insert->execute([$nome_cliente, $email, $telefone, $senhaHash]);
                $id_cliente = $pdo->lastInsertId();
                
                // Loga o cliente automaticamente para a sessão atual
                $_SESSION['cliente_id'] = $id_cliente;
                $_SESSION['cliente_nome'] = $nome_cliente;
                $_SESSION['cliente_email'] = $email;
                $_SESSION['cliente_telefone'] = $telefone;
            }
        } catch (Exception $e) {
            // Em caso de erro, segue como agendamento anônimo
            $id_cliente = null;
        }
    }
    // FIM DA LÓGICA DE CADASTRO RÁPIDO
    
    // Validação
    if (!$barbeiro_id || !$servicos_ids || !$data || !$hora || !$email) {
        header("Location: index.php?agendamento=erro&mensagem=Preencha todos os campos obrigatórios.");
        exit;
    }
    
    if ($pagamento === 'pix' && strlen($cpf_limpo) < 11) {
        header("Location: index.php?agendamento=erro&mensagem=CPF inválido para pagamento Pix.");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 2. Busca Token e Nome do Barbeiro direto do Banco
        $stmt = $pdo->prepare("SELECT nome, mp_access_token FROM barbeiros WHERE id = ?");
        $stmt->execute([$barbeiro_id]);
        $barbeiro_dados = $stmt->fetch();
        
        $token_mp = trim($barbeiro_dados['mp_access_token'] ?? '');
        $nome_barbeiro = $barbeiro_dados['nome'] ?? 'Barbeiro';

        // 3. Serviços
        $ids = explode(',', $servicos_ids);
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmtS = $pdo->prepare("SELECT nome FROM servicos WHERE id IN ($marks)");
        $stmtS->execute($ids);
        $nomes = $stmtS->fetchAll(PDO::FETCH_COLUMN);
        $descricao = "Corte Legacy - " . implode(", ", $nomes);

        // ======================================================
        // INTEGRAÇÃO MP (Genérica via CURL)
        // ======================================================
        $link_mp = null;
        $id_pref = null;
        $status_inicial = ($pagamento === 'pix') ? 'pendente' : 'agendado'; 

        if ($pagamento === 'pix') {
            if (empty($token_mp)) {
                header("Location: index.php?agendamento=erro&mensagem=Erro: Pagamento indisponível para este barbeiro.");
                exit;
            }

            $partes = explode(' ', $nome_cliente, 2);
            $primeiro = $partes[0];
            $sobrenome = $partes[1] ?? 'Cliente';

            $dados_mp = [
                "items" => [
                    [
                        "title" => $descricao,
                        "description" => "Profissional: " . $nome_barbeiro,
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => $valor_final
                    ]
                ],
                "payer" => [
                    "email" => $email,
                    "name" => $primeiro,
                    "surname" => $sobrenome,
                    "identification" => [
                        "type" => "CPF",
                        "number" => $cpf_limpo
                    ]
                ],
                "back_urls" => [
                    "success" => $url_sucesso,
                    "failure" => $url_falha,
                    "pending" => $url_pendente
                ],
                "auto_return" => "approved",
                "notification_url" => $url_webhook . "?barbeiro_id=" . $barbeiro_id,
                "payment_methods" => [
                    "excluded_payment_types" => [["id" => "ticket"]],
                    "installments" => 1
                ]
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($dados_mp),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token_mp
                ],
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $res = curl_exec($ch);
            curl_close($ch);
            $mp_res = json_decode($res, true);

            if (isset($mp_res['init_point'])) {
                $link_mp = (strpos($token_mp, 'TEST') !== false) ? $mp_res['sandbox_init_point'] : $mp_res['init_point'];
                $id_pref = $mp_res['id'];
            } else {
                header("Location: index.php?agendamento=erro&mensagem=Erro ao comunicar com Mercado Pago.");
                exit;
            }
        }

        // 4. Salvar no Banco (INCLUINDO data_criacao e id_cliente, se tiver)
        $sql = "INSERT INTO agendamentos (id_barbeiro, id_cliente, servicos_ids, nome_cliente, telefone, email, data, hora, valor_final, metodo_pagamento, status, mp_id, mp_status, data_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$barbeiro_id, $id_cliente, $servicos_ids, $nome_cliente, $telefone, $email, $data, $hora, $valor_final, $pagamento, $status_inicial, $id_pref, $status_inicial, $data_criacao]); 
        
        $pdo->commit();

        // 5. Redirecionar
        if ($pagamento === 'pix' && $link_mp) {
            header("Location: " . $link_mp);
        } else {
            header("Location: index.php?agendamento=sucesso&mensagem=Agendamento realizado! Te esperamos lá.");
        }
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: index.php?agendamento=erro&mensagem=Erro interno ao salvar. Detalhe: " . $e->getMessage()); // Adicionado detalhe do erro para debug
        exit;
    }
}
?>