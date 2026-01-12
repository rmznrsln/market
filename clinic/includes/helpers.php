<?php
/**
 * Yardımcı Fonksiyonlar
 */

session_start();

// Dil yönetimi
function setLanguage($lang) {
    $allowed = ['tr', 'en', 'ru', 'ar'];
    if (in_array($lang, $allowed)) {
        $_SESSION['lang'] = $lang;
    }
}

function getLanguage() {
    return $_SESSION['lang'] ?? 'tr';
}

function loadLanguage($lang = null) {
    $lang = $lang ?? getLanguage();
    $file = __DIR__ . '/../languages/' . $lang . '.php';
    if (file_exists($file)) {
        return require $file;
    }
    return require __DIR__ . '/../languages/tr.php';
}

function __($key, $lang = null) {
    static $translations = null;
    if ($translations === null) {
        $translations = loadLanguage($lang);
    }
    return $translations[$key] ?? $key;
}

function isRTL() {
    return getLanguage() === 'ar';
}

function getDirection() {
    return isRTL() ? 'rtl' : 'ltr';
}

// Güvenlik fonksiyonları
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// Tarih formatlama
function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function formatDateTime($date, $format = 'd.m.Y H:i') {
    return date($format, strtotime($date));
}

// Para formatı
function formatMoney($amount, $currency = 'TRY') {
    $symbols = [
        'TRY' => '₺',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'RUB' => '₽',
        'SAR' => 'ر.س'
    ];
    $symbol = $symbols[$currency] ?? $currency;
    return number_format($amount, 2, ',', '.') . ' ' . $symbol;
}

// Flash mesajları
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function hasFlash() {
    return isset($_SESSION['flash']);
}

// Yetkilendirme
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Yönlendirme
function redirect($url) {
    header("Location: $url");
    exit;
}

// JSON yanıt
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Cinsiyet çevirisi
function getGenderText($gender) {
    $map = [
        'male' => __('gender_male'),
        'female' => __('gender_female'),
        'other' => __('gender_other'),
        'not_specified' => __('gender_not_specified')
    ];
    return $map[$gender] ?? $gender;
}

// Sigara durumu çevirisi
function getSmokingText($smoking) {
    $map = [
        'yes' => __('smoking_yes'),
        'no' => __('smoking_no'),
        'quit' => __('smoking_quit')
    ];
    return $map[$smoking] ?? $smoking;
}

// Nasıl ulaştı çevirisi
function getHowFoundText($how) {
    $map = [
        'social_media' => __('how_social_media'),
        'website' => __('how_website'),
        'reference' => __('how_reference'),
        'recommendation' => __('how_recommendation'),
        'other' => __('how_other')
    ];
    return $map[$how] ?? $how;
}

// Durum badge rengi
function getStatusBadge($status) {
    $badges = [
        'waiting' => '<span class="badge bg-warning text-dark">Bekliyor</span>',
        'in_treatment' => '<span class="badge bg-primary">Tedavide</span>',
        'completed' => '<span class="badge bg-success">Tamamlandı</span>',
        'cancelled' => '<span class="badge bg-danger">İptal</span>',
        'pending' => '<span class="badge bg-secondary">Beklemede</span>',
        'in_progress' => '<span class="badge bg-info">Devam Ediyor</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}
