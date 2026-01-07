<?php
/**
 * Market Satış Sistemi - Yardımcı Fonksiyonlar
 */

require_once __DIR__ . '/../config/config.php';

/**
 * JSON yanıt gönder
 */
function jsonResponse($data, int $status = 200): void {
    http_response_code($status);
    setJsonHeaders();
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Başarılı yanıt
 */
function successResponse($data = null, string $message = 'İşlem başarılı', int $status = 200): void {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response, $status);
}

/**
 * Hata yanıtı
 */
function errorResponse(string $message, int $status = 400, ?array $errors = null): void {
    $response = ['success' => false, 'error' => $message];
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    jsonResponse($response, $status);
}

/**
 * JSON input al
 */
function getJsonInput(): array {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        errorResponse('Geçersiz JSON formatı', 400);
    }

    return $data ?? [];
}

/**
 * Zorunlu alanları kontrol et
 */
function validateRequired(array $data, array $fields): void {
    $missing = [];

    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        errorResponse('Eksik alanlar: ' . implode(', ', $missing), 400, ['missing_fields' => $missing]);
    }
}

/**
 * String temizle
 */
function sanitizeString(string $input, int $maxLength = 255): string {
    $clean = trim($input);
    $clean = strip_tags($clean);
    $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
    return mb_substr($clean, 0, $maxLength);
}

/**
 * Pozitif integer al
 */
function getPositiveInt($value, int $default = 0): int {
    $int = filter_var($value, FILTER_VALIDATE_INT);
    return ($int !== false && $int > 0) ? $int : $default;
}

/**
 * Pozitif float al
 */
function getPositiveFloat($value, float $default = 0.0): float {
    $float = filter_var($value, FILTER_VALIDATE_FLOAT);
    return ($float !== false && $float >= 0) ? $float : $default;
}

/**
 * Pagination parametrelerini al
 */
function getPaginationParams(): array {
    $page = getPositiveInt($_GET['page'] ?? 1, 1);
    $limit = getPositiveInt($_GET['limit'] ?? ITEMS_PER_PAGE, ITEMS_PER_PAGE);

    // Limit sınırlaması
    $limit = min($limit, 100);

    $offset = ($page - 1) * $limit;

    return [
        'page' => $page,
        'limit' => $limit,
        'offset' => $offset
    ];
}

/**
 * Pagination meta bilgisi oluştur
 */
function createPaginationMeta(int $totalItems, int $page, int $limit): array {
    $totalPages = ceil($totalItems / $limit);

    return [
        'current_page' => $page,
        'per_page' => $limit,
        'total_items' => $totalItems,
        'total_pages' => (int) $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Hata logla
 */
function logError(string $message, array $context = []): void {
    $logMessage = date('Y-m-d H:i:s') . " - $message";

    if (!empty($context)) {
        $logMessage .= " - " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    error_log($logMessage);
}

/**
 * HTTP method kontrolü
 */
function requireMethod(string ...$methods): void {
    $currentMethod = $_SERVER['REQUEST_METHOD'];

    if (!in_array($currentMethod, $methods)) {
        errorResponse('Desteklenmeyen HTTP metodu', 405);
    }
}

/**
 * Rate limiting (basit implementasyon)
 */
function checkRateLimit(string $identifier, int $maxRequests = 60, int $window = 60): bool {
    // Bu basit bir implementasyon, production'da Redis veya Memcached kullanın
    $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($identifier);

    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?? [];
    }

    $now = time();
    $windowStart = $now - $window;

    // Eski kayıtları temizle
    $data = array_filter($data, fn($timestamp) => $timestamp > $windowStart);

    if (count($data) >= $maxRequests) {
        return false;
    }

    $data[] = $now;
    file_put_contents($cacheFile, json_encode($data));

    return true;
}

/**
 * IP adresini al
 */
function getClientIP(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // X-Forwarded-For birden fazla IP içerebilir
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}
