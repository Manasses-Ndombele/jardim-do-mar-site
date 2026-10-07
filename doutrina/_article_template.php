<?php
/**
 * Doctrine article template
 * Include this after setting $article array and page vars
 */
?>

<!-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ -->
<section class="hero" style="min-height:340px;">
    <div class="hero-bg"
         style="background-image: url('<?= htmlspecialchars($article['hero_img']) ?>');"
         role="img" aria-label="<?= htmlspecialchars($article['title']) ?>"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content" style="padding-top:88px;">
        <h1 class="hero-title" style="font-size:clamp(20px,3.5vw,36px);"><?= htmlspecialchars($article['title']) ?></h1>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     ARTICLE CONTENT
══════════════════════════════════════════════ -->
<article class="article-page">
    <div class="container article-container">

        <img class="article-banner reveal"
             src="<?= htmlspecialchars($article['banner_img']) ?>"
             alt="<?= htmlspecialchars($article['title']) ?>"
             loading="lazy">

        <h2 class="article-title reveal"><?= htmlspecialchars($article['title']) ?></h2>

        <div class="article-body">
            <?= $article['body'] /* trusted HTML */ ?>
        </div>

        <a class="voltar-link reveal" href="index.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            Voltar à Doutrina
        </a>

    </div>
</article>

<style>
.hero-bg { background-attachment: fixed; }
.article-page { padding: 72px 0 80px; }
.article-container { max-width: 820px; }
.article-banner {
    width: 100%;
    max-height: 420px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 40px;
    box-shadow: 0 6px 32px rgba(0,0,0,.12);
}
.article-title {
    font-family: 'Cinzel', serif;
    font-size: clamp(22px, 3vw, 30px);
    font-weight: 700;
    color: var(--azul-escuro);
    margin-bottom: 30px;
    padding-bottom: 18px;
    border-bottom: 2px solid var(--azul-claro);
    line-height: 1.3;
}
.article-body p {
    font-size: 15.5px;
    line-height: 1.9;
    color: #444;
    margin-bottom: 22px;
}
.article-body blockquote {
    border-left: 4px solid var(--azul-claro);
    padding: 18px 26px;
    margin: 28px 0;
    background: #EBF5FB;
    border-radius: 0 10px 10px 0;
    font-style: italic;
    color: #555;
    font-size: 15.5px;
    line-height: 1.75;
}
.article-body h3 {
    font-family: 'Cinzel', serif;
    font-size: 17px;
    font-weight: 700;
    color: var(--azul-escuro);
    margin: 32px 0 14px;
}
</style>
