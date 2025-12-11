<?php
// Configuração Universal de Banco de Dados
// Versão 2.0 - Corrige o erro da porta 8080

$host = 'localhost'; 

// Pega o host atual (ex: localhost:8080 ou meusite.com)
$servidor_atual = $_SERVER['HTTP_HOST'];

// Verifica se "localhost" ou "127.0.0.1" faz parte do endereço
// Isso funciona mesmo se tiver :8080 no final
if (strpos($servidor_atual, 'localhost') === false && strpos($servidor_atual, '127.0.0.1') === false) {
    // --- AMBIENTE DE PRODUÇÃO (HOSTINGER) ---
    // Se NÃO tem localhost no nome, é produção
    $dbname = 'u292075858_legacy';
    $username = 'u292075858_admin';
    $password = 'Adminlegacy2000@'; 

} else {
    // --- AMBIENTE LOCAL ---
    // Agora verifica se é Windows ou Linux
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // --- WINDOWS (SEU XAMPP) ---
        $dbname = 'legacy'; 
        $username = 'root';
        $password = '123'; 
    } else {
        // --- LINUX (SEU UBUNTU ATUAL) ---
        $dbname = 'legacy_style'; 
        $username = 'ferreira';
        $password = '159741';
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Mostra qual usuário ele tentou usar para facilitar o debug
    die("Erro na conexão (Tentou usar: $username): " . $e->getMessage());
}
?>