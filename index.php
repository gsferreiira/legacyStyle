<?php
require 'db_connection.php';
session_start(); // Para guardar temporariamente os dados do agendamento
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legacy Style - Barbearia Premium</title>
    <link rel="shortcut icon" href="assets/LOGO LEGACY SF/1.png" type="image/x-icon">
    <style>
        /* Estilos Globais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            background-color: #f5f5f5;
            color: #333;
            overflow-x: hidden;
        }

        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            z-index: 3000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s, fadeOut 0.5s 3s forwards;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #28a745;
        }

        .alert-error {
            background-color: #dc3545;
        }

        @keyframes slideIn {
            from { top: -100px; opacity: 0; }
            to { top: 20px; opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Cabeçalho */
        header {
            background-color: #1a1a1a;
            color: #fff;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #d4af37;
        }
        
        .logo span {
            color: #fff;
        }
        
        /* Menu Mobile */
        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 24px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 16px;
        }
        
        nav ul li a:hover {
            color: #d4af37;
        }
        
        /* Hero Section */
        .hero {
            height: 100vh;
            min-height: 600px;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1599351431202-1e0f0137899a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            text-align: center;
            color: #fff;
            padding-top: 70px;
        }
        
        .hero-content h1 {
            font-size: 32px;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .hero-content p {
            font-size: 18px;
            margin-bottom: 25px;
            max-width: 100%;
            padding: 0 15px;
        }
        
        .btn {
            display: inline-block;
            background-color: #d4af37;
            color: #1a1a1a;
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: #c9a227;
            transform: translateY(-3px);
        }
        
        /* Sobre Nós */
        .about {
            padding: 60px 0;
            background-color: #f5f5f5;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .section-title h2 {
            font-size: 28px;
            color: #1a1a1a;
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
            background-color: #d4af37;
        }
        
        .about-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }
        
        .about-img {
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .about-img img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .about-text {
            width: 100%;
        }
        
        .about-text h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1a1a1a;
        }
        
        .about-text p {
            margin-bottom: 15px;
            line-height: 1.6;
            font-size: 16px;
        }
        
        /* Barbeiros */
        .barbers {
            padding: 60px 0;
            background-color: #fff;
        }
        
        .barbers-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin-top: 30px;
        }
        
        .barber-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .barber-card:hover {
            transform: translateY(-5px);
        }
        
        .barber-img {
            height: 500px;
            overflow: hidden;
        }
        
        .barber-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .barber-card:hover .barber-img img {
            transform: scale(1.05);
        }
        
        .barber-info {
            padding: 20px;
            text-align: center;
        }
        
        .barber-info h3 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        
        .barber-info p {
            color: #d4af37;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .barber-info p:last-child {
            color: #333;
            margin-bottom: 15px;
            font-size: 15px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-links a {
            color: #1a1a1a;
            font-size: 20px;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #d4af37;
        }
        
        /* Serviços */
        .service-selection {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin: 20px 0;
        }
        
        .service-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }
        
        .service-option:hover {
            border-color: #d4af37;
            background-color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .service-option.selected {
            border-color: #d4af37;
            background-color: rgba(212, 175, 55, 0.1);
        }
        
        .service-option input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin-right: 15px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .service-option input[type="checkbox"]:checked {
            background-color: #d4af37;
            border-color: #d4af37;
        }
        
        .service-option input[type="checkbox"]:checked::after {
            content: "✓";
            position: absolute;
            color: white;
            font-size: 14px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .service-info {
            flex-grow: 1;
        }
        
        .service-info h4 {
            margin: 0 0 5px 0;
            color: #1a1a1a;
            font-size: 16px;
        }
        
        .service-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }
        
        .service-duration {
            color: #666;
        }
        
        .service-price {
            color: #d4af37;
            font-weight: 600;
        }
        
        .service-total {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 10px;
            text-align: right;
            font-weight: 600;
        }
        
        .service-total span {
            color: #d4af37;
            font-size: 18px;
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        /* Contato */
        .contact {
            padding: 60px 0;
            background-color: #f5f5f5;
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .contact-info h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #1a1a1a;
        }
        
        .contact-info p {
            margin-bottom: 15px;
            line-height: 1.6;
            font-size: 16px;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .info-icon {
            font-size: 18px;
            color: #d4af37;
            margin-right: 12px;
            margin-top: 3px;
        }
        
        .info-text h4 {
            font-size: 16px;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        
        .info-text p, a {
            font-size: 15px;
            line-height: 1.5;
            text-decoration: none;
            color: #333;
        }
        
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .contact-form textarea {
            height: 120px;
            resize: none;
        }
        
        /* Rodapé */
        footer {
            background-color: #1a1a1a;
            color: #fff;
            padding: 30px 0;
            text-align: center;
        }
        
        .footer-content p {
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .footer-links a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 15px;
        }
        
        .footer-links a:hover {
            color: #d4af37;
        }
        
        .social-links-footer {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .social-links-footer a {
            color: #fff;
            font-size: 20px;
            transition: color 0.3s;
        }
        
        .social-links-footer a:hover {
            color: #d4af37;
        }
        
        .copyright {
            color: #777;
            font-size: 14px;
        }

        /* Estilos para o modal de agendamento simplificado */
        .booking-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            overflow-y: auto;
        }

        .booking-content {
            background-color: #fff;
            margin: 30px auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
            transition: all 0.3s ease;
            transform: translateY(20px);
            opacity: 0;
        }

        .booking-content.show {
            transform: translateY(0);
            opacity: 1;
        }

        .close-booking {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #1a1a1a;
        }

        .barber-selection {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .barber-option {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 20px;
            background-color: #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .barber-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #d4af37;
        }

        .barber-option.selected {
            border-color: #d4af37;
            background-color: rgba(212, 175, 55, 0.05);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.1);
        }

        .barber-option img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .barber-option.selected img {
            border-color: #d4af37;
        }

        .barber-option .barber-info {
            flex-grow: 1;
        }

        .barber-option .barber-info h4 {
            margin: 0 0 5px 0;
            color: #1a1a1a;
            font-size: 18px;
            font-weight: 600;
        }

        .barber-option .barber-info p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .barber-option .barber-rating {
            color: #d4af37;
            font-size: 14px;
            margin-top: 5px;
        }

        .barber-option .barber-badge {
            background-color: #d4af37;
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
            display: inline-block;
        }

        /* Título melhorado */
        .booking-title h3 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #1a1a1a;
            position: relative;
            padding-bottom: 10px;
        }

        .booking-title h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: #d4af37;
        }

        .booking-title p {
            font-size: 15px;
            color: #666;
            margin-top: 10px;
        }

        /* Estilos para os slots de horário */
        #timeSlots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .time-slot {
            background-color: #f5f5f5;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }
        
        .time-slot:hover {
            background-color: #d4af37;
            color: white;
            border-color: #d4af37;
            transform: translateY(-2px);
        }
        
        .time-slot.selected {
            background-color: #d4af37;
            color: white;
            border-color: #d4af37;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .time-slot.unavailable {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        /* Melhorias no calendário */
        #appointmentDate {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        /* Estilo para os dias da semana no calendário */
        #appointmentDate::-webkit-calendar-picker-indicator {
            padding: 5px;
            cursor: pointer;
            border-radius: 4px;
            background-color: #f5f5f5;
        }
        
        #appointmentDate::-webkit-calendar-picker-indicator:hover {
            background-color: #e9e9e9;
        }

        .services {
            padding: 60px 0;
            background-color: #fff;
        }

        .services-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin-top: 30px;
        }

        .service-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .service-icon {
            font-size: 40px;
            color: #d4af37;
            margin-bottom: 15px;
        }

        .service-card h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .service-card p {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .price {
            font-size: 18px;
            font-weight: 700;
            color: #d4af37;
        }

        /* Estilo para o spinner de loading */
        .loading-spinner {
            grid-column: 1/-1;
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .loading-spinner i {
            margin-right: 10px;
            color: #d4af37;
        }

        /* Efeito de hover para a seta do barbeiro */
        .barber-option .fa-chevron-right {
            transition: transform 0.3s ease;
        }
        
        /* Estilos do Carrossel */
        .carousel-container {
            position: relative;
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .carousel-slides {
            display: flex;
            transition: transform 0.5s ease-in-out;
            height: 400px;
        }

        .carousel-slide {
            min-width: 100%;
            position: relative;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .carousel-prev, .carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(212, 175, 55, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-prev:hover, .carousel-next:hover {
            background-color: rgba(212, 175, 55, 0.9);
        }

        .carousel-prev {
            left: 15px;
        }

        .carousel-next {
            right: 15px;
        }

        .carousel-dots {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            z-index: 10;
        }

        .carousel-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .carousel-dot.active {
            background-color: #d4af37;
            transform: scale(1.2);
        }

        .payment-options {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
        }

        .payment-options h4 {
            color: #1a1a1a;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }

        .payment-options label {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 8px;
            background-color: #fff;
            border: 1px solid #eee;
            transition: all 0.3s;
            cursor: pointer;
        }

        .payment-options label:hover {
            border-color: #d4af37;
            background-color: rgba(212, 175, 55, 0.05);
        }

        .payment-options input[type="radio"] {
            margin-right: 10px;
            accent-color: #d4af37;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .carousel-slides {
                height: 300px;
            }
            
            .carousel-prev, .carousel-next {
                width: 30px;
                height: 30px;
                font-size: 14px;
            }
        }
        /* Responsividade para mobile */
        @media (max-width: 576px) {
            .barber-option {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .barber-option img {
                width: 100px;
                height: 100px;
            }
            
            .barber-option .fa-chevron-right {
                display: none;
            }
        }
        
        @media (min-width: 768px) {
            .services-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 30px;
            }
        }


        /* Media Queries para responsividade */
        @media (min-width: 768px) {
            .logo {
                font-size: 28px;
            }
            
            .hero-content h1 {
                font-size: 42px;
            }
            
            .hero-content p {
                font-size: 20px;
                max-width: 700px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .section-title h2 {
                font-size: 32px;
            }
            
            .about-content {
                flex-direction: row;
                gap: 40px;
            }
            
            .barbers-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 30px;
            }
            
            .services-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 25px;
            }
            
            .contact-container {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 40px;
            }
        }

        @media (min-width: 992px) {
            .hero-content h1 {
                font-size: 48px;
            }
            
            .section-title h2 {
                font-size: 36px;
            }
        }

        /* Menu Mobile */
        @media (max-width: 767px) {
            .menu-toggle {
                display: block;
            }
            
            nav {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 80%;
                height: calc(100vh - 70px);
                background-color: #1a1a1a;
                transition: all 0.3s;
                z-index: 999;
            }
            
            nav.active {
                left: 0;
            }
            
            nav ul {
                flex-direction: column;
                padding: 20px;
            }
            
            nav ul li {
                margin: 15px 0;
            }
            
            .hero {
                padding-top: 70px;
            }
        }
        .confirmation-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .confirmation-title {
            color: #d4af37;
            font-size: 20px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .confirmation-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        
        .confirmation-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .confirmation-label {
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .confirmation-value {
            color: #555;
            text-align: right;
        }
        
        .confirmation-total {
            font-weight: 700;
            color: #d4af37;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #d4af37;
        }
        
        .client-form {
            margin-top: 20px;
        }
        
        .client-form input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .confirmation-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .btn-cancel {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .btn-cancel:hover {
            background-color: #e0e0e0;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Cabeçalho -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="login.php"><img src="assets/LOGO LEGACY SF/2.png" alt="Logo Legacy Style" style="height: 60px; vertical-align: middle;"></a>
                </div>
                <div class="logo">LEGACY <span>STYLE</span></div>
                <div class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
                <nav>
                    <ul>
                    <li><a href="#home">Início</a></li>
                    <li><a href="#about">Sobre</a></li>
                    <li><a href="#barbers">Barbeiros</a></li>
                    <li><a href="#services">Serviços</a></li>
                    
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li><a href="meus_agendamentos.php" style="color: #d4af37; font-weight: bold;">
                            <i class="fas fa-user-circle"></i> Olá, <?= explode(' ', $_SESSION['cliente_nome'])[0] ?>
                        </a></li>
                    <?php else: ?>
                        <li><a href="entrar.php" style="color: #d4af37; font-weight: bold;">
                            <i class="fas fa-sign-in-alt"></i> Entrar / Cadastrar
                        </a></li>
                    <?php endif; ?>
                </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1>LEGACY STYLE BARBEARIA</h1>
                <p>Estilo, tradição e excelência em cada corte. Fundada no final de 2024, a Legacy Style traz um novo conceito em cuidados masculinos.</p>
                <a href="#" class="btn" id="bookNowHero">Agende seu horário</a>
            </div>
        </div>
    </section>

    <!-- Sobre Nós -->
    <section class="about" id="about">
        <div class="container">
            <div class="section-title">
                <h2>Sobre Nós</h2>
            </div>
            <div class="about-content">
                <div class="about-img">
                    <div class="carousel-container">
                        <div class="carousel-slides">
                            <div class="carousel-slide active">
                                <img src="assets/interior1.jpg" alt="Interior da barbearia">
                            </div>
                            <div class="carousel-slide">
                                <img src="assets/interior2.jpg" alt="Equipe da barbearia">
                            </div>
                            <div class="carousel-slide">
                                <img src="assets/interior3.jpg" alt="Cliente sendo atendido">
                            </div>
                            <div class="carousel-slide">
                                <img src="assets/interior4.jpg" alt="Detalhes do ambiente">
                            </div>
                        </div>
                        <button class="carousel-prev"><i class="fas fa-chevron-left"></i></button>
                        <button class="carousel-next"><i class="fas fa-chevron-right"></i></button>
                        <div class="carousel-dots"></div>
                    </div>
                </div>
                <div class="about-text">
                    <h3>Nossa História</h3>
                    <p>A Legacy Style nasceu no final de 2024 na junção de dois talentos, Cauã e Vitinho. Com anos de experiência no mercado, decriram unir forças para criar um espaço único onde tradição e modernidade se encontram.</p>
                    <p>Nosso objetivo é proporcionar mais do que um simples corte de cabelo ou barba. Queremos oferecer uma experiência completa, onde cada cliente se sinta especial e saia não apenas com um visual renovado, mas também com a autoestima elevada.</p>
                    <p>Na Legacy Style, valorizamos os detalhes, a qualidade dos produtos utilizados e, principalmente, o relacionamento com nossos clientes. Aqui, você não é apenas mais um, é parte da nossa família.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Barbeiros -->
    <section class="barbers" id="barbers">
        <div class="container">
            <div class="section-title">
                <h2>Nossos Barbeiros</h2>
            </div>
            <div class="barbers-grid">
                <div class="barber-card">
                    <div class="barber-img">
                        <img src="assets/fotocaua.png" alt="Barbeiro Cauã">
                    </div>
                    <div class="barber-info">
                        <h3>Cauã</h3>
                        <p>Barbeiro Profissional</p>
                        <p>Especialista em cortes modernos e inovados, com 4 anos de experiência no mercado.</p>
                        <div class="social-links">
                            <a href="#" class="whatsapp-link" data-barber="caua"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://www.instagram.com/silva__barbeer/?hl=en"><i class="fab fa-instagram"></i></a>
                        </div>
                        <a href="caua.php" class="btn" style="margin-top: 15px; display: inline-block;">Ver Perfil</a>
                    </div>
                </div>
                <div class="barber-card">
                    <div class="barber-img">
                        <img src="assets/fotovitor.png" alt="Barbeiro Vitinho">
                    </div>
                    <div class="barber-info">
                        <h3>Vitinho</h3>
                        <p>Barbeiro Profissional</p>
                        <p>Mestre em cortes degradê e penteados, com 3 anos de esperiência no mercado.</p>
                        <div class="social-links">
                            <a href="#" class="whatsapp-link" data-barber="vitinho"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://www.instagram.com/ovitinhobarber/?hl=en"><i class="fab fa-instagram"></i></a>
                        </div>
                        <a href="vitinho.php" class="btn" style="margin-top: 15px; display: inline-block;">Ver Perfil</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-title">
                <h2>Nossos Serviços</h2>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-cut"></i>
                    </div>
                    <h3>Corte de Cabelo</h3>
                    <p>Corte profissional com técnicas modernas e acabamento perfeito para valorizar seu estilo.</p>
                    <div class="price">R$ 40,00</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-air-freshener"></i>
                    </div>
                    <h3>Barba Completa</h3>
                    <p>Modelagem, hidratação e acabamento para uma barba impecável e bem cuidada.</p>
                    <div class="price">R$ 30,00</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3>Pacote Completo</h3>
                    <p>Corte + barba + hidratação + tratamento com toalha quente para uma experiência premium.</p>
                    <div class="price">R$ 80,00</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3>Corte Infantil</h3>
                    <p>Atendimento especializado para os pequenos, com ambiente descontraído e profissionalismo.</p>
                    <div class="price">R$ 35,00</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h3>Luzes</h3>
                    <p>Técnicas avançadas de coloração para deixar fios brancos.</p>
                    <div class="price">R$ 100,00</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-magic"></i>
                    </div>
                    <h3>Tratamento Capilar</h3>
                    <p>Hidratação profunda e reconstrução para cabelos danificados ou ressecados.</p>
                    <div class="price">R$ 60,00</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contato -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Entre em Contato</h2>
            </div>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Agende seu horário</h3>
                    <p>Estamos à disposição para atender você da melhor forma possível. Venha nos visitar ou entre em contato pelos canais abaixo.</p>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-text">
                            <h4>Endereço</h4>
                            <a href="https://maps.app.goo.gl/RLH7x5x4VP5eMdGy5"><p>Rua Pedro Gusso, 744 - Capão Raso, Curitiba - Paraná</p></a>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-text">
                            <h4>Telefone</h4>
                            <a href="#" class="whatsapp-link" data-barber="caua">+55 (41) 999888727</a>
                            <a href="#" class="whatsapp-link" data-barber="vitinho"><p>+55 (41) 988383629</p></a>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-text">
                            <h4>Email</h4>
                            <p>legacystyle@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-text">
                            <h4>Horário de Funcionamento</h4>
                            <p>Segunda: 10:00 - 18:00</p>
                            <p>Terça a Quinta: 09:00 - 20:00</p>
                            <p>Sexta: 09:00 - 21:30</p>
                            <p>Sábado: 08:00 - 17:30</p>
                            <p>Domingo: Fechado</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <form>
                        <input type="text" placeholder="Seu Nome" required>
                        <input type="email" placeholder="Seu Email" required>
                        <input type="tel" placeholder="Seu Telefone">
                        <textarea placeholder="Sua Mensagem"></textarea>
                        <button type="submit" class="btn">Enviar Mensagem</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Rodapé -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="logo">LEGACY <span>STYLE</span></div>
                <p>Estilo que deixa legado. Fundada em 2024.</p>
                
                <div class="footer-links">
                    <a href="#home">Início</a>
                    <a href="#about">Sobre</a>
                    <a href="#barbers">Barbeiros</a>
                    <a href="#services">Serviços</a>
                    <a href="#contact">Contato</a>
                </div>
                
                <div class="social-links-footer">
                    <a href="https://www.instagram.com/legacystylebr/?hl=en"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="https://wa.me/554199888727"><i class="fab fa-whatsapp"></i></a>
                </div>
                
                <p class="copyright">&copy; 2024 Legacy Style Barbearia. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Modal de Agendamento -->
    <div class="booking-modal" id="bookingModal">
        <div class="booking-content">
            <span class="close-booking">&times;</span>
            
            <!-- Passo 1: Escolher Barbeiro -->
            <div id="step1">
                <div class="booking-title">
                    <h3>Escolha seu Barbeiro</h3>
                    <p>Selecione o profissional que melhor atende às suas necessidades</p>
                </div>
                
                <div class="barber-selection">
                    <?php
                    $barbeiros = $pdo->query("SELECT * FROM barbeiros")->fetchAll();
                    foreach ($barbeiros as $barbeiro): 
                    ?>
                        <div class="barber-option" data-barber="<?= $barbeiro['id'] ?>">
                            <img src="assets/<?= $barbeiro['foto'] ?>" alt="<?= $barbeiro['nome'] ?>">
                            <div class="barber-info">
                                <h4><?= $barbeiro['nome'] ?></h4>
                                <p><?= $barbeiro['especialidade'] ?></p>
                                <div class="barber-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    (<?= rand(20, 100) ?> avaliações)
                                </div>
                                <span class="barber-badge"><?= rand(80, 95) ?>% de satisfação</span>
                            </div>
                            <i class="fas fa-chevron-right" style="color: #d4af37;"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Passo 2: Selecionar Serviços -->
            <div id="step2" style="display:none;">
            <div class="booking-title">
                <h3>Selecione seus serviços</h3>
                <p>Escolha um ou mais serviços para agendar</p>
            </div>
            
            <div class="service-selection">
                <?php
                $servicos = $pdo->query("SELECT * FROM servicos")->fetchAll();
                foreach ($servicos as $servico): 
                ?>
                    <label class="service-option">
                        <input type="checkbox" id="servico-<?= $servico['id'] ?>" 
                            data-service="<?= $servico['id'] ?>" 
                            data-duration="<?= $servico['duracao'] ?>"
                            data-price="<?= $servico['preco'] ?>">
                        <div class="service-info">
                            <h4><?= $servico['nome'] ?></h4>
                            <div class="service-meta">
                                <span class="service-duration"><?= $servico['duracao'] ?> min</span>
                                <span class="service-price">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            
            <div class="service-total">
                Total: <span id="totalPrice">R$ 0,00</span> • <span id="totalDuration">0 min</span>
            </div>
            
            <div class="navigation-buttons">
                <button class="btn btn-secondary" id="backToStep1">Voltar</button>
                <button class="btn" id="nextToStep3">Continuar</button>
            </div>
        </div>

            <!-- Passo 3: Escolher Data/Horário -->
            <div id="step3" style="display:none;">
                <div class="booking-title">
                    <h3>Escolha a Data e Horário</h3>
                    <p>Selecione uma data abaixo para ver os horários disponíveis</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="appointmentDate" style="display: block; margin-bottom: 8px; font-weight: 500;">Data:</label>
                    <input type="date" id="appointmentDate" min="<?= date('Y-m-d') ?>" style="width: 100%;">
                </div>
                
                <div id="timeSlotsContainer" style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px; color: #555;">Horários Disponíveis</h4>
                    <div id="timeSlots"></div>
                </div>
                
                <button class="btn" id="backToStep2" style="margin-top: 20px; background-color: #f5f5f5; color: #333;">Voltar</button>
            </div>

            <!-- Passo 4: Confirmar Dados -->
            <div id="step4" style="display:none;">
                <div class="booking-title">
                    <h3>Confirme seu Agendamento</h3>
                    <p>Revise os detalhes abaixo e preencha seus dados</p>
                </div>
                
                <div class="confirmation-container">
                    <div class="confirmation-title">Detalhes do Agendamento</div>
                    <div class="confirmation-details" id="confirmationDetails">
                        <!-- Os detalhes serão preenchidos pelo JavaScript -->
                    </div>
                </div>
                
                <form method="POST" action="salvar_agendamento.php" class="client-form">
                <input type="hidden" name="barbeiro_id" id="confirmBarbeiroId">
                <input type="hidden" name="servicos" id="confirmServicosIds">
                <input type="hidden" name="data" id="confirmData">
                <input type="hidden" name="hora" id="confirmHora">
                <input type="hidden" name="valor_total" id="valorTotal">
                
                <!-- Campos do cliente -->
                <input type="text" name="nome" placeholder="Seu Nome Completo" required>
                <input type="tel" name="telefone" placeholder="Seu WhatsApp (ex: 41999999999)" required>
                <input type="email" name="email" placeholder="Seu E-mail" required>
                
                <!-- Adicione antes dos botões de ação -->
                <div id="pixInfo" style="display: none; margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #d4af37;">Pagamento via PIX</h4>
                    <p>Chave PIX: <strong>legacystyle@gmail.com</strong></p>
                    <p>Valor: <strong id="pixAmount">R$ 0,00</strong></p>
                    <p>Após o pagamento, envie o comprovante para nosso WhatsApp.</p>
                    <img src="assets/qrcode_pix.png" alt="QR Code PIX" style="max-width: 200px; margin: 10px auto; display: block;">
                </div>
                
                <div class="confirmation-actions">
                    <button type="button" class="btn btn-cancel" id="backToStep3">Voltar</button>
                    <button type="submit" class="btn">Confirmar Agendamento</button>
                </div>
            </form>
                <!-- No passo 4 de confirmação -->
                <div class="payment-options" style="margin: 20px 0;">
                    <h4 style="margin-bottom: 10px; color: #555;">Forma de Pagamento</h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="presencial" checked style="accent-color: #d4af37;">
                            Pagar na barbearia
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="pix" style="accent-color: #d4af37;">
                            Pagar com PIX (5% de desconto)
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Variáveis globais
    let selectedBarber;
    let selectedServices = [];
    let totalPrice = 0;
    let totalDuration = 0;

    // Passo 1: Selecionar Barbeiro
    document.querySelectorAll('.barber-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remover seleção anterior
            document.querySelectorAll('.barber-option').forEach(el => {
                el.classList.remove('selected');
                el.querySelector('.fa-chevron-right').style.transform = 'translateX(0)';
            });
            
            // Adicionar seleção atual
            this.classList.add('selected');
            this.querySelector('.fa-chevron-right').style.transform = 'translateX(5px)';
            
            selectedBarber = this.getAttribute('data-barber');
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            
            // Resetar seleções ao voltar para escolher barbeiro
            selectedServices = [];
            totalPrice = 0;
            totalDuration = 0;
            updateTotals();
        });
    });

    // Passo 2: Selecionar Serviços
    document.querySelectorAll('.service-option input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const serviceId = this.getAttribute('data-service');
            const duration = parseInt(this.getAttribute('data-duration'));
            const price = parseFloat(this.getAttribute('data-price'));
            
            if (this.checked) {
                selectedServices.push(serviceId);
                totalDuration += duration;
                totalPrice += price;
                this.closest('.service-option').classList.add('selected');
            } else {
                selectedServices = selectedServices.filter(id => id !== serviceId);
                totalDuration -= duration;
                totalPrice -= price;
                this.closest('.service-option').classList.remove('selected');
            }
            
            updateTotals();
        });
    });

    // Função para atualizar totais
    function updateTotals() {
        document.getElementById('totalPrice').textContent = `R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
        document.getElementById('totalDuration').textContent = `${totalDuration} min`;
    }

    // Navegação entre passos
    document.getElementById('backToStep1').addEventListener('click', function() {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
    });

    document.getElementById('nextToStep3').addEventListener('click', function() {
        if (selectedServices.length === 0) {
            alert('Por favor, selecione pelo menos um serviço');
            return;
        }
        
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step3').style.display = 'block';
        loadAvailableDates();
    });

    document.getElementById('backToStep2').addEventListener('click', function() {
        document.getElementById('step3').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
    });

    // Passo 3: Carregar Datas/Horários Disponíveis
    function loadAvailableDates() {
        const dateInput = document.getElementById('appointmentDate');
        
        // Definir a data mínima como hoje
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0'); // Janeiro é 0!
        const yyyy = today.getFullYear();
        dateInput.min = `${yyyy}-${mm}-${dd}`;
        
        dateInput.addEventListener('change', function() {
            if (!this.value) return;
            
            const timeSlots = document.getElementById('timeSlots');
            timeSlots.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Carregando horários...</div>';
            
            fetch(`get_horarios.php?barbeiro_id=${selectedBarber}&data=${this.value}&duracao=${totalDuration}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('O dia escolhido não possui horários disponíveis ou ocorreu um erro ao buscar os horários.');
                    }
                    return response.json();
                })
                .then(horarios => {
                    if (horarios.error) {
                        throw new Error(horarios.error);
                    }
                    
                    timeSlots.innerHTML = '';
                    
                    if (horarios.length === 0) {
                        timeSlots.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #666;">Nenhum horário disponível para esta data</p>';
                        return;
                    }
                    
                    // Agrupar horários por período (manhã/tarde)
                    const manha = [];
                    const tarde = [];
                    
                    horarios.forEach(horario => {
                        const hora = parseInt(horario.split(':')[0]);
                        if (hora < 12) {
                            manha.push(horario);
                        } else {
                            tarde.push(horario);
                        }
                    });
                    
                    // Adicionar título para manhã se houver horários
                    if (manha.length > 0) {
                        const tituloManha = document.createElement('h5');
                        tituloManha.textContent = 'Manhã';
                        tituloManha.style.gridColumn = '1/-1';
                        tituloManha.style.marginTop = '10px';
                        tituloManha.style.color = '#555';
                        timeSlots.appendChild(tituloManha);
                        
                        manha.forEach(horario => {
                            timeSlots.appendChild(createTimeSlot(horario));
                        });
                    }
                    
                    // Adicionar título para tarde se houver horários
                    if (tarde.length > 0) {
                        const tituloTarde = document.createElement('h5');
                        tituloTarde.textContent = 'Tarde';
                        tituloTarde.style.gridColumn = '1/-1';
                        tituloTarde.style.marginTop = '10px';
                        tituloTarde.style.color = '#555';
                        timeSlots.appendChild(tituloTarde);
                        
                        tarde.forEach(horario => {
                            timeSlots.appendChild(createTimeSlot(horario));
                        });
                    }
                })
                .catch(error => {
                    timeSlots.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: #721c24;">${error.message || 'Ocorreu um erro ao carregar os horários'}</p>`;
                    console.error('Erro ao carregar horários:', error);
                });
        });
    }

    // Verificar parâmetros da URL para mostrar mensagens
    const urlParams = new URLSearchParams(window.location.search);
    const agendamentoStatus = urlParams.get('agendamento');
    const mensagem = urlParams.get('mensagem');

    if (agendamentoStatus === 'sucesso') {
        showAlert('Agendamento realizado com sucesso!', 'success');
    } else if (agendamentoStatus === 'erro') {
        showAlert(mensagem || 'Não foi possível realizar o agendamento', 'error');
    }

    // Função auxiliar para criar um slot de horário
    function createTimeSlot(horario) {
        const button = document.createElement('button');
        button.textContent = horario;
        button.className = 'time-slot';
        button.addEventListener('click', function() {
            // Remover seleção anterior
            document.querySelectorAll('.time-slot.selected').forEach(el => {
                el.classList.remove('selected');
            });
            // Selecionar este
            this.classList.add('selected');
            selectTime(horario);
        });
        return button;
    }

    // Função para formatar data
    function formatDate(dateString) {
        const date = new Date(dateString);
        // Ajustar para o fuso horário local
        const adjustedDate = new Date(date.getTime() + (date.getTimezoneOffset() * 60000));
        
        const day = String(adjustedDate.getDate()).padStart(2, '0');
        const month = String(adjustedDate.getMonth() + 1).padStart(2, '0');
        const year = adjustedDate.getFullYear();
        
        return `${day}/${month}/${year}`;
    }

    // Passo 4: Confirmar Agendamento
    function selectTime(horario) {
        const dataInput = document.getElementById('appointmentDate');
        const dataValue = dataInput.value;
        
        // Criar objeto Date ajustado para o fuso horário local
        const dataObj = new Date(dataValue);
        const adjustedDate = new Date(dataObj.getTime() + (dataObj.getTimezoneOffset() * 60000));
        
        // Formatando a data para YYYY-MM-DD
        const year = adjustedDate.getFullYear();
        const month = String(adjustedDate.getMonth() + 1).padStart(2, '0');
        const day = String(adjustedDate.getDate()).padStart(2, '0');
        const dataCorreta = `${year}-${month}-${day}`;
        
        // Preencher formulário oculto
        document.getElementById('confirmBarbeiroId').value = selectedBarber;
        document.getElementById('confirmServicosIds').value = selectedServices.join(',');
        document.getElementById('confirmData').value = dataCorreta;
        document.getElementById('confirmHora').value = horario;
        
        // Mostrar resumo
        const confirmationDetails = document.getElementById('confirmationDetails');
        confirmationDetails.innerHTML = `
            <div class="confirmation-item">
                <span class="confirmation-label">Barbeiro:</span>
                <span class="confirmation-value">${document.querySelector(`.barber-option[data-barber="${selectedBarber}"] h4`).textContent}</span>
            </div>
            <div class="confirmation-item">
                <span class="confirmation-label">Serviços:</span>
                <span class="confirmation-value">
                    ${selectedServices.map(id => {
                        const el = document.querySelector(`input[data-service="${id}"]`);
                        return el ? el.closest('.service-option').querySelector('h4').textContent : '';
                    }).join('<br>')}
                </span>
            </div>
            <div class="confirmation-item">
                <span class="confirmation-label">Data:</span>
                <span class="confirmation-value">${formatDate(dataCorreta)}</span>
            </div>
            <div class="confirmation-item">
                <span class="confirmation-label">Horário:</span>
                <span class="confirmation-value">${horario}</span>
            </div>
            <div class="confirmation-item">
                <span class="confirmation-label">Duração:</span>
                <span class="confirmation-value">${totalDuration} minutos</span>
            </div>
            <div class="confirmation-total">
                <span>Valor Total: R$ ${totalPrice.toFixed(2).replace('.', ',')}</span>
            </div>
        `;
        
        document.getElementById('step3').style.display = 'none';
        document.getElementById('step4').style.display = 'block';
    }

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'pix') {
                const discountedPrice = totalPrice * 0.95; // 5% de desconto
                document.querySelector('.confirmation-total span').innerHTML = 
                    `Valor Total: <span style="text-decoration: line-through;">R$ ${totalPrice.toFixed(2).replace('.', ',')}</span> 
                    R$ ${discountedPrice.toFixed(2).replace('.', ',')} (com 5% de desconto PIX)`;
            } else {
                document.querySelector('.confirmation-total span').innerHTML = 
                    `Valor Total: R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
            }
        });
    });

    // Mostrar/Esconder informações PIX conforme seleção
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const pixInfo = document.getElementById('pixInfo');
            if (this.value === 'pix') {
                pixInfo.style.display = 'block';
                const discountedPrice = totalPrice * 0.95;
                document.getElementById('pixAmount').textContent = `R$ ${discountedPrice.toFixed(2).replace('.', ',')}`;
                document.getElementById('valorTotal').value = discountedPrice;
            } else {
                pixInfo.style.display = 'none';
                document.getElementById('valorTotal').value = totalPrice;
            }
        });
    });

    // Botão de voltar
    document.getElementById('backToStep3').addEventListener('click', function() {
        document.getElementById('step4').style.display = 'none';
        document.getElementById('step3').style.display = 'block';
    });

    // Abrir modal quando clicar em "Agende seu horário"
    document.getElementById('bookNowHero').addEventListener('click', function(e) {
        e.preventDefault();
        const modal = document.getElementById('bookingModal');
        const content = document.querySelector('.booking-content');
        
        modal.style.display = 'block';
        setTimeout(() => {
            content.classList.add('show');
        }, 10);
        
        // Resetar seleções ao abrir o modal
        selectedBarber = null;
        selectedServices = [];
        totalPrice = 0;
        totalDuration = 0;
        updateTotals();
        
        // Remover seleções visuais
        document.querySelectorAll('.barber-option, .service-option, .time-slot').forEach(el => {
            el.classList.remove('selected');
        });
        document.querySelectorAll('.service-option input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Resetar passos
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step3').style.display = 'none';
        document.getElementById('step4').style.display = 'none';
    });

    // Fechar modal
    document.querySelector('.close-booking').addEventListener('click', function() {
        const modal = document.getElementById('bookingModal');
        const content = document.querySelector('.booking-content');
        
        content.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    });

    // Fechar modal ao clicar fora do conteúdo
    document.getElementById('bookingModal').addEventListener('click', function(e) {
        if (e.target === this) {
            const modal = document.getElementById('bookingModal');
            const content = document.querySelector('.booking-content');
            
            content.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    });

    // Menu mobile
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('nav').classList.toggle('active');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.carousel-slide');
    const dotsContainer = document.querySelector('.carousel-dots');
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    let currentIndex = 0;
    
    // Criar dots
    slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('carousel-dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });
    
    // Atualizar carrossel
    function updateCarousel() {
        const slideWidth = document.querySelector('.carousel-slide').clientWidth;
        document.querySelector('.carousel-slides').style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        
        // Atualizar dots
        document.querySelectorAll('.carousel-dot').forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    // Navegação
    function goToSlide(index) {
        currentIndex = index;
        updateCarousel();
    }
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        updateCarousel();
    }
    
    function prevSlide() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateCarousel();
    }
    
    // Event listeners
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    // Auto-avanço (opcional)
    let interval = setInterval(nextSlide, 5000);
    
    // Pausar ao passar o mouse
    const carousel = document.querySelector('.carousel-container');
    carousel.addEventListener('mouseenter', () => clearInterval(interval));
    carousel.addEventListener('mouseleave', () => interval = setInterval(nextSlide, 5000));
});

// Função para mostrar alerta
function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    document.body.appendChild(alert);
    
    // Remover após alguns segundos
    setTimeout(() => {
        alert.remove();
    }, 3500);
}
</script>
</body>
</html>