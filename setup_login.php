<?php
require 'db_connection.php';

try {
    // 1. Adiciona as colunas email e senha se não existirem
    $pdo->exec("ALTER TABLE barbeiros ADD COLUMN email VARCHAR(100) AFTER nome");
    $pdo->exec("ALTER TABLE barbeiros ADD COLUMN senha VARCHAR(255) AFTER email");
    echo "Colunas criadas...<br>";
} catch (Exception $e) {
    echo "Colunas já existem ou erro: " . $e->getMessage() . "<br>"; // Ignora se já existir
}

// 2. Define a senha padrão '123456'
$senhaPadrao = password_hash('123456', PASSWORD_DEFAULT);

// 3. Atualiza os dados do Cauã
$stmt = $pdo->prepare("UPDATE barbeiros SET email = 'caua@legacystyle.com', senha = ? WHERE nome LIKE '%Cauã%'");
$stmt->execute([$senhaPadrao]);

// 4. Atualiza os dados do Vitinho
$stmt = $pdo->prepare("UPDATE barbeiros SET email = 'vitinho@legacystyle.com', senha = ? WHERE nome LIKE '%Vitinho%'");
$stmt->execute([$senhaPadrao]);

echo "<h3>Sucesso!</h3>";
echo "Login Cauã: caua@legacystyle.com <br>";
echo "Login Vitinho: vitinho@legacystyle.com <br>";
echo "Senha padrão para ambos: <strong>123456</strong>";
?>