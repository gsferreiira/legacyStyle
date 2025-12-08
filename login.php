<?php
session_start();
require 'db_connection.php';

// Se já estiver logado, joga direto pro admin
if (isset($_SESSION['barbeiro_id'])) {
    header("Location: admin.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    // Busca o barbeiro pelo e-mail
    $stmt = $pdo->prepare("SELECT * FROM barbeiros WHERE email = ?");
    $stmt->execute([$email]);
    $barbeiro = $stmt->fetch();

    if ($barbeiro && password_verify($senha, $barbeiro['senha'])) {
        $_SESSION['barbeiro_id'] = $barbeiro['id'];
        $_SESSION['barbeiro_nome'] = $barbeiro['nome'];
        header("Location: admin.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: #f5f5f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        .container { 
            background: white; 
            width: 90%; 
            max-width: 400px; 
            padding: 40px 30px; 
            border-radius: 10px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            text-align: center;
        }
        .logo { margin-bottom: 25px; }
        .logo img { height: 60px; }
        
        h2 { 
            color: #1a1a1a; 
            margin-bottom: 5px; 
            font-size: 22px;
        }
        p.subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        input { 
            width: 100%; 
            padding: 12px 12px 12px 40px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-sizing: border-box; 
            font-size: 14px;
            font-family: 'Montserrat', sans-serif;
            transition: border 0.3s;
        }
        
        input:focus {
            border-color: #d4af37;
            outline: none;
        }

        .btn { 
            width: 100%; 
            padding: 12px; 
            background: #1a1a1a; /* Preto para diferenciar do cliente */
            color: #d4af37;      /* Texto dourado */
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 16px; 
            transition: 0.3s;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover { 
            background: #000; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
            font-size: 13px; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            color: #666; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #d4af37;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="assets/LOGO LEGACY SF/2.png" alt="Legacy Style">
        </div>

        <h2>Área do Barbeiro</h2>
        <p class="subtitle">Acesso exclusivo para administração</p>

        <?php if ($erro): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail de acesso" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="senha" placeholder="Senha" required>
            </div>
            
            <button type="submit" class="btn">Entrar no Painel</button>
        </form>

        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar ao site
        </a>
    </div>
</body>
</html>