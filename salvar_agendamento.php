<?php
session_start();
require 'db_connection.php';
require 'envia_email.php';

// Limpeza e Definição de URLs
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
$base_url = $protocol . "://" . $host . $path;

define('URL_SUCESSO', $base_url . '/index.php?agendamento=sucesso&mensagem=Pagamento realizado!');
define('URL_FALHA', $base_url . '/index.php?agendamento=erro&mensagem=Pagamento falhou.');
define('URL_PENDENTE', $base_url . '/index.php?agendamento=sucesso&mensagem=Pagamento em análise.');
define('URL_NOTIFICACAO', 'https://seusite.com.br/notificacao.php'); // AJUSTE SEU DOMÍNIO AQUI

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Receber Dados
    $barbeiro_id = filter_input(INPUT_POST, 'barbeiro_id', FILTER_VALIDATE_INT);
    $servicos_ids = filter_input(INPUT_POST, 'servicos', FILTER_SANITIZE_STRING);
    $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $nome_cliente = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $metodo_pagamento = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING) ?? 'presencial';
    $valor_final = filter_input(INPUT_POST, 'valor_total', FILTER_VALIDATE_FLOAT);
    $id_cliente = isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null;

    if (!$barbeiro_id || !$servicos_ids || !$data || !$hora || !$email) die("Erro: Dados incompletos.");

    try {
        $pdo->beginTransaction();

        // 2. Dados do Barbeiro
        $stmt = $pdo->prepare("SELECT nome, mp_access_token FROM barbeiros WHERE id = ?");
        $stmt->execute([$barbeiro_id]);
        $barbeiro_dados = $stmt->fetch();
        $barbeiro_nome = $barbeiro_dados['nome'];
        $access_token = $barbeiro_dados['mp_access_token'];

        // Detalhes Serviços
        $ids_array = explode(',', $servicos_ids);
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
        $stmtServ = $pdo->prepare("SELECT nome, duracao FROM servicos WHERE id IN ($placeholders)");
        $stmtServ->execute($ids_array);
        $servicos = $stmtServ->fetchAll(PDO::FETCH_ASSOC);
        
        $duracao_total = 0;
        $nomes = [];
        foreach($servicos as $s) { $duracao_total += $s['duracao']; $nomes[] = $s['nome']; }
        $desc_servicos = implode(", ", $nomes);

        // 3. Mercado Pago (Checkout Pro)
        $link_pagamento = null;
        $mp_id = null;
        $status_inicial = 'pendente';

        if ($metodo_pagamento === 'pix') {
            if (empty($access_token)) die("Erro: Configuração de pagamento ausente.");
            
            $webhook = URL_NOTIFICACAO . "?barbeiro_id=" . $barbeiro_id;
            
            $dados_mp = [
                "items" => [[
                    "title" => "Legacy Style - " . $barbeiro_nome,
                    "description" => $desc_servicos,
                    "quantity" => 1,
                    "currency_id" => "BRL",
                    "unit_price" => (float)$valor_final
                ]],
                "payer" => [
                    "email" => $email,
                    "name" => "Cliente",
                    "surname" => "Legacy"
                ],
                "back_urls" => [
                    "success" => URL_SUCESSO,
                    "failure" => URL_FALHA,
                    "pending" => URL_PENDENTE
                ],
                "auto_return" => "approved",
                "notification_url" => $webhook,
                "payment_methods" => ["excluded_payment_types" => [["id" => "ticket"]]]
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($dados_mp),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $access_token],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            $response = curl_exec($curl);
            curl_close($curl);
            $mp_res = json_decode($response, true);

            if (isset($mp_res['init_point'])) {
                $link_pagamento = (strpos($access_token, 'TEST') === 0) ? $mp_res['sandbox_init_point'] : $mp_res['init_point'];
                $mp_id = $mp_res['id'];
            } else {
                die("Erro Checkout MP");
            }
        }

        // 4. Salvar Banco
        $sql = "INSERT INTO agendamentos (id_barbeiro, id_cliente, servicos_ids, nome_cliente, telefone, email, data, hora, duracao, valor_final, metodo_pagamento, status, mp_id, mp_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$barbeiro_id, $id_cliente, $servicos_ids, $nome_cliente, $telefone, $email, $data, $hora, $duracao_total, $valor_final, $metodo_pagamento, $status_inicial, $mp_id, $status_inicial]);
        
        $id_agendamento = $pdo->lastInsertId();

        // 5. Enviar Email (SÓ SE NÃO FOR PIX/MP)
        if ($metodo_pagamento !== 'pix') {
            $dadosEmail = [
                'id' => $id_agendamento,
                'nome_cliente' => $nome_cliente,
                'email' => $email,
                'data' => $data,
                'hora' => $hora,
                'barbeiro_nome' => $barbeiro_nome,
                'servicos' => $desc_servicos,
                'status_pagamento' => 'Pagar no Local'
            ];
            try { enviarEmailConfirmacao($dadosEmail); } catch (Exception $e) {}
        }

        $pdo->commit();

        if ($metodo_pagamento === 'pix' && $link_pagamento) {
            header("Location: " . $link_pagamento);
        } else {
            header("Location: index.php?agendamento=sucesso&mensagem=Agendamento realizado!");
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro: " . $e->getMessage());
    }
}
?>