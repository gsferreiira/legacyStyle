<?php
// google_callback.php
session_start();
require 'db_connection.php';
require 'config_google.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 1. Trocar o código pelo Token de Acesso
    $urlToken = 'https://oauth2.googleapis.com/token';
    $data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlToken);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Desativa SSL no localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenInfo = json_decode($response, true);

    if (isset($tokenInfo['access_token'])) {
        // 2. Usar o Token para pegar os dados do Usuário (Nome, Email, Foto)
        $urlUser = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $tokenInfo['access_token'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlUser);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $userInfoStr = curl_exec($ch);
        curl_close($ch);
        
        $userInfo = json_decode($userInfoStr, true);
        
        $email = $userInfo['email'];
        $nome = $userInfo['name'];
        $google_id = $userInfo['id'];
        
        // 3. Verificar se o cliente já existe no banco
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            // Cliente JÁ EXISTE: Só atualiza o google_id e Loga
            if (empty($cliente['google_id'])) {
                $pdo->prepare("UPDATE clientes SET google_id = ? WHERE id = ?")->execute([$google_id, $cliente['id']]);
            }
            
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome'];
            $_SESSION['cliente_email'] = $cliente['email'];
            $_SESSION['cliente_telefone'] = $cliente['telefone']; // Pode estar vazio
            
        } else {
            // Cliente NOVO: Cadastra
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, google_id) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $google_id]);
            $novo_id = $pdo->lastInsertId();
            
            // Busca agendamentos antigos feitos com esse email e vincula
            $pdo->prepare("UPDATE agendamentos SET id_cliente = ? WHERE email = ?")->execute([$novo_id, $email]);

            $_SESSION['cliente_id'] = $novo_id;
            $_SESSION['cliente_nome'] = $nome;
            $_SESSION['cliente_email'] = $email;
            $_SESSION['cliente_telefone'] = ''; // Google não manda telefone fácil
        }

        // Redireciona para o Painel
        header("Location: meus_agendamentos.php");
        exit;
    }
}

// Se der erro, volta pro login
header("Location: entrar.php?erro=google");
exit;
?>