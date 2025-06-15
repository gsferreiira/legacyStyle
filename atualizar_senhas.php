<?php
require 'db_connection.php';

$barbeiros = $pdo->query("SELECT id, senha FROM barbeiros")->fetchAll();

foreach ($barbeiros as $barbeiro) {
    if (!password_needs_rehash($barbeiro['senha'], PASSWORD_DEFAULT)) {
        continue;
    }
    
    $novaSenhaHash = password_hash($barbeiro['senha'], PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE barbeiros SET senha = ? WHERE id = ?")
        ->execute([$novaSenhaHash, $barbeiro['id']]);
}

echo "Senhas atualizadas com segurança!";
?>