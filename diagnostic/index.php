<?php

declare(strict_types=1);

$cfgFile = __DIR__ . '/diagnostico.config.aws.php';
$fileCfg = is_file($cfgFile) ? require $cfgFile : [];

function cfg(string $key, array $fileCfg, string $default = ''): string
{
    $v = getenv($key);
    if ($v !== false && $v !== '') {
        return $v;
    }
    return (string)($fileCfg[$key] ?? $default);
}

$project = 'ReparaYa';
$group = cfg('GROUP_CODE', $fileCfg, 'SIN_CODIGO');
$student = cfg('STUDENT_NAME', $fileCfg, 'SIN_NOMBRE');
$env = cfg('APP_ENV', $fileCfg, 'local');
$now = (new DateTime('now'))->format('d/m/Y H:i:s T');

# Conexión a la BBDD
$dbOk = false;
$dbMsg = '';
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        cfg('DB_HOST', $fileCfg, 'db'),
        cfg('DB_PORT', $fileCfg, '3306'),
        cfg('DB_DATABASE', $fileCfg)
    );

    $pdo = new PDO($dsn, cfg('DB_USERNAME', $fileCfg), cfg('DB_PASSWORD', $fileCfg), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3,
    ]);
    $dbOk = true;
    $dbMsg = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
} catch (PDOException $e) {
    $dbMsg = 'Error de conexión (código ' . $e->getCode() . ')';
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$extensions = get_loaded_extensions();
sort($extensions, SORT_NATURAL | SORT_FLAG_CASE);

$needed = ['pdo_mysql', 'mysqli', 'mbstring', 'openss1', 'tokenizer', 'xml', 'ctype', 'fileinfo', 'curl', 'dom', 'zip', 'gd'];
$missing = array_values(array_filter($needed, fn($x) => !extension_loaded($x)));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnóstico - <?= e($project) ?></title>
    <style>
        :root {
            --bg: #f4f6fb;
            --card: #ffffff;
            --text: #1c2333;
            --muted: #6b7489;
            --border: #e3e7f0;
            --accent: #4f46e5;
            --ok: #16a34a;
            --ok-bg: #dcfce7;
            --err: #dc2626;
            --err-bg: #fee2e2;
            --warn: #d97706;
            --warn-bg: #fef3c7;
            --radius: 14px;
            --shadow: 0 1px 2px rgb(16 24 40 / .06), 0 8px 24px rgb(16 24 40 / .06);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0e1220;
                --card: #171c2e;
                --text: #e8ecf8;
                --muted: #8f9ab3;
                --border: #262d45;
                --accent: #818cf8;
                --ok: #4ade80;
                --ok-bg: #12301f;
                --err: #f87171;
                --err-bg: #3a1618;
                --warn: #fbbf24;
                --warn-bg: #3a2c0c;
                --shadow: 0 8px 24px rgb(0 0 0 / .35);
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        .container {
            max-width: 860px;
            margin: 0 auto;
            padding: 2rem 1.25rem 4rem;
        }

        .header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .header h1 {
            margin: 0;
            font-size: 1.75rem;
            letter-spacing: -.02em;
        }

        .header p {
            margin: .25rem 0 0;
            color: var(--muted);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.25rem 1.4rem;
        }

        .card h2 {
            margin: 0 0 .75rem;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }

        .stat {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -.02em;
            color: var(--accent);
        }

        .stat small {
            display: block;
            margin-top: .25rem;
            font-size: .85rem;
            font-weight: 400;
            color: var(--muted);
            letter-spacing: 0;
        }

        .kv {
            display: grid;
            margin: 0;
        }

        .kv div {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .65rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .kv div:last-child {
            border-bottom: 0;
        }

        .kv dt {
            color: var(--muted);
        }

        .kv dd {
            margin: 0;
            font-weight: 500;
            text-align: right;
            word-break: break-all;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .2rem .7rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
        }

        .badge::before {
            content: "";
            width: .5rem;
            height: .5rem;
            border-radius: 50%;
            background: currentColor;
        }

        .badge.ok {
            color: var(--ok);
            background: var(--ok-bg);
        }

        .badge.ko {
            color: var(--err);
            background: var(--err-bg);
        }

        .badge.warn {
            color: var(--warn);
            background: var(--warn-bg);
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .chip {
            padding: .3rem .7rem;
            border-radius: 8px;
            font-size: .85rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .chip.ok {
            background: var(--ok-bg);
            color: var(--ok);
        }

        .chip.ko {
            background: var(--err-bg);
            color: var(--err);
        }

        .footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: .85rem;
            color: var(--muted);
        }

        @media (max-width: 600px) {
            .header h1 {
                font-size: 1.4rem;
            }

            .kv div {
                flex-direction: column;
                gap: .1rem;
            }

            .kv dd {
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <header class="header">
            <div>
                <h1>Diagnóstico del entorno</h1>
                <p><?= e($project) ?></p>
            </div>
            <span class="badge warn"><?= e(strtoupper($env)) ?></span>
        </header>

        <div class="grid">
            <section class="card">
                <h2>Versión de PHP (Apache)</h2>
                <div class="stat"><?= e(PHP_VERSION) ?></div>
            </section>

            <section class="card">
                <h2>Base de datos</h2>
                <span class="badge <?= $dbOk ? 'ok' : 'ko' ?>">
                    <?= $dbOk ? 'Conectada' : 'Sin conexión' ?>
                </span>
                <div class="stat" style="font-size:1.1rem; margin-top:.75rem;">
                    <small style="margin:0;"><?= e($dbMsg) ?></small>
                </div>
            </section>
        </div>

        <section class="card" style="margin-bottom:1.25rem;">
            <h2>Datos del proyecto</h2>
            <dl class="kv">
                <div>
                    <dt>Proyecto</dt>
                    <dd><?= e($project) ?></dd>
                </div>
                <div>
                    <dt>Código de grupo</dt>
                    <dd><?= e($group) ?></dd>
                </div>
                <div>
                    <dt>Estudiante</dt>
                    <dd><?= e($student) ?></dd>
                </div>
                <div>
                    <dt>Entorno</dt>
                    <dd><?= e(strtoupper($env)) ?></dd>
                </div>
                <div>
                    <dt>Fecha y hora del servidor</dt>
                    <dd><?= e($now) ?></dd>
                </div>
            </dl>
        </section>

        <section class="card">
            <h2>Extensiones PHP cargadas (<?= count($extensions) ?>)</h2>
            <div class="chips">
                <?php foreach ($extensions as $ext): ?>
                    <span class="chip ok"><?= e($ext) ?></span>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card" style="margin-top:1.25rem;">
            <h2>Extensiones necesarias que faltan (<?= count($missing) ?>)</h2>
            <?php if ($missing): ?>
                <div class="chips">
                    <?php foreach ($missing as $ext): ?>
                        <span class="chip ko"><?= e($ext) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="badge ok">Ninguna</span>
            <?php endif; ?>
        </section>

        <p class="footer">Generado el <?= e($now) ?></p>
    </div>
</body>

</html>