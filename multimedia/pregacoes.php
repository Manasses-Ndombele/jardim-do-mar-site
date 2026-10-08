<?php
$pageTitle       = 'Pregações & Áudios | Assembleia Cristã Jardim do Mar';
$pageDescription = 'Ouça e baixe as pregações da Assembleia Cristã Jardim do Mar. O Seu Alimento Espiritual No Tempo Adequado!';
$assetsPath      = '../';
$rootPath        = '../';
include_once __DIR__ . '/../includes/header.php';

/* ─── DATABASE ──────────────────────────────────────────
   SQLite connection — database shared with admin.php
───────────────────────────────────────────────────────── */
// ─── Load .env ────────────────────────────────────────────────────────────────
function loadEnv(string $path): void {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        // Strip surrounding quotes if present
        $value = trim($value, '"\'');
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
} 

loadEnv(__DIR__ . '/../.env');

$url = getenv('DATABASE_URL');

$p = parse_url($url);
parse_str($p['query'] ?? '', $q);

$dsn = sprintf(
    'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
    $p['host'],
    $p['port'] ?? 5432,
    ltrim($p['path'], '/'),
    $q['sslmode'] ?? 'require'
);

$pdo = new PDO($dsn, urldecode($p['user']), urldecode($p['pass']), [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Palette rotated deterministically per pregador id
$palette = ['0D2B5E','1A5276','154360','2E4A7A','1A5276','154360'];

// ── Load pregadores ──
$stmtP = $pdo->query("SELECT * FROM pregadores ORDER BY nome");
$pregadores = $stmtP->fetchAll(PDO::FETCH_ASSOC);

// ── Load pregacoes (with filters) ──
$where  = [];
$params = [];

if (!empty($_GET['titulo'])) {
    $where[]  = "pr.titulo LIKE :titulo";
    $params[':titulo'] = '%' . trim($_GET['titulo']) . '%';
}
if (!empty($_GET['data'])) {
    $where[]  = "pr.data = :data";
    $params[':data'] = $_GET['data'];
}
if (!empty($_GET['pregador_id'])) {
    $where[]  = "pr.pregador_id = :pregador_id";
    $params[':pregador_id'] = (int)$_GET['pregador_id'];
}

$sql = "SELECT pr.*, p.nome AS pregador_nome, p.foto AS pregador_foto
        FROM pregacoes pr
        LEFT JOIN pregadores p ON p.id = pr.pregador_id"
     . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY pr.data DESC";

$stmtQ = $pdo->prepare($sql);
$stmtQ->execute($params);
$pregacoes = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

// Build pregadores lookup map
$pregadoresMap = [];
foreach ($pregadores as $idx => $p) {
    $pregadoresMap[$p['id']] = array_merge($p, ['cor' => $palette[$idx % count($palette)]]);
}

// Helper: format date to Portuguese
function formatDatePt(string $date): string {
    $meses = ['01'=>'Janeiro','02'=>'Fevereiro','03'=>'Março','04'=>'Abril',
              '05'=>'Maio','06'=>'Junho','07'=>'Julho','08'=>'Agosto',
              '09'=>'Setembro','10'=>'Outubro','11'=>'Novembro','12'=>'Dezembro'];
    [$y, $m, $d] = explode('-', $date);
    return ltrim($d,'0').' de '.$meses[$m].' de '.$y;
}

// Get preacher avatar URL (used only for audio player avatar fallback)
function avatarUrl(string $name, string $color): string {
    return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background='.urlencode($color).'&color=fff&size=96&bold=true';
}
?>

<!-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ -->
<section class="hero pregacoes-hero">
    <div class="hero-bg"
         style="background-image: url('../assets/images/foto-1.jpeg');"
         role="img" aria-label="Pregador no púlpito"></div>
    <div class="pregacoes-hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">O Seu Alimento Espiritual<br>No Tempo Adequado!</h1>
        <p class="hero-subtitle">Ouça, estude e seja edificado pela Palavra de Deus. Baixe as pregações e leve consigo para onde for.</p>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     SEARCH BAR
══════════════════════════════════════════════ -->
<section class="section" style="padding-top:56px;padding-bottom:0;background:var(--cinza-bg);">
    <div class="container">
        <div class="search-box reveal">
            <h2 class="search-title">Encontrar Sermão</h2>
            <form method="GET" action="" class="search-form" role="search" aria-label="Pesquisar pregações">
                <div class="search-field">
                    <label for="q_titulo">Título</label>
                    <input type="text" id="q_titulo" name="titulo"
                           placeholder="Ex: Gozo Eterno"
                           value="<?= htmlspecialchars($_GET['titulo'] ?? '') ?>">
                </div>
                <div class="search-field">
                    <label for="q_data">Data</label>
                    <input type="date" id="q_data" name="data"
                           value="<?= htmlspecialchars($_GET['data'] ?? '') ?>">
                </div>
                <div class="search-field">
                    <label for="q_pregador">Pregador</label>
                    <select id="q_pregador" name="pregador_id">
                        <option value="">Todos os Pregadores</option>
                        <?php foreach ($pregadores as $p): ?>
                        <option value="<?= $p['id'] ?>"
                            <?= (isset($_GET['pregador_id']) && $_GET['pregador_id'] == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="search-submit" aria-label="Pesquisar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     PREGADORES GRID
══════════════════════════════════════════════ -->
<section style="padding:56px 0 0;background:var(--cinza-bg);">
    <div class="container">
        <h2 class="pregadores-section-title">Nossos Pregadores</h2>
        <div class="pregadores-grid reveal">
            <?php foreach ($pregadores as $idx => $p):
                $cor = $palette[$idx % count($palette)];
                $imgSrc = !empty($p['foto']) ? htmlspecialchars($p['foto']) : avatarUrl($p['nome'], $cor);
            ?>
            <a class="pregador-card" href="?pregador_id=<?= $p['id'] ?>" aria-label="Ver pregações de <?= htmlspecialchars($p['nome']) ?>">
                <div class="pregador-avatar">
                    <img src="<?= $imgSrc ?>"
                         alt="<?= htmlspecialchars($p['nome']) ?>"
                         loading="lazy"
                         onerror="this.src='<?= avatarUrl($p['nome'], $cor) ?>'">
                </div>
                <span><?= htmlspecialchars($p['nome']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     PREGAÇÕES LIST
══════════════════════════════════════════════ -->
<section class="section" style="background:var(--cinza-bg);">
    <div class="container">

        <?php if (empty($pregacoes)): ?>
        <div class="no-results" role="alert">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <p>Nenhuma pregação encontrada com os filtros seleccionados.</p>
            <a href="pregacoes.php" class="btn btn-blue">Ver todas as pregações</a>
        </div>
        <?php else: ?>

        <div class="pregacoes-grid">
            <?php foreach ($pregacoes as $index => $preg):
                $pregador = $pregadoresMap[$preg['pregador_id']] ?? ['nome' => 'Desconhecido', 'cor' => '0D2B5E', 'foto' => ''];
                $cor      = $pregador['cor'] ?? '0D2B5E';
                $audioId  = 'audio-' . $preg['id'];
                $playerId = 'player-' . $preg['id'];
                $delay    = ($index % 2) * 100;
                // Avatar: use pregador_foto from JOIN, fallback to avatarUrl
                $avatarSrc = !empty($preg['pregador_foto'])
                    ? htmlspecialchars($preg['pregador_foto'])
                    : avatarUrl($preg['pregador_nome'] ?? $pregador['nome'], $cor);
                $avatarFallback = avatarUrl($preg['pregador_nome'] ?? $pregador['nome'], $cor);
                $audioUrl    = $preg['audio'] ?? '';
                $hasAudio    = !empty($audioUrl);
                $hasDownload = $hasAudio;
                $hasVideo    = !empty($preg['video']);

                $downloadUrl = $hasAudio
                    ? preg_replace('#/upload/#', '/upload/fl_attachment/', $audioUrl, 1)
                    : '';
            ?>
            <article class="pregacao-card reveal" style="transition-delay:<?= $delay ?>ms;"
                     aria-label="Pregação: <?= htmlspecialchars($preg['titulo']) ?>">

                <!-- Header -->
                <div class="pregacao-header">
                    <div class="pregacao-avatar">
                        <img src="<?= $avatarSrc ?>"
                             alt="<?= htmlspecialchars($preg['pregador_nome'] ?? $pregador['nome']) ?>"
                             loading="lazy"
                             onerror="this.src='<?= $avatarFallback ?>'">
                    </div>
                    <div class="pregacao-meta">
                        <h3><?= htmlspecialchars($preg['titulo']) ?></h3>
                        <span class="pregador-name"><?= htmlspecialchars($preg['pregador_nome'] ?? $pregador['nome']) ?></span>
                        <time class="pregacao-date" datetime="<?= $preg['data'] ?>">
                            <?= formatDatePt($preg['data']) ?>
                        </time>
                        <?php if ($hasVideo): ?>
                        <a class="youtube-link"
                           href="<?= htmlspecialchars($preg['video']) ?>"
                           target="_blank"
                           rel="external"
                           aria-label="Ver vídeo no YouTube — <?= htmlspecialchars($preg['titulo']) ?>">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 0-3.77-2.7C13.67 3.75 12 3.75 12 3.75s-1.67 0-3.82.24a4.83 4.83 0 0 0-3.77 2.7A27.14 27.14 0 0 0 4 12a27.14 27.14 0 0 0 .41 5.31 4.83 4.83 0 0 0 3.77 2.7c2.15.24 3.82.24 3.82.24s1.67 0 3.82-.24a4.83 4.83 0 0 0 3.77-2.7A27.14 27.14 0 0 0 20 12a27.14 27.14 0 0 0-.41-5.31zM9.75 15V9l5.5 3-5.5 3z"/></svg>
                            YOUTUBE
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Custom Audio Player -->
                <div class="audio-player" id="<?= $playerId ?>">
                    <button class="play-btn"
                        onclick="togglePlay('<?= $audioId ?>')"
                        aria-label="Play/Pause — <?= htmlspecialchars($preg['titulo']) ?>"
                        <?= !$hasAudio ? 'disabled style="opacity:.4;cursor:not-allowed"' : '' ?>>
                        <svg class="icon-play" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg class="icon-pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="display:none;">
                            <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                        </svg>
                    </button>

                    <div class="player-body">
                        <div class="player-progress-track"
                             role="slider"
                             aria-label="Progresso"
                             aria-valuemin="0"
                             aria-valuemax="100"
                             aria-valuenow="0"
                             onclick="seekAudio(event, '<?= $audioId ?>')">
                            <div class="player-progress-fill" id="fill-<?= $audioId ?>"></div>
                        </div>
                        <div class="player-times">
                            <span id="cur-<?= $audioId ?>">0:00</span>
                            <span id="dur-<?= $audioId ?>">–:––</span>
                        </div>
                    </div>

                    <div class="player-actions">
                        <?php if ($hasDownload): ?>
                        <a class="download-btn"
                           href="<?= htmlspecialchars($downloadUrl) ?>"
                           download
                           aria-label="Baixar <?= htmlspecialchars($preg['titulo']) ?>">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Baixar
                        </a>
                        <?php else: ?>
                        <span class="download-btn disabled" title="Áudio não disponível">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Baixar
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Hidden HTML5 audio element -->
                    <audio id="<?= $audioId ?>"
                        preload="none"
                        <?= $hasAudio ? 'src="' . htmlspecialchars($audioUrl) . '"' : '' ?>
                        aria-label="Áudio: <?= htmlspecialchars($preg['titulo']) ?>">
                    </audio>
                </div>

            </article>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>

        <a class="voltar-link" href="index.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            Voltar à Galeria
        </a>

    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<!-- ══════════════════════════════════════════════
     PAGE-SPECIFIC CSS
══════════════════════════════════════════════ -->
<style>
/* Hero overlay */
.pregacoes-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, rgba(8,18,50,.88) 0%, rgba(13,43,94,.75) 60%, rgba(8,18,50,.9) 100%);
}

/* Search box */
.search-box {
    background: #fff;
    border-radius: 12px;
    padding: 32px 36px;
    box-shadow: 0 6px 32px rgba(0,0,0,.1);
    margin-bottom: 0;
}
.search-title {
    font-family: 'Cinzel', serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--azul-escuro);
    text-align: center;
    margin-bottom: 24px;
    letter-spacing: 1px;
}
.search-form {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 14px;
    align-items: end;
}
.search-field label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 7px;
}
.search-field input,
.search-field select {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--borda);
    border-radius: 7px;
    font-family: 'Lato', sans-serif;
    font-size: 14px;
    color: var(--texto);
    background: #fff;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.search-field input:focus,
