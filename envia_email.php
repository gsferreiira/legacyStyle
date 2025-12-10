<?php
function enviarEmailConfirmacao($dadosAgendamento) {
    // 1. Configuração da URL REAL do seu site
    $site_url = "https://www.legacystyle.com.br";

    // 2. Extrair dados
    $nomeCliente = explode(' ', $dadosAgendamento['nome_cliente'])[0]; // Pega só o primeiro nome
    $emailCliente = $dadosAgendamento['email'];
    $data = date('d/m/Y', strtotime($dadosAgendamento['data']));
    $hora = substr($dadosAgendamento['hora'], 0, 5);
    $barbeiro = $dadosAgendamento['barbeiro_nome'];
    $servicos = $dadosAgendamento['servicos'];
    $idAgendamento = $dadosAgendamento['id'];
    $statusPagamento = $dadosAgendamento['status_pagamento'] ?? 'Pagar no Local';
    
    // 3. Gerar Token Seguro para Cancelamento
    $segredo = "LEGACY_KEY_2024"; 
    $token = md5($idAgendamento . $emailCliente . $segredo);
    
    $linkCancelamento = "$site_url/cancelar_agendamento.php?id=$idAgendamento&token=$token";
    
    // 4. Configuração Anti-Spam (O Pulo do Gato)
    $assunto = "Confirmado: Seu horario na Legacy Style";
    
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    
    // O "From" DEVE ser do seu domínio para não cair no spam
    $headers .= "From: Legacy Style <agendamento@legacystyle.com.br>\r\n";
    
    // O "Reply-To" vai para o seu Gmail se o cliente responder
    $headers .= "Reply-To: legacystyle.com.br@gmail.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // 5. Layout do E-mail (Bonito e Responsivo)
    $mensagem = "
    <html>
    <head>
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            .header { background-color: #111111; padding: 30px; text-align: center; }
            .header h1 { color: #d4af37; margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
            .content { padding: 40px 30px; color: #333333; }
            .greeting { font-size: 20px; font-weight: bold; margin-bottom: 20px; color: #111; }
            .info-box { background-color: #f9f9f9; border-left: 4px solid #d4af37; padding: 20px; margin: 20px 0; border-radius: 4px; }
            .info-item { margin-bottom: 10px; font-size: 15px; }
            .info-item strong { color: #555; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
            .btn-cancel { display: block; width: 200px; margin: 30px auto 0; background-color: #ffffff; border: 1px solid #ff4d4d; color: #ff4d4d; text-align: center; padding: 12px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 14px; }
            .footer { background-color: #f4f4f4; color: #888; text-align: center; padding: 20px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header'>
                <h1>Legacy Style</h1>
            </div>
            <div class='content'>
                <p class='greeting'>Fala, $nomeCliente!</p>
                <p>Seu horário está confirmadíssimo. Nossos barbeiros já estão preparando o equipamento para te receber.</p>
                
                <div class='info-box'>
                    <div class='info-item'><strong>Data e Hora</strong><br>$data às $hora</div>
                    <div class='info-item'><strong>Profissional</strong><br>$barbeiro</div>
                    <div class='info-item'><strong>Serviços</strong><br>$servicos</div>
                    <div class='info-item'><strong>Pagamento</strong><br>$statusPagamento</div>
                </div>
                
                <p style='color: #666; font-size: 14px; line-height: 1.5;'>
                    Chegue com 5 minutinhos de antecedência para tomar aquele café ou cerveja gelada.<br>
                    Endereço: Rua Pedro Gusso, 744 - Curitiba.
                </p>

                <br>
                <p style='text-align: center; font-size: 12px; color: #999;'>Imprevistos acontecem? Se precisar desmarcar:</p>
                <a href='$linkCancelamento' class='btn-cancel'>Cancelar Agendamento</a>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " Legacy Style Barbearia<br>
                Não responda a este e-mail automaticamente.
            </div>
        </div>
    </body>
    </html>
    ";
    
    return mail($emailCliente, $assunto, $mensagem, $headers);
}
?>