<?php
require 'db_connection.php';
date_default_timezone_set('America/Sao_Paulo');

$barbeiro_id = filter_input(INPUT_GET, 'barbeiro_id', FILTER_VALIDATE_INT);
$data = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING);
$duracao = filter_input(INPUT_GET, 'duracao', FILTER_VALIDATE_INT);

if (!$barbeiro_id || !$data || !$duracao) {
    http_response_code(400); echo json_encode(['error' => 'Dados incompletos']); exit;
}

// Validar data limite
$dataSelecionada = new DateTime($data);
$dataLimite = (new DateTime())->modify('+15 days'); // Aumentei para 15 dias para teste
if ($dataSelecionada > $dataLimite) {
    http_response_code(400); echo json_encode(['error' => 'Data muito distante']); exit;
}

try {
    // 1. Verificar BLOQUEIOS (Novo!)
    // Primeiro vê se o dia inteiro está bloqueado
    $stmtBlock = $pdo->prepare("SELECT * FROM bloqueios WHERE id_barbeiro = ? AND data = ?");
    $stmtBlock->execute([$barbeiro_id, $data]);
    $bloqueios = $stmtBlock->fetchAll();

    foreach ($bloqueios as $bloqueio) {
        if ($bloqueio['dia_inteiro'] == 1) {
            // Se o dia todo ta bloqueado, retorna lista vazia e sai
            echo json_encode([]); 
            exit;
        }
    }

    // 2. Configuração de Horários da Barbearia
    $dia_semana = date('N', strtotime($data));
    switch($dia_semana) {
        case 1: $inicio = strtotime("10:00"); $fim = strtotime("18:00"); break; // Seg
        case 2: case 3: case 4: $inicio = strtotime("09:00"); $fim = strtotime("20:00"); break; // Ter-Qui
        case 5: $inicio = strtotime("09:00"); $fim = strtotime("21:30"); break; // Sex
        case 6: $inicio = strtotime("08:00"); $fim = strtotime("17:30"); break; // Sab
        default: echo json_encode([]); exit; // Domingo fechado
    }

    // 3. Busca Agendamentos Existentes
    $stmt = $pdo->prepare("SELECT hora, duracao FROM agendamentos WHERE id_barbeiro = ? AND data = ?");
    $stmt->execute([$barbeiro_id, $data]);
    $agendamentos = $stmt->fetchAll();

    $horarios_disponiveis = [];

    // Loop de 30 em 30 minutos
    for ($time = $inicio; $time <= ($fim - $duracao * 60); $time += 1800) {
        $slot_start = $time;
        $slot_end = $time + $duracao * 60;
        $disponivel = true;

        // A) Checa colisão com Agendamentos (Clientes)
        foreach ($agendamentos as $ag) {
            $ag_start = strtotime($ag['hora']);
            $ag_end = $ag_start + $ag['duracao'] * 60;
            if (($slot_start < $ag_end) && ($slot_end > $ag_start)) {
                $disponivel = false; break;
            }
        }

        // B) Checa colisão com Bloqueios Parciais (Almoço, Médico)
        if ($disponivel) {
            foreach ($bloqueios as $bl) {
                if ($bl['dia_inteiro'] == 0) {
                    $bl_start = strtotime($bl['hora_inicio']);
                    $bl_end = strtotime($bl['hora_fim']);
                    // Se o slot do cliente encosta ou entra no bloqueio
                    if (($slot_start < $bl_end) && ($slot_end > $bl_start)) {
                        $disponivel = false; break;
                    }
                }
            }
        }
        
        if ($disponivel) {
            $horarios_disponiveis[] = date("H:i", $time);
        }
    }

    echo json_encode($horarios_disponiveis);

} catch (PDOException $e) {
    http_response_code(500); echo json_encode(['error' => 'Erro interno']);
}
?>