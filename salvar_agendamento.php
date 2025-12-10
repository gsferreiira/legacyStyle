<?php
session_start();
require 'db_connection.php';
// require 'envia_email.php'; // Se tiver esse arquivo, pode descomentar

// --- CONFIGURAÇÃO MANUAL DA URL ---
// Importante: Alterei de 'msg' para 'mensagem' para bater com o JavaScript do index.php
define('SITE_URL', 'https://www.legacystyle.com.br'); 

$url_sucesso  = SITE_URL . "/index.php?agendamento=sucesso&mensagem=Pagamento confirmado!";
$url_falha    = SITE_URL . "/index.php?agendamento=erro&mensagem=O pagamento falhou. Tente novamente.";
$url_pendente = SITE_URL . "/index.php?agendamento=sucesso&mensagem=Estamos processando seu pagamento.";
$url_webhook  = SITE_URL . '/notificacao.php';

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Receber Dados
    $barbeiro_id    = filter_input(INPUT_POST, 'barbeiro_id', FILTER_VALIDATE_INT);
    $servicos_ids   = filter_input(INPUT_POST, 'servicos', FILTER_SANITIZE_STRING);
    $data           = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $hora           = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $nome_cliente   = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $telefone       = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $pagamento      = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING) ?? 'presencial';
    $valor_final    = (float) ($_POST['valor_total'] ?? 0);
    
    // CPF: Remove tudo que não for número
    $cpf_limpo      = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');

    $id_cliente     = $_SESSION['cliente_id'] ?? null;

    // Validação
    if (!$barbeiro_id || !$servicos_ids || !$data || !$hora || !$email) {
        // Redireciona com erro em vez de morrer na tela branca
        header("Location: index.php?agendamento=erro&mensagem=Preencha todos os campos obrigatórios.");
        exit;
    }
    
    if ($pagamento === 'pix' && strlen($cpf_limpo) < 11) {
        header("Location: index.php?agendamento=erro&mensagem=CPF inválido para pagamento Pix.");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 2. Token do Barbeiro
        $stmt = $pdo->prepare("SELECT nome, mp_access_token FROM barbeiros WHERE id = ?");
        $stmt->execute([$barbeiro_id]);
        $barbeiro_dados = $stmt->fetch();
        
        $token_mp = trim($barbeiro_dados['mp_access_token'] ?? '');
        $nome_barbeiro = $barbeiro_dados['nome'] ?? 'Barbeiro';

        // 3. Serviços
        $ids = explode(',', $servicos_ids);
        // Prepara query segura para array
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmtS = $pdo->prepare("SELECT nome FROM servicos WHERE id IN ($marks)");
        $stmtS->execute($ids);
        $nomes = $stmtS->fetchAll(PDO::FETCH_COLUMN);
        $descricao = "Corte Legacy - " . implode(", ", $nomes);

        // ======================================================
        // INTEGRAÇÃO MP
        // ======================================================
        $link_mp = null;
        $id_pref = null;
        $status = 'pendente';

        if ($pagamento === 'pix') {
            if (empty($token_mp)) {
                header("Location: index.php?agendamento=erro&mensagem=Pagamento online indisponível no momento.");
                exit;
            }

            $partes = explode(' ', $nome_cliente, 2);
            $primeiro = $partes[0];
            $sobrenome = $partes[1] ?? 'Cliente';

            $dados_mp = [
                "items" => [
                    [
                        "title" => $descricao,
                        "description" => "Servicos Barbearia",
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
                    "excluded_payment_types" => [["id" => "ticket"]]
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
                $link_mp = (strpos($token_mp, 'TEST') === 0) ? $mp_res['sandbox_init_point'] : $mp_res['init_point'];
                $id_pref = $mp_res['id'];
            } else {
                // Erro na API do MP
                header("Location: index.php?agendamento=erro&mensagem=Erro ao gerar PIX. Tente pagar na barbearia.");
                exit;
            }
        }

        // 4. Salvar no Banco
        $sql = "INSERT INTO agendamentos (id_barbeiro, id_cliente, servicos_ids, nome_cliente, telefone, email, data, hora, valor_final, metodo_pagamento, status, mp_id, mp_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$barbeiro_id, $id_cliente, $servicos_ids, $nome_cliente, $telefone, $email, $data, $hora, $valor_final, $pagamento, $status, $id_pref, $status]);
        $novo_id = $pdo->lastInsertId();

        // 5. Envia Email (opcional)
        if ($pagamento !== 'pix' && function_exists('enviarEmailConfirmacao')) {
            try {
                $dadosEmail = ['id'=>$novo_id, 'nome_cliente'=>$nome_cliente, 'email'=>$email, 'data'=>$data, 'hora'=>$hora, 'barbeiro_nome'=>$nome_barbeiro, 'servicos'=>$descricao, 'status_pagamento'=>'No Local'];
                enviarEmailConfirmacao($dadosEmail);
            } catch (Exception $e) {}
        }

        $pdo->commit();

        // --- REDIRECIONAMENTOS FINAIS ---
        if ($pagamento === 'pix' && $link_mp) {
            // Vai para o Mercado Pago
            header("Location: " . $link_mp);
        } else {
            // Volta para o site com a mensagem de SUCESSO VERDE
            header("Location: index.php?agendamento=sucesso&mensagem=Agendamento realizado com sucesso! Te esperamos lá.");
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        // Volta para o site com a mensagem de ERRO VERMELHO
        header("Location: index.php?agendamento=erro&mensagem=Ocorreu um erro interno. Tente novamente.");
        exit;
    }
}
?>