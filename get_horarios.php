<?php
require 'db_connection.php';

$barbeiro_id = $_GET['barbeiro_id'];
$data = $_GET['data'];
$duracao = $_GET['duracao'];

// Horário de funcionamento (9h às 19h)
$horarios_disponiveis = [];
$inicio = strtotime("09:00");
$fim = strtotime("19:00");

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

echo json_encode($horarios_disponiveis);
?>