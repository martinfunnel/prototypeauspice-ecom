<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AdminController;
// use App\Http\Controllers\WebhookController; // DÉSACTIVÉ — WhatsApp remplacé par Telegram

// Diagnostic + copie de toutes les images/vidéos vers public/storage
Route::get('/fix-images/{token}', function (string $token) {
    if ($token !== 'auspice123') { abort(403); }

    $basePath = realpath(__DIR__ . '/..'); // chemin racine du projet
    $destRoot = public_path('storage');
    $extensions = ['jpg','jpeg','png','gif','webp','svg','mp4','webm','mov','avi'];
    $found = [];
    $copied = [];
    $skipped = [];

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, $extensions)) continue;

        $relPath = str_replace($basePath . '/', '', $file->getPathname());
        $found[] = $relPath;

        // Détermine le sous-dossier cible selon le chemin source
        if (str_contains($relPath, 'products')) {
            $subFolder = 'products';
        } elseif (str_contains($relPath, 'categories')) {
            $subFolder = 'categories';
        } elseif (str_contains($relPath, 'testimonials')) {
            if (str_contains($relPath, 'video')) {
                $subFolder = 'testimonials/videos';
            } else {
                $subFolder = 'testimonials/images';
            }
        } elseif (str_contains($relPath, 'banners')) {
            $subFolder = 'banners';
        } else {
            $subFolder = 'uploads';
        }

        $destDir = $destRoot . '/' . $subFolder;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $destFile = $destDir . '/' . $file->getFilename();
        if (!file_exists($destFile)) {
            if (@copy($file->getPathname(), $destFile)) {
                $copied[] = 'storage/' . $subFolder . '/' . $file->getFilename() . '  (source: ' . $relPath . ')';
            } else {
                $skipped[] = 'ERREUR COPIE: ' . $relPath;
            }
        } else {
            $skipped[] = 'Déjà présent: storage/' . $subFolder . '/' . $file->getFilename();
        }
    }

    $html = '<h1>Diagnostic Images</h1>';
    $html .= '<h2>' . count($found) . ' fichiers trouvés dans le projet</h2>';
    $html .= '<h2 style="color:green">' . count($copied) . ' copiés vers public/storage</h2>';
    $html .= '<h2 style="color:orange">' . count($skipped) . ' ignorés (déjà présents ou erreur)</h2>';

    if ($copied) {
        $html .= '<h3>Copiés :</h3><pre>' . implode("\n", $copied) . '</pre>';
    }
    if ($skipped) {
        $html .= '<h3>Ignorés :</h3><pre>' . implode("\n", $skipped) . '</pre>';
    }
    if (!$found) {
        $html .= '<p><b>Aucune image trouvée.</b> Vérifie que les fichiers existent bien sur le serveur.</p>';
    }

    return response($html);
});

// Reset migration country_codes pour forcer sa ré-exécution
Route::get('/reset-migration/{token}', function (string $token) {
    if ($token !== 'auspice123') { abort(403); }
    DB::delete("DELETE FROM migrations WHERE migration LIKE '%country_codes%'");
    return 'Entrées country_codes supprimées de la table migrations. Lance maintenant php artisan migrate';
});

// Copie public/storage vers la vraie racine web (htdocs/storage) pour les anciennes images
Route::get('/sync-storage/{token}', function (string $token) {
    if ($token !== 'auspice123') { abort(403); }
    $src = public_path('storage');
    $dst = dirname(base_path()) . '/storage';
    $copied = [];
    $copyRec = function ($s, $d) use (&$copyRec, &$copied) {
        if (!is_dir($s)) return;
        @mkdir($d, 0755, true);
        foreach (scandir($s) as $f) {
            if ($f === '.' || $f === '..') continue;
            $sf = $s . '/' . $f;
            $df = $d . '/' . $f;
            if (is_dir($sf)) {
                $copyRec($sf, $df);
            } elseif (!file_exists($df)) {
                if (@copy($sf, $df)) $copied[] = $df;
            }
        }
    };
    $copyRec($src, $dst);
    return '<h2>' . count($copied) . ' fichiers copiés vers ' . $dst . '</h2><pre>'
        . implode("\n", array_map('basename', $copied)) . '</pre>';
});

