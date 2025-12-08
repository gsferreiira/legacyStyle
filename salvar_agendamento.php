<?php
session_start(); // INICIAR SESSÃO É OBRIGATÓRIO PARA PEGAR O ID DO CLIENTE
require 'db_connection.php';
require 'envia_email.php';

// Detecta automaticamente a URL para o Webhook (Funciona no Local e na Hostinger)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
define('NOTIFICATION_URL', $base_url . '/notificacao.php');

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

    // --- CORREÇÃO: Pegar o ID do cliente se ele estiver logado ---
    $id_cliente = isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null;

    if (!$barbeiro_id || !$servicos_ids || !$data || !$hora || !$email) {
        die("Erro: Dados incompletos. Por favor, volte e preencha tudo.");
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
        $mp_id = null;
        $qr_code_base64 = null;
        $qr_code_copia_cola = null;
        $status_inicial = 'pendente';

        if ($metodo_pagamento === 'pix') {
            
            if (empty($access_token)) {
                die("Erro Fatal: O barbeiro $barbeiro_nome não configurou o Mercado Pago.");
            }

            // Webhook dinâmico
            $webhook_target = NOTIFICATION_URL . "?barbeiro_id=" . $barbeiro_id;

            $dados_mp = [
                "transaction_amount" => (float)$valor_final,
                "description" => "Corte Legacy - " . $lista_servicos_texto,
                "payment_method_id" => "pix",
                "payer" => [
                    "email" => $email,
                    "first_name" => explode(' ', $nome_cliente)[0],
                    "last_name" => explode(' ', $nome_cliente)[1] ?? 'Cliente',
                    "identification" => ["type" => "CPF", "number" => "19119119100"]
                ],
                "notification_url" => $webhook_target
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($dados_mp),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                    'X-Idempotency-Key: ' . uniqid()
                ],
                // Desativa verificação SSL para funcionar no XAMPP (Localhost)
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) die("Erro cURL: " . $err);

            $mp_resposta = json_decode($response, true);

            if (isset($mp_resposta['id'])) {
                $mp_id = $mp_resposta['id'];
                $status_inicial = $mp_resposta['status'];
                
                if(isset($mp_resposta['point_of_interaction']['transaction_data']['qr_code_base64'])) {
                    $qr_code_base64 = $mp_resposta['point_of_interaction']['transaction_data']['qr_code_base64'];
                    $qr_code_copia_cola = $mp_resposta['point_of_interaction']['transaction_data']['qr_code'];
                }
            } else {
                // Se der erro no MP, mostra na tela para você saber o que foi
                echo "<h3>Erro do Mercado Pago:</h3>";
                echo "<pre>"; print_r($mp_resposta); echo "</pre>";
                die();
            }
        }

        // 4. Inserir no Banco (AGORA COM ID_CLIENTE!)
        $sql = "INSERT INTO agendamentos 
                (id_barbeiro, id_cliente, servicos_ids, nome_cliente, telefone, email, data, hora, duracao, valor_final, metodo_pagamento, status, mp_id, mp_status, qr_code_base64, qr_code_copia_cola) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $barbeiro_id,
            $id_cliente, // Aqui entra o ID da sessão!
            $servicos_ids,
            $nome_cliente,
            $telefone,
            $email,
            $data,
            $hora,
            $duracao_total,
            $valor_final,
            $metodo_pagamento,
            $status_inicial, 
            $mp_id,
            $status_inicial,
            $qr_code_base64,
            $qr_code_copia_cola
        ]);

        $id_agendamento = $pdo->lastInsertId();

        // 5. Email
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

        if ($metodo_pagamento === 'pix') {
            header("Location: pagamento_pix.php?id=" . $id_agendamento);
        } else {
            header("Location: index.php?agendamento=sucesso&mensagem=" . urlencode("Agendamento confirmado!"));
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro no Sistema: " . $e->getMessage());
    }
}
?>