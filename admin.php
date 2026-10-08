<?php
session_start();

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

loadEnv(__DIR__ . '/.env');

// ─── Cloudinary (from .env) ───────────────────────────────────────────────────
define('CLOUDINARY_CLOUD_NAME',    getenv('CLOUDINARY_CLOUD_NAME'));
define('CLOUDINARY_UPLOAD_PRESET1', getenv('CLOUDINARY_UPLOAD_PRESET1'));
define('CLOUDINARY_UPLOAD_PRESET2', getenv('CLOUDINARY_UPLOAD_PRESET2'));
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD'));

// ─── Database Setup (PostgreSQL / Supabase) ───────────────────────────────────
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


// ─── Schema (PostgreSQL syntax) ───────────────────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id       SERIAL PRIMARY KEY,
        username TEXT NOT NULL,
        email    TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS pregadores (
        id   SERIAL PRIMARY KEY,
        nome TEXT NOT NULL,
        foto TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS pregacoes (
        id          SERIAL PRIMARY KEY,
        titulo      TEXT    NOT NULL,
        audio       TEXT    NOT NULL,
        video       TEXT,
        data        TEXT    NOT NULL,
        pregador_id INTEGER NOT NULL REFERENCES pregadores(id) ON DELETE CASCADE
    );
");

// ─── Seed demo user if none exists ───────────────────────────────────────────
$count = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
if ($count == 0) {
    $hash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $st   = $pdo->prepare("INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)");
    $st->execute(['Admin', 'admin@jardimdomar.ao', $hash]);
}

// ─── Auth helpers ─────────────────────────────────────────────────────────────
function currentUser(PDO $pdo): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    $st = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $st->execute([$_SESSION['user_id']]);
    return $st->fetch() ?: null;
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?action=login');
        exit;
    }
}

// ─── Routing ──────────────────────────────────────────────────────────────────
$action = $_GET['action'] ?? 'dashboard';

