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
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="shortcut icon" href="assets/LOGO LEGACY SF/1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- VARIAVEIS E RESET --- */
        :root {
            --primary: #111111;
            --secondary: #d4af37; /* Dourado Premium */
            --secondary-hover: #b59530;
            --light-gray: #f4f4f4;
            --dark-gray: #1f1f1f;
            --text-color: #333;
            --text-light: #aaa;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        
        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: #fff; 
            color: var(--text-color); 
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* --- UTILITARIOS --- */
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .section-padding { padding: 100px 0; }
        .text-center { text-align: center; }
        .gold-text { color: var(--secondary); }
        
        .btn {
            display: inline-block;
            background: var(--secondary);
            color: #000;
            padding: 16px 40px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        .btn:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }
        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }
        .btn-outline:hover {
            background: var(--secondary);
            color: #000;
        }

        /* --- HEADER --- */
        header {
            background: rgba(0, 0, 0, 0.95);
            padding: 20px 0;
            position: fixed;
            width: 100%;
            z-index: 1000;
            border-bottom: 1px solid #333;
            transition: 0.3s;
        }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        
        .logo { font-size: 24px; font-weight: 800; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; display: flex; align-items: center; gap: 10px; }
        .logo span { color: var(--secondary); }
        .logo img { height: 50px; }

        nav ul { display: flex; list-style: none; gap: 30px; }
        nav a { color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        nav a:hover { color: var(--secondary); }
        
        .login-btn-menu {
            border: 1px solid var(--secondary);
            padding: 8px 20px;
            border-radius: 30px;
            color: var(--secondary) !important;
        }
        .login-btn-menu:hover { background: var(--secondary); color: #000 !important; }

        .menu-toggle { display: none; color: #fff; font-size: 24px; cursor: pointer; }

        /* --- HERO SECTION (PARALLAX) --- */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
            padding-top: 80px;
            overflow: hidden;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.6)), url('assets/interior1.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(32px, 6vw, 60px);
            margin-bottom: 20px;
            line-height: 1.1;
        }
        .hero p {
            font-size: clamp(14px, 2.2vw, 20px);
            margin-bottom: 40px;
            color: #ddd;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* --- FEATURE CARDS --- */
        .features-wrapper {
            margin-top: -80px;
            position: relative;
            z-index: 10;
            padding-bottom: 50px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .feature-card {
            background: #fff;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: 0.3s;
            border-bottom: 4px solid transparent;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            border-bottom-color: var(--secondary);
        }
        .feature-icon {
            font-size: 40px;
            color: var(--secondary);
            margin-bottom: 20px;
        }
        .feature-card h3 { font-size: 20px; margin-bottom: 15px; text-transform: uppercase; }
        .feature-card p { font-size: 14px; color: #666; }

        /* --- SOBRE --- */
        .about-section { background-color: #fff; }
        .about-grid { display: flex; align-items: center; gap: 50px; flex-wrap: wrap; }
        .about-content { flex: 1; min-width: 300px; }
        .about-title-small { color: var(--secondary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 14px; margin-bottom: 10px; display: block; }
        .about-content h2 { font-size: 36px; line-height: 1.2; margin-bottom: 25px; color: var(--primary); }
        .about-content p { margin-bottom: 20px; color: #555; font-size: 16px; }
        
        .stats-grid { 
            display: flex; 
            gap: 40px; 
            margin-top: 30px; 
            border-top: 1px solid #eee; 
            padding-top: 30px; 
        }
        .stat-item strong { display: block; font-size: 32px; color: var(--primary); font-weight: 800; }
        .stat-item span { font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 1px; }

        /* --- SERVIÇOS --- */
        .services-section { background-color: #111; color: #fff; background-image: url('assets/pattern-dark.png'); }
        .section-header { text-align: center; margin-bottom: 60px; }
        .section-header h2 { font-size: 36px; margin-bottom: 15px; color: #fff; }
        .section-header .divider { height: 3px; width: 70px; background: var(--secondary); margin: 0 auto; }
        
        .services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; }
        .service-card {
            background: #1f1f1f;
            padding: 35px;
            border-radius: 5px;
            border: 1px solid #333;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }
        .service-card:hover { border-color: var(--secondary); transform: translateY(-5px); }
        .service-card i { font-size: 35px; color: var(--secondary); margin-bottom: 20px; }
        .service-card h3 { font-size: 20px; margin-bottom: 10px; color: #fff; }
        .service-card p { color: #888; font-size: 14px; margin-bottom: 20px; }
        .service-card .price { font-size: 24px; font-weight: 700; color: #fff; }

        /* --- BARBEIROS --- */
        .barbers-section { background-color: #f9f9f9; }
        .barbers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 50px;
        }
        .barber-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .barber-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .barber-img { height: 380px; overflow: hidden; background: #eee; position: relative; }
        .barber-img img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .barber-card:hover .barber-img img { transform: scale(1.05); }
        .barber-info { padding: 30px; text-align: center; }
        .barber-info h3 { font-size: 22px; margin-bottom: 5px; }
        .barber-info span { color: var(--secondary); font-size: 13px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 15px; }
        .social-icons a { color: #333; margin: 0 5px; font-size: 18px; transition: 0.3s; text-decoration: none; list-style: none; }
        .social-icons a:hover { color: var(--secondary); }

        /* --- DEPOIMENTOS --- */
        .testimonials-section { padding: 80px 0; background: #fff; text-align: center; }
        .testimonial-slider { display: flex; gap: 30px; overflow-x: auto; padding: 20px 0; scroll-snap-type: x mandatory; }
        .testimonial-item { 
            min-width: 300px; 
            flex: 1;
            background: #fff; 
            padding: 30px; 
            border: 2px solid #f4f4f4; 
            border-radius: 10px;
            scroll-snap-align: center;
        }
        .stars { color: var(--secondary); margin-bottom: 15px; }
        .quote { font-style: italic; color: #555; margin-bottom: 20px; font-size: 15px; }
        .client-name { font-weight: 700; font-size: 14px; text-transform: uppercase; }

        /* --- CONTATO E MAPA --- */
        .contact-section { background: #111; color: #fff; }
        .contact-wrapper { display: flex; flex-wrap: wrap; }
        .contact-info { flex: 1; padding: 80px 50px; min-width: 300px; }
        .contact-map { flex: 1; min-width: 300px; height: 500px; background: #222; }
        
        .info-row { display: flex; gap: 20px; margin-bottom: 30px; }
        .info-row i { font-size: 24px; color: var(--secondary); margin-top: 5px; }
        .info-row h4 { font-size: 18px; margin-bottom: 5px; color: #fff; }
        .info-row p { color: #888; font-size: 15px; }

        /* --- FOOTER --- */
        footer { background: #000; color: #777; padding: 30px 0; text-align: center; font-size: 13px; border-top: 1px solid #222; }

        /* --- MODAL (NOVO DESIGN CLEAN) --- */
        .booking-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 2000; overflow-y: auto; backdrop-filter: blur(8px); }
        .booking-content { background: #fff; width: 95%; max-width: 500px; margin: 5vh auto; border-radius: 12px; overflow: hidden; animation: fadeInUp 0.4s ease; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        
        .booking-header { background: #1a1a1a; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .booking-header h3 { color: #fff; margin: 0; font-size: 18px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .close-booking { color: var(--secondary); font-size: 28px; cursor: pointer; line-height: 1; }
        
        .booking-body { padding: 30px; }
        .step-indicator { font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; display: block; }
        .step-title { font-size: 24px; color: var(--primary); margin-bottom: 25px; font-weight: 700; }

        /* Cards do Modal */
        .option-card {
            border: 2px solid #f0f0f0; border-radius: 10px; padding: 15px; margin-bottom: 15px;
            display: flex; align-items: center; gap: 15px; cursor: pointer; transition: 0.2s;
        }
        .option-card:hover, .option-card.selected { border-color: var(--secondary); background: #fffdf5; }
        .option-card img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
        
        /* Botões Modal */
        .modal-btn { width: 100%; padding: 15px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; margin-top: 10px; font-size: 16px; }
        .btn-next { background: var(--primary); color: #fff; }
        .btn-back { background: #f0f0f0; color: #555; margin-top: 10px; }

        /* --- RESPONSIVO --- */
        @media (max-width: 768px) {
            .hero h1 { font-size: 40px; }
            .features-wrapper { margin-top: 0; padding-top: 50px; background: #f9f9f9; }
            .menu-toggle { display: block; }
            nav { position: fixed; top: 70px; right: -100%; width: 80%; height: 100vh; background: #000; flex-direction: column; padding: 40px; transition: 0.4s; }
            nav.active { right: 0; }
            nav ul { flex-direction: column; }
            .about-grid { flex-direction: column; }
            .contact-wrapper { flex-direction: column; }
            .contact-map { height: 300px; }
        }
        
        /* Whats Flutuante */
        .whatsapp-float { position: fixed; bottom: 25px; text-decoration: none; right: 25px; background: #25d366; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 2000; transition: 0.3s; }
        .whatsapp-float:hover { transform: scale(1.1); }
        
        /* Estilos extras para o modal */
        #timeSlots { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px; }
        .time-slot { background: #f9f9f9; border: 1px solid #ddd; padding: 10px; border-radius: 6px; text-align: center; font-size: 13px; font-weight: 600; cursor: pointer; }
        .time-slot:hover, .time-slot.selected { background: var(--secondary); color: #000; border-color: var(--secondary); }
        .service-check-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        
        .input-group { width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .input-group input { width: 100%; border: none; outline: none; font-size: 14px; background: transparent; }
    </style>
</head>
<body>

    <header>
        <div class="container header-content">
            <a href="login.php" class="logo"><img src="assets/LOGO LEGACY SF/2.png" alt="Legacy"> LEGACY <span>STYLE</span></a>
            <div class="menu-toggle"><i class="fas fa-bars"></i></div>
            <nav>
                <ul>
                    <li><a href="#home">Início</a></li>
                    <li><a href="#about">Sobre</a></li>
                    <li><a href="#services">Serviços</a></li>
                    <li><a href="#barbers">Barbeiros</a></li>
                    <li><a href="#contact">Contato</a></li>
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li><a href="meus_agendamentos.php" class="login-btn-menu"><i class="fas fa-user"></i> Minha Conta</a></li>
                    <?php else: ?>
                        <li><a href="entrar.php" class="login-btn-menu">Entrar</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero" id="home">
        <div class="container">
            <p style="letter-spacing: 3px; font-size: 14px; text-transform: uppercase; margin-bottom: 10px;">Estilo & Tradição desde 2024</p>
            <h1>MAIS QUE UM CORTE,<br>UM LEGADO.</h1>
            <p>Descubra a melhor experiência em barbearia de Curitiba. Ambiente exclusivo, profissionais de elite e atendimento premium.</p>
            <div style="margin-top: 30px;">
                <a href="#" class="btn" id="bookNowHero">Agendar Horário</a>
            </div>
        </div>
    </section>

    <div class="features-wrapper">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-cut feature-icon"></i>
                    <h3>Técnica Apurada</h3>
                    <p>Dominamos do clássico ao moderno com precisão milimétrica e acabamento perfeito.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-glass-whiskey feature-icon"></i>
                    <h3>Ambiente Premium</h3>
                    <p>Espaço climatizado, cerveja gelada, café e aquela resenha que faz você se sentir em casa.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-clock feature-icon"></i>
                    <h3>Pontualidade</h3>
                    <p>Respeito absoluto ao seu tempo. Sistema de agendamento online rápido e sem filas.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="section-padding about-section" id="about">
        <div class="container about-grid">
            <div class="about-content">
                <span class="about-title-small">Nossa História</span>
                <h2>Onde a tradição encontra o estilo moderno.</h2>
                <p>A <strong>Legacy Style</strong> nasceu da união de dois propósitos: a paixão pela barbearia clássica e a necessidade de inovação. Fundada por Cauã e Vitinho, nossa missão vai além da estética. Queremos resgatar a autoestima masculina através de um serviço de excelência.</p>
                <p>Aqui, cada detalhe importa. Desde a toalha quente na barba até a finalização do penteado. Não vendemos apenas cortes, vendemos confiança para você enfrentar o dia a dia.</p>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <strong>+2000</strong>
                        <span>Cortes Realizados</span>
                    </div>
                    <div class="stat-item">
                        <strong>100%</strong>
                        <span>Satisfação</span>
                    </div>
                    <div class="stat-item">
                        <strong>4.9</strong>
                        <span>Avaliação Google</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding services-section" id="services">
        <div class="container">
            <div class="section-header">
                <h2>Nossos Serviços</h2>
                <div class="divider"></div>
            </div>
            <div class="services-grid">
                <?php
                $servicos = $pdo->query("SELECT * FROM servicos")->fetchAll();
                foreach($servicos as $s):
                ?>
                <div class="service-card">
                    <i class="fas fa-cut"></i> <h3><?= $s['nome'] ?></h3>
                    <p></p>
                    <div class="price">R$ <?= number_format($s['preco'], 2, ',', '.') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-padding barbers-section" id="barbers">
        <div class="container">
            <div class="section-header">
                <h2 style="color: #111;">Equipe de Elite</h2>
                <div class="divider"></div>
            </div>
            <div class="barbers-grid">
                <?php
                $barbeiros = $pdo->query("SELECT * FROM barbeiros")->fetchAll();
                foreach($barbeiros as $b):
                ?>
                <div class="barber-card">
                    <div class="barber-img">
                        <img src="assets/<?= $b['foto'] ?>" alt="<?= $b['nome'] ?>">
                    </div>
                    <div class="barber-info">
                        <h3><?= $b['nome'] ?></h3>
                        <span><?= $b['especialidade'] ?></span>
                        <div class="social-icons">
                            <a href="#" aria-label="Instagram <?= htmlspecialchars($b['nome']) ?>"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="WhatsApp <?= htmlspecialchars($b['nome']) ?>"><i class="fab fa-whatsapp"></i></a>
                        </div>
                        <a href="<?= $b['id'] == 1 ? 'caua.php' : 'vitinho.php' ?>" class="btn btn-outline" style="margin-top: 20px; padding: 10px 20px; font-size: 12px;">Ver Perfil</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 style="color: #111;">O que dizem os clientes</h2>
            </div>
            <div class="testimonial-slider">
                <div class="testimonial-item">
                    <div class="stars">★★★★★</div>
                    <p class="quote">"Melhor barbearia da região! O Vitinho manda muito no degradê. Ambiente top demais."</p>
                    <span class="client-name">- Gabriel S.</span>
                </div>
                <div class="testimonial-item">
                    <div class="stars">★★★★★</div>
                    <p class="quote">"Atendimento impecável do Cauã. O sistema de agendamento funciona muito bem, sem atrasos."</p>
                    <span class="client-name">- Lucas O.</span>
                </div>
                <div class="testimonial-item">
                    <div class="stars">★★★★★</div>
                    <p class="quote">"Fiz o combo barba e cabelo e saí renovado. A toalha quente é um diferencial. Recomendo!"</p>
                    <span class="client-name">- Ricardo A.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-section" id="contact">
        <div class="contact-wrapper">
            <div class="contact-info">
                <span class="about-title-small">Visite-nos</span>
                <h2 style="margin-bottom: 40px;">Estamos esperando por você.</h2>
                
                <div class="info-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Endereço</h4>
                        <p>Rua Pedro Gusso, 744 - Capão Raso<br>Curitiba - PR</p>
                    </div>
                </div>
                <div class="info-row">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Horários</h4>
                        <p>Seg a Sex: 09:00 - 20:00</p>
                        <p>Sábado: 08:00 - 17:30</p>
                    </div>
                </div>
                <div class="info-row">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h4>Contato</h4>
                        <p>(41) 99988-8727</p>
                        <p>legacystyle@gmail.com</p>
                    </div>
                </div>
            </div>
            <div class="contact-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3601.23456789!2d-49.29!3d-25.50!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjXCsDMwJzAwLjAiUyA0OcKwMTcnMDAuMCJX!5e0!3m2!1spt-BR!2sbr!4v1600000000000!5m2!1spt-BR!2sbr" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="logo" style="justify-content: center; margin-bottom: 20px;">LEGACY <span>STYLE</span></div>
            <p>© 2024 Legacy Style Barbearia. Todos os direitos reservados.</p>
        </div>
    </footer>

    <a href="https://wa.me/5541999888727" class="whatsapp-float" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>

    <div class="booking-modal" id="bookingModal">
        <div class="booking-content">
            <div class="booking-header">
                <h3>Agendar Horário</h3>
                <span class="close-booking" role="button" aria-label="Fechar">×</span>
            </div>
            <div class="booking-body">
                <div id="step1">
                    <span class="step-indicator">Passo 1 de 4</span>
                    <h4 class="step-title">Escolha o Profissional</h4>
                    <div class="barber-selection">
                        <?php foreach($barbeiros as $b): ?>
                        <div class="option-card barber-option" data-barber="<?= $b['id'] ?>">
                            <img src="assets/<?= $b['foto'] ?>" alt="<?= htmlspecialchars($b['nome']) ?>">
                            <div>
                                <h4 style="margin:0; font-size:16px; font-weight:700; color:#333;"><?= $b['nome'] ?></h4>
                                <span style="font-size:12px; color:#777;"><?= $b['especialidade'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="step2" style="display:none;">
                    <span class="step-indicator">Passo 2 de 4</span>
                    <h4 class="step-title">Serviços</h4>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <?php foreach($servicos as $s): ?>
                        <div class="service-check-item">
                            <label style="display:flex; align-items:center; width:100%; cursor:pointer;">
                                <input type="checkbox" style="width:18px; height:18px; margin-right:10px; accent-color:var(--secondary);"
                                    data-service="<?= $s['id'] ?>" 
                                    data-duration="<?= $s['duracao'] ?>" 
                                    data-price="<?= $s['preco'] ?>">
                                <div>
                                    <div style="font-weight:600; font-size:14px;"><?= $s['nome'] ?></div>
                                    <div style="font-size:12px; color:#888;">R$ <?= number_format($s['preco'], 2, ',', '.') ?> • <?= $s['duracao'] ?> min</div>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align:right; margin-top:15px; font-weight:700;">Total: <span id="totalPrice" style="color:var(--secondary);">R$ 0,00</span></div>
                    <button class="modal-btn btn-next" id="nextToStep3">Continuar</button>
                    <button class="modal-btn btn-back" onclick="goToStep(1)">Voltar</button>
                </div>

                <div id="step3" style="display:none;">
                    <span class="step-indicator">Passo 3 de 4</span>
                    <h4 class="step-title">Data e Hora</h4>
                    <input type="date" id="appointmentDate" class="form-control" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:6px;" min="<?= date('Y-m-d') ?>">
                    <div id="timeSlotsContainer" style="display:none; margin-top:15px;">
                        <span style="font-size:12px; font-weight:700;">Horários:</span>
                        <div id="timeSlots"></div>
                    </div>
                    <button class="modal-btn btn-back" onclick="goToStep(2)">Voltar</button>
                </div>

                <div id="step4" style="display:none;">
                    <span class="step-indicator">Passo 4 de 4</span>
                    <h4 class="step-title">Finalizar</h4>
                    
                    <form action="salvar_agendamento.php" method="POST">
                        <input type="hidden" name="barbeiro_id" id="inpBarbeiro">
                        <input type="hidden" name="servicos" id="inpServicos">
                        <input type="hidden" name="data" id="inpData">
                        <input type="hidden" name="hora" id="inpHora">
                        <input type="hidden" name="valor_total" id="inpValor">

                        <div class="input-group">
                            <input type="text" name="nome" placeholder="Nome Completo" required>
                        </div>
                        <div class="input-group">
                            <input type="tel" name="telefone" placeholder="WhatsApp (DDD+Num)" required>
                        </div>
                        
                        <div class="input-group">
                            <input type="text" name="cpf" placeholder="CPF (Apenas números)" required maxlength="14">
                        </div>

                        <div class="input-group">
                            <input type="email" name="email" placeholder="E-mail" required>
                        </div>

                        <div style="background:#f4f4f4; padding:15px; border-radius:8px; margin-bottom:20px;">
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin-bottom:10px;">
                                <input type="radio" name="payment_method" value="presencial" checked> <span>Pagar na Barbearia</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="pix"> <span>Pagar Online (Pix/Cartão)</span>
                            </label>
                        </div>

                        <div id="redirectMsg" style="display:none; padding:10px; background:#e3f2fd; font-size:12px; margin-bottom:15px; border-radius:5px; color:#0d47a1;">
                            Você será redirecionado para o <strong>Mercado Pago</strong> para finalizar.
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn-back" onclick="changeStep(3)">Voltar</button>
                            <button type="submit" class="btn-next">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- FUNÇÕES DO MODAL ---
        const modal = document.getElementById('bookingModal');
        const openBtns = [document.getElementById('bookNowHero'), document.getElementById('bookNowServices')].filter(Boolean);
        let currentBarber = null;
        let currentServices = [];
        let currentPrice = 0;
        let currentDuration = 0;

        function openModal() {
            if (!modal) return;
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            goToStep(1);
        }

        function closeModal() {
            if (!modal) return;
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        openBtns.forEach(btn => btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        }));

        const closeBtn = document.querySelector('.close-booking');
        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
        
        const menuToggle = document.querySelector('.menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                const nav = document.querySelector('nav');
                if (nav) nav.classList.toggle('active');
            });
        }

        function goToStep(step) {
            document.querySelectorAll('[id^="step"]').forEach(el => el.style.display = 'none');
            const target = document.getElementById(`step${step}`);
            if (target) target.style.display = 'block';
        }

        function changeStep(step) { goToStep(step); }

        const barberOptions = document.querySelectorAll('.barber-option') || [];
        barberOptions.forEach(opt => {
            opt.addEventListener('click', function() {
                barberOptions.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                currentBarber = this.dataset.barber || null;
                goToStep(2);
            });
        });

        const serviceCheckboxes = document.querySelectorAll('input[type="checkbox"][data-service]') || [];
        serviceCheckboxes.forEach(chk => {
            chk.addEventListener('change', function() {
                const price = parseFloat(this.dataset.price) || 0;
                const duration = parseInt(this.dataset.duration) || 0;
                const id = String(this.dataset.service || '');
                if (this.checked) {
                    if (!currentServices.includes(id)) currentServices.push(id);
                    currentPrice += price;
                    currentDuration += duration;
                } else {
                    currentServices = currentServices.filter(s => s !== id);
                    currentPrice -= price;
                    currentDuration -= duration;
                }
                const totalEl = document.getElementById('totalPrice');
                if (totalEl) totalEl.innerText = `R$ ${currentPrice.toFixed(2).replace('.', ',')}`;
            });
        });

        const nextToStep3 = document.getElementById('nextToStep3');
        if (nextToStep3) {
            nextToStep3.addEventListener('click', () => {
                if (currentServices.length === 0) return alert('Selecione pelo menos um serviço.');
                if (!currentBarber) return alert('Selecione um profissional.');
                goToStep(3);
            });
        }

        const dateInput = document.getElementById('appointmentDate');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                if (!this.value) return;
                const container = document.getElementById('timeSlots');
                const containerWrap = document.getElementById('timeSlotsContainer');
                if (containerWrap) containerWrap.style.display = 'block';
                if (!container) return;
                container.innerHTML = 'Carregando...';
                const barberId = encodeURIComponent(currentBarber || '');
                const data = encodeURIComponent(this.value);
                const dur = encodeURIComponent(currentDuration || 0);
                fetch(`get_horarios.php?barbeiro_id=${barberId}&data=${data}&duracao=${dur}`)
                .then(r => r.json())
                .then(times => {
                    container.innerHTML = '';
                    if (!Array.isArray(times) || times.length === 0) {
                        container.innerHTML = '<span style="color:red; grid-column:1/-1;">Sem horários.</span>';
                        return;
                    }
                    times.forEach(t => {
                        const btn = document.createElement('div'); 
                        btn.className = 'time-slot'; 
                        btn.innerText = t;
                        
                        btn.onclick = () => {
                            const confirmBarb = document.getElementById('inpBarbeiro');
                            const confirmServ = document.getElementById('inpServicos');
                            const confirmData = document.getElementById('inpData');
                            const confirmHora = document.getElementById('inpHora');
                            const valorTotal  = document.getElementById('inpValor');

                            if (confirmBarb) confirmBarb.value = currentBarber || '';
                            if (confirmServ) confirmServ.value = currentServices.join(',');
                            if (confirmData) confirmData.value = dateInput.value;
                            if (confirmHora) confirmHora.value = t;
                            if (valorTotal)  valorTotal.value = currentPrice.toFixed(2);

                            goToStep(4);
                        };
                        container.appendChild(btn);
                    });
                })
                .catch(() => {
                    container.innerHTML = '<span style="color:red;">Erro ao carregar horários.</span>';
                });
            });
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const redirectInfo = document.getElementById('redirectMsg');
                const valorTotal = document.getElementById('inpValor');
                if (this.value === 'pix') {
                    if (redirectInfo) redirectInfo.style.display = 'block';
                    if (valorTotal) valorTotal.value = currentPrice.toFixed(2);
                } else {
                    if (redirectInfo) redirectInfo.style.display = 'none';
                    if (valorTotal) valorTotal.value = currentPrice.toFixed(2);
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Verifica se existe mensagem na URL (ex: index.php?agendamento=sucesso)
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('agendamento');
            const mensagem = urlParams.get('mensagem');

            if (status && mensagem) {
                // Cria o alerta tipo TOAST (notificação de canto)
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000, // Fica 4 segundos
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                if (status === 'sucesso') {
                    // Notificação VERDE de Sucesso
                    Toast.fire({
                        icon: 'success',
                        title: 'Tudo certo!',
                        text: mensagem
                    });
                } else if (status === 'erro') {
                    // Notificação VERMELHA de Erro
                    Toast.fire({
                        icon: 'error',
                        title: 'Ops!',
                        text: mensagem
                    });
                }
                
                // Limpa a URL para não mostrar o alerta de novo se der F5
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>w