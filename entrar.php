<?php
session_start();
require 'db_connection.php';

$erro = '';
$sucesso = '';

// 1. Processar LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'login') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();

    if ($cliente && password_verify($senha, $cliente['senha'])) {
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nome'] = $cliente['nome'];
        $_SESSION['cliente_email'] = $cliente['email'];
        $_SESSION['cliente_telefone'] = $cliente['telefone'];
        
        // Redireciona para onde ele estava ou para o perfil
        header("Location: meus_agendamentos.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}

// 2. Processar CADASTRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastro') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $senha = $_POST['senha'];
    
    // Verifica se já existe
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $erro = "Este e-mail já está cadastrado. Tente fazer login.";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        try {
            $pdo->beginTransaction();
            
            // Cria o cliente
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $senhaHash]);
            $novo_id = $pdo->lastInsertId();
            
            // VINCULA AGENDAMENTOS ANTIGOS (O Pulo do Gato!)
            // Pega tudo que foi feito com esse email e atribui ao novo usuário
            $stmtUpdate = $pdo->prepare("UPDATE agendamentos SET id_cliente = ? WHERE email = ?");
            $stmtUpdate->execute([$novo_id, $email]);
            
            $pdo->commit();
            
            // Loga automaticamente
            $_SESSION['cliente_id'] = $novo_id;
            $_SESSION['cliente_nome'] = $nome;
            $_SESSION['cliente_email'] = $email;
            $_SESSION['cliente_telefone'] = $telefone;
            
            header("Location: meus_agendamentos.php");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; width: 90%; max-width: 400px; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .tabs { display: flex; margin-bottom: 20px; border-bottom: 2px solid #eee; }
        .tab { flex: 1; padding: 10px; text-align: center; cursor: pointer; font-weight: 600; color: #aaa; }
        .tab.active { color: #d4af37; border-bottom: 2px solid #d4af37; margin-bottom: -2px; }
        h2 { text-align: center; color: #1a1a1a; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #d4af37; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; }
        .btn:hover { background: #b59530; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="assets/LOGO LEGACY SF/1.png" alt="Legacy Style" style="height: 50px;">
        </div>

        <?php if ($erro): ?>
            <div class="error"><?= $erro ?></div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="showTab('login')">Já tenho conta</div>
            <div class="tab" onclick="showTab('cadastro')">Criar conta</div>
        </div>

        <form id="form-login" method="POST">
            <input type="hidden" name="acao" value="login">
            <input type="email" name="email" placeholder="Seu E-mail" required>
            <input type="password" name="senha" placeholder="Sua Senha" required>
            <button type="submit" class="btn">Entrar</button>
        </form>

        <form id="form-cadastro" method="POST" style="display: none;">
            <input type="hidden" name="acao" value="cadastro">
            <input type="text" name="nome" placeholder="Nome Completo" required>
            <input type="tel" name="telefone" placeholder="WhatsApp (DDD + Número)" required>
            <input type="email" name="email" placeholder="Seu E-mail" required>
            <input type="password" name="senha" placeholder="Crie uma Senha" required>
            <button type="submit" class="btn">Cadastrar</button>
        </form>

        <a href="index.php" class="back-link">Voltar ao site</a>
    </div>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            if(tab === 'login') {
                document.querySelectorAll('.tab')[0].classList.add('active');
                document.getElementById('form-login').style.display = 'block';
                document.getElementById('form-cadastro').style.display = 'none';
            } else {
                document.querySelectorAll('.tab')[1].classList.add('active');
                document.getElementById('form-login').style.display = 'none';
                document.getElementById('form-cadastro').style.display = 'block';
            }
        }
    </script>
</body>
</html>