// ── LOGIN ──
if ($action === 'do_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $em = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';
    $st = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $st->execute([$em]);
    $u = $st->fetch();
    if ($u && password_verify($pw, $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        header('Location: admin.php');
    } else {
        header('Location: ?action=login&err=1');
    }
    exit;
}

// ── LOGOUT ──
if ($action === 'logout') {
    session_destroy();
    header('Location: ?action=login');
    exit;
}

// ── AJAX ENDPOINTS ────────────────────────────────────────────────────────────
if ($action === 'api') {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'erro' => 'Sessão expirada. Recarregue a página.']);
        exit;
    }

    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];
    $target = $_GET['target'] ?? '';

    // ── PREGADORES ──
    if ($target === 'pregadores') {
        if ($method === 'GET') {
            $rows = $pdo->query("
                SELECT p.*,
                       (SELECT COUNT(*) FROM pregacoes pr WHERE pr.pregador_id = p.id) AS num_pregacoes
                FROM pregadores p
                ORDER BY p.nome
            ")->fetchAll();
            echo json_encode(['ok' => true, 'data' => $rows]);

        } elseif ($method === 'POST') {
            $d  = json_decode(file_get_contents('php://input'), true);
            $st = $pdo->prepare("INSERT INTO pregadores (nome, foto) VALUES (?, ?) RETURNING id");
            $st->execute([trim($d['nome']), trim($d['foto'])]);
            $id = $st->fetchColumn();
            echo json_encode(['ok' => true, 'id' => $id]);

        } elseif ($method === 'PUT') {
            $id = intval($_GET['id'] ?? 0);
            $d  = json_decode(file_get_contents('php://input'), true);
            if (!empty($d['fotoUrl'])) {
                $st = $pdo->prepare("UPDATE pregadores SET nome = ?, foto = ? WHERE id = ?");
                $st->execute([trim($d['nome']), trim($d['fotoUrl']), $id]);
            } else {
                $st = $pdo->prepare("UPDATE pregadores SET nome = ? WHERE id = ?");
                $st->execute([trim($d['nome']), $id]);
            }
            echo json_encode(['ok' => true]);

        } elseif ($method === 'DELETE') {
            $id = intval($_GET['id'] ?? 0);
            $pdo->prepare("DELETE FROM pregadores WHERE id = ?")->execute([$id]);
            echo json_encode(['ok' => true]);
        }
        exit;
    }

    // ── PREGAÇÕES ──
    if ($target === 'pregacoes') {
        if ($method === 'GET') {
            $rows = $pdo->query("
                SELECT pr.*, p.nome AS pregador_nome
                FROM pregacoes pr
                LEFT JOIN pregadores p ON p.id = pr.pregador_id
                ORDER BY pr.data DESC
            ")->fetchAll();
            echo json_encode(['ok' => true, 'data' => $rows]);

        } elseif ($method === 'POST') {
            $d  = json_decode(file_get_contents('php://input'), true);
            $st = $pdo->prepare("
                INSERT INTO pregacoes (titulo, audio, video, data, pregador_id)
                VALUES (?, ?, ?, ?, ?)
                RETURNING id
            ");
            $st->execute([
                trim($d['titulo']),
                trim($d['audio']),
                trim($d['video'] ?? ''),
                trim($d['data']),
                intval($d['pregador_id']),
            ]);
            $id = $st->fetchColumn();
            echo json_encode(['ok' => true, 'id' => $id]);

        } elseif ($method === 'PUT') {
            $id = intval($_GET['id'] ?? 0);
            $d  = json_decode(file_get_contents('php://input'), true);
            if (!empty($d['audio'])) {
                $st = $pdo->prepare("
                    UPDATE pregacoes
                    SET titulo = ?, audio = ?, video = ?, data = ?, pregador_id = ?
                    WHERE id = ?
                ");
                $st->execute([
                    trim($d['titulo']),
                    trim($d['audio']),
                    trim($d['video'] ?? ''),
                    trim($d['data']),
                    intval($d['pregador_id']),
                    $id,
                ]);
            } else {
                $st = $pdo->prepare("
                    UPDATE pregacoes
                    SET titulo = ?, video = ?, data = ?, pregador_id = ?
                    WHERE id = ?
                ");
                $st->execute([
                    trim($d['titulo']),
                    trim($d['video'] ?? ''),
                    trim($d['data']),
                    intval($d['pregador_id']),
                    $id,
                ]);
            }
            echo json_encode(['ok' => true]);

        } elseif ($method === 'DELETE') {
            $id = intval($_GET['id'] ?? 0);
            $pdo->prepare("DELETE FROM pregacoes WHERE id = ?")->execute([$id]);
            echo json_encode(['ok' => true]);
        }
        exit;
    }

    // ── CONTA ──
    if ($target === 'conta') {
        $user = currentUser($pdo);
        if ($method === 'PUT') {
            $d    = json_decode(file_get_contents('php://input'), true);
            $tipo = $d['tipo'] ?? '';
            if ($tipo === 'email') {
                $st = $pdo->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
                $st->execute([trim($d['email']), $user['id']]);
                echo json_encode(['ok' => true]);
            } elseif ($tipo === 'password') {
                $hash = password_hash($d['password'], PASSWORD_DEFAULT);
                $st   = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $st->execute([$hash, $user['id']]);
                echo json_encode(['ok' => true]);
            }
        } elseif ($method === 'DELETE') {
            $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$user['id']]);
            session_destroy();
            echo json_encode(['ok' => true, 'redirect' => '?action=login']);
        }
        exit;
    }

    // ── EXPORT ──
    if ($target === 'export') {
        $pregadores = $pdo->query("SELECT * FROM pregadores ORDER BY nome")->fetchAll();
        $pregacoes  = $pdo->query("
            SELECT pr.*, p.nome AS pregador_nome
            FROM pregacoes pr
            LEFT JOIN pregadores p ON p.id = pr.pregador_id
            ORDER BY pr.data DESC
        ")->fetchAll();
        echo json_encode(['ok' => true, 'pregadores' => $pregadores, 'pregacoes' => $pregacoes]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown target']);
    exit;
}

// ─── Pages ────────────────────────────────────────────────────────────────────
if ($action !== 'login') requireLogin();
$user = currentUser($pdo);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel Administrativo – Assembleia Cristã</title>
<link rel="icon" type="image/x-icon" href="<?= $assetsPath ?? '' ?>assets/images/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
:root {
    --azul-escuro:    #0D2B5E;
    --azul-medio:     #1A5276;
    --azul-claro:     #2E86C1;
    --azul-navbar:    #1565C0;
    --vermelho:       #C0392B;
    --vermelho-hover: #A93226;
    --cinza-escuro:   #2C3E50;
    --cinza-footer:   #111927;
    --cinza-bg:       #F4F7FB;
    --branco:         #FFFFFF;
    --texto:          #2C3E50;
    --texto-muted:    #95A5A6;
    --borda:          #DDE2E8;
    --sombra: 0 4px 20px rgba(13,43,94,.12);
    --sombra-lg: 0 8px 40px rgba(13,43,94,.18);
    --radius: 10px;
    --sidebar-w: 240px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-size:15px}
body{font-family:'Lato',sans-serif;background:var(--cinza-bg);color:var(--texto);min-height:100vh}

/* ── LOGIN ── */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--azul-escuro) 0%,var(--azul-medio) 60%,var(--azul-claro) 100%);position:relative;overflow:hidden}
.login-wrap::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.login-card{background:var(--branco);border-radius:16px;padding:48px 44px;width:100%;max-width:420px;box-shadow:var(--sombra-lg);position:relative;z-index:1;animation:fadeUp .5s ease}
.login-logo{text-align:center;margin-bottom:32px}
.login-logo .cross{font-size:2.4rem;color:var(--azul-escuro)}
.login-logo h1{font-family:'Cinzel',serif;font-size:1.35rem;color:var(--azul-escuro);letter-spacing:.04em;margin-top:6px}
.login-logo p{font-size:.82rem;color:var(--texto-muted);margin-top:4px}
.login-card h2{font-family:'Cinzel',serif;font-size:1.1rem;color:var(--azul-escuro);margin-bottom:24px;text-align:center}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:.82rem;font-weight:700;color:var(--cinza-escuro);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;border:1.5px solid var(--borda);border-radius:var(--radius);font-family:'Lato',sans-serif;font-size:.95rem;color:var(--texto);background:#fff;transition:border-color .2s,box-shadow .2s;outline:none}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--azul-claro);box-shadow:0 0 0 3px rgba(46,134,193,.15)}
.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border:none;border-radius:var(--radius);font-family:'Lato',sans-serif;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .2s;text-decoration:none;white-space:nowrap}
.btn-primary{background:var(--azul-navbar);color:#fff}
.btn-primary:hover{background:var(--azul-escuro);transform:translateY(-1px);box-shadow:0 4px 12px rgba(21,101,192,.35)}
.btn-danger{background:var(--vermelho);color:#fff}
.btn-danger:hover{background:var(--vermelho-hover)}
.btn-ghost{background:transparent;color:var(--azul-claro);border:1.5px solid var(--azul-claro)}
.btn-ghost:hover{background:var(--azul-claro);color:#fff}
.btn-sm{padding:6px 12px;font-size:.82rem}
.btn-icon{padding:7px;border-radius:8px;background:transparent;border:1.5px solid var(--borda);color:var(--texto-muted);cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;justify-content:center}
.btn-icon:hover{border-color:var(--azul-claro);color:var(--azul-claro);background:rgba(46,134,193,.08)}
.btn-icon.danger:hover{border-color:var(--vermelho);color:var(--vermelho);background:rgba(192,57,43,.08)}
.btn-full{width:100%;justify-content:center}
.err-msg{background:#fde8e6;border:1px solid #f5b7b1;color:var(--vermelho);border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:18px;display:none}
.err-msg.show{display:block}

/* ── LAYOUT ── */
.app{display:flex;min-height:100vh}
.sidebar{width:var(--sidebar-w);background:var(--azul-escuro);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:100;box-shadow:4px 0 20px rgba(0,0,0,.2)}
.sidebar-logo{padding:28px 20px 20px;border-bottom:1px solid rgba(255,255,255,.08)}
.sidebar-logo .cross{font-size:1.6rem;color:rgba(255,255,255,.9)}
.sidebar-logo h2{font-family:'Cinzel',serif;font-size:.95rem;color:#fff;letter-spacing:.04em;margin-top:4px;line-height:1.3}
.sidebar-logo p{font-size:.72rem;color:rgba(255,255,255,.45);margin-top:2px}
.sidebar-nav{flex:1;padding:16px 0;overflow-y:auto}
.nav-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.35);padding:12px 20px 6px}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 20px;color:rgba(255,255,255,.7);cursor:pointer;transition:all .2s;font-size:.9rem;border-left:3px solid transparent;user-select:none}
.nav-item:hover{background:rgba(255,255,255,.06);color:#fff}
.nav-item.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--azul-claro)}
.nav-item svg{flex-shrink:0}
.sidebar-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,.08)}
.user-chip{display:flex;align-items:center;gap:10px}
.user-avatar{width:34px;height:34px;border-radius:50%;background:var(--azul-claro);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#fff;flex-shrink:0}
.user-info{flex:1;min-width:0}
.user-info .name{font-size:.85rem;color:#fff;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.user-info .email{font-size:.72rem;color:rgba(255,255,255,.45);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{background:var(--branco);border-bottom:1px solid var(--borda);padding:16px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:90;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.topbar-title{font-family:'Cinzel',serif;font-size:1.05rem;color:var(--azul-escuro);font-weight:700}
.topbar-actions{display:flex;align-items:center;gap:10px}
.content{padding:28px;flex:1}
.section{display:none}
.section.active{display:block}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.page-header h3{font-family:'Cinzel',serif;font-size:1.1rem;color:var(--azul-escuro)}

/* ── CARDS ── */
.card{background:var(--branco);border-radius:var(--radius);box-shadow:var(--sombra);overflow:hidden}
.card-header{padding:18px 22px;border-bottom:1px solid var(--borda);font-family:'Cinzel',serif;font-size:.95rem;color:var(--azul-escuro);display:flex;align-items:center;justify-content:space-between}
.card-body{padding:22px}

/* ── STATS ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:28px}
.stat-card{background:var(--branco);border-radius:var(--radius);padding:22px;box-shadow:var(--sombra);display:flex;align-items:center;gap:14px;border-left:4px solid var(--azul-claro)}
.stat-icon{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--azul-claro),var(--azul-medio));display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon svg{color:#fff}
.stat-val{font-family:'Cinzel',serif;font-size:1.6rem;font-weight:700;color:var(--azul-escuro);line-height:1}
.stat-label{font-size:.78rem;color:var(--texto-muted);margin-top:3px;text-transform:uppercase;letter-spacing:.04em}

/* ── PREGADORES GRID ── */
.pregadores-grid{display:flex;flex-wrap:wrap;gap:16px}
.pregador-card{background:var(--branco);border-radius:var(--radius);box-shadow:var(--sombra);padding:18px;display:flex;align-items:center;gap:14px;width:320px;max-width:100%;position:relative;transition:box-shadow .2s}
.pregador-card:hover{box-shadow:var(--sombra-lg)}
.pregador-foto{width:60px;height:60px;border-radius:50%;object-fit:cover;flex-shrink:0;border:3px solid var(--azul-claro);background:var(--cinza-bg)}
.pregador-foto-fallback{width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--azul-claro),var(--azul-escuro));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;font-weight:700;flex-shrink:0;border:3px solid var(--azul-claro)}
.pregador-info{flex:1;min-width:0}
.pregador-nome{font-weight:700;color:var(--azul-escuro);font-size:.95rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pregador-count{font-size:.8rem;color:var(--texto-muted);margin-top:3px}
.pregador-actions{display:flex;gap:6px;flex-shrink:0}

/* ── TABLE ── */
.table-wrap{overflow-x:auto;border-radius:var(--radius);box-shadow:var(--sombra)}
table{width:100%;border-collapse:collapse;background:var(--branco)}
thead{background:var(--azul-escuro)}
thead th{color:#fff;font-family:'Cinzel',serif;font-size:.78rem;font-weight:600;padding:14px 16px;text-align:left;white-space:nowrap;letter-spacing:.04em}
tbody tr{border-bottom:1px solid var(--borda);transition:background .15s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#f0f6fd}
td{padding:12px 16px;font-size:.88rem;vertical-align:middle}
td a{color:var(--azul-claro);text-decoration:none;word-break:break-all}
td a:hover{text-decoration:underline}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:700}
.badge-audio{background:#e8f4fd;color:var(--azul-medio)}
.badge-video{background:#fdecea;color:var(--vermelho)}
.badge-none{background:#f0f0f0;color:var(--texto-muted)}
.table-actions{display:flex;gap:6px}

/* ── CONTA ── */
.conta-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)}
.modal-overlay.open{display:flex}
.modal{background:var(--branco);border-radius:14px;box-shadow:var(--sombra-lg);width:100%;max-width:480px;animation:fadeUp .25s ease;max-height:90vh;overflow-y:auto}
.modal-sm{max-width:380px}
.modal-header{padding:22px 24px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--borda)}
.modal-header h3{font-family:'Cinzel',serif;font-size:1rem;color:var(--azul-escuro)}
.modal-close{background:none;border:none;font-size:1.3rem;color:var(--texto-muted);cursor:pointer;padding:2px 6px;border-radius:6px;line-height:1;transition:color .2s}
.modal-close:hover{color:var(--texto)}
.modal-body{padding:22px 24px}
.modal-footer{padding:16px 24px;border-top:1px solid var(--borda);display:flex;justify-content:flex-end;gap:10px}
.modal-warn-icon{text-align:center;font-size:2.8rem;margin-bottom:12px}
.modal-warn p{text-align:center;color:var(--cinza-escuro);font-size:.93rem;line-height:1.6}
.modal-warn strong{color:var(--vermelho)}

/* ── TOAST ── */
.toast{position:fixed;bottom:28px;right:28px;background:var(--cinza-escuro);color:#fff;padding:12px 20px;border-radius:10px;font-size:.88rem;font-weight:700;z-index:9999;box-shadow:var(--sombra-lg);transform:translateY(80px);opacity:0;transition:all .35s cubic-bezier(.175,.885,.32,1.275)}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{background:#1a7a4a}
.toast.error{background:var(--vermelho)}

/* ── ANIMATIONS ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.animate-in{animation:fadeUp .35s ease}

/* ── EMPTY STATE ── */
.empty-state{text-align:center;padding:48px 20px;color:var(--texto-muted)}
.empty-state svg{margin-bottom:12px;opacity:.35}
.empty-state p{font-size:.9rem}

/* ── DOWNLOAD BAR ── */
.download-bar{background:linear-gradient(90deg,var(--azul-escuro),var(--azul-medio));border-radius:var(--radius);padding:18px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.download-bar p{color:rgba(255,255,255,.8);font-size:.88rem}
.download-bar strong{color:#fff;display:block;font-size:1rem;font-family:'Cinzel',serif}

/* ── LOGIN PAGE ── */
<?php if ($action === 'login'): ?>
body{overflow:hidden}
<?php endif; ?>
</style>
</head>
<body>

<?php if ($action === 'login'): ?>
<!-- ══════════════════ LOGIN PAGE ══════════════════ -->
<div class="login-wrap">
<div class="login-card">
  <div class="login-logo">
    <div class="cross">✝</div>
    <h1>Assembleia Cristã</h1>
    <p>Jardim do Mar · Painel Administrativo</p>
  </div>
  <h2>Entrar</h2>
  <?php if (isset($_GET['err'])): ?>
  <div class="err-msg show">Email ou senha incorrectos. Tente novamente.</div>
  <?php endif; ?>
  <form method="POST" action="?action=do_login">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" placeholder="admin@jardimdomar.ao" required autofocus>
    </div>
    <div class="form-group">
      <label>Senha</label>
      <input type="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Entrar
    </button>
  </form>
</div>
</div>

<?php else: ?>
<!-- ══════════════════ MAIN APP ══════════════════ -->
<div class="app">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="cross">✝</div>
    <h2>Assembleia Cristã</h2>
    <p>Jardim do Mar</p>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <div class="nav-item active" data-section="dashboard" onclick="showSection('dashboard',this)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </div>
    <div class="nav-item" data-section="pregadores" onclick="showSection('pregadores',this)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Pregadores
    </div>
    <div class="nav-item" data-section="pregacoes" onclick="showSection('pregacoes',this)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
      Pregações
    </div>
    <div class="nav-label" style="margin-top:8px">Conta</div>
    <div class="nav-item" data-section="conta" onclick="showSection('conta',this)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Minha Conta
    </div>
  </nav>
  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($user['username'],0,1)) ?></div>
      <div class="user-info">
        <div class="name"><?= htmlspecialchars($user['username']) ?></div>
        <div class="email"><?= htmlspecialchars($user['email']) ?></div>
      </div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <header class="topbar">
    <span class="topbar-title" id="topbar-title">Dashboard</span>
    <div class="topbar-actions">
      <a href="?action=logout" class="btn btn-ghost btn-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sair
      </a>
    </div>
  </header>

  <div class="content">

    <!-- ── DASHBOARD ── -->
    <section id="section-dashboard" class="section active">
      <div class="page-header"><h3>Visão Geral</h3></div>
      <div class="download-bar">
        <div>
          <strong>Exportar Dados</strong>
          <p>Baixe todos os pregadores e pregações em formato Excel (.xlsx)</p>
        </div>
        <button class="btn btn-primary" onclick="exportXLSX()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Baixar banco de dados (.xlsx)
        </button>
      </div>
      <div class="stats-grid" id="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
          <div><div class="stat-val" id="stat-pregadores">–</div><div class="stat-label">Pregadores</div></div>
        </div>
        <div class="stat-card" style="border-left-color:var(--vermelho)">
          <div class="stat-icon" style="background:linear-gradient(135deg,var(--vermelho),#8e1b12)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg></div>
          <div><div class="stat-val" id="stat-pregacoes">–</div><div class="stat-label">Pregações</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#27ae60">
          <div class="stat-icon" style="background:linear-gradient(135deg,#27ae60,#1a7a40)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg></div>
          <div><div class="stat-val" id="stat-audio">–</div><div class="stat-label">Com Áudio</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#8e44ad">
          <div class="stat-icon" style="background:linear-gradient(135deg,#8e44ad,#5b2c6f)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg></div>
          <div><div class="stat-val" id="stat-video">–</div><div class="stat-label">Com Vídeo</div></div>
        </div>
      </div>
    </section>

    <!-- ── PREGADORES ── -->
    <section id="section-pregadores" class="section">
      <div class="page-header">
        <h3>Pregadores</h3>
        <button class="btn btn-primary" onclick="openModal('modal-novo-pregador')">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Novo Pregador
        </button>
      </div>
      <div class="pregadores-grid" id="pregadores-grid">
        <div class="empty-state" style="width:100%">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <p>Nenhum pregador cadastrado ainda.</p>
        </div>
      </div>
    </section>

    <!-- ── PREGAÇÕES ── -->
    <section id="section-pregacoes" class="section">
      <div class="page-header">
        <h3>Pregações</h3>
        <button class="btn btn-primary" onclick="openModal('modal-nova-pregacao')">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nova Pregação
        </button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Título</th>
              <th>Pregador</th>
              <th>Data</th>
              <th>Link</th>
              <th>Vídeo</th>
              <th>Acções</th>
            </tr>
          </thead>
          <tbody id="pregacoes-tbody">
            <tr><td colspan="6" class="empty-state" style="padding:32px;text-align:center;color:var(--texto-muted)">Nenhuma pregação cadastrada.</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ── CONTA ── -->
    <section id="section-conta" class="section">
      <div class="page-header"><h3>Minha Conta</h3></div>
      <div class="conta-grid">
        <div class="card">
          <div class="card-header">Alterar Email</div>
          <div class="card-body">
            <div class="form-group">
              <label>Novo Email</label>
              <input type="email" id="conta-email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <button class="btn btn-primary btn-sm" onclick="salvarEmail()">Guardar Email</button>
          </div>
        </div>
        <div class="card">
          <div class="card-header">Alterar Senha</div>
          <div class="card-body">
            <div class="form-group">
              <label>Nova Senha</label>
              <input type="password" id="conta-senha" placeholder="••••••••">
            </div>
            <div class="form-group">
              <label>Confirmar Senha</label>
              <input type="password" id="conta-senha2" placeholder="••••••••">
            </div>
            <button class="btn btn-primary btn-sm" onclick="salvarSenha()">Guardar Senha</button>
          </div>
        </div>
        <div class="card">
          <div class="card-header" style="color:var(--vermelho)">Zona Perigosa</div>
          <div class="card-body">
            <p style="font-size:.88rem;color:var(--texto-muted);margin-bottom:16px">Estas acções são irreversíveis. Tome cuidado.</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
              <a href="?action=logout" class="btn btn-ghost btn-sm">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Deslogar
              </a>
              <button class="btn btn-danger btn-sm" onclick="openModal('modal-del-conta')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Eliminar Conta
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /app -->

<!-- ══════════ MODALS ══════════ -->

<!-- Novo Pregador -->
<div class="modal-overlay" id="modal-novo-pregador">
  <div class="modal">
    <div class="modal-header">
      <h3>Novo Pregador</h3>
      <button class="modal-close" onclick="closeModal('modal-novo-pregador')">&times;</button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Nome</label><input type="text" id="np-nome" placeholder="Nome completo"></div>
      <div class="form-group"><label>Foto</label><input type="file" id="np-foto" accept="image/jpeg,image/png,image/webp"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-novo-pregador')">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="cadastrarPregador()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Cadastrar
      </button>
    </div>
  </div>
</div>

<!-- Editar Pregador -->
<div class="modal-overlay" id="modal-edit-pregador">
  <div class="modal">
    <div class="modal-header">
      <h3>Editar Pregador</h3>
      <button class="modal-close" onclick="closeModal('modal-edit-pregador')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="ep-id">
      <div class="form-group"><label>Nome</label><input type="text" id="ep-nome"></div>
      <div class="form-group"><label>Foto</label><input type="file" id="ep-foto" accept="image/jpeg,image/png,image/webp"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-edit-pregador')">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="editarPregador()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Editar e Salvar
      </button>
    </div>
  </div>
</div>

<!-- Eliminar Pregador -->
<div class="modal-overlay" id="modal-del-pregador">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3>Eliminar Pregador</h3>
      <button class="modal-close" onclick="closeModal('modal-del-pregador')">&times;</button>
    </div>
    <div class="modal-body modal-warn">
      <div class="modal-warn-icon">⚠️</div>
      <p>Tem certeza que pretende eliminar o pregador <strong id="del-p-nome"></strong>? Todas as pregações deste mesmo pregador também serão apagadas.</p>
    </div>
    <div class="modal-footer">
      <input type="hidden" id="del-p-id">
      <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-del-pregador')">Cancelar</button>
      <button class="btn btn-danger btn-sm" onclick="eliminarPregador()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
        Eliminar
      </button>
    </div>
  </div>
</div>

<!-- Nova Pregação -->
<div class="modal-overlay" id="modal-nova-pregacao">
  <div class="modal">
    <div class="modal-header">
      <h3>Nova Pregação</h3>
      <button class="modal-close" onclick="closeModal('modal-nova-pregacao')">&times;</button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Título</label><input type="text" id="npreg-titulo" placeholder="Título da pregação"></div>
      <div class="form-group"><label>Áudio</label><input type="file" id="npreg-audio" accept="audio/mpeg,audio/mp4"></div>
      <div class="form-group"><label>Link do Vídeo <span style="color:var(--texto-muted);font-weight:400">(opcional)</span></label><input type="url" id="npreg-video" placeholder="https://youtube.com/..."></div>
      <div class="form-group"><label>Data</label><input type="date" id="npreg-data"></div>
      <div class="form-group"><label>Pregador</label><select id="npreg-pregador"><option value="">— Seleccione —</option></select></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-nova-pregacao')">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="cadastrarPregacao()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Cadastrar
      </button>
    </div>
  </div>
</div>

<!-- Editar Pregação -->
<div class="modal-overlay" id="modal-edit-pregacao">
  <div class="modal">
    <div class="modal-header">
      <h3>Editar Pregação</h3>
      <button class="modal-close" onclick="closeModal('modal-edit-pregacao')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="epreg-id">
      <div class="form-group"><label>Título</label><input type="text" id="epreg-titulo"></div>
      <div class="form-group"><label>Áudio</label><input type="file" id="epreg-audio" accept="audio/mpeg,audio/mp4"></div>
      <div class="form-group"><label>Link do Vídeo <span style="color:var(--texto-muted);font-weight:400">(opcional)</span></label><input type="url" id="epreg-video"></div>
      <div class="form-group"><label>Data</label><input type="date" id="epreg-data"></div>
      <div class="form-group"><label>Pregador</label><select id="epreg-pregador"><option value="">— Seleccione —</option></select></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-edit-pregacao')">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="editarPregacao()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Editar e Salvar
      </button>
    </div>
  </div>
</div>

<!-- Eliminar Pregação -->
<div class="modal-overlay" id="modal-del-pregacao">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3>Eliminar Pregação</h3>
      <button class="modal-close" onclick="closeModal('modal-del-pregacao')">&times;</button>
    </div>
    <div class="modal-body modal-warn">
      <div class="modal-warn-icon">⚠️</div>
      <p>Tem certeza que pretende eliminar a pregação <strong id="del-preg-titulo"></strong>? Esta acção é irreversível.</p>
    </div>
    <div class="modal-footer">
      <input type="hidden" id="del-preg-id">
      <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-del-pregacao')">Cancelar</button>
      <button class="btn btn-danger btn-sm" onclick="eliminarPregacao()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
        Eliminar
      </button>
    </div>
  </div>
</div>

<!-- Eliminar Conta -->
<div class="modal-overlay" id="modal-del-conta">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3>Eliminar Conta</h3>
      <button class="modal-close" onclick="closeModal('modal-del-conta')">&times;</button>
    </div>
    <div class="modal-body modal-warn">
      <div class="modal-warn-icon">🚨</div>
      <p>Tem certeza que pretende <strong>eliminar a sua conta</strong>? Esta acção é irreversível e você perderá o acesso ao painel.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-del-conta')">Cancelar</button>
      <button class="btn btn-danger btn-sm" onclick="eliminarConta()">Eliminar Conta</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// ─── Cloudinary Config ───────────────────────────────────────────────────────
const CLOUDINARY_CLOUD_NAME   = '<?= CLOUDINARY_CLOUD_NAME ?>';
const CLOUDINARY_UPLOAD_PRESET1 = '<?= CLOUDINARY_UPLOAD_PRESET1 ?>';
const CLOUDINARY_UPLOAD_PRESET2 = '<?= CLOUDINARY_UPLOAD_PRESET2 ?>';

// ─── State ───────────────────────────────────────────────────────────────────
let pregadores = [];
let pregacoes  = [];

// ─── Init ─────────────────────────────────────────────────────────────────────
(async () => {
  await Promise.all([loadPregadores(), loadPregacoes()]);
  updateStats();
})();

// ─── Navigation ──────────────────────────────────────────────────────────────
const sectionTitles = {
  dashboard:  'Dashboard',
  pregadores: 'Pregadores',
  pregacoes:  'Pregações',
  conta:      'Minha Conta'
};

function showSection(id, el) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');
  el.classList.add('active');
  document.getElementById('topbar-title').textContent = sectionTitles[id] || id;
}

// ─── API helpers ──────────────────────────────────────────────────────────────
async function api(target, method = 'GET', body = null, id = null) {
  const url  = 'admin.php?action=api&target=' + target + (id ? '&id=' + id : '');
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const r   = await fetch(url, opts);
  const data = await r.json();
  if (data.erro === 'Sessão expirada. Recarregue a página.') {
    window.location.href = '?action=login';
    return;
  }
  return data;
}

// ─── PREGADORES ──────────────────────────────────────────────────────────────
async function loadPregadores() {
  const res = await api('pregadores');
  pregadores = res.data || [];
  renderPregadores();
  populatePregadorSelects();
}

function renderPregadores() {
  const grid = document.getElementById('pregadores-grid');
  if (!pregadores.length) {
    grid.innerHTML = `<div class="empty-state" style="width:100%">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      <p>Nenhum pregador cadastrado ainda.</p></div>`;
    return;
  }
  grid.innerHTML = pregadores.map(p => `
    <div class="pregador-card animate-in">
      ${p.foto
        ? `<img src="${esc(p.foto)}" class="pregador-foto" alt="${esc(p.nome)}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">`
        : ''}
      <div class="pregador-foto-fallback" style="${p.foto ? 'display:none' : ''}">${esc(p.nome).charAt(0).toUpperCase()}</div>
      <div class="pregador-info">
        <div class="pregador-nome">${esc(p.nome)}</div>
        <div class="pregador-count">${p.num_pregacoes} pregaç${p.num_pregacoes == 1 ? 'ão' : 'ões'}</div>
      </div>
      <div class="pregador-actions">
        <button class="btn-icon" title="Editar" onclick="abrirEditPregador(${p.id})">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
        <button class="btn-icon danger" title="Eliminar" onclick="abrirDelPregador(${p.id}, '${esc(p.nome)}')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </button>
      </div>
    </div>`).join('');
}

function populatePregadorSelects() {
  ['npreg-pregador', 'epreg-pregador'].forEach(id => {
    const sel = document.getElementById(id);
    const cur = sel.value;
    sel.innerHTML = '<option value="">— Seleccione —</option>' +
      pregadores.map(p => `<option value="${p.id}">${esc(p.nome)}</option>`).join('');
    if (cur) sel.value = cur;
  });
}

async function cadastrarPregador() {
  const nome = document.getElementById('np-nome').value.trim();
  const fotoInput = document.getElementById('np-foto');
  const fotoFile = fotoInput.files[0];

  if (!nome || !fotoFile) return toast('Preencha todos os campos.', 'error');

  try {
    toast('Enviando foto...', 'info');

    const formData = new FormData();
    formData.append('file', fotoFile);
    formData.append('upload_preset', CLOUDINARY_UPLOAD_PRESET1);

    const cloudRes = await fetch(
      `https://api.cloudinary.com/v1_1/${CLOUDINARY_CLOUD_NAME}/auto/upload`,
      { method: 'POST', body: formData }
    );

    if (!cloudRes.ok) throw new Error('Falha no upload para o Cloudinary.');

    const cloudData = await cloudRes.json();
    const fotoUrl = cloudData.secure_url;

    await api('pregadores', 'POST', { nome, foto: fotoUrl });

    closeModal('modal-novo-pregador');
    document.getElementById('np-nome').value = '';
    fotoInput.value = '';

    await loadPregadores();
    updateStats();
    toast('Pregador cadastrado com sucesso!', 'success');

  } catch (err) {
    console.error(err);
    toast('Erro ao cadastrar pregador: ' + err.message, 'error');
  }
}

function abrirEditPregador(id) {
  const p = pregadores.find(x => x.id == id);
  if (!p) return;
  document.getElementById('ep-id').value = p.id;
  document.getElementById('ep-nome').value = p.nome;
  openModal('modal-edit-pregador');
}

async function editarPregador() {
  const id   = document.getElementById('ep-id').value;
  const nome = document.getElementById('ep-nome').value.trim();
  const fotoInput = document.getElementById('ep-foto');
  const foto = fotoInput.files[0];
  if (!nome) return toast('Preencha todos os campos.', 'error');
  if (foto) {
    const formData = new FormData();
    formData.append('file', foto);
    formData.append('upload_preset', CLOUDINARY_UPLOAD_PRESET1);

    const cloudRes = await fetch(
      `https://api.cloudinary.com/v1_1/${CLOUDINARY_CLOUD_NAME}/auto/upload`,
      { method: 'POST', body: formData }
    );

    if (!cloudRes.ok) throw new Error('Falha no upload para o Cloudinary.');

    const cloudData = await cloudRes.json();
    const fotoUrl = cloudData.secure_url;
    await api('pregadores', 'PUT', { nome, fotoUrl }, id);
  } else {
    await api('pregadores', 'PUT', { nome }, id);
  }

  closeModal('modal-edit-pregador');
  await loadPregadores();
  updateStats();
  toast('Pregador actualizado!', 'success');
}

function abrirDelPregador(id, nome) {
  document.getElementById('del-p-id').value = id;
  document.getElementById('del-p-nome').textContent = nome;
  openModal('modal-del-pregador');
}

async function eliminarPregador() {
  const id = document.getElementById('del-p-id').value;
  await api('pregadores', 'DELETE', null, id);
  closeModal('modal-del-pregador');
  await loadPregadores();
  await loadPregacoes();
  updateStats();
  toast('Pregador e pregações eliminados.', 'success');
}

// ─── PREGAÇÕES ────────────────────────────────────────────────────────────────
async function loadPregacoes() {
  const res = await api('pregacoes');
  pregacoes = res.data || [];
  renderPregacoes();
}

function renderPregacoes() {
  const tbody = document.getElementById('pregacoes-tbody');
  if (!pregacoes.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="padding:32px;text-align:center;color:var(--texto-muted)">Nenhuma pregação cadastrada.</td></tr>';
    return;
  }
  tbody.innerHTML = pregacoes.map(p => `
    <tr>
      <td><strong>${esc(p.titulo)}</strong></td>
      <td>${esc(p.pregador_nome || '–')}</td>
      <td>${formatDate(p.data)}</td>
      <td>${p.audio ? `<a href="${esc(p.audio)}" target="_blank" rel="external"><span class="badge badge-audio">⬇ Baixar</span></a>` : '<span class="badge badge-none">–</span>'}</td>
      <td>${p.video ? `<a href="${esc(p.video)}" target="_blank" rel="external"><span class="badge badge-video">▶ Vídeo</span></a>` : '<span class="badge badge-none">–</span>'}</td>
      <td>
        <div class="table-actions">
          <button class="btn-icon" title="Editar" onclick="abrirEditPregacao(${p.id})">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="btn-icon danger" title="Eliminar" onclick="abrirDelPregacao(${p.id}, '${esc(p.titulo).replace(/'/g,"\\'")}')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          </button>
        </div>
      </td>
    </tr>`).join('');
}

async function cadastrarPregacao() {
  const titulo         = document.getElementById('npreg-titulo').value.trim();
  const audio          = document.getElementById('npreg-audio').files[0];
  const video          = document.getElementById('npreg-video').value.trim();
  const data           = document.getElementById('npreg-data').value;
  const pregador_id    = document.getElementById('npreg-pregador').value;
  if (!titulo || !audio || !data || !pregador_id) return toast('Preencha os campos obrigatórios.', 'error');
  try {
    toast('Enviando áudio...');
    const formData = new FormData();
    formData.append('file', audio);
    formData.append('upload_preset', CLOUDINARY_UPLOAD_PRESET2);

    const cloudRes = await fetch(
      `https://api.cloudinary.com/v1_1/${CLOUDINARY_CLOUD_NAME}/auto/upload`,
      { method: 'POST', body: formData }
    );

    if (!cloudRes.ok) throw new Error('Falha no upload para o Cloudinary.');
    const cloudData = await cloudRes.json();
    const audioUrl = cloudData.secure_url;
    await api('pregacoes', 'POST', { titulo, audio: audioUrl, video, data, pregador_id });
    closeModal('modal-nova-pregacao');
    ['npreg-titulo','npreg-audio','npreg-video','npreg-data'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('npreg-pregador').value = '';
    await loadPregacoes();
    await loadPregadores();
    updateStats();
    toast('Pregação cadastrada com sucesso!', 'success');
  } catch (error) {
    toast('Erro ao enviar áudio.', 'error');
  }
}

function abrirEditPregacao(id) {
  const p = pregacoes.find(x => x.id == id);
  if (!p) return;
  document.getElementById('epreg-id').value          = p.id;
  document.getElementById('epreg-titulo').value      = p.titulo;
  document.getElementById('epreg-video').value       = p.video || '';
  document.getElementById('epreg-data').value        = p.data;
  document.getElementById('epreg-pregador').value    = p.pregador_id;
  document.getElementById('epreg-audio').dataset.audioAtual = p.audio || '';
  document.getElementById('epreg-audio').value = '';
  openModal('modal-edit-pregacao');
}

async function editarPregacao() {
  const id          = document.getElementById('epreg-id').value;
  const titulo      = document.getElementById('epreg-titulo').value.trim();
  const audioInput  = document.getElementById('epreg-audio');
  const novoAudio   = audioInput.files[0];
  const audioAtual  = audioInput.dataset.audioAtual || '';
  const video       = document.getElementById('epreg-video').value.trim();
  const data        = document.getElementById('epreg-data').value;
  const pregador_id = document.getElementById('epreg-pregador').value;

  if (!titulo || !data || !pregador_id) return toast('Preencha os campos obrigatórios.', 'error');

  try {
    let audioUrl = audioAtual;
    if (novoAudio) {
      toast('Enviando áudio...');
      const formData = new FormData();
      formData.append('file', novoAudio);
      formData.append('upload_preset', CLOUDINARY_UPLOAD_PRESET2);

      const cloudRes = await fetch(
        `https://api.cloudinary.com/v1_1/${CLOUDINARY_CLOUD_NAME}/auto/upload`,
        { method: 'POST', body: formData }
      );
      if (!cloudRes.ok) throw new Error('Falha no upload para o Cloudinary.');
      const cloudData = await cloudRes.json();
      audioUrl = cloudData.secure_url;
    }

    await api('pregacoes', 'PUT', { titulo, audio: audioUrl, video, data, pregador_id }, id);
    closeModal('modal-edit-pregacao');
    await loadPregacoes();
    updateStats();
    toast('Pregação actualizada!', 'success');
  } catch (err) {
    console.error(err);
    toast('Erro ao actualizar pregação: ' + err.message, 'error');
  }
}

function abrirDelPregacao(id, titulo) {
  document.getElementById('del-preg-id').value = id;
  document.getElementById('del-preg-titulo').textContent = titulo;
  openModal('modal-del-pregacao');
}

async function eliminarPregacao() {
  const id = document.getElementById('del-preg-id').value;
  await api('pregacoes', 'DELETE', null, id);
  closeModal('modal-del-pregacao');
  await loadPregacoes();
  await loadPregadores();
  updateStats();
  toast('Pregação eliminada.', 'success');
}

// ─── CONTA ────────────────────────────────────────────────────────────────────
async function salvarEmail() {
  const email = document.getElementById('conta-email').value.trim();
  if (!email) return toast('Digite um email válido.', 'error');
  await api('conta', 'PUT', { tipo: 'email', email });
  toast('Email actualizado!', 'success');
}

async function salvarSenha() {
  const pw  = document.getElementById('conta-senha').value;
  const pw2 = document.getElementById('conta-senha2').value;
  if (!pw) return toast('Digite uma senha.', 'error');
  if (pw !== pw2) return toast('As senhas não coincidem.', 'error');
  await api('conta', 'PUT', { tipo: 'password', password: pw });
  document.getElementById('conta-senha').value = '';
  document.getElementById('conta-senha2').value = '';
  toast('Senha actualizada!', 'success');
}

async function eliminarConta() {
  const res = await api('conta', 'DELETE');
  if (res.ok && res.redirect) window.location.href = res.redirect;
}

// ─── STATS ────────────────────────────────────────────────────────────────────
function updateStats() {
  document.getElementById('stat-pregadores').textContent = pregadores.length;
  document.getElementById('stat-pregacoes').textContent  = pregacoes.length;
  document.getElementById('stat-audio').textContent = pregacoes.filter(p => p.audio).length;
  document.getElementById('stat-video').textContent = pregacoes.filter(p => p.video).length;
}

// ─── XLSX EXPORT ─────────────────────────────────────────────────────────────
async function exportXLSX() {
  toast('A gerar ficheiro Excel...', '');
  const wb = XLSX.utils.book_new();

  // Sheet: Pregadores
  const pregData = [['ID','Nome','Foto','Nº Pregações']].concat(
    pregadores.map(p => [p.id, p.nome, p.foto, p.num_pregacoes])
  );
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(pregData), 'Pregadores');

  // Sheet: Pregações
  const pregaData = [['ID','Título','Pregador','Data','Link','Vídeo']].concat(
    pregacoes.map(p => [p.id, p.titulo, p.pregador_nome, p.data, p.audio || '', p.video || ''])
  );
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(pregaData), 'Pregações');

  XLSX.writeFile(wb, 'banco_de_dados_jardim_do_mar.xlsx');
  toast('Ficheiro Excel gerado!', 'success');
}

// ─── MODAL helpers ────────────────────────────────────────────────────────────
function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

// ─── TOAST ────────────────────────────────────────────────────────────────────
let toastTimer;
function toast(msg, type = '') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'toast ' + type + ' show';
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 3000);
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatDate(d) {
  if (!d) return '–';
  const [y,m,day] = d.split('-');
  return `${day}/${m}/${y}`;
}
</script>

<?php endif; ?>
</body>
</html>
