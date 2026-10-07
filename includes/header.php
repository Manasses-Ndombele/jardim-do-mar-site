<?php
// Shared header — include at top of every page
// Usage: include_once __DIR__ . '/includes/header.php';
// Set $pageTitle and $pageDescription before including

$pageTitle       = $pageTitle       ?? 'Assembleia Cristã Jardim do Mar';
$pageDescription = $pageDescription ?? 'Assembleia Cristã Jardim do Mar — Município do Cazenga, Luanda, Angola. Jesus Cristo é o mesmo Ontem, Hoje e Eternamente! — Hebreus 13:8';
$pageOgImage     = $pageOgImage     ?? 'https://placehold.co/1200x630/0D2B5E/ffffff?text=Assembleia+Cristã+Jardim+do+Mar';

// Determine active nav item from current script name
$currentFile = basename($_SERVER['PHP_SELF']);
function isActive(string $file, string $current): string {
    return $file === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">

<!-- Open Graph -->
<meta property="og:title"       content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta property="og:image"       content="<?= $pageOgImage ?>">
<meta property="og:type"        content="website">
<meta property="og:locale"      content="pt_AO">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="<?= $assetsPath ?? '' ?>assets/images/favicon.ico">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">

<!-- Global CSS -->
<link rel="stylesheet" href="<?= $assetsPath ?? '' ?>assets/css/global.css">
</head>
<body>

<!-- ═══════════════════════ NAVBAR ═══════════════════════ -->
<nav class="navbar" id="navbar">
    <a class="navbar-brand" href="<?= $rootPath ?? '' ?>index.php">
        <div class="navbar-logo">
            <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="20" cy="20" r="18" fill="none" stroke="white" stroke-width="1.5"/>
                <path d="M20 8 L20 32 M12 16 L28 16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M8 28 Q20 22 32 28" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="navbar-title">
            <span>ASSEMBLEIA CRISTÃ</span>
            <span>JARDIM DO MAR</span>
        </div>
    </a>

    <ul class="navbar-nav" role="navigation" aria-label="Menu principal">
        <li><a href="<?= $rootPath ?? '' ?>index.php"              class="<?= isActive('index.php', $currentFile) ?>">Início</a></li>
        <li><a href="<?= $rootPath ?? '' ?>multimedia/index.php"   class="<?= isActive('index.php',   $currentFile) && strpos($_SERVER['PHP_SELF'],'multimedia') !== false ? 'active' : (isActive('pregacoes.php', $currentFile) ? 'active' : (strpos($_SERVER['PHP_SELF'],'multimedia') !== false ? 'active' : '')) ?>">Multimídia</a></li>
        <li><a href="<?= $rootPath ?? '' ?>doutrina/index.php"     class="<?= (strpos($_SERVER['PHP_SELF'],'doutrina') !== false) ? 'active' : '' ?>">Doutrina</a></li>
        <li><a href="<?= $rootPath ?? '' ?>sobre.php"              class="<?= isActive('sobre.php', $currentFile) ?>">Sobre</a></li>
    </ul>

    <button class="hamburger" id="hamburger" aria-label="Abrir menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Menu mobile">
    <button class="mobile-menu-close" id="mobileClose" aria-label="Fechar menu">&#10005;</button>
    <a href="<?= $rootPath ?? '' ?>index.php">Início</a>
    <a href="<?= $rootPath ?? '' ?>multimedia/index.php">Multimídia</a>
    <a href="<?= $rootPath ?? '' ?>doutrina/index.php">Doutrina</a>
    <a href="<?= $rootPath ?? '' ?>sobre.php">Sobre</a>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Visualizar imagem">
    <button class="lightbox-close" id="lightboxClose" aria-label="Fechar">&#10005;</button>
    <img id="lightboxImg" src="" alt="">
</div>
