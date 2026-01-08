<?php
/**
 * Market Satış Sistemi - Yapılandırma Dosyası
 */

// Hata raporlama (production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Veritabanı ayarları
// HOSTING ICIN ASAGIDAKI BILGILERI DEGISTIRIN:
define('DB_HOST', 'localhost');           // Genellikle localhost kalir
define('DB_NAME', 'markacra_market_db'); // cPanel: kullaniciadi_veritabaniadi
define('DB_USER', 'markacra_ramazan');    // cPanel: kullaniciadi_dbuser
define('DB_PASS', 'Gti.Abc!1');            // Veritabani sifresi
define('DB_CHARSET', 'utf8mb4');

// Güvenlik ayarları
define('SESSION_LIFETIME', 8 * 60 * 60); // 8 saat
define('SESSION_TOKEN_LENGTH', 64);
define('MAX_LOGIN_ATTEMPTS', 999);
define('LOGIN_LOCKOUT_TIME', 15 * 60); // 15 dakika

// CORS ayarları - KENDI DOMAININIZI EKLEYIN
define('ALLOWED_ORIGINS', [
    'http://localhost',
    'http://127.0.0.1',
    'https://megedigital.xyz',      // Kendi domaininiz
    'https://www.megedigital.xyz'   // www ile
]);

// Uygulama ayarları
define('APP_NAME', 'Market Satış Sistemi');
define('APP_VERSION', '2.0.0');
define('ITEMS_PER_PAGE', 20);

// Kullanıcı rolleri
define('ROLE_ADMIN', 'admin');
define('ROLE_CASHIER', 'cashier');

/**
 * CORS başlıklarını ayarla
 */
function setCorsHeaders() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        // Development için tüm originlere izin ver (production'da kaldırın)
        header("Access-Control-Allow-Origin: *");
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');

    // Preflight request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * JSON response header'larını ayarla
 */
function setJsonHeaders() {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}
