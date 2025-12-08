<?php
// notificacao.php - Versão Multi-Contas
require 'db_connection.php';

// Recebe o ID do barbeiro via GET (configurado no notification_url)
$barbeiro_id = filter_input(INPUT_GET, 'barbeiro_id', FILTER_VALIDATE_INT);

// Se não tiver ID do barbeiro na URL, não temos como saber qual token usar
if (!$barbeiro_id) {
    http_response_code(400); 
    exit;
}

// Busca o token deste barbeiro específico
$stmt = $pdo->prepare("SELECT mp_access_token FROM barbeiros WHERE id = ?");
$stmt->execute([$barbeiro_id]);
$token = $stmt->fetchColumn();

if (!$token) {
    http_response_code(400); // Barbeiro sem token configurado
    exit;
}

// Recebe a notificação
$input = file_get_contents("php://input");
$event = json_decode($input, true);

if (!isset($event['type'])) {
    http_response_code(200);
    exit;
}

if ($event['type'] === 'payment') {
    $payment_id = $event['data']['id'];

    // Consulta status usando o token DO BARBEIRO CORRETO
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $payment_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token // Token dinâmico
        ],
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode == 200) {
        $payment = json_decode($response, true);
        
        if (isset($payment['status'])) {
            $status = $payment['status']; 

            // Atualiza no banco
            $stmt = $pdo->prepare("UPDATE agendamentos SET mp_status = ? WHERE mp_id = ?");
            $stmt->execute([$status, $payment_id]);
        }
    }
}

http_response_code(200);
?>