<?php
require 'db_connection.php';

// Definir o fuso horário para o do Brasil (São Paulo)
date_default_timezone_set('America/Sao_Paulo');

// Validar e sanitizar os parâmetros
$barbeiro_id = filter_input(INPUT_GET, 'barbeiro_id', FILTER_VALIDATE_INT);
$data = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING);
$duracao = filter_input(INPUT_GET, 'duracao', FILTER_VALIDATE_INT);

// Verificar se os parâmetros são válidos
if (!$barbeiro_id || !$data || !$duracao) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit;
}

// Verificar se a data é válida
if (!strtotime($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data inválida']);
    exit;
}

try {
    // Horário de funcionamento
    $dia_semana = date('N', strtotime($data)); // 1=segunda, 2=terça, etc.
    $horarios_disponiveis = [];

    // Definir horários por dia da semana
    switch($dia_semana) {
        case 1: // Segunda
            $inicio = strtotime("10:00");
            $fim = strtotime("18:00");
            break;
        case 2: // Terça
        case 3: // Quarta
        case 4: // Quinta
            $inicio = strtotime("09:00");
            $fim = strtotime("20:00");
            break;
        case 5: // Sexta
            $inicio = strtotime("09:00");
            $fim = strtotime("21:30");
            break;
        case 6: // Sábado
            $inicio = strtotime("08:00");
            $fim = strtotime("17:30");
            break;
        case 7: // Domingo
            // Fechado - não deve ter agendamentos
            http_response_code(400);
            echo json_encode(['error' => 'A barbearia está fechada aos domingos']);
            exit;
    }

    // Consulta agendamentos existentes para o barbeiro na data selecionada
    $stmt = $pdo->prepare("SELECT hora, duracao FROM agendamentos 
                          WHERE id_barbeiro = ? AND data = ?");
    $stmt->execute([$barbeiro_id, $data]);
    $agendamentos = $stmt->fetchAll();

    // Verifica cada intervalo de 30 minutos
    for ($time = $inicio; $time <= ($fim - $duracao * 60); $time += 1800) {
        $disponivel = true;
        $slot_start = $time;
        $slot_end = $time + $duracao * 60;
        
        foreach ($agendamentos as $agendamento) {
            $agendamento_start = strtotime($agendamento['hora']);
            $agendamento_end = $agendamento_start + $agendamento['duracao'] * 60;
            
            // Verifica se há sobreposição de horários
            if (($slot_start < $agendamento_end) && ($slot_end > $agendamento_start)) {
                $disponivel = false;
                break;
            }
        }
        
        if ($disponivel) {
            $horarios_disponiveis[] = date("H:i", $time);
        }
    }

    // Ordena os horários disponíveis
    sort($horarios_disponiveis);

    header('Content-Type: application/json');
    echo json_encode($horarios_disponiveis);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao consultar o banco de dados']);
}
?>