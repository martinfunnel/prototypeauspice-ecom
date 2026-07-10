<?php
$storagePath = __DIR__ . '/../storage/app/public';
$publicStorage = __DIR__ . '/storage';

function copyDir($src, $dst) {
    if (!is_dir($src)) return;
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $srcFile = $src . '/' . $file;
        $dstFile = $dst . '/' . $file;
        if (is_dir($srcFile)) {
            copyDir($srcFile, $dstFile);
        } elseif (!file_exists($dstFile)) {
            copy($srcFile, $dstFile);
            echo "  Copié : " . str_replace(__DIR__ . '/', '', $dstFile) . "\n";
        }
    }
    closedir($dir);
}

// 1. Si c'est un symlink déjà OK
if (is_link($publicStorage)) {
    echo "OK - Le lien symbolique existe déjà.\n";
    exit;
}

// 2. S'assurer que public/storage existe
if (!is_dir($publicStorage)) {
    mkdir($publicStorage, 0755, true);
}

// 3. Copier les fichiers manquants depuis storage/app/public vers public/storage
if (is_dir($storagePath)) {
    echo "Fusion des images en cours...\n";
    copyDir($storagePath, $publicStorage);
    echo "\n✅ Terminé. Les images produits/catégories ont été copiées dans public/storage/\n";
    echo "Rafraîchis le site pour vérifier.\n";
} else {
    echo "⚠️ storage/app/public n'existe pas.\n";
}
