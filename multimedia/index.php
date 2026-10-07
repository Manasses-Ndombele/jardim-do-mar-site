<?php
$pageTitle       = 'Multimídia — Galeria | Assembleia Cristã Jardim do Mar';
$pageDescription = 'Galeria de fotos da Assembleia Cristã Jardim do Mar. Quão bom e suave que os irmãos estejam juntos!';
$assetsPath      = '../';
$rootPath        = '../';
include_once __DIR__ . '/../includes/header.php';
?>

<!-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ -->
<section class="hero" style="min-height:420px;">
    <div class="hero-bg"
         style="background-image: url('../assets/images/galeria-hero.jpeg');"
         role="img" aria-label="Congregação"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title"> Oh! Quão Bom E Quão Suave É Que <br>Os Irmãos Vivam Em União!</h1>
        <span class="hero-verse">Salmos 133:1</span>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     GALERIA COMPLETA
══════════════════════════════════════════════ -->
<section class="section">
    <div class="container">

        <h2 class="section-title reveal">Galeria</h2>

        <?php
        // In a real project these would come from a database or filesystem scan
        $photos = [
            ['src' => '../assets/images/adoracao.jpeg',    'alt' => 'Culto — 01'],
            ['src' => '../assets/images/congregacao.jpeg',    'alt' => 'Culto — 02'],
            ['src' => '../assets/images/culto-dominical.jpeg',    'alt' => 'Culto — 03'],
            ['src' => '../assets/images/galeria-hero.jpeg', 'alt' => 'Adoração — 01'],
            ['src' => '../assets/images/foto-1.jpeg', 'alt' => 'Adoração — 02'],
            ['src' => '../assets/images/jovens.jpeg', 'alt' => 'Adoração — 03'],
            ['src' => '../assets/images/foto-6.jpeg', 'alt' => 'Pregação — 01'],
            ['src' => '../assets/images/louvor.jpeg', 'alt' => 'Pregação — 02'],
            ['src' => '../assets/images/foto-2.jpeg', 'alt' => 'Pregação — 03'],
            ['src' => '../assets/images/foto-3.jpeg',   'alt' => 'Jovens — 01'],
            ['src' => '../assets/images/foto-4.jpeg',   'alt' => 'Jovens — 02'],
            ['src' => '../assets/images/foto-5.jpeg',   'alt' => 'Jovens — 03'],
        ];
        ?>

        <div class="gallery-grid reveal">
            <?php foreach ($photos as $photo): ?>
            <div class="gallery-item" aria-label="<?= htmlspecialchars($photo['alt']) ?>">
                <img src="<?= htmlspecialchars($photo['src']) ?>"
                     alt="<?= htmlspecialchars($photo['alt']) ?>"
                     loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:16px;">
            <a class="voltar-link" href="../index.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                Voltar ao Início
            </a>
        </div>

    </div>
</section>

<!-- SUB-NAV MULTIMÍDIA -->
<section class="multimedia-subnav">
    <div class="container">
        <div class="subnav-grid">
            <a class="subnav-card active" href="index.php">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M3 15h18M9 3v18"/>
                </svg>
                <span>Galeria</span>
            </a>
            <a class="subnav-card" href="pregacoes.php">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <polygon points="10 8 16 12 10 16 10 8"/>
                </svg>
                <span>Pregações &amp; Áudios</span>
            </a>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<style>
.multimedia-subnav {
    background: var(--cinza-bg);
    padding: 48px 0;
    border-top: 1px solid var(--borda);
}
.subnav-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    max-width: 600px;
    margin: 0 auto;
}
.subnav-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 32px 20px;
    background: #fff;
    border-radius: 10px;
    border: 2px solid var(--borda);
    text-decoration: none;
    color: var(--azul-escuro);
    font-family: 'Cinzel', serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    transition: border-color .2s, box-shadow .2s, transform .2s;
}
.subnav-card:hover,
.subnav-card.active {
    border-color: var(--azul-claro);
    box-shadow: 0 4px 20px rgba(46,134,193,.18);
    transform: translateY(-2px);
}
.subnav-card.active {
    background: var(--azul-escuro);
    color: #fff;
    border-color: var(--azul-escuro);
}
@media (max-width: 500px) {
    .subnav-grid { grid-template-columns: 1fr; }
}
</style>
