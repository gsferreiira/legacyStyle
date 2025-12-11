<?php
session_start();
require 'db_connection.php';

// Se não tiver logado, manda pro login
if (!isset($_SESSION['cliente_id'])) {
    header("Location: entrar.php");
    exit;
}

// --- LÓGICA DE CANCELAMENTO COM E-MAIL ---
if (isset($_POST['cancelar_id'])) {
    $id_ag = $_POST['cancelar_id'];
    
    // 1. BUSCAR DADOS DO AGENDAMENTO (Antes de excluir, para poder avisar no email qual foi)
    // Verificamos se pertence ao ID do cliente OU ao Email dele
    $sql_busca = "SELECT a.data, a.hora, b.nome as nome_barbeiro 
                  FROM agendamentos a 
                  JOIN barbeiros b ON a.id_barbeiro = b.id 
                  WHERE a.id = ? AND (a.id_cliente = ? OR a.email = ?)";
    
    $stmt_busca = $pdo->prepare($sql_busca);
    $stmt_busca->execute([$id_ag, $_SESSION['cliente_id'], $_SESSION['cliente_email']]);
    $dados_agendamento = $stmt_busca->fetch();

    // Se encontrou o agendamento, prossegue com a exclusão e aviso
    if ($dados_agendamento) {
        
        // 2. EXCLUIR O AGENDAMENTO
        $stmt_del = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
        $stmt_del->execute([$id_ag]);

        // 3. ENVIAR O E-MAIL
        $para = $_SESSION['cliente_email'];
        $assunto = "Cancelamento Confirmado - Legacy Style";
        
        // Formata a data e hora para ficar bonito no texto
        $data_formatada = date('d/m/Y', strtotime($dados_agendamento['data']));
        $hora_formatada = substr($dados_agendamento['hora'], 0, 5);
        $barbeiro = $dados_agendamento['nome_barbeiro'];
        $nome_cliente = $_SESSION['cliente_nome'];

        $mensagem = "
        Olá, $nome_cliente.

        Confirmamos o cancelamento do seu agendamento:
        ------------------------------------
        📅 Data: $data_formatada
        ⏰ Horário: $hora_formatada
        ✂️ Barbeiro: $barbeiro
        ------------------------------------

        Seu horário foi liberado para outros clientes.
        Esperamos te ver em breve!

        Atenciosamente,
        Equipe Legacy Style
        ";

        // Cabeçalhos (Ajuste o 'From' para um email real do seu domínio quando estiver na Hostinger)
        $headers = "From: no-reply@legacystyle.com.br" . "\r\n" .
                   "Reply-To: contato@legacystyle.com.br" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // O @ serve para evitar erro na tela se você estiver no Localhost sem servidor de email
        @mail($para, $assunto, $mensagem, $headers);
    }
    
    header("Location: meus_agendamentos.php?msg=cancelado");
    exit;
}

// --- BUSCAR LISTA DE AGENDAMENTOS (Para exibir na tela) ---
$sql = "
    SELECT a.*, b.nome as barbeiro_nome, 
           (SELECT GROUP_CONCAT(nome SEPARATOR ', ') FROM servicos WHERE FIND_IN_SET(id, REPLACE(a.servicos_ids, ' ', ''))) as servicos_nomes
    FROM agendamentos a
    JOIN barbeiros b ON a.id_barbeiro = b.id
    WHERE a.id_cliente = ? OR a.email = ?
    ORDER BY a.data DESC, a.hora DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['cliente_id'], $_SESSION['cliente_email']]);
$agendamentos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f9f9f9; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        
        .header-profile { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .user-info h2 { margin: 0; color: #1a1a1a; font-size: 20px; }
        .user-info p { margin: 5px 0 0; color: #666; font-size: 14px; }
        .btn-sair { color: #dc3545; text-decoration: none; font-weight: 600; border: 1px solid #dc3545; padding: 8px 15px; border-radius: 5px; transition: 0.3s; }
        .btn-sair:hover { background: #dc3545; color: white; }
        .btn-novo { background: #d4af37; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; display: inline-block; margin-bottom: 20px; }

        .card-agendamento { background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #d4af37; position: relative; }
        .card-agendamento.passado { border-left-color: #ccc; opacity: 0.7; }
        
        .card-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .data-hora { font-weight: bold; font-size: 18px; color: #1a1a1a; }
        .status { font-size: 12px; font-weight: bold; padding: 4px 8px; border-radius: 4px; }
        .status.pendente { background: #fff3cd; color: #856404; }
        .status.pago { background: #d4edda; color: #155724; }

        .detalhes p { margin: 5px 0; color: #555; }
        .detalhes i { width: 20px; color: #d4af37; text-align: center; margin-right: 5px; }

        .actions { margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px; }
        .btn-cancelar { background: none; border: none; color: #dc3545; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-pix { background: #d4af37; color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-profile">
            <div class="user-info">
                <h2>Olá, <?= htmlspecialchars($_SESSION['cliente_nome']) ?></h2>
                <p><?= $_SESSION['cliente_email'] ?></p>
            </div>
            <a href="logout_cliente.php" class="btn-sair">Sair</a>
        </div>

        <a href="index.php" class="btn-novo"><i class="fas fa-plus"></i> Novo Agendamento</a>

        <h3 style="color: #333; margin-bottom: 15px;">Seus Horários</h3>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'cancelado'): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                Agendamento cancelado com sucesso! Um e-mail de confirmação foi enviado.
            </div>
        <?php endif; ?>

        <?php if (count($agendamentos) == 0): ?>
            <p style="text-align: center; color: #777; padding: 40px;">Você ainda não tem agendamentos.</p>
        <?php endif; ?>

        <?php foreach ($agendamentos as $ag): 
            $is_futuro = strtotime($ag['data']) >= strtotime(date('Y-m-d'));
            $classe = $is_futuro ? '' : 'passado';
        ?>
            <div class="card-agendamento <?= $classe ?>">
                <div class="card-header">
                    <div class="data-hora">
                        <?= date('d/m/Y', strtotime($ag['data'])) ?> às <?= substr($ag['hora'], 0, 5) ?>
                    </div>
                    <div>
                        <?php if($ag['metodo_pagamento'] == 'pix'): ?>
                            <span class="status <?= ($ag['mp_status'] == 'approved') ? 'pago' : 'pendente' ?>">
                                <?= ($ag['mp_status'] == 'approved') ? 'PIX PAGO' : 'PIX PENDENTE' ?>
                            </span>
                        <?php else: ?>
                            <span class="status" style="background:#eee; color:#333">PAGAR NO LOCAL</span>
                        <?php endif; ?>
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