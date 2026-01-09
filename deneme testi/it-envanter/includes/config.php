<?php
/**
 * IT Envanter Yönetim Sistemi - Konfigürasyon Dosyası
 */

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'it_envanter');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Uygulama ayarları
define('APP_NAME', 'IT Envanter Yönetim Sistemi');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/it-envanter/');

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
