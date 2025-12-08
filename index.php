<?php
require 'db_connection.php';
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legacy Style - Barbearia Premium</title>
    <link rel="shortcut icon" href="assets/LOGO LEGACY SF/1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- ESTILOS GERAIS --- */
        :root {
            --primary: #1a1a1a;
            --secondary: #d4af37;
            --light-bg: #f9f9f9;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        html { scroll-behavior: smooth; }
        body { background-color: var(--light-bg); color: #333; overflow-x: hidden; }

        /* --- ALERTAS --- */
        .alert {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            padding: 15px 25px; border-radius: 5px; color: white; font-weight: 500;
            z-index: 3000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s, fadeOut 0.5s 3s forwards;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background-color: #28a745; }
        .alert-error { background-color: #dc3545; }
        @keyframes slideIn { from { top: -100px; opacity: 0; } to { top: 20px; opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }

        /* --- HEADER & NAV --- */
        header {
            background-color: var(--primary); color: #fff; padding: 15px 0;
            position: fixed; width: 100%; z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: 700; color: var(--secondary); display: flex; align-items: center; gap: 10px; text-decoration: none;}
        .logo span { color: #fff; }
        .logo img { height: 50px; }
        
        .menu-toggle { display: none; cursor: pointer; font-size: 24px; color: var(--secondary); }
        nav ul { display: flex; list-style: none; gap: 25px; align-items: center; }
        nav ul li a { color: #fff; text-decoration: none; font-weight: 500; transition: color 0.3s; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        nav ul li a:hover { color: var(--secondary); }

        .login-link {
            border: 1px solid var(--secondary); padding: 8px 20px; border-radius: 30px;
            color: var(--secondary) !important; font-weight: bold; transition: all 0.3s;
        }
        .login-link:hover { background: var(--secondary); color: var(--primary) !important; }

        /* --- HERO SECTION --- */
        .hero {
            height: 100vh; min-height: 600px;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.7)), url('assets/interior1.jpg'); 
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; align-items: center; text-align: center; color: #fff; padding-top: 70px;
        }
        .hero-content h1 { font-size: 48px; margin-bottom: 20px; line-height: 1.2; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; }
        .hero-content p { font-size: 18px; margin-bottom: 35px; max-width: 700px; margin-left: auto; margin-right: auto; color: #ddd; }
        
        .btn {
            display: inline-block; background-color: var(--secondary); color: var(--primary);
            padding: 15px 40px; border-radius: 30px; text-decoration: none; font-weight: 700;
            transition: all 0.3s; font-size: 14px; text-transform: uppercase; border: none; cursor: pointer; letter-spacing: 1px;
        }
        .btn:hover { background-color: #fff; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3); }

        /* --- SECTIONS PADRÃO --- */
        section { padding: 80px 0; }
        .section-title { text-align: center; margin-bottom: 60px; }
        .section-title h2 { font-size: 36px; color: var(--primary); position: relative; display: inline-block; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .section-title h2::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 60px; height: 3px; background-color: var(--secondary); }

        /* --- SOBRE NÓS (SEM FOTO / MANIFESTO) --- */
        .about-text-full { text-align: center; max-width: 900px; margin: 0 auto; }
        .about-text-full h3 { font-size: 28px; color: var(--primary); margin-bottom: 30px; font-weight: 300; line-height: 1.4; }
        .about-text-full p { font-size: 16px; color: #555; line-height: 1.8; margin-bottom: 20px; }
        
        .values-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-top: 60px; }
        .value-item { padding: 30px; background: white; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; border-bottom: 3px solid transparent; }
        .value-item:hover { transform: translateY(-10px); border-bottom-color: var(--secondary); }
        .value-icon { font-size: 40px; color: var(--secondary); margin-bottom: 20px; }
        .value-item h4 { font-size: 20px; margin-bottom: 15px; text-transform: uppercase; }
        .value-item p { font-size: 14px; color: #666; line-height: 1.6; }

        /* --- BARBEIROS --- */
        .barbers { background-color: #fff; }
        .barbers-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .barber-card { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; }
        .barber-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .barber-img { height: 400px; overflow: hidden; }
        .barber-img img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .barber-card:hover .barber-img img { transform: scale(1.05); }
        .barber-info { padding: 25px; text-align: center; }
        .barber-info h3 { font-size: 22px; margin-bottom: 5px; }
        .barber-info p.role { color: var(--secondary); font-weight: 600; margin-bottom: 15px; text-transform: uppercase; font-size: 13px; }

        /* --- SERVIÇOS --- */
        .services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .service-card {
            background: #fff; padding: 40px 30px; border-radius: 5px; text-align: center;
            border: 1px solid #eee; transition: 0.3s; position: relative;
        }
        .service-card:hover { border-color: var(--secondary); transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .service-icon { font-size: 40px; color: var(--secondary); margin-bottom: 20px; }
        .service-card h3 { font-size: 18px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .service-card .price { color: var(--secondary); font-size: 22px; font-weight: 700; margin-top: 15px; display: block; }

        /* --- DEPOIMENTOS --- */
        .testimonials { background-color: var(--primary); color: white; }
        .testimonials h2 { color: white; }
        .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .testimonial-card { background: #222; padding: 40px; border-radius: 5px; position: relative; }
        .testimonial-card::before { content: '"'; font-size: 60px; color: var(--secondary); opacity: 0.3; position: absolute; top: 10px; left: 20px; font-family: serif; }
        .testimonial-text { font-style: italic; margin-bottom: 20px; color: #ddd; line-height: 1.6; position: relative; z-index: 1; }
        .testimonial-author h4 { color: var(--secondary); margin-bottom: 2px; font-size: 16px; }
        .testimonial-author span { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; }

        /* --- CONTATO --- */
        .contact-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 50px; }
        .info-item { display: flex; gap: 20px; margin-bottom: 30px; }
        .info-icon { width: 50px; height: 50px; background: rgba(212, 175, 55, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary); font-size: 20px; flex-shrink: 0; }
        .info-text h4 { margin-bottom: 8px; font-size: 18px; color: var(--primary); }
        .info-text p, .info-text a { color: #666; text-decoration: none; font-size: 15px; line-height: 1.6; }
        
        .contact-form input, .contact-form textarea {
            width: 100%; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd;
            border-radius: 5px; background: #fff; font-size: 14px; font-family: 'Montserrat', sans-serif;
        }
        .contact-form textarea { height: 150px; resize: none; }

        /* --- FOOTER --- */
        footer { background: #000; color: #fff; padding: 60px 0 20px; text-align: center; }
        .footer-logo { font-size: 28px; font-weight: 700; margin-bottom: 30px; color: var(--secondary); letter-spacing: 2px; }
        .footer-logo span { color: #fff; }
        .footer-links { display: flex; justify-content: center; gap: 30px; margin-bottom: 40px; flex-wrap: wrap; }
        .footer-links a { color: #888; text-decoration: none; transition: 0.3s; font-size: 14px; text-transform: uppercase; }
        .footer-links a:hover { color: var(--secondary); }
        .copyright { border-top: 1px solid #222; padding-top: 30px; color: #444; font-size: 12px; letter-spacing: 1px; }

        /* --- WHATSAPP FLUTUANTE --- */
        .whatsapp-float {
            position: fixed; bottom: 30px; right: 30px;
            background-color: #25d366; color: white;
            width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
            z-index: 2000; transition: 0.3s; text-decoration: none;
        }
        .whatsapp-float:hover { transform: scale(1.1); background-color: #1da851; }

        /* --- MODAL DE AGENDAMENTO --- */
        .booking-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 2000; overflow-y: auto; backdrop-filter: blur(5px); }
        .booking-content { background: #fff; margin: 5vh auto; width: 95%; max-width: 600px; border-radius: 5px; position: relative; padding: 0; overflow: hidden; animation: slideUp 0.3s; }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .booking-header { background: var(--primary); padding: 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .booking-body { padding: 30px; }
        
        .barber-option { display: flex; align-items: center; gap: 20px; padding: 15px; border: 2px solid #eee; border-radius: 5px; margin-bottom: 15px; cursor: pointer; transition: 0.3s; }
        .barber-option:hover, .barber-option.selected { border-color: var(--secondary); background: rgba(212, 175, 55, 0.05); }
        .barber-option img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
        
        .service-option { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; }
        .service-option input { width: 20px; height: 20px; accent-color: var(--secondary); }
        
        #timeSlots { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin-top: 15px; }
        .time-slot { padding: 10px; background: #eee; border: none; border-radius: 3px; cursor: pointer; font-weight: 600; font-size: 13px; }
        .time-slot:hover, .time-slot.selected { background: var(--secondary); color: white; }
        
        .confirmation-total { margin-top: 20px; font-size: 20px; font-weight: 700; color: var(--secondary); text-align: right; }
        
        .btn-secondary { background: #eee; color: #333; }
        .navigation-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .navigation-buttons button { flex: 1; }

        /* --- RESPONSIVO --- */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 32px; }
            .menu-toggle { display: block; }
            nav { position: fixed; top: 70px; left: -100%; width: 100%; height: calc(100vh - 70px); background: #1a1a1a; transition: 0.3s; flex-direction: column; justify-content: center; }
            nav.active { left: 0; }
            nav ul { flex-direction: column; }
            .about-text-full h3 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <img src="assets/LOGO LEGACY SF/2.png" alt="Legacy Style">
                    LEGACY <span>STYLE</span>
                </a>
                <div class="menu-toggle"><i class="fas fa-bars"></i></div>
                <nav>
                    <ul>
                        <li><a href="#home">Início</a></li>
                        <li><a href="#about">A Marca</a></li>
                        <li><a href="#barbers">Equipe</a></li>
                        <li><a href="#services">Serviços</a></li>
                        <li><a href="#contact">Contato</a></li>
                        
                        <?php if (isset($_SESSION['cliente_id'])): ?>
                            <li><a href="meus_agendamentos.php" class="login-link">
                                <i class="fas fa-user-circle"></i> Olá, <?= explode(' ', $_SESSION['cliente_nome'])[0] ?>
                            </a></li>
                        <?php else: ?>
                            <li><a href="entrar.php" class="login-link">
                                <i class="fas fa-sign-in-alt"></i> Agendar / Login
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1>Mais que um corte,<br>Um Legado.</h1>
                <p>Experiência premium em barbearia. Onde o estilo clássico encontra a técnica moderna.</p>
                <a href="#" class="btn" id="bookNowHero">Agendar Horário</a>
            </div>
        </div>
    </section>

    <section id="about">
        <div class="container">
            <div class="section-title">
                <h2>O Conceito Legacy</h2>
            </div>
            
            <div class="about-text-full">
                <h3>Resgatando a autoestima masculina com tradição e estilo.</h3>
                <p>A <strong>Legacy Style</strong> nasceu no final de 2024 da união de dois propósitos: a técnica apurada e o atendimento de excelência. Fundada por Cauã e Vitinho, nossa missão vai muito além de cortar cabelo. Queremos que cada homem que senta em nossa cadeira levante se sentindo mais confiante, renovado e pronto para conquistar seus objetivos.</p>
                <p>Aqui, a pressa fica da porta para fora. Valorizamos a resenha, o ambiente descontraído e o cuidado com os detalhes. Usamos produtos de primeira linha e técnicas que misturam a barbearia clássica com as tendências urbanas atuais.</p>

                <div class="values-grid">
                    <div class="value-item">
                        <i class="fas fa-cut value-icon"></i>
                        <h4>Precisão Técnica</h4>
                        <p>Nossos profissionais estão em constante evolução. Do degradê navalhado ao corte social na tesoura, garantimos simetria e acabamento impecável.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-glass-cheers value-icon"></i>
                        <h4>Ambiente Premium</h4>
                        <p>Mais que uma barbearia, um ponto de encontro. Música boa, ambiente climatizado e aquela resenha que faz você se sentir em casa.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-clock value-icon"></i>
                        <h4>Pontualidade</h4>
                        <p>Respeitamos o seu tempo. Com nosso sistema de agendamento online, você chega e é atendido, sem filas e sem espera desnecessária.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="barbers" id="barbers">
        <div class="container">
            <div class="section-title">
                <h2>Nossos Especialistas</h2>
            </div>
            <div class="barbers-grid">
                <div class="barber-card">
                    <div class="barber-img">
                        <img src="assets/fotocaua.png" alt="Barbeiro Cauã">
                    </div>
                    <div class="barber-info">
                        <h3>Cauã Silva</h3>
                        <p class="role">Co-Founder & Master Barber</p>
                        <a href="caua.php" class="btn" style="padding: 10px 25px; font-size: 13px;">Ver Perfil Completo</a>
                    </div>
                </div>

                <div class="barber-card">
                    <div class="barber-img">
                        <img src="assets/fotovitor.png" alt="Barbeiro Vitinho">
                    </div>
                    <div class="barber-info">
                        <h3>Vitinho</h3>
                        <p class="role">Co-Founder & Fade Specialist</p>
                        <a href="vitinho.php" class="btn" style="padding: 10px 25px; font-size: 13px;">Ver Perfil Completo</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" style="background: #f9f9f9;">
        <div class="container">
            <div class="section-title">
                <h2>Menu de Serviços</h2>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-cut service-icon"></i>
                    <h3>Corte Premium</h3>
                    <p>Consultoria de visagismo, corte e finalização com pomada.</p>
                    <span class="price">R$ 40,00</span>
                </div>
                <div class="service-card">
                    <i class="fas fa-user-tie service-icon"></i>
                    <h3>Barba Terapia</h3>
                    <p>Toalha quente, esfoliação e alinhamento dos fios.</p>
                    <span class="price">R$ 30,00</span>
                </div>
                <div class="service-card">
                    <i class="fas fa-crown service-icon"></i>
                    <h3>Combo Legacy</h3>
                    <p>A experiência completa: Cabelo + Barba + Bebida.</p>
                    <span class="price">R$ 80,00</span>
                </div>
                <div class="service-card">
                    <i class="fas fa-paint-brush service-icon"></i>
                    <h3>Pigmentação</h3>
                    <p>Acabamento perfeito para cobrir falhas e realçar o perfil.</p>
                    <span class="price">R$ 35,00</span>
                </div>
                <div class="service-card">
                    <i class="fas fa-bolt service-icon"></i>
                    <h3>Platinado</h3>
                    <p>Descoloração global segura com matização premium.</p>
                    <span class="price">R$ 100,00</span>
                </div>
                <div class="service-card">
                    <i class="fas fa-child service-icon"></i>
                    <h3>Kids</h3>
                    <p>Atendimento paciente e especializado para crianças.</p>
                    <span class="price">R$ 35,00</span>
                </div>
            </div>
            <div style="text-align: center; margin-top: 50px;">
                <a href="#" class="btn" id="bookNowServices">Garantir meu horário</a>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Feedback dos Clientes</h2>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Ambiente sensacional! O Cauã entende exatamente o que a gente pede. O corte ficou perfeito e o atendimento nota 10."</p>
                    <div class="testimonial-author">
                        <h4>Rafael Mendes</h4>
                        <span>Cliente Mensalista</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Finalmente achei uma barbearia que respeita o horário marcado. O sistema de agendamento é muito prático e o Vitinho manda muito no degradê."</p>
                    <div class="testimonial-author">
                        <h4>Gustavo Lima</h4>
                        <span>Cliente há 6 meses</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Qualidade absurda. Fiz o combo cabelo e barba e saí renovado. A toalha quente na barba faz toda a diferença. Recomendo!"</p>
                    <div class="testimonial-author">
                        <h4>André Ferreira</h4>
                        <span>Cliente Novo</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Onde Estamos</h2>
            </div>
            <div class="contact-container">
                <div class="contact-info">
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="info-text">
                            <h4>Localização</h4>
                            <p>Rua Pedro Gusso, 744<br>Capão Raso, Curitiba - PR</p>
                            <a href="https://maps.google.com/?q=Rua+Pedro+Gusso+744+Curitiba" target="_blank" style="color:var(--secondary); font-size:13px; font-weight:600;">Ver no Mapa</a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="info-text">
                            <h4>WhatsApp</h4>
                            <p>(41) 99988-8727 (Cauã)</p>
                            <p>(41) 98838-3629 (Vitinho)</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-clock"></i></div>
                        <div class="info-text">
                            <h4>Horários</h4>
                            <p>Seg - Sex: 09:00 às 20:00</p>
                            <p>Sábado: 08:00 às 17:30</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <form>
                        <input type="text" placeholder="Nome Completo">
                        <input type="email" placeholder="E-mail">
                        <textarea placeholder="Como podemos ajudar?"></textarea>
                        <button class="btn" style="width: 100%;">Enviar Mensagem</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-logo">LEGACY <span>STYLE</span></div>
            <div class="footer-links">
                <a href="#home">Home</a>
                <a href="#about">Conceito</a>
                <a href="entrar.php">Área do Cliente</a>
                <a href="login.php">Acesso Restrito</a>
            </div>
            <div class="social-links" style="justify-content: center;">
                <a href="https://instagram.com" target="_blank" style="background: #222; color: #fff;"><i class="fab fa-instagram"></i></a>
                <a href="https://facebook.com" target="_blank" style="background: #222; color: #fff;"><i class="fab fa-facebook"></i></a>
            </div>
            <div class="copyright">
                © 2024 Legacy Style Barbearia. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <a href="https://wa.me/5541999888727" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <div class="booking-modal" id="bookingModal">
        <div class="booking-content">
            <div class="booking-header">
                <h3>Agendar Horário</h3>
                <span class="close-booking"><i class="fas fa-times"></i></span>
            </div>
            
            <div class="booking-body">
                <div id="step1">
                    <p style="margin-bottom: 15px; color: #666;">Escolha o profissional:</p>
                    <div class="barber-selection">
                        <?php
                        $barbeiros = $pdo->query("SELECT * FROM barbeiros")->fetchAll();
                        foreach ($barbeiros as $barbeiro): 
                        ?>
                            <div class="barber-option" data-barber="<?= $barbeiro['id'] ?>">
                                <img src="assets/<?= $barbeiro['foto'] ?>" alt="<?= $barbeiro['nome'] ?>">
                                <div>
                                    <h4 style="margin:0; color: var(--primary);"><?= $barbeiro['nome'] ?></h4>
                                    <small style="color: #888;"><?= $barbeiro['especialidade'] ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="step2" style="display:none;">
                    <p style="margin-bottom: 15px; color: #666;">Selecione os serviços:</p>
                    <div class="service-selection">
                        <?php
                        $servicos = $pdo->query("SELECT * FROM servicos")->fetchAll();
                        foreach ($servicos as $servico): 
                        ?>
                            <label class="service-option">
                                <div>
                                    <h4 style="margin:0; font-size: 15px;"><?= $servico['nome'] ?></h4>
                                    <small style="color: #888;"><?= $servico['duracao'] ?> min • R$ <?= number_format($servico['preco'], 2, ',', '.') ?></small>
                                </div>
                                <input type="checkbox" 
                                    data-service="<?= $servico['id'] ?>" 
                                    data-duration="<?= $servico['duracao'] ?>"
                                    data-price="<?= $servico['preco'] ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="confirmation-total">
                        Total: <span id="totalPrice">R$ 0,00</span>
                    </div>
                    <div class="navigation-buttons">
                        <button class="btn btn-secondary" id="backToStep1">Voltar</button>
                        <button class="btn" id="nextToStep3">Continuar</button>
                    </div>
                </div>

                <div id="step3" style="display:none;">
                    <p style="margin-bottom: 15px; color: #666;">Escolha a data:</p>
                    <input type="date" id="appointmentDate" min="<?= date('Y-m-d') ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%;">
                    
                    <div id="timeSlotsContainer" style="margin-top: 20px;">
                        <p style="margin-bottom: 10px; font-weight: 600;">Horários disponíveis:</p>
                        <div id="timeSlots"></div>
                    </div>
                    
                    <button class="btn btn-secondary" id="backToStep2" style="width: 100%; margin-top: 20px;">Voltar</button>
                </div>

                <div id="step4" style="display:none;">
                    <div id="confirmationDetails" style="margin-bottom: 20px;"></div>
                    
                    <form method="POST" action="salvar_agendamento.php" class="client-form">
                        <input type="hidden" name="barbeiro_id" id="confirmBarbeiroId">
                        <input type="hidden" name="servicos" id="confirmServicosIds">
                        <input type="hidden" name="data" id="confirmData">
                        <input type="hidden" name="hora" id="confirmHora">
                        <input type="hidden" name="valor_total" id="valorTotal">
                        
                        <input type="text" name="nome" class="form-control" placeholder="Seu Nome Completo" required>
                        <input type="tel" name="telefone" class="form-control" placeholder="WhatsApp (41999999999)" required>
                        <input type="email" name="email" class="form-control" placeholder="Seu E-mail" required>
                        
                        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                            <p style="font-weight: 600; margin-bottom: 10px;">Forma de Pagamento:</p>
                            <label style="display: flex; gap: 10px; margin-bottom: 10px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="presencial" checked> Pagar na Barbearia
                            </label>
                            <label style="display: flex; gap: 10px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="pix"> Pagar agora (Pix/Cartão/MP)
                            </label>
                        </div>

                        <div id="redirectInfo" style="display: none; margin: 20px 0; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                            <h4 style="margin-bottom: 10px; color: #0d47a1;">Pagamento Seguro</h4>
                            <p style="font-size: 14px;">Você será redirecionado para a página do <strong>Mercado Pago</strong> para finalizar.</p>
                            <p style="font-size: 14px;">Lá você poderá escolher: <strong>Pix, Cartão de Crédito ou Saldo.</strong></p>
                        </div>

                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-secondary" id="backToStep3">Voltar</button>
                            <button type="submit" class="btn">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menu Mobile
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav');
        menuToggle.addEventListener('click', () => {
            nav.classList.toggle('active');
        });

        // Variáveis do Agendamento
        let selectedBarber, selectedServices = [], totalPrice = 0, totalDuration = 0;

        // Abrir Modal
        const openModalBtns = [document.getElementById('bookNowHero'), document.getElementById('bookNowServices')];
        openModalBtns.forEach(btn => {
            if(btn) btn.addEventListener('click', (e) => {
                e.preventDefault();
                document.getElementById('bookingModal').style.display = 'block';
                resetBooking();
            });
        });

        // Fechar Modal
        document.querySelector('.close-booking').addEventListener('click', () => {
            document.getElementById('bookingModal').style.display = 'none';
        });

        function resetBooking() {
            selectedBarber = null; selectedServices = []; totalPrice = 0; totalDuration = 0;
            document.querySelectorAll('.barber-option').forEach(el => el.classList.remove('selected'));
            document.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false);
            updatePrice();
            goToStep(1);
        }

        function goToStep(step) {
            [1, 2, 3, 4].forEach(s => document.getElementById(`step${s}`).style.display = 'none');
            document.getElementById(`step${step}`).style.display = 'block';
        }

        // Lógica Passo 1: Barbeiro
        document.querySelectorAll('.barber-option').forEach(opt => {
            opt.addEventListener('click', function() {
                document.querySelectorAll('.barber-option').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');
                selectedBarber = this.getAttribute('data-barber');
                goToStep(2);
            });
        });

        // Lógica Passo 2: Serviços
        document.querySelectorAll('.service-option input').forEach(chk => {
            chk.addEventListener('change', function() {
                const price = parseFloat(this.getAttribute('data-price'));
                const duration = parseInt(this.getAttribute('data-duration'));
                const id = this.getAttribute('data-service');
                
                if(this.checked) {
                    selectedServices.push(id);
                    totalPrice += price;
                    totalDuration += duration;
                } else {
                    selectedServices = selectedServices.filter(s => s !== id);
                    totalPrice -= price;
                    totalDuration -= duration;
                }
                updatePrice();
            });
        });

        function updatePrice() {
            document.getElementById('totalPrice').innerText = `R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
        }

        document.getElementById('backToStep1').addEventListener('click', () => goToStep(1));
        document.getElementById('nextToStep3').addEventListener('click', () => {
            if(selectedServices.length === 0) return alert('Selecione ao menos um serviço.');
            goToStep(3);
            setupDate();
        });

        // Lógica Passo 3: Data e Hora
        function setupDate() {
            const dateInput = document.getElementById('appointmentDate');
            dateInput.addEventListener('change', function() {
                if(!this.value) return;
                const container = document.getElementById('timeSlots');
                container.innerHTML = 'Carregando...';
                
                fetch(`get_horarios.php?barbeiro_id=${selectedBarber}&data=${this.value}&duracao=${totalDuration}`)
                .then(r => r.json())
                .then(times => {
                    container.innerHTML = '';
                    if(!times.length) container.innerHTML = 'Sem horários livres.';
                    times.forEach(time => {
                        const btn = document.createElement('div');
                        btn.className = 'time-slot';
                        btn.innerText = time;
                        btn.onclick = () => confirmTime(this.value, time);
                        container.appendChild(btn);
                    });
                });
            });
        }

        function confirmTime(date, time) {
            document.getElementById('confirmBarbeiroId').value = selectedBarber;
            document.getElementById('confirmServicosIds').value = selectedServices.join(',');
            document.getElementById('confirmData').value = date;
            document.getElementById('confirmHora').value = time;
            document.getElementById('valorTotal').value = totalPrice;

            // Preenche o resumo visual
            document.getElementById('confirmationDetails').innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #eee;"><span>Data:</span> <strong>${date.split('-').reverse().join('/')} às ${time}</strong></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #eee;"><span>Serviços:</span> <strong>${selectedServices.length} selecionados</strong></div>
                <div class="confirmation-total">Total: R$ ${totalPrice.toFixed(2).replace('.', ',')}</div>
            `;
            goToStep(4);
        }

        // Lógica de Pagamento e Redirecionamento
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const redirectInfo = document.getElementById('redirectInfo');
                
                if (this.value === 'pix') {
                    redirectInfo.style.display = 'block';
                    
                    // Simulação visual de desconto
                    const discountedPrice = totalPrice * 0.95; 
                    document.getElementById('valorTotal').value = discountedPrice;
                    document.querySelector('.confirmation-total').innerHTML = 
                        `Total: <span style="text-decoration: line-through; font-size: 0.8em; color: #999;">R$ ${totalPrice.toFixed(2).replace('.', ',')}</span> 
                        R$ ${discountedPrice.toFixed(2).replace('.', ',')}`;
                } else {
                    redirectInfo.style.display = 'none';
                    document.getElementById('valorTotal').value = totalPrice;
                    document.querySelector('.confirmation-total').innerHTML = 
                        `Total: R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
                }
            });
        });

        document.getElementById('backToStep2').addEventListener('click', () => goToStep(2));
        document.getElementById('backToStep3').addEventListener('click', () => goToStep(3));
    });

    // Função Alerta
    function showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = message;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3500);
    }

    // Verificar mensagens na URL
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('agendamento') === 'sucesso') showAlert(urlParams.get('mensagem'), 'success');
    if(urlParams.get('agendamento') === 'erro') showAlert(urlParams.get('mensagem'), 'error');
    </script>
</body>
</html>