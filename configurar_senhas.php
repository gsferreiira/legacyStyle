<?php
require 'db_connection.php';

try {
    echo "<h2>Configurando Sistema de Login...</h2>";

    // 1. Tenta adicionar a coluna EMAIL (se não existir)
    try {
        $pdo->exec("ALTER TABLE barbeiros ADD COLUMN email VARCHAR(100) AFTER nome");
        echo "✅ Coluna 'email' criada.<br>";
    } catch (PDOException $e) {
        // Ignora erro se a coluna já existir
        echo "ℹ️ Coluna 'email' já existia.<br>";
    }

    // 2. Tenta adicionar a coluna SENHA (se não existir)
    try {
        $pdo->exec("ALTER TABLE barbeiros ADD COLUMN senha VARCHAR(255) AFTER email");
        echo "✅ Coluna 'senha' criada.<br>";
    } catch (PDOException $e) {
        echo "ℹ️ Coluna 'senha' já existia.<br>";
    }

    // 3. Define a senha padrão '123456' (Criptografada)
    $senhaHash = password_hash('123456', PASSWORD_DEFAULT);

    // 4. Atualiza o usuário CAUÃ
    $stmt = $pdo->prepare("UPDATE barbeiros SET email = 'caua@legacystyle.com', senha = ? WHERE nome LIKE '%Cauã%'");
    $stmt->execute([$senhaHash]);
    if ($stmt->rowCount() > 0) {
        echo "✅ Login do Cauã configurado.<br>";
    } else {
        echo "⚠️ Barbeiro Cauã não encontrado no banco.<br>";
    }

    // 5. Atualiza o usuário VITINHO
    $stmt = $pdo->prepare("UPDATE barbeiros SET email = 'vitinho@legacystyle.com', senha = ? WHERE nome LIKE '%Vitinho%'");
    $stmt->execute([$senhaHash]);
    if ($stmt->rowCount() > 0) {
        echo "✅ Login do Vitinho configurado.<br>";
    } else {
        echo "⚠️ Barbeiro Vitinho não encontrado no banco.<br>";
    }

    echo "<hr>";
    echo "<h3>Tudo pronto!</h3>";
    echo "<p>Agora você pode fazer login com:</p>";
    echo "<ul>";
    echo "<li><strong>E-mail:</strong> caua@legacystyle.com (ou vitinho@...)</li>";
    echo "<li><strong>Senha:</strong> 123456</li>";
    echo "</ul>";
    echo "<a href='login.php'><button>Ir para o Login</button></a>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Erro Fatal:</h3>";
    echo $e->getMessage();
}
?>