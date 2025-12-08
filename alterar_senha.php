<?php
session_start();
require 'db_connection.php';

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['barbeiro_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = '';
$tipo_msg = '';

// 2. Processa a mudança de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $id_barbeiro = $_SESSION['barbeiro_id'];

    // Busca a senha atual (hash) no banco
    $stmt = $pdo->prepare("SELECT senha FROM barbeiros WHERE id = ?");
    $stmt->execute([$id_barbeiro]);
    $barbeiro = $stmt->fetch();

    if ($barbeiro) {
        // Verifica se a senha atual digitada bate com o hash do banco
        if (password_verify($senha_atual, $barbeiro['senha'])) {
            if ($nova_senha === $confirmar_senha) {
                if (strlen($nova_senha) >= 6) {
                    // Criptografa a nova senha e atualiza
                    $hash_nova = password_hash($nova_senha, PASSWORD_DEFAULT);
                    
                    $update = $pdo->prepare("UPDATE barbeiros SET senha = ? WHERE id = ?");
                    if ($update->execute([$hash_nova, $id_barbeiro])) {
                        $mensagem = "Senha alterada com sucesso!";
                        $tipo_msg = "success";
                    } else {
                        $mensagem = "Erro ao atualizar no banco de dados.";
                        $tipo_msg = "error";
                    }
                } else {
                    $mensagem = "A nova senha deve ter pelo menos 6 caracteres.";
                    $tipo_msg = "error";
                }
            } else {
                $mensagem = "A nova senha e a confirmação não coincidem.";
                $tipo_msg = "error";
            }
        } else {
            $mensagem = "A senha atual está incorreta.";
            $tipo_msg = "error";
        }
    } else {
        // Caso raro: usuário logado na sessão mas deletado do banco
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: #f5f5f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            width: 90%; 
            max-width: 400px; 
        }
        h2 { 
            text-align: center; 
            color: #1a1a1a; 
            margin-bottom: 25px; 
            font-size: 24px;
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 14px; }
        input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-size: 16px;
        }
        input:focus { border-color: #d4af37; outline: none; }
        
        .btn { 
            width: 100%; 
            padding: 12px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-primary { background-color: #d4af37; color: white; margin-top: 10px; }
        .btn-primary:hover { background-color: #b59530; }
        
        .btn-secondary { 
            background-color: transparent; 
            color: #666; 
            margin-top: 15px; 
            display: block; 
            text-align: center; 
            text-decoration: none; 
            font-size: 14px;
        }
        .btn-secondary:hover { color: #333; text-decoration: underline; }
        
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-lock"></i> Alterar Senha</h2>
        
        <?php if ($mensagem): ?>
            <div class="alert <?= $tipo_msg ?>">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Senha Atual</label>
                <input type="password" name="senha_atual" placeholder="Sua senha atual" required>
            </div>
            <div class="form-group">
                <label>Nova Senha</label>
                <input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="form-group">
                <label>Confirmar Nova Senha</label>
                <input type="password" name="confirmar_senha" placeholder="Repita a nova senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Nova Senha</button>
            <a href="admin.php" class="btn btn-secondary">Voltar ao Painel</a>
        </form>
    </div>
</body>
</html>