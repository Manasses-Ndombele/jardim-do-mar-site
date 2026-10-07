<?php
$pageTitle       = 'Assembleia Cristã Jardim do Mar — Início';
$pageDescription = 'Assembleia Cristã Jardim do Mar, Luanda, Angola. Jesus Cristo é o mesmo Ontem, Hoje e Eternamente! Hebreus 13:8.';
$assetsPath      = '';
$rootPath        = '';
include_once __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ -->
<section class="hero" id="inicio">
    <div class="hero-bg" style="background-image: url('./assets/images/hero-home.jpeg');" role="img" aria-label="Congregação reunida no templo"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">Jesus Cristo é o mesmo<br>Ontem, Hoje e Eternamente!</h1>
        <span class="hero-verse">Hebreus 13:8</span>
        <a class="btn btn-red"
           href="https://www.youtube.com/@AssembleiaCristaJardimdoMar./streams"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Assistir lives no YouTube">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
            Assistir Lives
        </a>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     INTRODUÇÃO
══════════════════════════════════════════════ -->
<section class="section" id="introducao" style="background:#fff;">
    <div class="container">
        <div class="intro-grid">

            <div class="intro-player reveal">
                <div class="youtube-embed">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/IMYJ9h53jRc?si=yl6oBdXkgHuR0w9J" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>

            <div class="intro-text reveal">
                <h2>Introdução à Assembleia Cristã Jardim do Mar</h2>
                <p>Somos uma igreja da Mensagem do Último Tempo situada no Bairro São João, Município do Cazenga, em Luanda. Fundada sobre as sólidas bases do Evangelho de nosso Senhor Jesus Cristo, a nossa missão é proclamar a Palavra de Deus com fidelidade, amor e poder sobrenatural.</p>
                <p>Cremos que Jesus Cristo é o mesmo ontem, hoje e para sempre — e que o Seu poder de curar, libertar e salvar permanece ativo nos nossos dias, tal como manifestado no dia de Pentecostes.</p>
                <a class="btn btn-red"
                   href="https://www.youtube.com/@AssembleiaCristaJardimdoMar./streams"
                   target="_blank" rel="noopener noreferrer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    Assistir Lives
                </a>
            </div>

        </div>
    </div>
</section>

<div class="sep"></div>

<!-- ══════════════════════════════════════════════
     GALERIA
══════════════════════════════════════════════ -->
<section class="section" id="galeria">
    <div class="container">
        <h2 class="section-title reveal">Galeria</h2>
        <div class="gallery-grid reveal">
            <?php
            $galleryImages = [
                ['src' => './assets/images/culto-dominical.jpeg', 'alt' => 'Culto Dominical'],
                ['src' => './assets/images/congregacao.jpeg', 'alt' => 'Congregação reunida'],
                ['src' => './assets/images/louvor.jpeg', 'alt' => 'Momento de louvor'],
                ['src' => './assets/images/foto-1.jpeg', 'alt' => 'Pregação da Palavra'],
                ['src' => './assets/images/batismo.jpeg', 'alt' => 'Batismo nas águas'],
                ['src' => './assets/images/jovens.jpeg', 'alt' => 'Jovens da congregação'],
            ];
            foreach ($galleryImages as $img): ?>
            <div class="gallery-item" aria-label="<?= htmlspecialchars($img['alt']) ?>">
                <img src="<?= $img['src'] ?>" alt="<?= htmlspecialchars($img['alt']) ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
        <div class="gallery-cta reveal">
            <a class="btn btn-red" href="multimedia/index.php">→ VER TUDO</a>
        </div>
    </div>
</section>

<div class="sep"></div>

<!-- ══════════════════════════════════════════════
     CULTO EM DESTAQUE
══════════════════════════════════════════════ -->
<section class="culto-destaque">
    <div class="culto-destaque-bg" role="img" aria-label="Congregação em adoração"></div>
    <div class="culto-overlay"></div>
    <div class="container">
        <div class="culto-content reveal">
            <h2>Culto De Adoração Ao Senhor</h2>
            <div class="culto-meta">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>Toda segunda Quarta-feira do mês</span>
            </div>
            <p>Um momento especial de adoração e comunhão com o Senhor. Venha participar desta celebração sagrada onde a presença de Deus se manifesta poderosamente entre os Seus filhos reunidos em unidade e amor fraternal.</p>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     PROGRAMA DOS CULTOS
