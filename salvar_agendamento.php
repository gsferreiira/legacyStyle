<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barbeiro_id = $_POST['barbeiro_id'];
    $servicos = $_POST['servicos']; // IDs separados por vírgula
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $nome_cliente = $_POST['nome'];
    $telefone = $_POST['telefone'];

    // Calcular duração total
    $servicos_selecionados = explode(',', $servicos);
    $duracao_total = 0;
    
    foreach ($servicos_selecionados as $servico_id) {
        $stmt = $pdo->prepare("SELECT duracao FROM servicos WHERE id = ?");
        $stmt->execute([$servico_id]);
        $duracao_total += $stmt->fetchColumn();
    }

    // Salvar no banco
    $stmt = $pdo->prepare("INSERT INTO agendamentos 
        (id_barbeiro, id_servico, nome_cliente, telefone, data, hora, duracao) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $barbeiro_id,
        $servicos,
        $nome_cliente,
        $telefone,
        $data,
        $hora,
        $duracao_total
    ]);

    // Redirecionar com mensagem de sucesso
    header("Location: index.php?agendamento=sucesso");
    exit;
}
?>