.search-field select:focus {
    border-color: var(--azul-claro);
    box-shadow: 0 0 0 3px rgba(46,134,193,.12);
}
.search-submit {
    width: 48px;
    height: 48px;
    background: var(--azul-escuro);
    border: none;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    transition: background .2s;
}
.search-submit:hover { background: var(--azul-claro); }

/* Pregadores */
.pregadores-section-title {
    font-family: 'Cinzel', serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--azul-escuro);
    margin-bottom: 28px;
    letter-spacing: 1px;
    text-align: center;
}
.pregadores-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 20px;
    margin-bottom: 0;
}
.pregador-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    cursor: pointer;
    transition: transform .22s;
}
.pregador-card:hover { transform: translateY(-5px); }
.pregador-avatar {
    width: 86px;
    height: 86px;
    border-radius: 50%;
    overflow: hidden;
    outline: 3px solid rgba(46,134,193,.35);
    outline-offset: 2px;
    box-shadow: 0 4px 18px rgba(0,0,0,.14);
    transition: outline-color .2s, box-shadow .2s;
}
.pregador-card:hover .pregador-avatar {
    outline-color: var(--azul-claro);
    box-shadow: 0 8px 24px rgba(13,43,94,.22);
}
.pregador-avatar img { width: 100%; height: 100%; object-fit: cover; }
.pregador-card span {
    font-size: 11.5px;
    font-weight: 700;
    text-align: center;
    color: var(--azul-escuro);
    letter-spacing: .2px;
    line-height: 1.3;
}

