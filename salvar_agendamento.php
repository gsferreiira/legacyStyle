<?php
session_start();
require 'db_connection.php';
require 'envia_email.php';

// CONFIGURAÇÃO DE URL (Ajuste se necessário)
define('SITE_URL', 'https://www.legacystyle.com.br'); 

$url_sucesso  = SITE_URL . "/index.php?agendamento=sucesso&msg=Pagamento_Aprovado";
$url_falha    = SITE_URL . "/index.php?agendamento=erro&msg=Pagamento_Falhou";
$url_pendente = SITE_URL . "/index.php?agendamento=sucesso&msg=Pagamento_Pendente";
$url_webhook  = SITE_URL . '/notificacao.php';

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Receber Dados (SEM CPF)
    $barbeiro_id    = filter_input(INPUT_POST, 'barbeiro_id', FILTER_VALIDATE_INT);
    $servicos_ids   = filter_input(INPUT_POST, 'servicos', FILTER_SANITIZE_STRING);
    $data           = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $hora           = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $nome_cliente   = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $telefone       = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $pagamento      = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING) ?? 'presencial';
    $valor_final    = (float) ($_POST['valor_total'] ?? 0);
    $id_cliente     = $_SESSION['cliente_id'] ?? null;

    if (!$barbeiro_id || !$servicos_ids || !$data || !$hora || !$email) {
        die("<h3>Erro:</h3> Dados incompletos. <a href='index.php'>Voltar</a>");
    }

    try {
        $pdo->beginTransaction();

        // 2. Token do Barbeiro
        $stmt = $pdo->prepare("SELECT nome, mp_access_token FROM barbeiros WHERE id = ?");
        $stmt->execute([$barbeiro_id]);
        $dados_barbeiro = $stmt->fetch();
        
        $token_mp = trim($dados_barbeiro['mp_access_token']);
        $nome_barbeiro = $dados_barbeiro['nome'];

        // 3. Serviços
        $ids = explode(',', $servicos_ids);
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmtS = $pdo->prepare("SELECT nome FROM servicos WHERE id IN ($marks)");
        $stmtS->execute($ids);
        $servicos_nomes = $stmtS->fetchAll(PDO::FETCH_COLUMN);
        $descricao = "Corte Legacy - " . implode(", ", $servicos_nomes);

        // ======================================================
        // INTEGRAÇÃO MP SEM CPF (Deixa o MP pedir se precisar)
        // ======================================================
        $link_mp = null;
        $id_pref = null;
        $status = 'pendente';

        if ($pagamento === 'pix') {
            if (empty($token_mp)) die("Erro: Pagamento indisponível.");

            // Nome e Sobrenome para ajudar o MP
            $partes = explode(' ', $nome_cliente, 2);
            $primeiro = $partes[0];
            $sobrenome = $partes[1] ?? 'Cliente';

            $dados_mp = [
                "items" => [
                    [
                        "title" => $descricao,
                        "description" => "Serviços Barbearia",
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => $valor_final
                    ]
                ],
                "payer" => [
                    "email" => $email,
                    "name" => $primeiro,
                    "surname" => $sobrenome
                    // REMOVEMOS O BLOCO IDENTIFICATION (CPF) DAQUI
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
                // Debug se der erro
                echo "Erro MP: <pre>" . print_r($mp_res, true) . "</pre>"; die();
            }
        }

        // 4. Salvar
        $sql = "INSERT INTO agendamentos (id_barbeiro, id_cliente, servicos_ids, nome_cliente, telefone, email, data, hora, valor_final, metodo_pagamento, status, mp_id, mp_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$barbeiro_id, $id_cliente, $servicos_ids, $nome_cliente, $telefone, $email, $data, $hora, $valor_final, $pagamento, $status, $id_pref, $status]);
        $novo_id = $pdo->lastInsertId();

        // 5. Email (Se não for Pix)
        if ($pagamento !== 'pix') {
            try {
                if(function_exists('enviarEmailConfirmacao')) {
                    $dadosEmail = ['id'=>$novo_id, 'nome_cliente'=>$nome_cliente, 'email'=>$email, 'data'=>$data, 'hora'=>$hora, 'barbeiro_nome'=>$nome_barbeiro, 'servicos'=>$descricao, 'status_pagamento'=>'No Local'];
                    enviarEmailConfirmacao($dadosEmail);
                }
            } catch (Exception $e) {}
        }

        $pdo->commit();

        if ($pagamento === 'pix' && $link_mp) {
            header("Location: " . $link_mp);
        } else {
            header("Location: index.php?agendamento=sucesso&msg=Agendamento_Realizado");
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro: " . $e->getMessage());
    }
}
?>