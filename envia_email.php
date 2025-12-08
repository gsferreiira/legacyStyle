<?php
function enviarEmailConfirmacao($dadosAgendamento) {
    // Dados do agendamento
    $nomeCliente = $dadosAgendamento['nome_cliente'];
    $emailCliente = $dadosAgendamento['email'];
    $data = date('d/m/Y', strtotime($dadosAgendamento['data']));
    $hora = $dadosAgendamento['hora'];
    $barbeiro = $dadosAgendamento['barbeiro_nome'];
    $servicos = $dadosAgendamento['servicos'];
    $idAgendamento = $dadosAgendamento['id'];
    $statusPagamento = $dadosAgendamento['status_pagamento'] ?? 'Pagar no Local';
    
    // Token único para cancelamento
    $token = md5($idAgendamento . 'SECRET_SALT_' . date('Ymd'));
    
    // Link de cancelamento (Ajuste "localhost/seu_projeto" para o caminho real se precisar)
    $linkCancelamento = "http://localhost/legacystyle/cancelar_agendamento.php?id=$idAgendamento&token=$token";
    
    // Assunto
    $assunto = "Confirmação de Agendamento - Legacy Style";

    // Corpo do E-mail HTML
    $mensagem = "
    <html>
    <head>
        <style>
            body { font-family: 'Montserrat', Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
            .header { background-color: #1a1a1a; color: #d4af37; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #fff; }
            .details { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            .btn { background-color: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Agendamento Confirmado!</h1>
            </div>
            <div class='content'>
                <p>Olá, <strong>$nomeCliente</strong>!</p>
                <p>Seu horário na <strong>Legacy Style</strong> foi reservado com sucesso.</p>
                
                <div class='details'>
                    <p><strong>Barbeiro:</strong> $barbeiro</p>
                    <p><strong>Data:</strong> $data</p>
                    <p><strong>Horário:</strong> $hora</p>
                    <p><strong>Serviços:</strong> $servicos</p>
                    <p><strong>Pagamento:</strong> $statusPagamento</p>
                </div>
                
                <p>Se precisar cancelar, clique no botão abaixo:</p>
                <p style='text-align: center; margin-top: 20px;'>
                    <a href='$linkCancelamento' class='btn'>Cancelar Agendamento</a>
                </p>
            </div>
            <div class='footer'>
                <p>Legacy Style Barbearia - Estilo e Tradição</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Cabeçalhos
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Legacy Style <noreply@legacystyle.com>\r\n";
    
    // Tenta enviar
    return mail($emailCliente, $assunto, $mensagem, $headers);
}
?>