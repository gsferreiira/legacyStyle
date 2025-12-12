<?php
session_start();
// Destrói as variáveis de sessão específicas do cliente
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nome']);
unset($_SESSION['cliente_email']);
unset($_SESSION['cliente_telefone']);

// Redirecionamento CORRETO para o login do cliente
header("Location: entrar.php"); 
exit;
?>