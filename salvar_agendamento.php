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
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // Verificar se todos os campos estão preenchidos e válidos
    $erros = [];
    
    if (!$barbeiro_id || $barbeiro_id < 1) {
        $erros[] = "Barbeiro inválido";
    }
    
    if (!$servicos || !preg_match('/^\d+(,\d+)*$/', $servicos)) {
        $erros[] = "Serviços inválidos";
    }
    
    if (!$data) {
        $erros[] = "Data inválida";
    }
    
    if (!$hora || !preg_match('/^\d{2}:\d{2}$/', $hora)) {
        $erros[] = "Horário inválido";
    }
    
    if (!$nome_cliente || strlen($nome_cliente) < 3) {
        $erros[] = "Nome deve ter pelo menos 3 caracteres";
    }
    
    if (!$telefone || !preg_match('/^\d{10,15}$/', $telefone)) {
        $erros[] = "Telefone inválido";
    }
    
    if (!$email) {
        $erros[] = "E-mail inválido";
    }

    if (!empty($erros)) {
        header("Location: index.php?agendamento=erro&mensagem=" . urlencode(implode(" | ", $erros)));
        exit;
    }

    // Verificar se a data é válida e está dentro do limite de 7 dias
    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
    $dataAtual = new DateTime();
    $dataLimite = (new DateTime())->modify('+7 days');

    if (!$dataObj || $dataObj < $dataAtual || $dataObj > $dataLimite) {
        header("Location: index.php?agendamento=erro&mensagem=Data inválida ou fora do limite de 7 dias para agendamento");
        exit;
    }

    try {
        // Iniciar transação para garantir consistência
        $pdo->beginTransaction();

        // Calcular duração total e verificar serviços
        $servicos_selecionados = explode(',', $servicos);
        $duracao_total = 0;
        $servicos_validos = [];
        
        foreach ($servicos_selecionados as $servico_id) {
            if (!is_numeric($servico_id)) continue;
            
            $stmt = $pdo->prepare("SELECT id, duracao, nome FROM servicos WHERE id = ?");
            $stmt->execute([$servico_id]);
            $servico = $stmt->fetch();
            
            if ($servico) {
                $duracao_total += $servico['duracao'];
                $servicos_validos[] = $servico['nome'];
            }
        }

        // Verificar se há serviços válidos
        if ($duracao_total <= 0 || empty($servicos_validos)) {
            $pdo->rollBack();
            header("Location: index.php?agendamento=erro&mensagem=Nenhum serviço válido selecionado");
            exit;
        }

        // Verificar se o horário ainda está disponível
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM agendamentos 
            WHERE id_barbeiro = ? AND data = ? AND hora = ?
        ");
        $stmt->execute([$barbeiro_id, $data, $hora]);
        
        if ($stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            header("Location: index.php?agendamento=erro&mensagem=Horário já ocupado");
            exit;
        }

        // Salvar no banco
        $stmt = $pdo->prepare("INSERT INTO agendamentos 
            (id_barbeiro, id_servico, nome_cliente, telefone, email, data, hora, duracao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $barbeiro_id,
            $servicos,
            $nome_cliente,
            $telefone,
            $email,
            $data,
            $hora,
            $duracao_total
        ]);

        if (!$success) {
            throw new PDOException('Falha ao inserir no banco de dados');
        }

        $agendamento_id = $pdo->lastInsertId();

        // Busca dados completos para o email
        $stmt = $pdo->prepare("
            SELECT a.*, b.nome AS barbeiro_nome, b.foto AS barbeiro_foto
            FROM agendamentos a
            JOIN barbeiros b ON a.id_barbeiro = b.id
            WHERE a.id = ?
        ");
        $stmt->execute([$agendamento_id]);
        $agendamento = $stmt->fetch();

        // Prepara dados para o email
        $dadosEmail = [
            'id' => $agendamento_id,
            'nome_cliente' => $nome_cliente,
            'email' => $email,
            'data' => $data,
            'hora' => $hora,
            'barbeiro_nome' => $agendamento['barbeiro_nome'],
            'barbeiro_foto' => $agendamento['barbeiro_foto'],
            'servicos' => implode(", ", $servicos_validos),
            'duracao' => $duracao_total,
            'total' => calcularTotalServicos($servicos_selecionados, $pdo)
        ];

        // Envia o email
        require 'envia_email.php';
        enviarEmailConfirmacao($dadosEmail);

        // Commit da transação
        $pdo->commit();

        // Redirecionar com mensagem de sucesso
        header("Location: index.php?agendamento=sucesso");
        exit;

    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erro ao salvar agendamento: ' . $e->getMessage());
        header("Location: index.php?agendamento=erro&mensagem=Erro ao processar agendamento. Tente novamente.");
        exit;
    }
}

// Função auxiliar para calcular o total dos serviços
function calcularTotalServicos($servicos_ids, $pdo) {
    $total = 0;
    $placeholders = implode(',', array_fill(0, count($servicos_ids), '?'));
    
    $stmt = $pdo->prepare("SELECT SUM(preco) FROM servicos WHERE id IN ($placeholders)");
    $stmt->execute($servicos_ids);
    
    return $stmt->fetchColumn();
}

header("Location: index.php");
exit;
?>