/* Pregações grid */
.pregacoes-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 22px;
}
.pregacao-card {
    background: #fff;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 2px 18px rgba(0,0,0,.07);
    border: 1px solid var(--borda);
    transition: box-shadow .25s, transform .25s;
}
.pregacao-card:hover {
    box-shadow: 0 10px 36px rgba(13,43,94,.13);
    transform: translateY(-2px);
}
.pregacao-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 18px;
}
.pregacao-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--azul-escuro);
}
.pregacao-avatar img { width: 100%; height: 100%; object-fit: cover; }
.pregacao-meta h3 {
    font-family: 'Cinzel', serif;
    font-size: 14px;
    font-weight: 700;
    color: var(--azul-escuro);
    margin-bottom: 4px;
    line-height: 1.3;
}
.pregador-name {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--azul-claro);
    letter-spacing: .3px;
}
.pregacao-date {
    display: block;
    font-size: 11.5px;
    color: #aaa;
    margin-top: 3px;
}
.youtube-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 6px;
    background: #FF0000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .8px;
    padding: 3px 9px;
    border-radius: 4px;
    text-decoration: none;
    transition: background .2s, transform .15s;
}
.youtube-link:hover { background: #cc0000; transform: scale(1.04); }

/* Audio player */
.audio-player {
    background: var(--azul-escuro);
    border-radius: 10px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.play-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--azul-claro);
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background .2s, transform .12s;
}
.play-btn:hover { background: #1a6fa3; }
.play-btn:active { transform: scale(.93); }
.player-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.player-progress-track {
    width: 100%;
    height: 5px;
    background: rgba(255,255,255,.18);
    border-radius: 3px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.player-progress-fill {
    height: 100%;
    background: var(--azul-claro);
    border-radius: 3px;
    width: 0%;
    transition: width .1s linear;
    pointer-events: none;
}
.player-times {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    font-weight: 700;
    color: rgba(255,255,255,.5);
    letter-spacing: .4px;
}
.player-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.download-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 5px;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 11px;
    text-decoration: none;
    letter-spacing: .3px;
    transition: background .2s;
    cursor: pointer;
    white-space: nowrap;
}
.download-btn:hover { background: rgba(255,255,255,.2); }
.download-btn.disabled {
    opacity: .35;
    cursor: not-allowed;
    pointer-events: none;
}

/* No results */
.no-results {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 64px 24px;
    text-align: center;
    color: #aaa;
}
.no-results p { font-size: 16px; }

/* Responsive */
@media (max-width: 860px) {
    .search-form { grid-template-columns: 1fr; }
    .search-submit { width: 100%; height: 48px; }
    .pregadores-grid { grid-template-columns: repeat(3, 1fr); }
    .pregacoes-grid  { grid-template-columns: 1fr; }
}
@media (max-width: 500px) {
    .pregadores-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<!-- ══════════════════════════════════════════════
     AUDIO PLAYER JAVASCRIPT
══════════════════════════════════════════════ -->
<script>
'use strict';
(function () {

    let activeId = null;

    function fmt(s) {
        if (!s || isNaN(s)) return '0:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return m + ':' + String(sec).padStart(2, '0');
    }

    function getEl(id) { return document.getElementById(id); }

    window.togglePlay = function (audioId, src) {
        const audio   = getEl(audioId);
        const btnEl   = audio?.closest('.audio-player')?.querySelector('.play-btn');
        const playIco = btnEl?.querySelector('.icon-play');
        const pauseIco = btnEl?.querySelector('.icon-pause');
        if (!audio) return;

        // Stop currently playing audio
        if (activeId && activeId !== audioId) {
            const prev = getEl(activeId);
            if (prev) {
                prev.pause();
                const prevBtn = prev.closest('.audio-player')?.querySelector('.play-btn');
                if (prevBtn) {
                    prevBtn.querySelector('.icon-play').style.display  = '';
                    prevBtn.querySelector('.icon-pause').style.display = 'none';
                }
            }
        }

        if (audio.paused) {
            if (src && src !== '' && !audio.src.endsWith(src)) audio.src = src;
            audio.play().catch(() => console.warn('Audio play failed — demo mode'));
            if (playIco)  playIco.style.display  = 'none';
            if (pauseIco) pauseIco.style.display  = '';
            activeId = audioId;
            bindAudioEvents(audio, audioId);
        } else {
            audio.pause();
            if (playIco)  playIco.style.display  = '';
            if (pauseIco) pauseIco.style.display  = 'none';
            activeId = null;
        }
    };

    function bindAudioEvents(audio, audioId) {
        audio.addEventListener('timeupdate', () => {
            const pct  = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
            const fill = getEl('fill-' + audioId);
            const cur  = getEl('cur-'  + audioId);
            const dur  = getEl('dur-'  + audioId);
            if (fill) fill.style.width   = pct + '%';
            if (cur)  cur.textContent    = fmt(audio.currentTime);
            if (dur)  dur.textContent    = fmt(audio.duration);
        });
        audio.addEventListener('ended', () => {
            const btn = audio.closest('.audio-player')?.querySelector('.play-btn');
            if (btn) {
                btn.querySelector('.icon-play').style.display  = '';
                btn.querySelector('.icon-pause').style.display = 'none';
            }
            const fill = getEl('fill-' + audioId);
            if (fill) fill.style.width = '0%';
            activeId = null;
        });
        audio.addEventListener('loadedmetadata', () => {
            const dur = getEl('dur-' + audioId);
            if (dur) dur.textContent = fmt(audio.duration);
        });
    }

    window.seekAudio = function (e, audioId) {
        const audio = getEl(audioId);
        const track = e.currentTarget;
        if (!audio || !track) return;
        const rect = track.getBoundingClientRect();
        const pct  = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        if (audio.duration) {
            audio.currentTime = pct * audio.duration;
        }
    };



})();
</script>
