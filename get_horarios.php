<?php
// Garante que o PHP não mostre erros na tela (quebra o JSON)
error_reporting(0); 
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
require 'db_connection.php';
date_default_timezone_set('America/Sao_Paulo');

try {
    $barbeiro_id = filter_input(INPUT_GET, 'barbeiro_id', FILTER_VALIDATE_INT);
    $data = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING);
    $duracao = filter_input(INPUT_GET, 'duracao', FILTER_VALIDATE_INT);

    // Validação básica
    if (!$barbeiro_id || !$data || !$duracao) {
        echo json_encode([]); 
        exit;
    }

    // Configuração de Horários (Pode ajustar conforme necessidade)
    $dia_semana = date('N', strtotime($data)); // 1=Seg, 7=Dom
    
    // Defina seus horários aqui:
    if ($dia_semana == 7) { 
        // Domingo Fechado
        echo json_encode([]); 
        exit; 
    } elseif ($dia_semana == 6) {
        // Sábado: 08:00 as 17:30
        $inicio = strtotime("$data 08:00");
        $fim = strtotime("$data 17:30");
    } else {
        // Seg-Mex: 09:00 as 20:00
        $inicio = strtotime("$data 09:00");
        $fim = strtotime("$data 20:00");
    }

    // Busca agendamentos já feitos, IGNORANDO PENDENTES EXPIRADOS (NOVO FILTRO)
    $sql_ocupados = "
        SELECT hora, duracao 
        FROM agendamentos 
        WHERE id_barbeiro = ? AND data = ?
        AND (
            metodo_pagamento != 'pix' OR mp_status = 'approved' 
            OR 
            (mp_status = 'pendente' AND data_criacao > DATE_SUB(NOW(), INTERVAL 10 MINUTE)) --
        )
    ";

    $stmt = $pdo->prepare($sql_ocupados);
    $stmt->execute([$barbeiro_id, $data]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $horarios_disponiveis = [];
    
    // Loop de 30 em 30 minutos (1800 segundos)
    for ($time = $inicio; $time <= ($fim - ($duracao * 60)); $time += 1800) {
        
        $slot_inicio = $time;
        $slot_fim = $time + ($duracao * 60);
        $disponivel = true;

        // 1. Verifica se já passou da hora atual (se for hoje)
        if (date('Y-m-d') == $data && $time < time()) {
            $disponivel = false;
        }

        // 2. Verifica colisão com agendamentos existentes (ocupados ou pendentes recentes)
        if ($disponivel) {
            foreach ($agendamentos as $ag) {
                // A duração do agendamento (ag['duracao']) é usada na checagem
                $ag_inicio = strtotime("$data " . $ag['hora']);
                $ag_fim = $ag_inicio + ($ag['duracao'] * 60);

                // Se o horário desejado encavala com um agendamento existente
                if ( ($slot_inicio < $ag_fim) && ($slot_fim > $ag_inicio) ) {
                    $disponivel = false;
                    break;
                }
            }
        }

        if ($disponivel) {
            $horarios_disponiveis[] = date('H:i', $time);
        }
    }

    echo json_encode($horarios_disponiveis);

} catch (Exception $e) {
    echo json_encode([]);
}
?>