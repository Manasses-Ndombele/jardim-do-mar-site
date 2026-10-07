<?php
$pageTitle       = 'Sobre Nós | Assembleia Cristã Jardim do Mar';
$pageDescription = 'Conheça a história e a missão da Assembleia Cristã Jardim do Mar, em Luanda, Angola.';
$assetsPath      = '';
$rootPath        = '';
include_once __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ -->
<section class="hero sobre-hero">
    <div class="hero-bg"
         style="background-image: url('./assets/images/hero-home.jpeg');"
         role="img" aria-label="Assembleia Cristã Jardim do Mar"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">Introdução à Assembleia Cristã<br>Jardim do Mar</h1>
        <a class="btn btn-red"
           href="https://www.youtube.com/@AssembleiaCristaJardimdoMar./streams"
           target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
            Assistir Lives
        </a>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     YOUTUBE PLAYER
══════════════════════════════════════════════ -->
<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div class="sobre-youtube-wrap reveal">
            <div class="youtube-embed">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/IMYJ9h53jRc?si=yl6oBdXkgHuR0w9J" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     NOSSA HISTÓRIA
══════════════════════════════════════════════ -->
<section class="section">
    <div class="container">

        <h2 class="section-title reveal">Nossa História</h2>

        <div class="historia-text reveal">
            <p>A Assembleia Cristã Jardim do Mar é uma congregação evangélica fundada sobre as sólidas bases do Evangelho de Jesus Cristo, localizada no Município do Cazenga, Bairro São João (Cuca), em Luanda, Angola. Desde a sua fundação, a nossa missão tem sido proclamar a Palavra de Deus com fidelidade, amor e poder sobrenatural.</p>

            <p>Cremos que Jesus Cristo é o mesmo ontem, hoje e para sempre — e que o Seu poder de curar, libertar e salvar permanece ativo nos nossos dias, tal como foi manifestado no dia de Pentecostes. Seguimos os ensinamentos bíblicos restaurados através do ministério profético do irmão William Marrion Branham, cujas mensagens têm edificado e transformado vidas ao redor do mundo.</p>

            <p>A nossa congregação cresceu ao longo dos anos, tornando-se uma família espiritual unida pelo amor de Deus e pelo desejo de ver Angola e o mundo inteiro alcançados pelo poderoso Evangelho de Jesus Cristo. O nosso lema é simples mas profundo: Jesus Cristo é o mesmo ontem, hoje e eternamente — Hebreus 13:8.</p>
        </div>

        <!-- SPLIT SECTION: image + text -->
        <div class="historia-split reveal">
            <div class="historia-split-img">
                <img src="./assets/images/pastor.jpg"
                     alt="Pastor e Congregação da Assembleia Cristã Jardim do Mar"
                     loading="lazy">
            </div>
            <div class="historia-split-text">
                <h3>Crescimento e Bênção</h3>
                <p>A história da Assembleia Cristã Jardim do Mar é uma história de fé, sacrifício e graça divina. Desde os primeiros cultos realizados em condições simples, até ao moderno templo que hoje nos acolhe, cada etapa desta jornada foi marcada pela mão poderosa de Deus.</p>
                <p>Os nossos cultos são momentos de profunda comunhão com Deus e entre os irmãos. Cada domingo, centenas de fiéis se reúnem para adorar ao Senhor em espírito e em verdade, ouvir a Palavra de Deus pregada com unção e poder, e testemunhar as obras milagrosas do Senhor Jesus Cristo.</p>
                <p>A nossa visão é continuar expandindo o Reino de Deus em Angola e além-fronteiras, através da pregação do Evangelho, da oração, do ensino bíblico e do amor fraternal que deve caracterizar todos os filhos de Deus.</p>
                <a class="btn btn-blue" href="multimedia/pregacoes.php" style="margin-top:8px;">
                    Ouvir Pregações
                </a>
            </div>
        </div>

    </div>
</section>

<div class="sep"></div>

<!-- ══════════════════════════════════════════════
     GALERIA DA CONSTRUÇÃO
══════════════════════════════════════════════ -->
<section class="section" style="background:var(--cinza-bg);">
    <div class="container">
        <h2 class="section-title reveal">Galeria da Construção</h2>
        <?php
        $construcao = [
            ['src' => './assets/images/foto-1.jpeg', 'alt' => 'Templo — Vista Frontal'],
            ['src' => './assets/images/foto-2.jpeg', 'alt' => 'Templo — Vista Lateral'],
            ['src' => './assets/images/foto-3.jpeg', 'alt' => 'Interior do Templo'],
            ['src' => './assets/images/foto-4.jpeg', 'alt' => 'Construção — Fase 1'],
            ['src' => './assets/images/foto-5.jpeg', 'alt' => 'Construção — Fase 2'],
            ['src' => './assets/images/foto-6.jpeg', 'alt' => 'Templo Inaugurado'],
        ];
        ?>
        <div class="gallery-grid reveal">
            <?php foreach ($construcao as $foto): ?>
            <div class="gallery-item" aria-label="<?= htmlspecialchars($foto['alt']) ?>">
                <img src="<?= htmlspecialchars($foto['src']) ?>"
                     alt="<?= htmlspecialchars($foto['alt']) ?>"
                     loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>

<style>
.sobre-hero { min-height: 480px; }

/* YouTube player */
.sobre-youtube-wrap {
    max-width: 640px;
    margin: 0 auto 0;
}
.youtube-embed {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 8px 36px rgba(0,0,0,.16);
}
.youtube-embed iframe {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border: none;
    border-radius: 12px;
}

/* Historia */
.historia-text p {
    font-size: 15.5px;
    line-height: 1.9;
    color: #555;
    margin-bottom: 20px;
    max-width: 820px;
    margin-left: auto;
    margin-right: auto;
}
.historia-split {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 52px;
    align-items: start;
    margin-top: 56px;
}
.historia-split-img img {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0 8px 30px rgba(0,0,0,.14);
}
.historia-split-text h3 {
    font-family: 'Cinzel', serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--azul-escuro);
    margin-bottom: 20px;
}
.historia-split-text p {
    font-size: 15px;
    line-height: 1.85;
    color: #555;
    margin-bottom: 18px;
}

/* Values */
.sobre-values { background: #fff; }
.values-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}
.value-card {
    background: var(--cinza-bg);
    border-radius: 12px;
    padding: 32px 24px;
    text-align: center;
    border: 1px solid var(--borda);
    transition: transform .25s, box-shadow .25s;
}
.value-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(13,43,94,.1);
}
.value-icon {
    width: 60px;
    height: 60px;
    background: var(--azul-escuro);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    color: #fff;
}
.value-card h3 {
    font-family: 'Cinzel', serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--azul-escuro);
    margin-bottom: 10px;
    letter-spacing: .5px;
}
.value-card p {
    font-size: 13.5px;
    color: #777;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 960px) {
    .values-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 800px) {
    .historia-split { grid-template-columns: 1fr; gap: 32px; }
}
@media (max-width: 500px) {
    .values-grid { grid-template-columns: 1fr; }
}
</style>
