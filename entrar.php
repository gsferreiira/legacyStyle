<?php
session_start();
require 'db_connection.php';

// --- PROTEÇÃO CONTRA ERRO 500 ---
// Só tenta carregar o Google se o arquivo existir
$googleConfigurado = false;
if (file_exists('config_google.php')) {
    include 'config_google.php';
    // Verifica se as constantes foram definidas corretamente dentro do arquivo
    if (defined('GOOGLE_CLIENT_ID') && defined('GOOGLE_REDIRECT_URL')) {
        $googleConfigurado = true;
    }
}

// Se já logado, redireciona
if (isset($_SESSION['cliente_id'])) {
    header("Location: meus_agendamentos.php");
    exit;
}

$erro = '';

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
    
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $erro = "Este e-mail já está cadastrado. Tente fazer login.";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $senhaHash]);
            $novo_id = $pdo->lastInsertId();
            
            // Vincula agendamentos antigos
            $pdo->prepare("UPDATE agendamentos SET id_cliente = ? WHERE email = ?")->execute([$novo_id, $email]);
            $pdo->commit();
            
            $_SESSION['cliente_id'] = $novo_id;
            $_SESSION['cliente_nome'] = $nome;
            $_SESSION['cliente_email'] = $email;
            
            header("Location: meus_agendamentos.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}

// Gera Link do Google apenas se estiver configurado
$googleUrl = '#';
if ($googleConfigurado) {
    $googleUrl = 'https://accounts.google.com/o/oauth2/v2/auth?response_type=code' .
            '&client_id=' . GOOGLE_CLIENT_ID . 
            '&redirect_uri=' . urlencode(GOOGLE_REDIRECT_URL) . 
            '&scope=email profile';
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
        body { font-family: 'Montserrat', sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; width: 90%; max-width: 420px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area img { height: 50px; margin-bottom: 10px; }
        .logo-area h2 { margin: 0; color: #111; font-size: 22px; text-transform: uppercase; letter-spacing: 1px; }
        .tabs { display: flex; margin-bottom: 25px; border-bottom: 2px solid #eee; }
        .tab { flex: 1; padding: 12px; text-align: center; cursor: pointer; font-weight: 600; color: #aaa; transition: 0.3s; }
        .tab.active { color: #d4af37; border-bottom: 2px solid #d4af37; margin-bottom: -2px; }
        .form-group { position: relative; margin-bottom: 15px; }
        .form-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        input { width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: 'Montserrat', sans-serif; transition: 0.3s; font-size: 14px; }
        input:focus { border-color: #d4af37; outline: none; }
        .btn { width: 100%; padding: 14px; background: #111; color: #d4af37; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .btn:hover { background: #000; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .btn-google { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px; margin-bottom: 25px; background: #fff; border: 1px solid #ddd; border-radius: 6px; color: #555; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .btn-google:hover { background: #f9f9f9; border-color: #ccc; }
        .separator { text-align: center; margin: 20px 0; color: #aaa; font-size: 12px; position: relative; }
        .separator::before, .separator::after { content: ''; position: absolute; top: 50%; width: 40%; height: 1px; background: #eee; }
        .separator::before { left: 0; } .separator::after { right: 0; }
        .error { background: #fff0f0; color: #d63031; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 13px; text-align: center; border: 1px solid #ffcccc; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #777; text-decoration: none; font-size: 13px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-area">
            <img src="assets/LOGO LEGACY SF/1.png" alt="Legacy Style">
            <h2>Área do Cliente</h2>
        </div>

        <?php if ($erro): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $erro ?></div>
        <?php endif; ?>

        <?php if ($googleConfigurado): ?>
            <a href="<?= $googleUrl ?>" class="btn-google">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" alt="G">
                Entrar com Google
            </a>
            <div class="separator">OU USE SEU E-MAIL</div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="showTab('login')">Entrar</div>
            <div class="tab" onclick="showTab('cadastro')">Cadastrar</div>
        </div>

        <form id="form-login" method="POST">
            <input type="hidden" name="acao" value="login">
            <div class="form-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Seu E-mail" required></div>
            <div class="form-group"><i class="fas fa-lock"></i><input type="password" name="senha" placeholder="Sua Senha" required></div>
            <button type="submit" class="btn">Acessar Conta</button>
        </form>

        <form id="form-cadastro" method="POST" style="display: none;">
            <input type="hidden" name="acao" value="cadastro">
            <div class="form-group"><i class="fas fa-user"></i><input type="text" name="nome" placeholder="Nome Completo" required></div>
            <div class="form-group"><i class="fab fa-whatsapp"></i><input type="tel" name="telefone" placeholder="WhatsApp (DDD + Número)" required></div>
            <div class="form-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Seu E-mail" required></div>
            <div class="form-group"><i class="fas fa-lock"></i><input type="password" name="senha" placeholder="Crie uma Senha" required></div>
            <button type="submit" class="btn">Criar Conta</button>
        </form>

        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Voltar ao site</a>
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