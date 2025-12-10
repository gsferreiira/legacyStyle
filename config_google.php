<?php
// config_google.php

// DETECTA SE ESTÁ LOCAL OU ONLINE
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // Se estiver no XAMPP, usa localhost
    define('GOOGLE_REDIRECT_URL', 'http://localhost/legacystyle/google_callback.php');
} else {
    // Se estiver na Hostinger, usa o domínio real
    define('GOOGLE_REDIRECT_URL', 'https://www.legacystyle.com.br/google_callback.php');
}

// --- SUAS CHAVES DO GOOGLE ---

// Seu ID do Cliente (Já coloquei aqui pra você)
define('GOOGLE_CLIENT_ID', 'COLOCAR_ID_NA_HOSTINGER'); 
define('GOOGLE_CLIENT_SECRET', 'COLOCAR_SENHA_NA_HOSTINGER');
?>