══════════════════════════════════════════════ -->
<section class="section" id="programa">
    <div class="container">
        <h2 class="section-title reveal">Programa dos Cultos</h2>

        <?php
        $cultos = [
            ['dia' => 'DOM', 'hora' => '09:00 – 12:30', 'cor' => 'bg-vermelho',    'nome' => 'Culto Dominical', 'desc' => 'O culto principal da semana, com louvor, adoração, pregação da Palavra e comunhão entre os irmãos em Cristo.'],
            ['dia' => 'TER', 'hora' => '00:00 – 03:30', 'cor' => 'bg-azul-escuro', 'nome' => 'Culto De Vigília',  'desc' => 'Uma noite de intercessão, oração intensa e busca pela presença de Deus. Venha velar e orar pelo nosso povo.'],
            ['dia' => 'QUA', 'hora' => '15:00 – 17:30', 'cor' => 'bg-azul-medio',  'nome' => 'Culto De Oração',  'desc' => 'Momento dedicado à oração em conjunto, intercessão pelos enfermos, necessitados e pela igreja de Deus.'],
            ['dia' => 'SEX', 'hora' => '15:00 – 17:30', 'cor' => 'bg-azul-claro',  'nome' => 'Culto De Ensino',  'desc' => 'Estudo aprofundado da Palavra de Deus, edificando os crentes na fé e no conhecimento das Escrituras Sagradas.'],
        ];
        ?>

        <div class="programa-grid reveal">
            <?php foreach ($cultos as $culto): ?>
            <div class="programa-card">
                <div class="programa-dia <?= $culto['cor'] ?>">
                    <span class="programa-dia-label"><?= $culto['dia'] ?></span>
                    <span class="programa-dia-hora"><?= $culto['hora'] ?></span>
                </div>
                <div class="programa-info">
                    <h3><?= htmlspecialchars($culto['nome']) ?></h3>
                    <p><?= htmlspecialchars($culto['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="sep"></div>

<!-- ══════════════════════════════════════════════
     CONTATOS
══════════════════════════════════════════════ -->
<section class="section contatos-section" id="contatos">
    <div class="container">
        <h2 class="section-title reveal">Contatos</h2>

        <div class="contatos-grid reveal">
            <div class="contato-item">
                <div class="contato-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6 19.79 19.79 0 0 1 1.61 5 2 2 0 0 1 3.6 2.84h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.5a16 16 0 0 0 6 6l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 17z"/>
                    </svg>
                </div>
                <div class="contato-text">
                    <h4>Telefone</h4>
                    <p><a href="tel:+244923224456">+244 923 224 456</a></p>
                </div>
            </div>
            <div class="contato-item">
                <div class="contato-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>
                <div class="contato-text">
                    <h4>Email</h4>
                    <p><a href="mailto:jardimdomar@gmail.com">jardimdomar@gmail.com</a></p>
                </div>
            </div>
        </div>

        <div class="map-embed reveal" aria-label="Mapa da localização da igreja">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15772.728!2d13.3267!3d-8.8368!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1a51f15f32b15555%3A0x8830016f98c2ffa9!2sCazenga%2C%20Luanda%2C%20Angola!5e0!3m2!1spt!2sao!4v1700000000000"
                allowfullscreen=""
                loading="lazy"
                title="Localização da Assembleia Cristã Jardim do Mar"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>

<!-- Page-specific CSS -->
<style>
/* ── Intro Grid ── */
.intro-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 64px;
    align-items: center;
}
.youtube-embed {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 8px 36px rgba(0,0,0,.16);
}
.youtube-embed iframe {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border: none;
    border-radius: 10px;
}
.intro-text h2 {
    font-family: 'Cinzel', serif;
    font-size: clamp(20px, 2.6vw, 26px);
    font-weight: 700;
    color: var(--azul-escuro);
    line-height: 1.35;
    margin-bottom: 22px;
}
.intro-text p {
    font-size: 15px;
    line-height: 1.85;
    color: #555;
    margin-bottom: 20px;
}

/* ── Culto Destaque ── */
.culto-destaque {
    position: relative;
    padding: 88px 0;
    overflow: hidden;
}
.culto-destaque-bg {
    position: absolute;
    inset: 0;
    background: url('./assets/images/adoracao.jpeg') center/cover no-repeat;
    background-attachment: fixed;
}
.culto-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(10,28,68,.9) 0%, rgba(26,74,122,.82) 100%);
}
.culto-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #fff;
    max-width: 680px;
    margin: 0 auto;
}
.culto-content h2 {
    font-family: 'Cinzel', serif;
    font-size: clamp(22px, 3.5vw, 36px);
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 0 2px 10px rgba(0,0,0,.5);
}
.culto-meta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 16px;
    color: rgba(255,255,255,.82);
    margin-bottom: 20px;
}
.culto-content p {
    font-size: 15px;
    line-height: 1.75;
    color: rgba(255,255,255,.72);
}

/* ── Responsive ── */
@media (max-width: 860px) {
    .intro-grid { grid-template-columns: 1fr; gap: 36px; }
}
</style>
