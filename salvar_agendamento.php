<?php
session_start();
require 'db_connection.php';
require 'envia_email.php';

// --- CORREÇÃO DE URL (FIX PARA WINDOWS/XAMPP) ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Remove barras invertidas e espaços extras
$path = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
$base_url = $protocol . "://" . $host . $path;

// URLs Limpas (Sem espaços ou caracteres estranhos)
define('URL_SUCESSO', $base_url . '/index.php?agendamento=sucesso');
define('URL_FALHA', $base_url . '/index.php?agendamento=erro');
define('URL_PENDENTE', $base_url . '/index.php?agendamento=sucesso');

// Webhook (Na Hostinger, mude para o domínio real)
define('URL_NOTIFICACAO', 'https://legacystyle.com.br/notificacao.php'); 

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Receber e Validar Dados
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

    if (!$barbeiro_id || !$servicos_ids || !$data || !$hora || !$email) {
        die("Erro: Dados incompletos.");
    }

    try {
        $pdo->beginTransaction();

        // 2. Buscar Token do Barbeiro
        $stmtBarbeiro = $pdo->prepare("SELECT nome, mp_access_token FROM barbeiros WHERE id = ?");
        $stmtBarbeiro->execute([$barbeiro_id]);
        $barbeiro_dados = $stmtBarbeiro->fetch();

        if (!$barbeiro_dados) die("Erro: Barbeiro não encontrado.");
        
        $barbeiro_nome = $barbeiro_dados['nome'];
        $access_token = $barbeiro_dados['mp_access_token'];

        // Calcular serviços
        $ids_array = explode(',', $servicos_ids);
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
        $stmtServicos = $pdo->prepare("SELECT duracao, nome FROM servicos WHERE id IN ($placeholders)");
        $stmtServicos->execute($ids_array);
        $dados_servicos = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);

        $duracao_total = 0;
        $nomes_servicos = [];
        foreach ($dados_servicos as $s) {
            $duracao_total += $s['duracao'];
            $nomes_servicos[] = $s['nome'];
        }
        $lista_servicos_texto = implode(", ", $nomes_servicos);

        // --- INTEGRAÇÃO MERCADO PAGO ---
        $link_pagamento = null;
        $mp_id = null; 
        $status_inicial = 'pendente';

        if ($metodo_pagamento === 'pix') {
            if (empty($access_token)) {
                die("Erro: Barbeiro sem token MP configurado.");
            }

            $webhook_url = URL_NOTIFICACAO . "?barbeiro_id=" . $barbeiro_id;

            $dados_mp = [
                "items" => [
                    [
                        "id" => "legacy_srv",
                        "title" => "Legacy Style - Servicos",
                        "description" => "Agendamento Barbearia",
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => (float)$valor_final
                    ]
                ],
                "payer" => [
                    "email" => $email,
                    "name" => "Cliente", // Simplifiquei para evitar erros de caractere
                    "surname" => "Legacy"
                ],
                "back_urls" => [
                    "success" => URL_SUCESSO,
                    "failure" => URL_FALHA,
                    "pending" => URL_PENDENTE
                ],
                "auto_return" => "approved",
                "notification_url" => $webhook_url,
                "payment_methods" => [
                    "excluded_payment_types" => [["id" => "ticket"]]
                ]
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($dados_mp),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token
                ],
                CURLOPT_SSL_VERIFYPEER => false, 
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) die("Erro Curl: " . $err);

            $mp_resposta = json_decode($response, true);

            if (isset($mp_resposta['init_point'])) {
                // Se for token TESTE, tenta usar sandbox, senao usa init_point normal
                if (strpos($access_token, 'TEST') === 0 && isset($mp_resposta['sandbox_init_point'])) {
                    $link_pagamento = $mp_resposta['sandbox_init_point'];
                } else {
                    $link_pagamento = $mp_resposta['init_point'];
                }
                $mp_id = $mp_resposta['id'];
            } else {
                // Debug na tela se der erro de novo
                echo "<h3>Erro MP:</h3><pre>";
                print_r($mp_resposta);
                echo "</pre>";
                die();
            }
        }

        // 3. Inserir no Banco
        $sql = "INSERT INTO agendamentos 
                (id_barbeiro, id_cliente, servicos_ids, nome_cliente, telefone, email, data, hora, duracao, valor_final, metodo_pagamento, status, mp_id, mp_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $barbeiro_id, $id_cliente, $servicos_ids, $nome_cliente, $telefone, $email, $data, $hora, 
            $duracao_total, $valor_final, $metodo_pagamento, $status_inicial, $mp_id, $status_inicial
        ]);

        $id_agendamento = $pdo->lastInsertId();

        $dadosEmail = [
            'id' => $id_agendamento,
            'nome_cliente' => $nome_cliente,
            'email' => $email,
            'data' => $data,
            'hora' => $hora,
            'barbeiro_nome' => $barbeiro_nome,
            'servicos' => $lista_servicos_texto,
            'status_pagamento' => ($metodo_pagamento === 'pix') ? 'Aguardando Pagamento' : 'Pagar no Local'
        ];
        try { enviarEmailConfirmacao($dadosEmail); } catch (Exception $e) {}

        $pdo->commit();

        if ($metodo_pagamento === 'pix' && $link_pagamento) {
            header("Location: " . $link_pagamento);
        } else {
            header("Location: index.php?agendamento=sucesso&mensagem=Agendamento realizado!");
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro Interno: " . $e->getMessage());
    }
}
?>