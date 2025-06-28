<?php
require 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitinho - Especialista em Degradês | Legacy Style</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #1a1a1a;
            --secondary: #d4af37;
            --light: #f5f5f5;
            --dark: #121212;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            background-color: var(--primary);
            color: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content p {
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            gap: 10px;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .logo img {
            height: 50px;
            vertical-align: middle;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Hero Section */
        .barber-hero {
            padding-top: 100px;
            background: linear-gradient(135deg, rgba(26,26,26,0.9) 0%, rgba(26,26,26,0.7) 100%), url('assets/bg-vitinho.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding-bottom: 60px;
        }
        
        .barber-hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .barber-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--secondary);
        }
        
        .barber-hero p {
            font-size: 1.1rem;
            margin-bottom: 25px;
        }
        
        .barber-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--secondary);
            margin-bottom: 20px;
        }
        
        .specialty-badge {
            display: inline-block;
            background-color: var(--secondary);
            color: var(--primary);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(212, 175, 55, 0.2);
            color: var(--secondary);
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .social-link:hover {
            background-color: var(--secondary);
            color: var(--primary);
            transform: translateY(-3px);
        }
        
        /* About Section */
        .about-section {
            padding: 60px 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .section-title h2 {
            font-size: 2rem;
            color: var(--primary);
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--secondary);
        }
        
        .about-content {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: center;
        }
        
        .about-text {
            flex: 1;
            min-width: 300px;
        }
        
        .about-text h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .about-text p {
            margin-bottom: 15px;
        }
        
        .skills {
            margin-top: 20px;
        }
        
        .skill-item {
            margin-bottom: 15px;
        }
        
        .skill-name {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .skill-bar {
            height: 8px;
            background-color: #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .skill-progress {
            height: 100%;
            background-color: var(--secondary);
            border-radius: 4px;
        }
        
        .about-image {
            flex: 1;
            min-width: 300px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Portfolio Section */
        .portfolio-section {
            padding: 60px 0;
            background-color: var(--light);
        }
        
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .portfolio-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .portfolio-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .portfolio-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .portfolio-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(26,26,26,0.9) 0%, transparent 100%);
            padding: 20px;
            color: white;
            transform: translateY(100%);
            transition: all 0.3s;
        }
        
        .portfolio-item:hover .portfolio-overlay {
            transform: translateY(0);
        }
        
        .portfolio-title {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        
        .portfolio-category {
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        /* Video Section */
        .video-section {
            padding: 60px 0;
            background-color: white;
            text-align: center;
        }
        
        .video-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .video-container video {
            width: 100%;
            display: block;
        }
        
        /* Testimonials Section */
        .testimonials-section {
            padding: 60px 0;
            background-color: var(--light);
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .testimonial-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            position: relative;
        }
        
        .testimonial-text::before {
            content: '"';
            font-size: 60px;
            color: var(--secondary);
            opacity: 0.2;
            position: absolute;
            top: -20px;
            left: -10px;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .author-info h4 {
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .author-info p {
            color: #777;
            font-size: 0.9rem;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            color: white;
            text-align: center;
        }
        
        .cta-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .cta-title {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--secondary);
        }
        
        .cta-text {
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--secondary);
            color: var(--primary);
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background-color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-whatsapp {
            background-color: #25D366;
            color: white;
        }
        
        .btn-whatsapp:hover {
            background-color: #1da851;
            color: white;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        .footer-content p {
            margin-bottom: 15px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--secondary);
        }
        
        .social-links-footer {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .social-links-footer a {
            color: white;
            font-size: 20px;
            transition: color 0.3s;
        }
        
        .social-links-footer a:hover {
            color: var(--secondary);
        }
        
        .copyright {
            color: #aaa;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .barber-hero h1 {
                font-size: 2rem;
            }
            
            .about-content {
                flex-direction: column;
            }
            
            .portfolio-grid {
                grid-template-columns: 1fr;
            }
            
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <img src="assets/LOGO LEGACY SF/2.png" alt="Legacy Style">
                    <p><span>LEGACY</p></span><p>STYLE</p>
                </a>
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="barber-hero">
        <div class="barber-hero-content">
            <img src="assets/fotovitor.png" alt="Vitinho" class="barber-avatar">
            <h1>Vitor "Vitinho"</h1>
            <span class="specialty-badge">Especialista em Degradês</span>
            <p>Mestre na arte do degradê perfeito, transformando cortes em verdadeiras obras de arte.</p>
            
            <div class="social-links">
                <a href="https://wa.me/5541988383629" class="social-link" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://www.instagram.com/ovitinhobarber/" class="social-link" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-link">
                    <i class="fas fa-calendar-alt"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Sobre o Barbeiro</h2>
            </div>
            
            <div class="about-content">
                <div class="about-text">
                    <h3>Conheça Vitinho</h3>
                    <p>Com 3 anos de experiência no mercado, Vitinho é conhecido por sua precisão nos degradês e penteados clássicos. Sua paixão pela barbearia começou cedo, aprendendo com os melhores profissionais da região.</p>
                    <p>Especializado em técnicas de degradê navalhado e cortes milimétricos, Vitinho traz para cada cliente um atendimento personalizado, entendendo suas necessidades e superando expectativas para criar um visual único e marcante.</p>
                    
                    <div class="skills">
                        <div class="skill-item">
                            <div class="skill-name">
                                <span>Degradê Navalhado</span>
                                <span>98%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: 98%"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item">
                            <div class="skill-name">
                                <span>Penteados Clássicos</span>
                                <span>92%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: 92%"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item">
                            <div class="skill-name">
                                <span>Visagismo</span>
                                <span>88%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: 88%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about-image">
                    <img src="assets/vitinho-working.jpg" alt="Vitinho trabalhando">
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="portfolio-section">
        <div class="container">
            <div class="section-title">
                <h2>Trabalhos Recentes</h2>
                <p>Alguns dos melhores cortes realizados por Vitinho</p>
            </div>
            
            <div class="portfolio-grid">
                <div class="portfolio-item">
                    <img src="assets/degrade1.jpg" alt="Degradê perfeito" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Degradê Navalhado</h3>
                        <span class="portfolio-category">Técnica Avançada</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/degrade2.jpg" alt="Degradê americano" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Degradê Americano</h3>
                        <span class="portfolio-category">Corte + Design</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/penteado1.jpg" alt="Penteado clássico" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Penteado Pompadour</h3>
                        <span class="portfolio-category">Estilo Clássico</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/degrade3.jpg" alt="Degradê criativo" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Degradê Criativo</h3>
                        <span class="portfolio-category">Corte + Desenho</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/penteado2.jpg" alt="Penteado moderno" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Penteado Moderno</h3>
                        <span class="portfolio-category">Estilo Contemporâneo</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/degrade4.jpg" alt="Degradê high fade" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">High Fade</h3>
                        <span class="portfolio-category">Técnica Premium</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section class="video-section">
        <div class="container">
            <div class="section-title">
                <h2>Técnica em Ação</h2>
                <p>Assista Vitinho realizando um degradê perfeito</p>
            </div>
            
            <div class="video-container">
                <video controls poster="assets/video-thumbnail-vitinho.jpg">
                    <source src="assets/vitinho-video.mp4" type="video/mp4">
                    Seu navegador não suporta vídeos HTML5.
                </video>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-title">
                <h2>O que dizem os clientes</h2>
                <p>Depoimentos de quem já experimentou o trabalho de Vitinho</p>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>O Vitinho é simplesmente incrível! Fez o melhor degradê que já tive na vida. Precisão milimétrica e atenção aos detalhes. Não vou mais em nenhum outro barbeiro!</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="assets/cliente1.jpg" alt="Cliente" class="author-avatar">
                        <div class="author-info">
                            <h4>Marcos Silva</h4>
                            <p>Cliente há 2 anos</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>Sou cliente do Vitinho desde que ele começou na barbearia. Ver a evolução dele é impressionante. Hoje é referência em degradê na cidade!</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="assets/cliente2.jpg" alt="Cliente" class="author-avatar">
                        <div class="author-info">
                            <h4>Ricardo Almeida</h4>
                            <p>Cliente há 3 anos</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>Fui indicado por um amigo e não me arrependi. Vitinho tem uma técnica impecável e ainda faz ótimas recomendações de cuidado com a barba. Recomendo demais!</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="assets/cliente3.jpg" alt="Cliente" class="author-avatar">
                        <div class="author-info">
                            <h4>Gustavo Oliveira</h4>
                            <p>Cliente há 1 ano</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="logo" style="margin-bottom: 20px;">
                    <span style="color: var(--secondary);">LEGACY</span> STYLE
                </div>
                
                <div class="footer-links">
                    <a href="index.php">Início</a>
                    <a href="index.php#about">Sobre</a>
                    <a href="index.php#barbers">Barbeiros</a>
                    <a href="index.php#services">Serviços</a>
                    <a href="index.php#contact">Contato</a>
                </div>
                
                <div class="social-links-footer">
                    <a href="https://www.instagram.com/legacystylebr/"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="https://wa.me/5541988383629"><i class="fab fa-whatsapp"></i></a>
                </div>
                
                <p class="copyright">&copy; 2024 Legacy Style Barbearia. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>