// Diagnostic racine web — vérifie si public_path correspond à la vraie racine servie
Route::get('/debug-docroot/{token}', function (string $token) {
    if ($token !== 'auspice123') { abort(403); }
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'inconnu';
    $info = [
        'DOCUMENT_ROOT' => $docRoot,
        'public_path()' => public_path(),
        'public == docroot ?' => (rtrim($docRoot, '/') === rtrim(public_path(), '/')) ? 'OUI' : 'NON',
        'base_path()' => base_path(),
        '__DIR__ (public/index.php devrait être ici)' => realpath(public_path()),
    ];
    // Crée un fichier test dans public/uploads pour voir s'il est accessible
    $testFile = public_path('uploads/test-access.txt');
    @file_put_contents($testFile, 'test ' . time());
    $info['test file créé'] = file_exists($testFile) ? 'OUI' : 'NON';
    $info['test URL à ouvrir'] = asset('uploads/test-access.txt');
    // Liste fichiers banners
    $banners = glob(public_path('storage/banners/*'));
    $info['fichiers public/storage/banners'] = array_map('basename', $banners ?: []);
    return '<pre>' . print_r($info, true) . '</pre>';
});

// Debug produits — voir les URLs d'images stockées en base + fichiers physiques
Route::get('/debug-products/{token}', function (string $token) {
    if ($token !== 'auspice123') { abort(403); }
    $products = \App\Models\Product::orderByDesc('created_at')->limit(5)->get();
    $out = [];
    foreach ($products as $p) {
        $out[] = [
            'name' => $p->name,
            'images' => $p->images,
            'detail_images' => $p->detail_images,
        ];
    }
    $files = glob(public_path('uploads/products/*'));
    $fileList = array_map(fn($f) => str_replace(public_path(), '', $f), $files);
    return '<h2>En base (5 derniers produits)</h2><pre>' . print_r($out, true) . '</pre>'
         . '<h2>Fichiers physiques dans uploads/products/</h2><pre>' . print_r($fileList, true) . '</pre>';
});

// Test upload image produit — affiche le chemin exact et vérifie les permissions
Route::get('/test-upload-path/{token}', function (string $token) {
    if ($token !== 'auspice123') { abort(403); }
    $paths = [
        'public_path()' => public_path(),
        'public_path(uploads)' => public_path('uploads'),
        'public_path(uploads/products)' => public_path('uploads/products'),
        'storage_path(app/public)' => storage_path('app/public'),
        'storage_path(app/public/products)' => storage_path('app/public/products'),
        'exists uploads/' => is_dir(public_path('uploads')) ? 'OUI' : 'NON',
        'exists uploads/products/' => is_dir(public_path('uploads/products')) ? 'OUI' : 'NON',
        'writable uploads/' => is_writable(public_path('uploads')) ? 'OUI' : 'NON',
        'writable uploads/products/' => is_writable(public_path('uploads/products')) ? 'OUI' : 'NON',
    ];
    return '<h2>Diagnostics chemins</h2><pre>' . print_r($paths, true) . '</pre>';
});

