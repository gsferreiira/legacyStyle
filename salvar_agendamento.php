<?php
require 'db_connection.php';

// Definir o fuso horário para o do Brasil (São Paulo)
date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $barbeiro_id = filter_input(INPUT_POST, 'barbeiro_id', FILTER_VALIDATE_INT);
    $servicos = filter_input(INPUT_POST, 'servicos', FILTER_SANITIZE_STRING);
    $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $nome_cliente = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);

    // Verificar se todos os campos estão preenchidos
    if (!$barbeiro_id || !$servicos || !$data || !$hora || !$nome_cliente || !$telefone) {
        header("Location: index.php?agendamento=erro&mensagem=Dados incompletos");
        exit;
    }

    // Verificar se a data é válida
    if (!DateTime::createFromFormat('Y-m-d', $data)) {
        header("Location: index.php?agendamento=erro&mensagem=Data inválida");
        exit;
    }

    try {
        // Calcular duração total
        $servicos_selecionados = explode(',', $servicos);
        $duracao_total = 0;
        
        foreach ($servicos_selecionados as $servico_id) {
            if (!is_numeric($servico_id)) continue;
            
            $stmt = $pdo->prepare("SELECT duracao FROM servicos WHERE id = ?");
            $stmt->execute([$servico_id]);
            $duracao = $stmt->fetchColumn();
            
            if ($duracao) {
                $duracao_total += $duracao;
            }
        }

        // Verificar se há serviços válidos
        if ($duracao_total <= 0) {
            header("Location: index.php?agendamento=erro&mensagem=Nenhum serviço válido selecionado");
            exit;
        }

        // Salvar no banco
        $stmt = $pdo->prepare("INSERT INTO agendamentos 
            (id_barbeiro, id_servico, nome_cliente, telefone, data, hora, duracao) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $barbeiro_id,
            $servicos,
            $nome_cliente,
            $telefone,
            $data,
            $hora,
            $duracao_total
        ]);

        if (!$success) {
            throw new PDOException('Falha ao inserir no banco de dados');
        }

        // Busca dados completos para o email
        $stmt = $pdo->prepare("
            SELECT a.*, b.nome AS barbeiro_nome, 
            GROUP_CONCAT(s.nome SEPARATOR ', ') AS servicos_nomes
            FROM agendamentos a
            JOIN barbeiros b ON a.id_barbeiro = b.id
            JOIN servicos s ON FIND_IN_SET(s.id, a.id_servico)
            WHERE a.id = ?
        ");
        $stmt->execute([$pdo->lastInsertId()]);
        $agendamento = $stmt->fetch();

        // Prepara dados para o email
        $dadosEmail = [
            'id' => $agendamento['id'],
            'nome_cliente' => $agendamento['nome_cliente'],
            'email' => $_POST['email'], // Pega o email do formulário
            'data' => $agendamento['data'],
            'hora' => $agendamento['hora'],
            'barbeiro_nome' => $agendamento['barbeiro_nome'],
            'servicos' => $agendamento['servicos_nomes']
        ];

        // Envia o email
        require 'envia_email.php';
        enviarEmailConfirmacao($dadosEmail);

        // Redireciona com sucesso
        header("Location: index.php?agendamento=sucesso");
        exit;

        // Redirecionar com mensagem de sucesso
        header("Location: index.php?agendamento=sucesso");
        exit;

    } catch (PDOException $e) {
        error_log('Erro ao salvar agendamento: ' . $e->getMessage());
        header("Location: index.php?agendamento=erro&mensagem=Erro ao salvar agendamento");
        exit;
    }
}

header("Location: index.php");
exit;
?>