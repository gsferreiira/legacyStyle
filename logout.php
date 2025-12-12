<?php
session_start();

// Destrói a sessão e variáveis de barbeiro.
unset($_SESSION['barbeiro_id']); 
// Você pode adicionar outras variáveis de barbeiro aqui.

// Redireciona o usuário para a página de login do barbeiro (login.php).
header("Location: login.php");
exit;
?>