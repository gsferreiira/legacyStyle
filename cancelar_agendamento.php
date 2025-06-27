<?php
require 'db_connection.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (!$id || !$token) {
    die("Parâmetros inválidos para cancelamento.");
}

// Verificar se o token é válido
$tokenEsperado = md5($id . 'SECRET_SALT_' . date('Ymd'));
if ($token !== $tokenEsperado) {
    die("Token de cancelamento inválido ou expirado.");
}

// Buscar o agendamento
$stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id = ?");
$stmt->execute([$id]);
$agendamento = $stmt->fetch();

if (!$agendamento) {
    die("Agendamento não encontrado.");
}

// Cancelar o agendamento
$stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
$stmt->execute([$id]);

// Mostrar mensagem de sucesso
echo "<h2>Agendamento Cancelado</h2>";
echo "<p>Seu agendamento para " . date('d/m/Y', strtotime($agendamento['data'])) . " às " . $agendamento['hora'] . " foi cancelado com sucesso.</p>";
echo "<p><a href='index.php'>Voltar ao site</a></p>";
?>