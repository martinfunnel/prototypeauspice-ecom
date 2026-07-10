<?php
/**
 * PWA Debug — Diagnostic complet du manifest et du service worker
 * Uploade ce fichier à la racine de ton site et ouvre-le dans le navigateur :
 * https://auspicemarket.com/pwa-debug.php
 */

header('Content-Type: text/html; charset=utf-8');

function section($title) {
    echo "<div class='section'><h2>" . htmlspecialchars($title) . "</h2>";
}
function endSection() {
    echo "</div>";
}
function row($label, $value, $ok = null) {
    $class = ($ok === true) ? 'ok' : (($ok === false) ? 'ko' : '');
    echo "<div class='row {$class}'><strong>" . htmlspecialchars($label) . "</strong><span>" . $value . "</span></div>";
}
function path($p) { return htmlspecialchars($p); }
function exists($p) { return file_exists($p); }
function readable($p) { return is_readable($p); }
function size($p) { return exists($p) ? filesize($p) . ' octets' : '—'; }

$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$scriptPath = realpath(__FILE__);
$scriptDir = dirname($scriptPath);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>PWA Debug — Auspice Market</title>
<style>
body{font-family:system-ui,-apple-system,sans-serif;background:#0f172a;color:#e2e8f0;padding:20px;line-height:1.6;}
.container{max-width:900px;margin:0 auto;}
h1{color:#38bdf8;border-bottom:2px solid #38bdf8;padding-bottom:10px;}
.section{background:#1e293b;border-radius:12px;padding:20px;margin-bottom:20px;border:1px solid #334155;}
.section h2{margin-top:0;color:#a78bfa;font-size:1.1em;text-transform:uppercase;letter-spacing:1px;}
.row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #334155;}
.row:last-child{border-bottom:none;}
.ok{color:#4ade95;font-weight:bold;}
.ok::before{content:"OK ";}
.ko{color:#f87171;font-weight:bold;}
.ko::before{content:"KO ";}
.warn{color:#fbbf24;font-weight:bold;}
.warn::before{content:"! ";}
pre{background:#0f172a;padding:10px;border-radius:6px;overflow-x:auto;font-size:12px;color:#94a3b8;border:1px solid #334155;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th,td{padding:8px;text-align:left;border-bottom:1px solid #334155;}
th{color:#94a3b8;font-weight:600;}
.hint{background:#334155;padding:12px;border-radius:8px;margin-top:10px;font-size:13px;color:#cbd5e1;}
.code{background:#0f172a;padding:2px 6px;border-radius:4px;font-family:monospace;color:#f472b6;}
</style>
</head>
<body>
<div class="container">

<h1>PWA Debug — Diagnostic complet</h1>

<?php section("1. Environnement serveur"); ?>
<?php row("DOCUMENT_ROOT", path($docRoot), true); ?>
<?php row("Script courant", path($scriptPath), true); ?>
<?php row("Dossier du script", path($scriptDir), true); ?>
<?php row("PHP Version", phpversion(), true); ?>
<?php row("Host HTTP", htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A'), true); ?>
<?php endSection(); ?>

<?php section("2. Fichiers PWA — Analyse des emplacements"); ?>
<?php
$checkedPaths = [];
// Chemins à tester (relatifs au dossier de ce script)
$relPaths = ['manifest.json', 'sw.js', 'images/logo.png', 'images/logo-192.png', 'images/logo-512.png'];
foreach ($relPaths as $rel) {
    $abs = $scriptDir . '/' . $rel;
    $exists = exists($abs);
    $read = $exists ? readable($abs) : false;
    $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/' . $rel;
    $status = ($exists && $read) ? true : false;
    $checkedPaths[$rel] = ['exists' => $exists, 'path' => $abs, 'url' => $url];
    row(
        $rel . " <br><small style='color:#64748b'>" . path($abs) . "</small>",
        ($exists ? ($read ? 'EXiste & lisible (' . size($abs) . ')' : 'Existe mais NON LISIBLE') : 'MANQUANT') .
        "<br><a href='" . htmlspecialchars($url) . "' style='color:#38bdf8;font-size:11px;' target='_blank'>" . htmlspecialchars($url) . "</a>",
        $status
    );
}
?>
<?php endSection(); ?>

<?php section("3. Recherche récursive de manifest.json et sw.js"); ?>
<?php
$foundManifest = [];
$foundSw = [];
function pwaScanDir($dir, &$manifests, &$sws, $base = '') {
    if (!is_dir($dir)) return;
    $items = @scandir($dir);
    if (!$items) return;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dir . '/' . $item;
        if (is_dir($full)) {
            if (strpos($full, 'node_modules') !== false) continue;
            if (strpos($full, 'vendor') !== false) continue;
            if (strpos($full, '.git') !== false) continue;
            pwaScanDir($full, $manifests, $sws, $base . $item . '/');
        } else {
            if ($item === 'manifest.json') $manifests[] = $full;
            if ($item === 'sw.js') $sws[] = $full;
        }
    }
}
pwaScanDir($docRoot, $foundManifest, $foundSw);

if (empty($foundManifest)) {
    row("manifest.json", "Aucun fichier trouvé sous DOCUMENT_ROOT", false);
} else {
    foreach ($foundManifest as $m) {
        $rel = str_replace($docRoot . '/', '', $m);
        $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/' . $rel;
        row("manifest.json trouvé", path($m) . "<br><a href='" . htmlspecialchars($url) . "' style='color:#38bdf8;font-size:11px;' target='_blank'>" . htmlspecialchars($url) . "</a>", true);
    }
}

if (empty($foundSw)) {
    row("sw.js", "Aucun fichier trouvé sous DOCUMENT_ROOT", false);
} else {
    foreach ($foundSw as $s) {
        $rel = str_replace($docRoot . '/', '', $s);
        $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/' . $rel;
        row("sw.js trouvé", path($s) . "<br><a href='" . htmlspecialchars($url) . "' style='color:#38bdf8;font-size:11px;' target='_blank'>" . htmlspecialchars($url) . "</a>", true);
    }
}
?>
<?php endSection(); ?>

<?php section("4. Contenu du manifest.json trouvé"); ?>
<?php
if (!empty($foundManifest)) {
    $primaryManifest = $foundManifest[0];
    $content = @file_get_contents($primaryManifest);
    if ($content) {
        $json = @json_decode($content, true);
        if ($json) {
            row("name", htmlspecialchars($json['name'] ?? 'NON DÉFINI'), isset($json['name']));
            row("short_name", htmlspecialchars($json['short_name'] ?? 'NON DÉFINI'), isset($json['short_name']));
            row("start_url", htmlspecialchars($json['start_url'] ?? 'NON DÉFINI'), isset($json['start_url']));
            row("display", htmlspecialchars($json['display'] ?? 'NON DÉFINI'), isset($json['display']));
            row("theme_color", htmlspecialchars($json['theme_color'] ?? 'NON DÉFINI'), isset($json['theme_color']));
            $iconsOk = isset($json['icons']) && count($json['icons']) > 0;
            row("icons count", ($iconsOk ? count($json['icons']) . ' icônes' : 'AUCUNE ICÔNE'), $iconsOk);
            if ($iconsOk) {
                foreach ($json['icons'] as $i => $icon) {
                    $iconPath = $icon['src'] ?? '';
                    $iconAbs = $docRoot . $iconPath;
                    $iconRel = ltrim($iconPath, '/');
                    $iconExists = exists($docRoot . '/' . $iconRel);
                    row("  → icon $i (" . ($icon['sizes'] ?? '?') . ")", "src=" . htmlspecialchars($iconPath) . ($iconExists ? ' (fichier OK)' : ' <span style="color:#f87171">(FICHIER MANQUANT)</span>'), $iconExists);
                }
            }
            echo "<div class='row'><pre>" . htmlspecialchars($content) . "</pre></div>";
        } else {
            row("Erreur JSON", "Le fichier manifest.json contient du JSON invalide", false);
        }
    } else {
        row("Erreur lecture", "Impossible de lire le fichier", false);
    }
} else {
    row("Info", "Pas de manifest.json trouvé pour l'analyse", false);
}
?>
<?php endSection(); ?>

<?php section("5. Analyse des vues Blade (si Laravel détecté)"); ?>
<?php
$laravelPaths = [];
function pwaFindLaravel($dir, &$paths) {
    if (!is_dir($dir)) return;
    $items = @scandir($dir);
    if (!$items) return;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dir . '/' . $item;
        if (is_dir($full)) {
            if ($item === 'node_modules' || $item === 'vendor' || $item === '.git') continue;
            if ($item === 'resources' && is_dir($full . '/views')) {
                $paths[] = dirname($full);
            }
            pwaFindLaravel($full, $paths);
        }
    }
}
pwaFindLaravel($docRoot, $laravelPaths);

if (empty($laravelPaths)) {
    row("Laravel", "Aucune installation Laravel trouvée sous DOCUMENT_ROOT", false);
} else {
    foreach ($laravelPaths as $lp) {
        row("Laravel trouvé", path($lp), true);
        $layout = $lp . '/resources/views/components/layout.blade.php';
        $admin = $lp . '/resources/views/components/admin-shell.blade.php';

        foreach (['layout.blade.php' => $layout, 'admin-shell.blade.php' => $admin] as $name => $file) {
            $hasManifest = false;
            $hasTheme = false;
            $hasSw = false;
            if (exists($file)) {
                $html = @file_get_contents($file);
                $hasManifest = strpos($html, 'rel="manifest"') !== false;
                $hasTheme = strpos($html, 'theme-color') !== false;
                $hasSw = strpos($html, 'serviceWorker') !== false || strpos($html, 'navigator.serviceWorker') !== false;
                row("  → {$name} manifest", $hasManifest ? 'PRÉSENT' : 'MANQUANT', $hasManifest);
                row("  → {$name} theme-color", $hasTheme ? 'PRÉSENT' : 'MANQUANT', $hasTheme);
                row("  → {$name} service worker", $hasSw ? 'PRÉSENT' : 'MANQUANT', $hasSw);
            } else {
                row("  → {$name}", "FICHIER INTROUVABLE", false);
            }
        }
    }
}
?>
<?php endSection(); ?>

<?php section("6. Test HTTP Headers (requête cURL vers le manifest)"); ?>
<?php
if (!empty($foundManifest)) {
    $rel = str_replace($docRoot . '/', '', $foundManifest[0]);
    $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/' . $rel;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        row("URL testée", htmlspecialchars($url));
        row("HTTP Code", $httpCode, $httpCode == 200);
        row("Content-Type", htmlspecialchars($contentType ?? 'N/A'), strpos($contentType ?? '', 'json') !== false);
        row("Réponse headers", "<pre>" . htmlspecialchars($response ?: 'Pas de réponse') . "</pre>");
    } else {
        row("cURL", "Extension cURL non disponible sur ce serveur", false);
    }
} else {
    row("Info", "Pas de manifest.json à tester", false);
}
?>
<?php endSection(); ?>

<?php section("7. Résumé et recommandations"); ?>
<div class="hint">
<strong>Le problème le plus fréquent :</strong><br>
Si <code>https://auspicemarket.com/manifest.json</code> retourne 404, c'est que le fichier <code>manifest.json</code> n'est pas dans le bon dossier sur le serveur.<br><br>

<strong>Solution :</strong>
<ol>
<li>Vérifie où se trouve <code>index.php</code> à la racine du site dans ton FTP.</li>
<li>Copie <code>manifest.json</code> et <code>sw.js</code> dans le MÊME dossier que ce <code>index.php</code>.</li>
<li>Assure-toi aussi que les fichiers Blade (<code>layout.blade.php</code> et <code>admin-shell.blade.php</code>) sont uploadés.</li>
<li>Vide le cache navigateur (Ctrl+Maj+R) et reteste.</li>
</ol>
</div>
<?php endSection(); ?>

</div>
</body>
</html>
