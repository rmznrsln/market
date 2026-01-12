<?php
/**
 * Yardımcı Fonksiyonlar
 */

session_start();

// Dil yönetimi
function setLanguage($lang) {
    $allowed = ['tr', 'en', 'ru', 'ar', 'de', 'fr', 'it'];
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

// Cinsiyet çevirisi (Türkçe - Admin Panel için)
function getGenderText($gender) {
    $map = [
        'male' => 'Erkek',
        'female' => 'Kadın',
        'other' => 'Diğer',
        'not_specified' => 'Belirtilmemiş'
    ];
    return $map[$gender] ?? $gender;
}

// Sigara durumu çevirisi (Türkçe - Admin Panel için)
function getSmokingText($smoking) {
    $map = [
        'yes' => 'Evet, kullanıyor',
        'no' => 'Hayır, kullanmıyor',
        'quit' => 'Bırakmış'
    ];
    return $map[$smoking] ?? $smoking;
}

// Nasıl ulaştı çevirisi (Türkçe - Admin Panel için)
function getHowFoundText($how) {
    $map = [
        'social_media' => 'Sosyal Medya',
        'website' => 'Web Sitesi',
        'reference' => 'Referans',
        'recommendation' => 'Tavsiye',
        'other' => 'Diğer'
    ];
    return $map[$how] ?? $how;
}

// Uyruk kodu çevirisi (Türkçe - Admin Panel için)
function getNationalityText($code) {
    $map = [
        'TR' => 'Türkiye',
        'DE' => 'Almanya',
        'US' => 'Amerika Birleşik Devletleri',
        'GB' => 'Birleşik Krallık',
        'FR' => 'Fransa',
        'IT' => 'İtalya',
        'ES' => 'İspanya',
        'NL' => 'Hollanda',
        'BE' => 'Belçika',
        'AT' => 'Avusturya',
        'CH' => 'İsviçre',
        'RU' => 'Rusya',
        'UA' => 'Ukrayna',
        'PL' => 'Polonya',
        'RO' => 'Romanya',
        'BG' => 'Bulgaristan',
        'GR' => 'Yunanistan',
        'CZ' => 'Çekya',
        'SE' => 'İsveç',
        'NO' => 'Norveç',
        'DK' => 'Danimarka',
        'FI' => 'Finlandiya',
        'PT' => 'Portekiz',
        'IE' => 'İrlanda',
        'HU' => 'Macaristan',
        'SK' => 'Slovakya',
        'HR' => 'Hırvatistan',
        'RS' => 'Sırbistan',
        'SI' => 'Slovenya',
        'BA' => 'Bosna Hersek',
        'AL' => 'Arnavutluk',
        'MK' => 'Kuzey Makedonya',
        'ME' => 'Karadağ',
        'XK' => 'Kosova',
        'MD' => 'Moldova',
        'BY' => 'Belarus',
        'LT' => 'Litvanya',
        'LV' => 'Letonya',
        'EE' => 'Estonya',
        'GE' => 'Gürcistan',
        'AM' => 'Ermenistan',
        'AZ' => 'Azerbaycan',
        'KZ' => 'Kazakistan',
        'UZ' => 'Özbekistan',
        'TM' => 'Türkmenistan',
        'KG' => 'Kırgızistan',
        'TJ' => 'Tacikistan',
        'SA' => 'Suudi Arabistan',
        'AE' => 'Birleşik Arap Emirlikleri',
        'QA' => 'Katar',
        'KW' => 'Kuveyt',
        'BH' => 'Bahreyn',
        'OM' => 'Umman',
        'JO' => 'Ürdün',
        'LB' => 'Lübnan',
        'SY' => 'Suriye',
        'IQ' => 'Irak',
        'IR' => 'İran',
        'IL' => 'İsrail',
        'PS' => 'Filistin',
        'YE' => 'Yemen',
        'EG' => 'Mısır',
        'LY' => 'Libya',
        'TN' => 'Tunus',
        'DZ' => 'Cezayir',
        'MA' => 'Fas',
        'SD' => 'Sudan',
        'ET' => 'Etiyopya',
        'KE' => 'Kenya',
        'NG' => 'Nijerya',
        'GH' => 'Gana',
        'ZA' => 'Güney Afrika',
        'CN' => 'Çin',
        'JP' => 'Japonya',
        'KR' => 'Güney Kore',
        'IN' => 'Hindistan',
        'PK' => 'Pakistan',
        'BD' => 'Bangladeş',
        'ID' => 'Endonezya',
        'MY' => 'Malezya',
        'SG' => 'Singapur',
        'TH' => 'Tayland',
        'VN' => 'Vietnam',
        'PH' => 'Filipinler',
        'AU' => 'Avustralya',
        'NZ' => 'Yeni Zelanda',
        'CA' => 'Kanada',
        'MX' => 'Meksika',
        'BR' => 'Brezilya',
        'AR' => 'Arjantin',
        'CL' => 'Şili',
        'CO' => 'Kolombiya',
        'PE' => 'Peru',
        'VE' => 'Venezuela',
        'CU' => 'Küba',
        'AF' => 'Afganistan',
        'LU' => 'Lüksemburg',
        'MT' => 'Malta',
        'CY' => 'Kıbrıs',
        'IS' => 'İzlanda',
        'MC' => 'Monako',
        'LI' => 'Lihtenştayn',
        'AD' => 'Andorra',
        'SM' => 'San Marino',
        'VA' => 'Vatikan'
    ];
    return $map[$code] ?? $code;
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
