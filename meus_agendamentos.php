<?php
session_start();
require 'db_connection.php';

// Se não tiver logado, manda pro login
if (!isset($_SESSION['cliente_id'])) {
    header("Location: entrar.php");
    exit;
}

// --- LÓGICA DE CANCELAMENTO COM E-MAIL (COM LIMITE) ---
if (isset($_POST['cancelar_id'])) {
    $id_ag = $_POST['cancelar_id'];
    $cliente_id = $_SESSION['cliente_id'];
    $cliente_email = $_SESSION['cliente_email'];
    $data_hoje = date('Y-m-d');

    // 1. CHECAGEM DO LIMITE DE CANCELAMENTOS POR DIA (NOVO CÓDIGO)
    $stmt_count = $pdo->prepare("
        SELECT COUNT(*) 
        FROM log_cancelamentos 
        WHERE id_cliente = ? AND data_cancelamento = ?
    ");
    $stmt_count->execute([$cliente_id, $data_hoje]);
    $cancelamentos_hoje = $stmt_count->fetchColumn();

    if ($cancelamentos_hoje >= 3) {
        // Redireciona com mensagem de limite atingido
        header("Location: meus_agendamentos.php?msg=limite_cancelamento");
        exit;
    }

    // 2. BUSCAR DADOS DO AGENDAMENTO (Se passou na checagem)
    $sql_busca = "SELECT a.data, a.hora, b.nome as nome_barbeiro 
                  FROM agendamentos a 
                  JOIN barbeiros b ON a.id_barbeiro = b.id 
                  WHERE a.id = ? AND (a.id_cliente = ? OR a.email = ?)";
    
    $stmt_busca = $pdo->prepare($sql_busca);
    $stmt_busca->execute([$id_ag, $cliente_id, $cliente_email]);
    $dados_agendamento = $stmt_busca->fetch();

    // Se encontrou o agendamento, prossegue com a exclusão e aviso
    if ($dados_agendamento) {
        
        // 3. EXCLUIR O AGENDAMENTO
        $stmt_del = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
        $stmt_del->execute([$id_ag]);
        
        // 4. REGISTRAR O CANCELAMENTO NO LOG
        $stmt_log = $pdo->prepare("
            INSERT INTO log_cancelamentos (id_cliente, agendamento_id, data_cancelamento, hora_cancelamento) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt_log->execute([$cliente_id, $id_ag, $data_hoje, date('H:i:s')]);
        
        // 5. ENVIAR O E-MAIL (HTML)
        $para = $cliente_email;
        $assunto = "Cancelamento Confirmado - Legacy Style";
        
        // Formata a data e hora para ficar bonito no texto
        $data_formatada = date('d/m/Y', strtotime($dados_agendamento['data']));
        $hora_formatada = substr($dados_agendamento['hora'], 0, 5);
        $barbeiro = $dados_agendamento['nome_barbeiro'];
        $nome_cliente = $_SESSION['cliente_nome'];
        
        // MENSAGEM EM HTML
        $mensagem = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                .header { background-color: #1a1a1a; padding: 20px; text-align: center; color: #d4af37; border-bottom: 3px solid #d4af37; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 30px; color: #333; line-height: 1.6; }
                .details-box { border: 1px solid #ddd; border-radius: 6px; padding: 15px; background-color: #f9f9f9; margin-top: 15px; }
                .detail-row { margin: 5px 0; font-size: 14px; }
                .detail-row strong { color: #1a1a1a; display: inline-block; width: 80px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class=\"email-container\">
                <div class=\"header\">
                    <h1>CANCELAMENTO CONFIRMADO</h1>
                </div>
                <div class=\"content\">
                    <p>Olá, <strong>$nome_cliente</strong>.</p>
                    <p>Conforme solicitado, seu agendamento foi cancelado com sucesso e o horário foi liberado.</p>

                    <div class=\"details-box\">
                        <p style=\"font-weight: bold; color: #d4af37;\">Detalhes do Agendamento Cancelado:</p>
                        <div class=\"detail-row\"><strong>Data:</strong> $data_formatada</div>
                        <div class=\"detail-row\"><strong>Horário:</strong> $hora_formatada</div>
                        <div class=\"detail-row\"><strong>Barbeiro:</strong> $barbeiro</div>
                    </div>
                    
                    <p style=\"margin-top: 25px;\">Esperamos te ver em breve para uma nova experiência Legacy Style!</p>
                </div>
                <div class=\"footer\">
                    <p>Legacy Style Barbearia | Não responda a este e-mail.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Cabeçalhos (Adiciona o Content-Type para HTML)
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@legacystyle.com.br" . "\r\n";
        $headers .= "Reply-To: contato@legacystyle.com.br" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // O @ serve para evitar erro na tela se você estiver no Localhost sem servidor de email
        @mail($para, $assunto, $mensagem, $headers);
    }
    
    header("Location: meus_agendamentos.php?msg=cancelado");
    exit;
}

// --- BUSCAR AGENDAMENTOS DO CLIENTE (COM FIND_IN_SET CORRIGIDO) ---
$sql = "
    SELECT 
        a.id, a.data, a.hora, a.valor_final, a.metodo_pagamento, a.status, a.mp_status,
        b.nome AS barbeiro_nome,
        (SELECT GROUP_CONCAT(nome SEPARATOR ', ') FROM servicos WHERE FIND_IN_SET(id, REPLACE(a.servicos_ids, ' ', ''))) AS servicos_nomes
    FROM agendamentos a
    JOIN barbeiros b ON a.id_barbeiro = b.id
    WHERE a.id_cliente = ? OR a.email = ?
    ORDER BY a.data DESC, a.hora DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['cliente_id'], $_SESSION['cliente_email']]);
$agendamentos = $stmt->fetchAll();

date_default_timezone_set('America/Sao_Paulo');
$current_datetime = new DateTime();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #111111;
            --secondary: #d4af37; /* Dourado Premium */
            --light-gray: #f9f9f9;
        }

        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: var(--light-gray); 
            color: var(--primary); 
            line-height: 1.6;
            padding-top: 80px;
        }

        .header {
            background: var(--primary);
            color: #fff;
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 3px solid var(--secondary);
        }
        .header-content {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .logo span { color: var(--secondary); }
        .nav a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
            transition: 0.3s;
        }
        .nav a:hover { color: var(--secondary); }
        .btn-logout {
            background: var(--secondary);
            color: var(--primary);
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        h1 { font-size: 28px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        /* Card de Agendamento */
        .agendamento-card {
            border: 2px solid #f0f0f0;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            transition: 0.3s;
        }
        .agendamento-card:hover { border-color: var(--secondary); }

        .header-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .data-hora {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .detalhes p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        .detalhes i {
            color: var(--secondary);
            margin-right: 8px;
        }

        .actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-cancelar {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }
        .btn-cancelar:hover { background: #c82333; }
        
        .btn-pix {
            background: #1a1a1a;
            color: #fff;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-pix:hover { background: var(--secondary); color: var(--primary); }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">LEGACY <span>STYLE</span></a>
            <div class="nav">
                <span>Olá, <?= htmlspecialchars($_SESSION['cliente_nome']) ?></span>
                <a href="logout_cliente.php" class="btn-logout">Sair</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h1>Meus Agendamentos</h1>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'cancelado'): ?>
            <div style="background: #e9ffed; color: #007bff; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                Agendamento cancelado com sucesso! Um e-mail de confirmação foi enviado.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'limite_cancelamento'): ?>
            <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                ⚠️ **Limite Diário Atingido:** Você já cancelou o limite de 3 agendamentos hoje. Tente novamente amanhã.
            </div>
        <?php endif; ?>
        
        <?php if (count($agendamentos) == 0): ?>
            <p style="text-align: center; color: #777;">Você ainda não tem agendamentos registrados.</p>
            <p style="text-align: center; margin-top: 20px;"><a href="index.php" style="color: var(--secondary); text-decoration: none;">Clique aqui para agendar seu horário.</a></p>
        <?php endif; ?>

        <?php foreach ($agendamentos as $ag): 
            $agendamento_datetime = new DateTime($ag['data'] . ' ' . $ag['hora']);
            $is_futuro = $agendamento_datetime > $current_datetime;
        ?>
            <div class="agendamento-card">
                <div class="header-card">
                    <span class="data-hora">
                        <?= date('d/m/Y', strtotime($ag['data'])) ?> às <?= substr($ag['hora'], 0, 5) ?>
                    </span>
                    <div>
                        <?php 
                        // Lógica de Status
                        $status_label = 'AGENDADO';
                        $status_style = 'background:#28a745; color:#fff'; // Padrão
                        
                        if (!$is_futuro) {
                            $status_label = 'REALIZADO/PASSADO';
                            $status_style = 'background:#6c757d; color:#fff';
                        }
                        
                        if ($ag['metodo_pagamento'] == 'pix' && $ag['mp_status'] == 'approved') {
                            $status_label = 'PAGO (Pix/Cartão)';
                            $status_style = 'background:#007bff; color:#fff';
                        } elseif ($ag['metodo_pagamento'] == 'pix' && $ag['mp_status'] == 'pending') {
                            $status_label = 'PAGAMENTO PENDENTE';
                            $status_style = 'background:#ffc107; color:#333';
                        } elseif ($ag['metodo_pagamento'] == 'pix' && $ag['mp_status'] == 'rejected') {
                            $status_label = 'PAGAMENTO FALHOU';
                            $status_style = 'background:#dc3545; color:#fff';
                        } elseif ($ag['metodo_pagamento'] == 'presencial') {
                             $status_label = 'PAGAR NO LOCAL';
                             $status_style = 'background:#eee; color:#333';
                        }
                        ?>
                        <span class="status" style="<?= $status_style ?>"><?= $status_label ?></span>
                    </div>
                </div>

                <div class="detalhes">
                    <p><i class="fas fa-user"></i> Barbeiro: <strong><?= $ag['barbeiro_nome'] ?></strong></p>
                    <p><i class="fas fa-cut"></i> Serviços: <?= $ag['servicos_nomes'] ?></p>
                    <p><i class="fas fa-tag"></i> Valor: R$ <?= number_format($ag['valor_final'], 2, ',', '.') ?></p>
                </div>

                <?php if ($is_futuro): ?>
                    <div class="actions">
                        <?php if ($ag['metodo_pagamento'] == 'pix' && $ag['mp_status'] != 'approved'): ?>
                            <a href="pagamento_pix.php?id=<?= $ag['id'] ?>" class="btn-pix">
                                <i class="fas fa-qrcode"></i> Pagar / Ver QR Code
                            </a>
                        <?php endif; ?>

                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja cancelar?');">
                            <input type="hidden" name="cancelar_id" value="<?= $ag['id'] ?>">
                            <button type="submit" class="btn-cancelar">Cancelar Horário</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>