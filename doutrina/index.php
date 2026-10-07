<?php
$pageTitle       = 'Doutrina | Assembleia Cristã Jardim do Mar';
$pageDescription = 'Conheça a doutrina da Assembleia Cristã Jardim do Mar: William Branham, Ewald Frank, Batismo por Imersão e Unicismo.';
$assetsPath      = '../';
$rootPath        = '../';
include_once __DIR__ . '/../includes/header.php';

$articles = [
    [
        'slug'   => 'william-branham',
        'title'  => 'William Marrion Branham',
        'img'    => '../assets/images/wmb 1.jpg',
        'teaser' => 'O profeta do séc. XX cujo ministério marcou o maior derramamento do Espírito Santo desde o dia de Pentecostes.',
    ],
    [
        'slug'   => 'ewald-frank',
        'title'  => 'Ewald Frank',
        'img'    => '../assets/images/ewald-frank.jpeg',
        'teaser' => 'Mensageiro do Evangelho nascido na Alemanha, dedicado a proclamar as mensagens bíblicas ao redor do mundo.',
    ],
    [
        'slug'   => 'batismo-por-imersao',
        'title'  => 'Batismo Por Imersão',
        'img'    => '../assets/images/batismo-imersao.png',
        'teaser' => 'O símbolo do sepultamento do homem velho e da ressurreição para uma nova vida em Cristo Jesus nas águas.',
    ],
    [
        'slug'   => 'fundamentos-da-crenca',
        'title'  => 'Fundamentos da crença',
        'img'    => '../assets/images/unicismo.png',
        'teaser' => 'A doutrina bíblica que afirma que Deus é absolutamente Um, manifestado como Pai, Filho e Espírito Santo.',
    ],
];
?>

<!-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ -->
<section class="hero" style="min-height:380px;">
    <div class="hero-bg"
         style="background-image: url('../assets/images/galeria-hero.jpeg');"
         role="img" aria-label="Doutrina da Igreja"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">Doutrina</h1>
        <span class="hero-verse">O Fundamento da Nossa Fé</span>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     CARDS
══════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <p class="doutrina-intro reveal">
            A Assembleia Cristã Jardim do Mar está firmemente alicerçada nas verdades eternas das Sagradas Escrituras. Conheça os pilares doutrinários que fundamentam a nossa fé e prática cristã.
        </p>
        <div class="doutrina-grid">
            <?php foreach ($articles as $i => $art): ?>
            <a class="doutrina-card reveal" style="transition-delay:<?= $i * 80 ?>ms;"
               href="<?= $art['slug'] ?>.php"
               aria-label="Ler sobre <?= htmlspecialchars($art['title']) ?>">
                <div class="doutrina-card-img-wrap">
                    <img src="<?= htmlspecialchars($art['img']) ?>"
                         alt="<?= htmlspecialchars($art['title']) ?>"
                         loading="lazy">
                    <div class="doutrina-card-overlay"></div>
                </div>
                <div class="doutrina-card-body">
                    <h2><?= htmlspecialchars($art['title']) ?></h2>
                    <p><?= htmlspecialchars($art['teaser']) ?></p>
                    <span class="doutrina-card-link">Ler mais →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<style>
.doutrina-intro {
    text-align: center;
    font-size: 16px;
    line-height: 1.8;
    color: #666;
    max-width: 680px;
    margin: -24px auto 52px;
}
.doutrina-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 28px;
}
.doutrina-card {
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    box-shadow: 0 4px 24px rgba(0,0,0,.1);
    transition: transform .28s ease, box-shadow .28s ease;
    background: #fff;
}
.doutrina-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 48px rgba(13,43,94,.18);
}
.doutrina-card-img-wrap {
    position: relative;
    overflow: hidden;
    height: 220px;
}
.doutrina-card-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .45s ease;
}
.doutrina-card:hover .doutrina-card-img-wrap img { transform: scale(1.06); }
.doutrina-card-overlay {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 60%;
    background: linear-gradient(to top, rgba(13,43,94,.5), transparent);
}
.doutrina-card-body {
    padding: 22px 24px 26px;
    border-top: 3px solid var(--azul-claro);
}
.doutrina-card-body h2 {
    font-family: 'Cinzel', serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--azul-escuro);
    margin-bottom: 10px;
}
.doutrina-card-body p {
    font-size: 13.5px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 16px;
}
.doutrina-card-link {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--azul-claro);
    letter-spacing: .8px;
    text-transform: uppercase;
    transition: color .2s;
}
.doutrina-card:hover .doutrina-card-link { color: var(--azul-escuro); }

@media (max-width: 700px) {
    .doutrina-grid { grid-template-columns: 1fr; }
}
</style>
