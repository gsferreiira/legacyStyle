<?php
session_start();
require 'db_connection.php';
require 'envia_email.php';

// =========================================================================
// ⚠️ CONFIGURAÇÃO OBRIGATÓRIA (EDITE AQUI PARA FUNCIONAR) ⚠️
// =========================================================================

// Digite abaixo o endereço EXATO do seu site, SEM a barra no final.
// EXEMPLO LOCAL: http://localhost/legacystyle
// EXEMPLO HOSTINGER: https://barbearialegacy.com.br

define('SITE_URL', 'https://legacystyle.com.br'); 

// =========================================================================

// Configuração dos links de retorno (Não mexa aqui)
$url_sucesso  = SITE_URL . "/index.php?agendamento=sucesso&msg=Pagamento_Aprovado";
$url_falha    = SITE_URL . "/index.php?agendamento=erro&msg=Pagamento_Falhou";
$url_pendente = SITE_URL . "/index.php?agendamento=sucesso&msg=Pagamento_Pendente";
$url_webhook  = str_replace('localhost', 'seusite.com.br', SITE_URL) . '/notificacao.php';

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
    
    // Corrige valor para formato decimal (ex: 40.00)
    $valor_raw      = $_POST['valor_total'] ?? 0;
    $valor_final    = (float) $valor_raw; 
    
    $id_cliente     = isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null;

    if (!$barbeiro_id || !$servicos_ids || !$data || !$hora || !$email) {
        die("<h3>Erro:</h3> Dados incompletos. <a href='index.php'>Voltar</a>");
    }

    try {
        $pdo->beginTransaction();

        // 2. Token do Barbeiro
        $stmt = $pdo->prepare("SELECT nome, mp_access_token FROM barbeiros WHERE id = ?");
        $stmt->execute([$barbeiro_id]);
        $barbeiro_dados = $stmt->fetch();

        if (!$barbeiro_dados) die("Erro: Barbeiro não encontrado.");
        
        $nome_barbeiro = $barbeiro_dados['nome'];
        $token_mp = trim($barbeiro_dados['mp_access_token']); // Remove espaços extras

        // 3. Serviços
        $ids_array = explode(',', $servicos_ids);
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
        $stmtS = $pdo->prepare("SELECT nome, duracao FROM servicos WHERE id IN ($placeholders)");
        $stmtS->execute($ids_array);
        $servicos_db = $stmtS->fetchAll(PDO::FETCH_ASSOC);
        
        $duracao_total = 0;
        $lista_nomes = [];
        foreach($servicos_db as $s) {
            $duracao_total += $s['duracao'];
            $lista_nomes[] = $s['nome'];
        }
        $descricao = "Agendamento Legacy"; // Descrição curta e segura

        // ======================================================
        // INTEGRAÇÃO MERCADO PAGO
        // ======================================================
        $link_mp = null;
        $id_preferencia = null;
        $status = 'pendente';

        if ($pagamento === 'pix') {
            
            if (empty($token_mp)) die("Erro: Token de pagamento não configurado.");

            // JSON Payload
            $dados = [
                "items" => [
                    [
                        "title" => $descricao,
                        "description" => "Servicos com " . $nome_barbeiro,
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => $valor_final
                    ]
                ],
                "payer" => [
                    "email" => $email,
                    "name" => "Cliente",
                    "surname" => "Legacy"
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

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($dados),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token_mp
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($curl);
            $erro_curl = curl_error($curl);
            curl_close($curl);

            if ($erro_curl) die("Erro de Conexão MP: " . $erro_curl);

            $mp_res = json_decode($response, true);

            // Verifica Resposta
            if (isset($mp_res['init_point'])) {
                // Se o token for TESTE, usa sandbox
                if (strpos($token_mp, 'TEST') === 0) {
                    $link_mp = $mp_res['sandbox_init_point'];
                } else {
                    $link_mp = $mp_res['init_point'];
                }
                $id_preferencia = $mp_res['id'];
            } else {
                // Debug na tela
                echo "<div style='font-family:sans-serif; padding:20px; border:2px solid red;'>";
                echo "<h2 style='color:red'>Erro no Mercado Pago</h2>";
                echo "<p>Ocorreu um erro ao gerar o link. Detalhes:</p>";
                echo "<pre>" . print_r($mp_res, true) . "</pre>";
                echo "<hr>";
                echo "<strong>URL Base configurada:</strong> " . SITE_URL;
                echo "</div>";
                die();
            }
        }

        // 4. Salvar no Banco
        $sql = "INSERT INTO agendamentos 
        (id_barbeiro, id_cliente, servicos_ids, nome_cliente, telefone, email, data, hora, duracao, valor_final, metodo_pagamento, status, mp_id, mp_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $barbeiro_id, $id_cliente, $servicos_ids, $nome_cliente, $telefone, $email, 
            $data, $hora, $duracao_total, $valor_final, $pagamento, 
            $status, $id_preferencia, $status
        ]);

        $novo_id = $pdo->lastInsertId();

        // 5. Enviar Email (Se for Presencial)
        if ($pagamento !== 'pix') {
            try { 
                if(function_exists('enviarEmailConfirmacao')) {
                    $dadosEmail = ['id' => $novo_id, 'nome_cliente' => $nome_cliente, 'email' => $email, 'data' => $data, 'hora' => $hora, 'barbeiro_nome' => $nome_barbeiro, 'servicos' => $descricao, 'status_pagamento' => 'No Local'];
                    enviarEmailConfirmacao($dadosEmail); 
                }
            } catch (Exception $e) {}
        }

        $pdo->commit();

        if ($pagamento === 'pix' && $link_mp) {
            header("Location: " . $link_mp);
        } else {
            header("Location: index.php?agendamento=sucesso&mensagem=Agendamento_Realizado");
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro Interno: " . $e->getMessage());
    }
}
?>