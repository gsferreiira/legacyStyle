<?php
// Configuração Universal de Banco de Dados
// Detecta automaticamente se está no XAMPP ou na Hostinger

$host = 'localhost'; // Ambos usam localhost

// Verifica o endereço do servidor
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // --- AMBIENTE LOCAL (SEU COMPUTADOR/XAMPP) ---
    $dbname = 'legacystyle'; // Confirme se esse é o nome do seu banco no XAMPP
    $username = 'root';
    $password = '';
} else {
    // --- AMBIENTE DE PRODUÇÃO (HOSTINGER) ---
    // Peguei esses dados do arquivo que você mandou antes
    $dbname = 'u292075858_legacy';
    $username = 'u292075858_admin';
    $password = 'Adminlegacy2000@'; 
}

try {
    // Cria a conexão PDO com charset utf8mb4 (evita problemas de acentuação)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configura para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Em caso de erro, mata o script e mostra a mensagem
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>