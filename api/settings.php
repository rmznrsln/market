<?php
/**
 * Market Satis Sistemi - Ayarlar API
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

setCorsHeaders();
setJsonHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $pdo = getDB();

    // Public endpoint - ayarlari oku (auth gerektirmez)
    if ($action === 'public' && $method === 'GET') {
        handlePublicGet($pdo);
        exit;
    }

    // Auth gerektiren endpoints
    $currentUser = Auth::requireAuth();

    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;

        case 'POST':
        case 'PUT':
            handleSave($pdo, $currentUser);
            break;

        default:
            errorResponse('Desteklenmeyen metod', 405);
    }
} catch (Exception $e) {
    $status = http_response_code() ?: 500;
    if ($status === 200) $status = 500;
    errorResponse($e->getMessage(), $status);
}

/**
 * Public ayarlari getir (auth gerektirmez)
 */
function handlePublicGet(PDO $pdo): void {
    $key = $_GET['key'] ?? '';

    if ($key) {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        successResponse(['value' => $value !== false ? $value : null]);
    } else {
        // Sadece public ayarlari dondur
        $publicKeys = ['delivery_fee'];
        $placeholders = implode(',', array_fill(0, count($publicKeys), '?'));

        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)");
        $stmt->execute($publicKeys);
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        successResponse($settings);
    }
}

/**
 * Ayarlari getir
 */
function handleGet(PDO $pdo): void {
    $key = $_GET['key'] ?? '';

    if ($key) {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        successResponse(['value' => $value !== false ? $value : null]);
    } else {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        successResponse($settings);
    }
}

/**
 * Ayar kaydet
 */
function handleSave(PDO $pdo, array $currentUser): void {
    $input = getJsonInput();
    validateRequired($input, ['key', 'value']);

    $key = sanitizeString($input['key'], 50);
    $value = sanitizeString((string)$input['value'], 255);

    // Upsert (varsa guncelle, yoksa ekle)
    $stmt = $pdo->prepare("
        INSERT INTO settings (setting_key, setting_value, updated_by)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            updated_by = VALUES(updated_by),
            updated_at = NOW()
    ");
    $stmt->execute([$key, $value, $currentUser['id']]);

    successResponse(['key' => $key, 'value' => $value], 'Ayar kaydedildi');
}
