<?php
require 'db_connection.php';
session_start();
$barbeiro_id = 2; // ID DO VITINHO
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitinho | Legacy Style</title>
    <link rel="shortcut icon" href="assets/LOGO LEGACY SF/1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* (COPIE O MESMO ESTILO DO CAUÃ.PHP AQUI PARA MANTER O PADRÃO) */
        :root { --gold: #d4af37; --black: #111; --white: #fff; --gray: #f9f9f9; }
        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; }
        body { font-family: 'Montserrat', sans-serif; background: var(--white); color: #333; overflow-x: hidden; }
        
        header { background: rgba(0,0,0,0.95); padding: 20px 0; position: fixed; width: 100%; z-index: 1000; border-bottom: 1px solid #333; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: 'Playfair Display', serif; font-size: 24px; color: #fff; text-decoration: none; font-weight: 700; display: flex; gap:10px; align-items: center; }
        .logo span { color: var(--gold); }
        .logo img { height: 45px; }
        .btn-back { color: #fff; text-decoration: none; font-size: 13px; text-transform: uppercase; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-back:hover { color: var(--gold); }

        .profile-hero { padding-top: 140px; padding-bottom: 80px; background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); color: #fff; }
        .hero-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .hero-info h1 { font-family: 'Playfair Display', serif; font-size: 56px; line-height: 1.1; margin-bottom: 10px; }
        .hero-role { color: var(--gold); font-size: 14px; text-transform: uppercase; letter-spacing: 3px; font-weight: 700; display: block; margin-bottom: 30px; }
        .hero-bio { color: #ccc; font-size: 16px; line-height: 1.8; margin-bottom: 40px; }
        
        .stats-row { display: flex; gap: 40px; border-top: 1px solid #333; padding-top: 30px; margin-bottom: 40px; }
        .stat strong { display: block; font-size: 32px; color: #fff; font-family: 'Playfair Display', serif; }
        .stat small { color: var(--gold); text-transform: uppercase; font-size: 11px; letter-spacing: 1px; }

        .hero-img-box { position: relative; text-align: center; }
        .hero-img-box img { width: 100%; max-width: 450px; border-radius: 12px; box-shadow: 20px 20px 0 var(--gold); filter: grayscale(100%); transition: 0.5s; }
        .hero-img-box img:hover { filter: grayscale(0%); transform: translateY(-5px); box-shadow: 25px 25px 0 rgba(212,175,55,0.5); }

        .btn-cta { background: var(--gold); color: #000; padding: 18px 40px; font-weight: 700; text-transform: uppercase; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; transition:0.3s; font-size: 14px; display: inline-block; }
        .btn-cta:hover { transform: translateY(-5px); background: #fff; }

        .skills-section { padding: 100px 0; background: #fff; }
        .skills-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; }
        .section-title h2 { font-family: 'Playfair Display', serif; font-size: 36px; margin-bottom: 20px; color: #111; }
        .section-title p { color: #666; margin-bottom: 30px; }
        
        .skill-item { margin-bottom: 25px; }
        .skill-info { display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 700; font-size: 14px; text-transform: uppercase; }
        .progress-bar { width: 100%; height: 6px; background: #eee; border-radius: 3px; overflow: hidden; }
        .progress { height: 100%; background: var(--gold); width: 0; transition: width 1.5s ease-in-out; }

        .portfolio { padding: 100px 0; background: var(--gray); }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 40px; }
        .gallery-item { height: 350px; overflow: hidden; border-radius: 8px; position: relative; cursor: pointer; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .gallery-item:hover img { transform: scale(1.1); filter: brightness(60%); }
        .overlay { position: absolute; bottom: 20px; left: 20px; opacity: 0; transition: 0.3s; }
        .gallery-item:hover .overlay { opacity: 1; }
        .overlay h4 { color: #fff; margin: 0; font-size: 18px; }
        .overlay p { color: var(--gold); font-size: 13px; text-transform: uppercase; }

        .reviews-section { padding: 100px 0; background: #111; color: #fff; text-align: center; }
        .review-card { background: #1a1a1a; padding: 40px; border-radius: 8px; border: 1px solid #333; max-width: 800px; margin: 0 auto; position: relative; }
        .review-card::before { content: '"'; font-family: serif; font-size: 100px; color: var(--gold); opacity: 0.2; position: absolute; top: -20px; left: 20px; }
        .review-text { font-size: 18px; font-style: italic; color: #ddd; margin-bottom: 20px; line-height: 1.6; }
        .review-author { font-weight: 700; color: var(--gold); text-transform: uppercase; letter-spacing: 1px; }

        footer { background: #000; color: #666; padding: 50px 0; text-align: center; font-size: 13px; border-top: 1px solid #222; }

        /* Modal Styles */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 2000; overflow-y: auto; }
        .modal-box { background: #fff; width: 95%; max-width: 500px; margin: 50px auto; border-radius: 12px; overflow: hidden; }
        .modal-header { background: #111; padding: 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid var(--gold); }
        .close { color: var(--gold); font-size: 28px; cursor: pointer; }
        .modal-body { padding: 30px; }
        .step { display: none; } .step.active { display: block; }
        .opt-card { border: 2px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 15px; transition:0.2s; }
        .opt-card.selected { border-color: var(--gold); background: #fffdf0; }
        .btn-next { width: 100%; background: var(--black); color: #fff; padding: 15px; border: none; cursor: pointer; font-weight: 700; border-radius: 5px; margin-top: 15px; }
        .btn-back-modal { width: 100%; background: #eee; color: #333; padding: 15px; border: none; cursor: pointer; font-weight: 700; border-radius: 5px; margin-top: 10px; }
        .input-box { width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        #slots { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px; }
        .slot { background: #f4f4f4; padding: 10px; text-align: center; border-radius: 5px; cursor: pointer; font-size: 13px; }
        .slot.selected { background: var(--gold); color: #000; }

        @media(max-width:768px){ 
            .hero-grid, .skills-grid { grid-template-columns: 1fr; }
            .hero-img-box { order: -1; margin-bottom: 40px; }
            .profile-hero { padding-top: 100px; text-align: center; }
            .hero-info h1 { font-size: 40px; }
            .stats-row { justify-content: center; }
        }
    </style>
</head>
<body>

    <header>
        <div class="container header-flex">
            <a href="index.php" class="logo"><img src="assets/LOGO LEGACY SF/2.png" alt="Legacy"> LEGACY <span>STYLE</span></a>
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </header>

    <section class="profile-hero">
        <div class="container hero-grid">
            <div class="hero-info">
                <h1>Vitor "Vitinho"</h1>
                <span class="hero-role">Fade Specialist & Colorist</span>
                <p class="hero-bio">"Estilo é a única coisa que você não pode comprar. Mas um bom fade ajuda."</p>
                <p>Referência em degradês (Fade) e colorimetria avançada (Platinado). Vitinho traz precisão milimétrica e um estilo urbano que define tendências. Se você busca o corte perfeito com aquele acabamento navalhado impecável, você encontrou seu barbeiro.</p>
                
                <div class="stats-row">
                    <div class="stat"><strong>+1.8k</strong><small>Degradês</small></div>
                    <div class="stat"><strong>4</strong><small>Anos Exp.</small></div>
                    <div class="stat"><strong>4.9</strong><small>Avaliação</small></div>
                </div>

                <button class="btn-cta" onclick="openModal()">Agendar com Vitinho</button>
            </div>
            <div class="hero-img-box">
                <img src="assets/fotovitor.png" alt="Vitinho">
            </div>
        </div>
    </section>

    <section class="skills-section">
        <div class="container skills-grid">
            <div class="skills-text">
                <div class="section-title">
                    <h2>Especialidades</h2>
                    <p>Foco total em técnicas modernas e acabamentos de alta definição.</p>
                </div>
                <p>Meu diferencial é o detalhe. O fade limpo, sem marcações, e o alinhamento geométrico do perfil. Além disso, sou especialista em química capilar, garantindo platinados saudáveis e com a tonalidade perfeita.</p>
            </div>
            <div class="skills-bars">
                <div class="skill-item">
                    <div class="skill-info"><span>Degradê (Fade)</span><span>100%</span></div>
                    <div class="progress-bar"><div class="progress" style="width:100%"></div></div>
                </div>
                <div class="skill-item">
                    <div class="skill-info"><span>Platinado (Color)</span><span>95%</span></div>
                    <div class="progress-bar"><div class="progress" style="width:95%"></div></div>
                </div>
                <div class="skill-item">
                    <div class="skill-info"><span>Freestyle (Desenho)</span><span>90%</span></div>
                    <div class="progress-bar"><div class="progress" style="width:90%"></div></div>
                </div>
                <div class="skill-item">
                    <div class="skill-info"><span>Pigmentação</span><span>95%</span></div>
                    <div class="progress-bar"><div class="progress" style="width:95%"></div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="portfolio">
        <div class="container">
            <div class="section-title" style="text-align: center;">
                <h2>Galeria de Trabalhos</h2>
                <div style="width:60px; height:3px; background:var(--gold); margin:10px auto;"></div>
            </div>
            
            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1599351431202-6e0c06e76553?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Fade">
                    <div class="overlay"><h4>High Fade</h4><p>Degradê Alto</p></div>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1621605815971-fbc98d665033?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Platinado">
                    <div class="overlay"><h4>Nevou</h4><p>Platinado Global</p></div>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1503951914875-befbb649186f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Freestyle">
                    <div class="overlay"><h4>Razor Art</h4><p>Desenho na Navalha</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews-section">
        <div class="container">
            <div class="review-card">
                <p class="review-text">"O melhor degradê que já fiz em Curitiba. O Vitinho é detalhista demais, não deixa passar nada. Virei cliente fiel."</p>
                <span class="review-author">- Gustavo Lima, Cliente há 1 ano</span>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 Legacy Style - Vitinho. Todos os direitos reservados.</p>
        </div>
    </footer>

    <div id="modal" class="modal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Agendar com Vitinho</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                
                <div id="step1" class="step active">
                    <h4 style="margin-bottom:20px;">Selecione os Serviços:</h4>
                    <?php 
                    $servicos = $pdo->query("SELECT * FROM servicos")->fetchAll();
                    foreach($servicos as $s): 
                    ?>
                    <label class="opt-card">
                        <input type="checkbox" class="chk-serv" data-id="<?= $s['id'] ?>" data-price="<?= $s['preco'] ?>" data-dur="<?= $s['duracao'] ?>" style="display:none;">
                        <div style="flex:1"><strong><?= $s['nome'] ?></strong><br><small style="color:#777;"><?= $s['duracao'] ?> min</small></div>
                        <div style="font-weight:700;">R$ <?= number_format($s['preco'], 2, ',', '.') ?></div>
                        <i class="fas fa-check-circle icon-check" style="color:#eee; font-size:20px;"></i>
                    </label>
                    <?php endforeach; ?>
                    <div style="text-align:right; margin-top:20px; font-weight:700;">Total: <span id="totalDisplay" style="color:var(--gold);">R$ 0,00</span></div>
                    <button class="btn-next" onclick="checkServ()">Escolher Horário</button>
                </div>

                <div id="step2" class="step">
                    <h4 style="margin-bottom:20px;">Data e Hora:</h4>
                    <input type="date" id="dateInp" class="input-box" min="<?= date('Y-m-d') ?>">
                    <div id="slots"></div>
                    <button class="btn-back-modal" onclick="toStep(1)">Voltar</button>
                </div>

                <div id="step3" class="step">
                    <h4 style="margin-bottom:20px;">Seus Dados:</h4>
                    <form action="salvar_agendamento.php" method="POST">
                        <input type="hidden" name="barbeiro_id" value="<?= $barbeiro_id ?>">
                        <input type="hidden" name="servicos" id="h_servs">
                        <input type="hidden" name="data" id="h_date">
                        <input type="hidden" name="hora" id="h_time">
                        <input type="hidden" name="valor_total" id="h_price">

                        <input type="text" name="nome" placeholder="Seu Nome" class="input-box" required>
                        <input type="tel" name="telefone" placeholder="WhatsApp" class="input-box" required>
                        <input type="email" name="email" placeholder="E-mail" class="input-box" required>

                        <div style="background:#f4f4f4; padding:15px; border-radius:8px; margin-bottom:15px;">
                            <label style="display:flex; gap:10px; margin-bottom:10px; cursor:pointer;"><input type="radio" name="payment_method" value="presencial" checked> Pagar na Loja</label>
                            <label style="display:flex; gap:10px; cursor:pointer;"><input type="radio" name="payment_method" value="pix"> Pagar Online (Pix)</label>
                        </div>

                        <button type="submit" class="btn-next">Confirmar Agendamento</button>
                        <button type="button" class="btn-back-modal" onclick="toStep(2)">Voltar</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        let booking = { services:[], price:0, duration:0 };
        const modal = document.getElementById('modal');
        const barberId = <?= $barbeiro_id ?>;

        function openModal() { modal.style.display = 'block'; toStep(1); }
        document.querySelector('.close').onclick = () => modal.style.display = 'none';

        function toStep(n) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step'+n).classList.add('active');
        }

        document.querySelectorAll('.chk-serv').forEach(chk => {
            chk.onchange = () => {
                let parent = chk.closest('.opt-card');
                let icon = parent.querySelector('.icon-check');
                let id = chk.dataset.id;
                let p = parseFloat(chk.dataset.price);
                let d = parseInt(chk.dataset.dur);

                if(chk.checked) {
                    parent.classList.add('selected');
                    icon.style.color = 'var(--gold)';
                    booking.services.push(id);
                    booking.price += p;
                    booking.duration += d;
                } else {
                    parent.classList.remove('selected');
                    icon.style.color = '#eee';
                    booking.services = booking.services.filter(x => x !== id);
                    booking.price -= p;
                    booking.duration -= d;
                }
                document.getElementById('totalDisplay').innerText = "R$ " + booking.price.toFixed(2).replace('.',',');
            }
        });

        function checkServ() {
            if(booking.services.length === 0) alert('Selecione um serviço.');
            else toStep(2);
        }

        document.getElementById('dateInp').onchange = function() {
            let val = this.value;
            let div = document.getElementById('slots');
            div.innerHTML = 'Carregando...';
            
            fetch(`get_horarios.php?barbeiro_id=${barberId}&data=${val}&duracao=${booking.duration}`)
            .then(res => res.json())
            .then(data => {
                div.innerHTML = '';
                if(data.length === 0) div.innerHTML = '<span style="color:red">Sem horários.</span>';
                data.forEach(t => {
                    let s = document.createElement('div');
                    s.className = 'slot'; s.innerText = t;
                    s.onclick = () => {
                        document.getElementById('h_servs').value = booking.services.join(',');
                        document.getElementById('h_date').value = val;
                        document.getElementById('h_time').value = t;
                        document.getElementById('h_price').value = booking.price;
                        toStep(3);
                    };
                    div.appendChild(s);
                });
            });
        };
    </script>
</body>
</html>