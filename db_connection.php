<?php
$host = "localhost";  // Padrão do XAMPP
$dbname = "legacystyle";  // Nome do seu banco
$username = "root";  // Usuário padrão do XAMPP
$password = "";  // Senha vazia no XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
?>