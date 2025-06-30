<?php
function enviarEmailConfirmacao($dadosAgendamento) {
    // Configurações do servidor SMTP (substitua com seus dados)
    $smtpHost = 'smtp.seuprovedor.com';
    $smtpUsername = 'seuemail@barbearia.com';
    $smtpPassword = 'suasenha';
    $smtpPort = 587;
    
    // Dados do agendamento
    $nomeCliente = $dadosAgendamento['nome_cliente'];
    $emailCliente = $dadosAgendamento['email']; // Você precisará adicionar campo de email no formulário
    $data = date('d/m/Y', strtotime($dadosAgendamento['data']));
    $hora = $dadosAgendamento['hora'];
    $barbeiro = $dadosAgendamento['barbeiro_nome'];
    $servicos = $dadosAgendamento['servicos'];
    $idAgendamento = $dadosAgendamento['id'];
    
    // Token único para cancelamento (usando o ID do agendamento e um salt)
    $token = md5($idAgendamento . 'SECRET_SALT_' . date('Ymd'));
    
    // Link de cancelamento
    $linkCancelamento = "https://sua-barbearia.com/cancelar_agendamento.php?id=$idAgendamento&token=$token";
    
    // Corpo do e-mail em HTML
    
$status_pagamento = $_POST['status_pagamento'] ?? 'pendente';
$assunto = "Confirmação do seu agendamento na Legacy Style";
$mensagem = "";

if ($status_pagamento === "pago") {
    $mensagem = "
    <h2>✅ Pagamento Confirmado!</h2>
    <p>Olá {$nome_cliente}, seu horário foi agendado com sucesso e o pagamento via PIX foi confirmado.</p>
    <p>Nos vemos em breve na <strong>Legacy Style</strong>!</p>
    ";
} else {
    $mensagem = "
    <h2>🕓 Agendamento Confirmado</h2>
    <p>Olá {$nome_cliente}, seu horário foi agendado com sucesso.</p>
    <p>Assim que o pagamento for confirmado, você receberá um novo aviso.</p>
    ";
}

    
    $mensagem = "
    <html>
    <head>
        <title>Confirmação de Agendamento</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .header { background-color: #1a1a1a; color: #d4af37; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 12px; }
            .btn-cancelar { 
                background-color: #dc3545; 
                color: white; 
                padding: 10px 15px; 
                text-decoration: none; 
                border-radius: 5px;
                display: inline-block;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Legacy Style Barbearia</h2>
        </div>
        <div class='content'>
            <h3>Olá, $nomeCliente!</h3>
            <p>Seu agendamento foi confirmado com sucesso. Abaixo estão os detalhes:</p>
            
            <p><strong>Barbeiro:</strong> $barbeiro</p>
            <p><strong>Data:</strong> $data</p>
            <p><strong>Horário:</strong> $hora</p>
            <p><strong>Serviços:</strong> $servicos</p>
            
            <p>Se precisar cancelar ou alterar seu agendamento, clique no botão abaixo:</p>
            <a href='$linkCancelamento' class='btn-cancelar'>Cancelar Agendamento</a>
            
            <p>Atenciosamente,<br>Equipe Legacy Style</p>
        </div>
        <div class='footer'>
            <p>Este é um e-mail automático, por favor não responda.</p>
        </div>
    </body>
    </html>
    ";
    
    // Cabeçalhos do e-mail
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Legacy Style <contato@legacystyle.com>\r\n";
    $headers .= "Reply-To: contato@legacystyle.com\r\n";
    
    // Envia o e-mail (usando a função mail() do PHP ou uma biblioteca como PHPMailer)
    $mailSent = mail($emailCliente, $assunto, $mensagem, $headers);
    
    return $mailSent;
}
?>