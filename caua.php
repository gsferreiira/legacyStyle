<?php
require 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cauã - Barbeiro Profissional | Legacy Style</title>
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
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary);
        }
        
        .logo span {
            color: white;
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
            background: linear-gradient(135deg, rgba(26,26,26,0.9) 0%, rgba(26,26,26,0.7) 100%), url('assets/bg-caua.jpg');
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
                    <span>LEGACY STYLE</span>
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
            <img src="assets/fotocaua.png" alt="Cauã" class="barber-avatar">
            <h1>Cauã Silva</h1>
            <span class="specialty-badge">Barbeiro Profissional</span>
            <p>Especialista em cortes modernos e inovadores, transformando visuais com técnica e criatividade.</p>
            
            <div class="social-links">
                <a href="https://wa.me/5541999888727" class="social-link" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://www.instagram.com/silva__barbeer/" class="social-link" target="_blank">
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
                    <h3>Conheça Cauã</h3>
                    <p>Com mais de 4 anos de experiência no mercado, Cauã é um dos fundadores da Legacy Style e se destaca por sua atenção aos detalhes e busca constante por aperfeiçoamento.</p>
                    <p>Formado nas melhores academias de barbeiro do país, Cauã traz para seus clientes as técnicas mais modernas e tendências internacionais, adaptadas para o estilo brasileiro.</p>
                    
                    <div class="skills">
                        <div class="skill-item">
                            <div class="skill-name">
                                <span>Cortes Modernos</span>
                                <span>95%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: 95%"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item">
                            <div class="skill-name">
                                <span>Navalha</span>
                                <span>90%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: 90%"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item">
                            <div class="skill-name">
                                <span>Visagismo</span>
                                <span>85%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about-image">
                    <img src="assets/caua-working.jpg" alt="Cauã trabalhando">
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="portfolio-section">
        <div class="container">
            <div class="section-title">
                <h2>Trabalhos Recentes</h2>
                <p>Alguns dos melhores cortes realizados por Cauã</p>
            </div>
            
            <div class="portfolio-grid">
                <div class="portfolio-item">
                    <img src="assets/corte1.jpg" alt="Corte moderno" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Degradê Americano</h3>
                        <span class="portfolio-category">Corte + Barba</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/corte2.jpg" alt="Corte social" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Social Premium</h3>
                        <span class="portfolio-category">Corte + Visagismo</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/corte3.jpg" alt="Corte degradê" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Degradê Navalhado</h3>
                        <span class="portfolio-category">Técnica Avançada</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/corte4.jpg" alt="Corte infantil" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Infantil Criativo</h3>
                        <span class="portfolio-category">Corte + Desenho</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/corte5.jpg" alt="Barba" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Barba Premium</h3>
                        <span class="portfolio-category">Hidratação + Modelagem</span>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <img src="assets/corte6.jpg" alt="Penteado" class="portfolio-image">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Penteado Clássico</h3>
                        <span class="portfolio-category">Pomada + Finalização</span>
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
                <p>Assista Cauã realizando um de seus cortes especiais</p>
            </div>
            
            <div class="video-container">
                <video controls poster="assets/video-thumbnail.jpg">
                    <source src="assets/caua-video.mp4" type="video/mp4">
                    Seu navegador não suporta vídeos HTML5.
                </video>
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
                    <a href="https://wa.me/5541999888727"><i class="fab fa-whatsapp"></i></a>
                </div>
                
                <p class="copyright">&copy; 2024 Legacy Style Barbearia. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>