<?php
// Garante que o PHP não mostre erros na tela
error_reporting(0); 
ini_set('display_errors', 0);

// Inclua a conexão com o banco de dados
require 'db_connection.php'; 

// Defina o fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Define o tempo limite de pendência (10 minutos)
$limite_minutos = 10;
// Calcula o ponto de corte: Agora menos o limite
$tempo_corte = new DateTime();
$tempo_corte->modify("-{$limite_minutos} minutes");

// Converte para o formato MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
$timestamp_corte = $tempo_corte->format('Y-m-d H:i:s');

// SQL: Deleta agendamentos que:
// 1. Têm método de pagamento 'pix'
// 2. Estão com status 'pendente'
// 3. Foram criados antes do nosso tempo de corte (mais de 10 minutos atrás)
$sql_delete = "
    DELETE FROM agendamentos
    WHERE metodo_pagamento = 'pix'
    AND mp_status = 'pendente'
    AND data_criacao <= ?
";

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare($sql_delete);
    $stmt->execute([$timestamp_corte]);
    $pdo->commit();
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Em um ambiente de produção, é altamente recomendável logar o erro aqui.
}
?>