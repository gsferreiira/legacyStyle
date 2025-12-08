<?php
$host = "localhost"; // Na Hostinger, deixe 'localhost' mesmo

// --- PREENCHA COM OS DADOS DA HOSTINGER ---
$dbname = "u292075858_legacy"; // Nome do Banco (veja onde achar abaixo)
$username = "u292075858_admin";     // Usuário do Banco
$password = "Adminlegacy2000@";     // A senha que você criou para o banco

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
?>