// Vidage rapide du cache des vues (pour résoudre les problèmes de hash Vite périmés)
Route::get('/clear-views/{token}', function (string $token) {
    if ($token !== 'auspice123') {
        abort(403);
    }
    $viewsPath = storage_path('framework/views');
    $count = 0;
    foreach (glob($viewsPath . '/*.php') as $file) {
        unlink($file);
        $count++;
    }
    return response("✅ {$count} vues compilées supprimées. Recharge ton site avec Ctrl+F5.");
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog');
Route::get('/produit/{slug}', [ProductController::class, 'show'])->name('product');

Route::get('/panier', [CartController::class, 'index'])->name('cart');
Route::post('/panier/ajouter', [CartController::class, 'add'])->name('cart.add');
Route::post('/panier/supprimer', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/panier/maj', [CartController::class, 'update'])->name('cart.update');

Route::get('/politique-confidentialite', function () {
    return view('privacy');
})->name('privacy');

Route::get('/commande', [OrderController::class, 'create'])->name('order.create');
Route::post('/commande', [OrderController::class, 'store'])->name('order.store');
Route::post('/commande-directe', [OrderController::class, 'storeDirect'])->name('order.direct');

Route::get('/suivi', [TrackController::class, 'index'])->name('track');
Route::post('/suivi', [TrackController::class, 'search'])->name('track.search');
Route::get('/suivi/{order}', [TrackController::class, 'show'])->name('track.show');

// Test complet WhatsApp — DÉSACTIVÉ, remplacé par Telegram
/*
Route::get('/test-whatsapp/{token}', function (string $token) {
    if ($token !== 'auspice123') { abort(403); }

    $out = [];
    $out[] = '<h1>=== TEST WHATSAPP COMPLET ===</h1>';

    // 1. CONFIG
    $enabled     = config('services.whatsapp.enabled', false);
    $phoneId     = config('services.whatsapp.phone_number_id', '');
    $accessToken = config('services.whatsapp.access_token', '');
    $apiVersion  = config('services.whatsapp.api_version', 'v18.0');
    $template    = config('services.whatsapp.client_template', '');

    $out[] = '<h2>ETAPE 1 — CONFIGURATION LUE</h2>';
    $out[] = '<pre>';
    $out[] = 'enabled      = ' . ($enabled ? 'true' : 'false') . "\n";
    $out[] = 'phone_id     = ' . ($phoneId ?: '(VIDE !)') . "\n";
    $out[] = 'access_token = ' . (empty($accessToken) ? '(VIDE !)' : substr($accessToken, 0, 12) . '...') . "\n";
    $out[] = 'api_version  = ' . $apiVersion . "\n";
    $out[] = 'template     = ' . ($template ?: '(VIDE)') . "\n";
    $out[] = '</pre>';

    if (empty($phoneId) || empty($accessToken)) {
        $out[] = '<p style="color:red;font-weight:bold;">ERREUR : phone_number_id ou access_token est VIDE. Le test s\'arrête ici.</p>';
        return implode("\n", $out);
    }

    // 2. TEST CONNEXION API META (GET /me)
    $out[] = '<h2>ETAPE 2 — TEST CONNEXION API META (/me)</h2>';
    $meUrl = "https://graph.facebook.com/{$apiVersion}/me?access_token=" . urlencode($accessToken);
    $out[] = '<p>URL appelée : ' . htmlspecialchars($meUrl) . '</p>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $meUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $meResponse = curl_exec($ch);
    $meHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $meCurlErr  = curl_error($ch);
    curl_close($ch);

    $out[] = '<pre>';
    $out[] = 'HTTP code = ' . $meHttpCode . "\n";
    $out[] = 'cURL error = ' . ($meCurlErr ?: 'aucun') . "\n";
    $out[] = 'Réponse brute = ' . $meResponse . "\n";
    $out[] = '</pre>';

    $meJson = json_decode($meResponse, true);
    if (isset($meJson['error'])) {
        $out[] = '<p style="color:red;font-weight:bold;">ERREUR API META : ' . htmlspecialchars(json_encode($meJson['error'])) . '</p>';
        return implode("\n", $out);
    }
    if (!isset($meJson['id'])) {
        $out[] = '<p style="color:red;font-weight:bold;">ERREUR : la réponse /me ne contient pas d\'id. Token probablement invalide.</p>';
        return implode("\n", $out);
    }
    $out[] = '<p style="color:green;font-weight:bold;">Connexion OK — App ID : ' . htmlspecialchars($meJson['id']) . '</p>';

    // 2b. VÉRIFICATION DES PERMISSIONS DU TOKEN
    $out[] = '<h2>ETAPE 2b — PERMISSIONS DU TOKEN</h2>';
    $permUrl = "https://graph.facebook.com/{$apiVersion}/me/permissions?access_token=" . urlencode($accessToken);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $permUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $permResponse = curl_exec($ch);
    $permHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $out[] = '<pre>';
    $out[] = 'HTTP code = ' . $permHttpCode . "\n";
    $out[] = 'Réponse brute = ' . $permResponse . "\n";
    $out[] = '</pre>';

    $permJson = json_decode($permResponse, true);
    $hasWaMessaging = false;
    $hasWaManagement = false;
    if (!empty($permJson['data'])) {
        $out[] = '<p><strong>Permissions trouvées :</strong></p><ul>';
        foreach ($permJson['data'] as $p) {
            $status = $p['status'] ?? 'unknown';
            $name = $p['permission'] ?? '?';
            $out[] = '<li>' . htmlspecialchars($name) . ' — ' . htmlspecialchars($status) . '</li>';
            if ($name === 'whatsapp_business_messaging') { $hasWaMessaging = ($status === 'granted'); }
            if ($name === 'whatsapp_business_management') { $hasWaManagement = ($status === 'granted'); }
        }
        $out[] = '</ul>';
    } else {
        $out[] = '<p style="color:red;">Aucune permission listée (token probablement User Token sans scope business).</p>';
    }

    if (!$hasWaMessaging) {
        $out[] = '<p style="color:red;font-weight:bold;font-size:16px;">❌ PERMISSION MANQUANTE : <code>whatsapp_business_messaging</code></p>';
        $out[] = '<p style="color:red;">Ce token ne peut PAS envoyer de messages WhatsApp. Il faut un token généré depuis l\'app WhatsApp Business (section API Setup).</p>';
    }
    if (!$hasWaManagement) {
        $out[] = '<p style="color:orange;font-weight:bold;">⚠️ PERMISSION MANQUANTE : <code>whatsapp_business_management</code></p>';
        $out[] = '<p style="color:orange;">Ce token ne peut PAS lire les templates ni les numéros WhatsApp.</p>';
    }
    if ($hasWaMessaging && $hasWaManagement) {
        $out[] = '<p style="color:green;font-weight:bold;">✓ Les 2 permissions WhatsApp sont présentes.</p>';
    }

    // 3. TEST RÉCUPÉRATION NUMÉROS WHATSAPP
    $out[] = '<h2>ETAPE 3 — LISTE DES NUMÉROS WHATSAPP ASSOCIÉS</h2>';
    $waUrl = "https://graph.facebook.com/{$apiVersion}/{$meJson['id']}/phone_numbers?access_token=" . urlencode($accessToken);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $waUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $waResponse = curl_exec($ch);
    $waHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $out[] = '<pre>';
    $out[] = 'HTTP code = ' . $waHttpCode . "\n";
    $out[] = 'Réponse brute = ' . $waResponse . "\n";
    $out[] = '</pre>';

    $waJson = json_decode($waResponse, true);
    if (!empty($waJson['data'])) {
        foreach ($waJson['data'] as $num) {
            $out[] = '<p>Numéro trouvé : <strong>' . htmlspecialchars($num['display_phone_number'] ?? '?') . '</strong> — ID : ' . htmlspecialchars($num['id'] ?? '?') . '</p>';
            if (($num['id'] ?? '') === $phoneId) {
                $out[] = '<p style="color:green;">  ✓ Ce numéro correspond à ton PHONE_NUMBER_ID config</p>';
            }
        }
    } else {
        $out[] = '<p style="color:orange;">Aucun numéro trouvé (vérifie que ton app est bien associée à un Business Manager avec numéro WhatsApp).</p>';
    }

    // 4. LISTE DES TEMPLATES APPROUVÉS
    $out[] = '<h2>ETAPE 4 — TEMPLATES APPROUVÉS SUR CE NUMÉRO</h2>';
    $tplUrl = "https://graph.facebook.com/{$apiVersion}/{$phoneId}/message_templates?access_token=" . urlencode($accessToken);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tplUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $tplResponse = curl_exec($ch);
    $tplHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $out[] = '<pre>';
    $out[] = 'HTTP code = ' . $tplHttpCode . "\n";
    $out[] = 'Réponse brute (tronquée) = ' . substr($tplResponse, 0, 2000) . "\n";
    $out[] = '</pre>';

    $tplJson = json_decode($tplResponse, true);
    $foundTemplate = false;
    if (!empty($tplJson['data'])) {
        foreach ($tplJson['data'] as $t) {
            $status = $t['status'] ?? 'unknown';
            $name = $t['name'] ?? '?';
            $out[] = '<p>Template : <strong>' . htmlspecialchars($name) . '</strong> — Statut : ' . htmlspecialchars($status) . '</p>';
            if ($name === $template && $status === 'APPROVED') {
                $foundTemplate = true;
            }
        }
    }
    if (!$foundTemplate && !empty($template)) {
        $out[] = '<p style="color:orange;font-weight:bold;">⚠️ Template "' . htmlspecialchars($template) . '" non trouvé ou non approuvé. L\'envoi échouera.</p>';
    } elseif ($foundTemplate) {
        $out[] = '<p style="color:green;font-weight:bold;">✓ Template "' . htmlspecialchars($template) . '" trouvé et approuvé.</p>';
    }

    // 5. TENTATIVE D'ENVOI RÉEL
    $out[] = '<h2>ETAPE 5 — TENTATIVE D\'ENVOI RÉEL</h2>';
    $testPhone = '+22500000000'; // numéro bidon pour le test
    $out[] = '<p>Numéro destinataire test : ' . htmlspecialchars($testPhone) . ' (bidon, l\'API rejettera probablement mais on verra le code d\'erreur exact)</p>';

    $sendUrl = "https://graph.facebook.com/{$apiVersion}/{$phoneId}/messages";
    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $testPhone,
        'type' => 'template',
        'template' => [
            'name' => $template ?: 'test_non_existant',
            'language' => ['code' => 'fr'],
            'components' => [[
                'type' => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], ['Test', 'CMD-TEST', '0 FCFA', 'https://test.com']),
            ]],
        ],
    ];

    $out[] = '<p>URL d\'envoi : ' . htmlspecialchars($sendUrl) . '</p>';
    $out[] = '<pre>Payload JSON = ' . json_encode($payload, JSON_PRETTY_PRINT) . '</pre>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $sendResponse = curl_exec($ch);
    $sendHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $sendCurlErr = curl_error($ch);
    curl_close($ch);

    $out[] = '<pre>';
    $out[] = 'HTTP code = ' . $sendHttpCode . "\n";
    $out[] = 'cURL error = ' . ($sendCurlErr ?: 'aucun') . "\n";
    $out[] = 'Réponse brute = ' . $sendResponse . "\n";
    $out[] = '</pre>';

    $sendJson = json_decode($sendResponse, true);
    if (isset($sendJson['messages'][0]['id'])) {
        $out[] = '<p style="color:green;font-size:18px;font-weight:bold;">✅ MESSAGE ENVOYÉ AVEC SUCCÈS ! Message ID : ' . htmlspecialchars($sendJson['messages'][0]['id']) . '</p>';
    } elseif (isset($sendJson['error'])) {
        $out[] = '<p style="color:red;font-size:18px;font-weight:bold;">❌ ÉCHEC D\'ENVOI</p>';
        $out[] = '<pre>Erreur détaillée : ' . htmlspecialchars(json_encode($sendJson['error'], JSON_PRETTY_PRINT)) . '</pre>';
    } else {
        $out[] = '<p style="color:orange;font-weight:bold;">⚠️ Réponse inattendue (ni succès ni erreur structurée)</p>';
    }

    $out[] = '<hr><p><em>Fin du test — ' . now() . '</em></p>';
    return implode("\n", $out);
});
*/

// Webhook WhatsApp — DÉSACTIVÉ, remplacé par Telegram
// Route::get('/webhook/whatsapp', [WebhookController::class, 'verifyWhatsApp']);
// Route::post('/webhook/whatsapp', [WebhookController::class, 'receiveWhatsApp']);

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Claim first admin (doit être connecté mais pas forcément admin)
Route::middleware(['auth'])->post('/admin/claim-first-admin', [AuthController::class, 'claimFirstAdmin'])->name('admin.claim');

// Admin — protégé par permissions granulaires
Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {

    Route::middleware('can:view_dashboard')->get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Profile
    Route::get('/profil', [AdminController::class, 'profile'])->name('admin.profile');
    Route::patch('/profil', [AdminController::class, 'updateProfile']);
    Route::post('/profil/password', [AdminController::class, 'updatePassword']);

    // Products
    Route::middleware('can:view_products')->get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::middleware('can:create_products')->post('/products', [AdminController::class, 'storeProduct']);
    Route::middleware('can:edit_products')->patch('/products/{id}', [AdminController::class, 'updateProduct']);
    Route::middleware('can:delete_products')->delete('/products/{id}', [AdminController::class, 'destroyProduct']);
    Route::middleware('can:edit_products')->post('/products/{id}/toggle', [AdminController::class, 'toggleProduct'])->name('admin.products.toggle');
    Route::middleware('can:edit_products')->post('/products/{id}/stock', [AdminController::class, 'adjustStock'])->name('admin.products.stock');
    Route::middleware('can:edit_products')->post('/products/{id}/promo', [AdminController::class, 'setPromo'])->name('admin.products.promo');

    // Categories
    Route::middleware('can:view_categories')->get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::middleware('can:create_categories')->post('/categories', [AdminController::class, 'storeCategory']);
    Route::middleware('can:edit_categories')->patch('/categories/{id}', [AdminController::class, 'updateCategory']);
    Route::middleware('can:delete_categories')->delete('/categories/{id}', [AdminController::class, 'destroyCategory']);

    // Communes
    Route::middleware('can:view_communes')->get('/communes', [AdminController::class, 'communes'])->name('admin.communes');
    Route::middleware('can:create_communes')->post('/communes', [AdminController::class, 'storeCommune']);
    Route::middleware('can:edit_communes')->patch('/communes/{id}', [AdminController::class, 'updateCommune']);
    Route::middleware('can:edit_communes')->post('/communes/{id}/toggle', [AdminController::class, 'toggleCommune'])->name('admin.communes.toggle');
    Route::middleware('can:delete_communes')->delete('/communes/{id}', [AdminController::class, 'destroyCommune']);

    // Orders
    Route::middleware('can:view_orders')->get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::middleware('can:view_orders')->get('/orders/pending-count', [AdminController::class, 'pendingOrdersCount']);
    Route::middleware('can:update_orders')->patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    Route::middleware('can:update_orders')->post('/orders/bulk-status', [AdminController::class, 'bulkUpdateStatus']);
    Route::middleware('can:delete_orders')->delete('/orders/{id}', [AdminController::class, 'destroyOrder']);

    // Order Managers (Telegram notifications)
    Route::middleware('can:order_telegram_notification')->get('/order-managers', [AdminController::class, 'orderManagers'])->name('admin.order-managers');
    Route::middleware('can:order_telegram_notification')->post('/order-managers', [AdminController::class, 'storeOrderManager']);
    Route::middleware('can:order_telegram_notification')->patch('/order-managers/{id}', [AdminController::class, 'updateOrderManager']);
    Route::middleware('can:order_telegram_notification')->post('/order-managers/{id}/toggle', [AdminController::class, 'toggleOrderManager'])->name('admin.order-managers.toggle');
    Route::middleware('can:order_telegram_notification')->delete('/order-managers/{id}', [AdminController::class, 'destroyOrderManager']);

    // Testimonials
    Route::middleware('can:view_testimonials')->get('/testimonials', [AdminController::class, 'testimonials'])->name('admin.testimonials');
    Route::middleware('can:create_testimonials')->post('/testimonials', [AdminController::class, 'storeTestimonial']);
    Route::middleware('can:edit_testimonials')->patch('/testimonials/{id}', [AdminController::class, 'updateTestimonial']);
    Route::middleware('can:delete_testimonials')->delete('/testimonials/{id}', [AdminController::class, 'destroyTestimonial']);

    // Banners
    Route::middleware('can:view_banners')->get('/banners', [AdminController::class, 'banners'])->name('admin.banners');
    Route::middleware('can:edit_banners')->post('/banners', [AdminController::class, 'updateBanner']);

    // Country Codes
    Route::middleware('can:manage_country_codes')->get('/country-codes', [AdminController::class, 'countryCodes'])->name('admin.country-codes');
    Route::middleware('can:manage_country_codes')->post('/country-codes', [AdminController::class, 'storeCountryCode']);
    Route::middleware('can:manage_country_codes')->patch('/country-codes/{id}', [AdminController::class, 'updateCountryCode']);
    Route::middleware('can:manage_country_codes')->post('/country-codes/{id}/toggle', [AdminController::class, 'toggleCountryCode']);
    Route::middleware('can:manage_country_codes')->delete('/country-codes/{id}', [AdminController::class, 'destroyCountryCode']);

    // Activity Logs
    Route::middleware('super_admin')->get('/logs', [AdminController::class, 'logs'])->name('admin.logs');

    // Users & Roles — Super Admin only
    Route::middleware('super_admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        Route::post('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset');
        Route::get('/users/{id}/details', [AdminController::class, 'userDetails'])->name('admin.users.details');
        Route::get('/users/{id}/logs', [AdminController::class, 'userLogs'])->name('admin.users.logs');

        Route::get('/roles', [AdminController::class, 'roles'])->name('admin.roles');
        Route::post('/roles', [AdminController::class, 'storeRole']);
        Route::get('/roles/{id}/edit', [AdminController::class, 'editRole'])->name('admin.roles.edit');
        Route::patch('/roles/{id}', [AdminController::class, 'updateRole']);
        Route::delete('/roles/{id}', [AdminController::class, 'destroyRole']);
    });
});
