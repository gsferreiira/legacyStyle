<?php
session_start();
// Destroi apenas as sessões do cliente, mantendo as do admin se houver (opcional)
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nome']);
unset($_SESSION['cliente_email']);
unset($_SESSION['cliente_telefone']);

header("Location: entrar.php");
exit;
?>