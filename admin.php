<?php
session_start();
$filter_date = isset($_GET['date']) ? $_GET['date'] : null;
require 'db_connection.php';

// Verifica se o barbeiro está logado
if (!isset($_SESSION['barbeiro_id'])) {
    header("Location: login.php");
    exit;
}

// Processar cancelamento de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_id'])) {
    $id = $_POST['cancelar_id'];
    $pdo->prepare("DELETE FROM agendamentos WHERE id = ?")->execute([$id]);
    header("Location: admin.php");
    exit;
}

// Busca agendamentos
$sql = "
    SELECT 
        a.*, 
        b.nome AS barbeiro_nome,
        s.nome AS servico_nome,
        s.duracao AS servico_duracao
    FROM agendamentos a
    JOIN barbeiros b ON a.id_barbeiro = b.id
    JOIN servicos s ON FIND_IN_SET(s.id, a.id_servico)
    WHERE a.id_barbeiro = ?
";

$params = [$_SESSION['barbeiro_id']];

if ($filter_date) {
    $sql .= " AND a.data = ?";
    $params[] = $filter_date;
}

$sql .= " ORDER BY a.nome_cliente, a.data, a.hora";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$agendamentos = $stmt->fetchAll();

// Agrupa por cliente para exibição
$agendamentos_agrupados = [];
foreach ($agendamentos as $agendamento) {
    $servicos = explode(',', $agendamento['id_servico']);
    $nomes_servicos = explode(',', $agendamento['servico_nome']);
    $duracoes_servicos = explode(',', $agendamento['servico_duracao']);
    
    foreach ($servicos as $index => $servico_id) {
        $agendamento_copy = $agendamento;
        $agendamento_copy['servico_nome'] = $nomes_servicos[$index];
        $agendamento_copy['servico_duracao'] = $duracoes_servicos[$index];
        $agendamentos_agrupados[] = $agendamento_copy;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Painel Admin - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #1a1a1a;
            --secondary: #d4af37;
            --light: #f5f5f5;
            --danger: #dc3545;
        }
        
        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary);
        }
        
        .header h1 {
            color: var(--primary);
            margin: 0;
        }
        
        .logout {
            background-color: var(--danger);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .logout:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: var(--primary);
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        table {
            width: 100%; 
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td { 
            padding: 12px 15px; 
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th { 
            background: var(--primary); 
            color: var(--secondary);
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-today {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-upcoming {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .btn {
            padding: 8px 22px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-whatsapp {
            background-color: #25D366;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-whatsapp:hover {
            background-color: #1da851;
            transform: none; /* Removendo o translateY se existia */
            box-shadow: none; /* Removendo sombra se existia */
        }
        
        .no-appointments {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .date-filter {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .date-filter input, .date-filter button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .date-filter button {
            background-color: var(--secondary);
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
            
            .date-filter {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cut"></i> Painel do <?= htmlspecialchars($_SESSION['barbeiro_nome']) ?></h1>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
        
        <div class="card">
            <h2><i class="far fa-calendar-alt"></i> Agendamentos</h2>
            
            <div class="date-filter">
                <input type="date" id="filterDate" value="<?= date('Y-m-d') ?>">
                <button onclick="filterByDate()">Filtrar</button>
                <button onclick="resetFilter()">Hoje</button>
            </div>
            
            <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Serviço</th>
                    <th>Horário</th>
                    <th>Duração</th>
                    <th>Contato</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($agendamentos_agrupados)): ?>
                    <tr>
                        <td colspan="7" class="no-appointments">Nenhum agendamento encontrado</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $cliente_anterior = null;
                    foreach ($agendamentos_agrupados as $agendamento): 
                    ?>
                        <tr>
                            <td>
                                <?php if ($cliente_anterior != $agendamento['nome_cliente']): ?>
                                    <?= htmlspecialchars($agendamento['nome_cliente']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($agendamento['data'])) ?>
                                <?php if (date('Y-m-d') == $agendamento['data']): ?>
                                    <span class="badge badge-today">Hoje</span>
                                <?php elseif (date('Y-m-d') < $agendamento['data']): ?>
                                    <span class="badge badge-upcoming">Futuro</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($agendamento['servico_nome']) ?></td>
                            <td><?= date('H:i', strtotime($agendamento['hora'])) ?></td>
                            <td><?= $agendamento['servico_duracao'] ?> min</td>
                            <td>
                                <a href="https://wa.me/55<?= $agendamento['telefone'] ?>" class="btn btn-whatsapp" target="_blank">
                                    <i class="fab fa-whatsapp"></i> Contato
                                </a>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="cancelar_id" value="<?= $agendamento['id'] ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                        $cliente_anterior = $agendamento['nome_cliente'];
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <script>
        function filterByDate() {
            const date = document.getElementById('filterDate').value;
            window.location.href = `admin.php?date=${date}`;
        }
        
        function resetFilter() {
            window.location.href = 'admin.php';
        }
        
        // Verifica se há parâmetro de data na URL
        const urlParams = new URLSearchParams(window.location.search);
        const dateParam = urlParams.get('date');
        if (dateParam) {
            document.getElementById('filterDate').value = dateParam;
        }
    </script>
</body>
</html>