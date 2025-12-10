<?php
require 'db_connection.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
$mensagem = "";
$tipo = ""; // sucesso ou erro

if (!$id || !$token) {
    $mensagem = "Link inválido ou incompleto.";
    $tipo = "erro";
} else {
    // 1. Busca dados para validar
    $stmt = $pdo->prepare("SELECT email, data, hora, nome_cliente FROM agendamentos WHERE id = ?");
    $stmt->execute([$id]);
    $agendamento = $stmt->fetch();

    if (!$agendamento) {
        $mensagem = "Este agendamento não foi encontrado ou já foi cancelado.";
        $tipo = "erro";
    } else {
        // 2. Valida Token
        $segredo = "LEGACY_KEY_2024";
        $tokenEsperado = md5($id . $agendamento['email'] . $segredo);

        if ($token !== $tokenEsperado) {
            $mensagem = "Chave de segurança inválida.";
            $tipo = "erro";
        } elseif (strtotime($agendamento['data'] . ' ' . $agendamento['hora']) < time()) {
            $mensagem = "Não é possível cancelar agendamentos que já passaram.";
            $tipo = "erro";
        } else {
            // 3. Cancela
            $pdo->prepare("DELETE FROM agendamentos WHERE id = ?")->execute([$id]);
            $mensagem = "Seu horário foi cancelado com sucesso.";
            $tipo = "sucesso";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelamento - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }
        .card {
            background: #fff;
            width: 90%;
            max-width: 450px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 5px solid #d4af37;
        }
        .logo img { height: 60px; margin-bottom: 20px; }
        
        .icon-box {
            font-size: 50px;
            margin-bottom: 20px;
        }
        .sucesso { color: #28a745; }
        .erro { color: #dc3545; }
        
        h1 { font-size: 24px; margin-bottom: 10px; color: #111; }
        p { color: #666; line-height: 1.6; margin-bottom: 30px; }
        
        .btn {
            display: inline-block;
            background: #111;
            color: #d4af37;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 30px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn:hover {
            background: #d4af37;
            color: #000;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <img src="assets/LOGO LEGACY SF/2.png" alt="Legacy Style">
        </div>

        <?php if ($tipo === 'sucesso'): ?>
            <div class="icon-box sucesso"><i class="fas fa-check-circle"></i></div>
            <h1>Agendamento Cancelado</h1>
            <p><?= $mensagem ?></p>
            <p style="font-size: 14px;">Uma pena que você não virá. Esperamos te ver em breve!</p>
        <?php else: ?>
            <div class="icon-box erro"><i class="fas fa-exclamation-circle"></i></div>
            <h1>Ops! Algo deu errado</h1>
            <p><?= $mensagem ?></p>
        <?php endif; ?>

        <a href="index.php" class="btn">Voltar ao Início</a>
    </div>
</body>
</html>