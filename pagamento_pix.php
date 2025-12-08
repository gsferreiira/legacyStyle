<?php
require 'db_connection.php';
$id_agendamento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_agendamento) {
    header("Location: index.php");
    exit;
}

// Busca os dados do pagamento e do barbeiro (para o nome no Google Agenda)
$stmt = $pdo->prepare("SELECT a.*, b.nome as nome_barbeiro 
                       FROM agendamentos a 
                       JOIN barbeiros b ON a.id_barbeiro = b.id 
                       WHERE a.id = ?");
$stmt->execute([$id_agendamento]);
$agendamento = $stmt->fetch();

// Verificações de segurança
if (!$agendamento || $agendamento['metodo_pagamento'] !== 'pix') {
    header("Location: index.php");
    exit;
}

// Se já estiver pago, avisa e não mostra mais o QR Code
if ($agendamento['mp_status'] === 'approved') {
    header("Location: index.php?agendamento=sucesso&mensagem=Pagamento já aprovado!");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento PIX - Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .payment-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); max-width: 500px; width: 100%; text-align: center; }
        .logo img { height: 60px; margin-bottom: 20px; }
        h2 { color: #1a1a1a; margin-bottom: 10px; }
        .amount { font-size: 32px; color: #d4af37; font-weight: 700; margin: 20px 0; }
        .qr-container { margin: 20px auto; padding: 10px; border: 2px dashed #ddd; border-radius: 10px; display: inline-block; }
        .qr-container img { max-width: 100%; height: auto; }
        .copy-area { margin-top: 20px; }
        textarea { width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: none; font-size: 12px; background: #f9f9f9; color: #555; margin-bottom: 10px; }
        .btn { background-color: #d4af37; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; display: inline-block; transition: all 0.3s; }
        .btn:hover { background-color: #b59530; }
        .btn-outline { background: transparent; border: 2px solid #d4af37; color: #d4af37; margin-top: 10px; width: 100%; box-sizing: border-box; }
        .btn-google { background-color: #4285F4; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 15px; width: 100%; box-sizing: border-box; }
        .btn-google:hover { background-color: #3367D6; }
        .instructions { font-size: 14px; color: #555; margin-bottom: 20px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="payment-card">
        <div class="logo">
            <img src="assets/LOGO LEGACY SF/2.png" alt="Legacy Style">
        </div>
        
        <h2>Quase lá!</h2>
        <p>Escaneie o QR Code ou copie o código abaixo para finalizar o pagamento.</p>
        
        <div class="amount">R$ <?= number_format($agendamento['valor_final'], 2, ',', '.') ?></div>
        
        <div class="qr-container">
            <?php if ($agendamento['qr_code_base64']): ?>
                <img src="data:image/png;base64,<?= $agendamento['qr_code_base64'] ?>" alt="QR Code PIX">
            <?php else: ?>
                <p>Erro ao carregar QR Code. Use o código abaixo.</p>
            <?php endif; ?>
        </div>
        
        <div class="instructions">
            1. Abra o app do seu banco<br>
            2. Escolha pagar via PIX > Ler QR Code ou Copia e Cola<br>
            3. Confirme o pagamento
        </div>

        <div class="copy-area">
            <p style="font-size: 14px; font-weight: 600; margin-bottom: 5px;">Código Copia e Cola:</p>
            <textarea id="pixCode" readonly><?= $agendamento['qr_code_copia_cola'] ?></textarea>
            <button onclick="copyCode()" class="btn" style="width: 100%;"><i class="far fa-copy"></i> Copiar Código</button>
        </div>

        <?php
        // --- Botão Adicionar ao Google Agenda ---
        try {
            $data_inicio = new DateTime($agendamento['data'] . ' ' . $agendamento['hora']);
            $data_fim = clone $data_inicio;
            $data_fim->add(new DateInterval('PT' . $agendamento['duracao'] . 'M'));

            $g_inicio = $data_inicio->format('Ymd\THis');
            $g_fim = $data_fim->format('Ymd\THis');

            $titulo_evento = urlencode("Corte na Legacy Style");
            $detalhes_evento = urlencode("Agendamento com " . $agendamento['nome_barbeiro']);
            $local_evento = urlencode("Legacy Style Barbearia");

            $link_google = "https://www.google.com/calendar/render?action=TEMPLATE&text=$titulo_evento&dates=$g_inicio/$g_fim&details=$detalhes_evento&location=$local_evento&sf=true&output=xml";
        ?>
            <a href="<?= $link_google ?>" target="_blank" class="btn btn-google">
                <i class="far fa-calendar-plus"></i> Adicionar ao Google Agenda
            </a>
        <?php } catch (Exception $e) { } ?>

        <div style="margin-top: 15px;">
            <a href="index.php?agendamento=sucesso&mensagem=Agendamento realizado. Aguardando confirmação." class="btn btn-outline">Já paguei, voltar ao início</a>
        </div>
    </div>

    <script>
        function copyCode() {
            var copyText = document.getElementById("pixCode");
            copyText.select();
            copyText.setSelectionRange(0, 99999); 
            navigator.clipboard.writeText(copyText.value);
            alert("Código PIX copiado para a área de transferência!");
        }
    </script>
</body>
</html>