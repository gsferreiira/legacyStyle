<?php
require 'db_connection.php';
require 'envia_email.php'; // Garanta que este arquivo existe na pasta

$barbeiro_id = filter_input(INPUT_GET, 'barbeiro_id', FILTER_VALIDATE_INT);
if (!$barbeiro_id) { http_response_code(400); exit; }

$stmt = $pdo->prepare("SELECT mp_access_token FROM barbeiros WHERE id = ?");
$stmt->execute([$barbeiro_id]);
$token = $stmt->fetchColumn();
if (!$token) { http_response_code(400); exit; }

$input = file_get_contents("php://input");
$event = json_decode($input, true);

if (!isset($event['type'])) { http_response_code(200); exit; }

if ($event['type'] === 'payment') {
    $payment_id = $event['data']['id'];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $payment_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode == 200) {
        $payment = json_decode($response, true);
        
        if (isset($payment['status'])) {
            $status = $payment['status']; 

            // Atualiza Banco
            $stmt = $pdo->prepare("UPDATE agendamentos SET mp_status = ?, status = ? WHERE mp_id = ?"); // Atualiza statusMP e statusGeral
            
            // Se aprovado, muda status geral também
            $status_geral = ($status === 'approved') ? 'confirmado' : 'pendente';
        }
    }
}
http_response_code(200);
?>