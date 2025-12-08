<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['barbeiro_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = '';

// Processar o Bloqueio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'bloquear') {
    $data = $_POST['data'];
    $tipo = $_POST['tipo']; // 'dia_inteiro' ou 'horario'
    $motivo = $_POST['motivo'];
    $id_barbeiro = $_SESSION['barbeiro_id'];
    
    if ($tipo === 'dia_inteiro') {
        $sql = "INSERT INTO bloqueios (id_barbeiro, data, dia_inteiro, motivo) VALUES (?, ?, 1, ?)";
        $params = [$id_barbeiro, $data, $motivo];
    } else {
        $inicio = $_POST['hora_inicio'];
        $fim = $_POST['hora_fim'];
        $sql = "INSERT INTO bloqueios (id_barbeiro, data, hora_inicio, hora_fim, dia_inteiro, motivo) VALUES (?, ?, ?, ?, 0, ?)";
        $params = [$id_barbeiro, $data, $inicio, $fim, $motivo];
    }
    
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        $mensagem = "Bloqueio realizado com sucesso!";
    } else {
        $mensagem = "Erro ao bloquear.";
    }
}

// Processar Exclusão de Bloqueio
if (isset($_GET['deletar'])) {
    $id_bloqueio = $_GET['deletar'];
    $pdo->prepare("DELETE FROM bloqueios WHERE id = ? AND id_barbeiro = ?")->execute([$id_bloqueio, $_SESSION['barbeiro_id']]);
    header("Location: bloqueios.php");
    exit;
}

// Listar bloqueios futuros
$stmt = $pdo->prepare("SELECT * FROM bloqueios WHERE id_barbeiro = ? AND data >= CURDATE() ORDER BY data");
$stmt->execute([$_SESSION['barbeiro_id']]);
$bloqueios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Bloqueios - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f9f9f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h2 { color: #1a1a1a; border-bottom: 2px solid #d4af37; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background-color: #d4af37; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn:hover { background-color: #b59530; }
        .btn-voltar { background-color: #333; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background-color: #f5f5f5; }
        .delete-btn { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin.php" class="btn btn-voltar"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
        
        <h2>Bloquear Agenda</h2>
        <?php if($mensagem) echo "<p style='color:green'>$mensagem</p>"; ?>

        <form method="POST">
            <input type="hidden" name="acao" value="bloquear">
            
            <div class="form-group">
                <label>Data:</label>
                <input type="date" name="data" required min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Tipo de Bloqueio:</label>
                <select name="tipo" id="tipo" onchange="toggleHorarios()">
                    <option value="dia_inteiro">Dia Inteiro (Folga/Feriado)</option>
                    <option value="horario">Horário Específico</option>
                </select>
            </div>

            <div id="horarios" style="display:none; gap: 10px;">
                <div class="form-group" style="flex:1">
                    <label>Início:</label>
                    <input type="time" name="hora_inicio">
                </div>
                <div class="form-group" style="flex:1">
                    <label>Fim:</label>
                    <input type="time" name="hora_fim">
                </div>
            </div>

            <div class="form-group">
                <label>Motivo (Opcional):</label>
                <input type="text" name="motivo" placeholder="Ex: Médico, Folga, Almoço">
            </div>

            <button type="submit" class="btn">Confirmar Bloqueio</button>
        </form>

        <h3 style="margin-top: 40px;">Seus Bloqueios Futuros</h3>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Motivo</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bloqueios as $b): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($b['data'])) ?></td>
                    <td>
                        <?= $b['dia_inteiro'] ? 'Dia Todo' : date('H:i', strtotime($b['hora_inicio'])) . ' às ' . date('H:i', strtotime($b['hora_fim'])) ?>
                    </td>
                    <td><?= $b['motivo'] ?></td>
                    <td><a href="?deletar=<?= $b['id'] ?>" class="delete-btn" onclick="return confirm('Desbloquear esta data?')">Excluir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleHorarios() {
            var tipo = document.getElementById('tipo').value;
            var div = document.getElementById('horarios');
            div.style.display = (tipo === 'horario') ? 'flex' : 'none';
        }
    </script>
</body>
</html>