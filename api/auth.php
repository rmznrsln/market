<?php
/**
 * Market Satış Sistemi - Kimlik Doğrulama API
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

setCorsHeaders();
setJsonHeaders();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'login':
            requireMethod('POST');
            handleLogin();
            break;

        case 'logout':
            requireMethod('POST');
            handleLogout();
            break;

        case 'check':
            requireMethod('GET');
            handleCheck();
            break;

        case 'change-password':
            requireMethod('POST');
            handleChangePassword();
            break;

        case 'users':
            handleUsers();
            break;

        default:
            errorResponse('Geçersiz işlem', 400);
    }
} catch (Exception $e) {
    $status = http_response_code() ?: 500;
    if ($status === 200) $status = 500;
    errorResponse($e->getMessage(), $status);
}

/**
 * Giriş işlemi
 */
function handleLogin(): void {
    $input = getJsonInput();
    validateRequired($input, ['username', 'password']);

    // Rate limiting
    $ip = getClientIP();
    if (!checkRateLimit("login_$ip", MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
        errorResponse('Çok fazla başarısız giriş denemesi. Lütfen bekleyin.', 429);
    }

    $result = Auth::login($input['username'], $input['password']);

    successResponse([
        'user' => $result['user'],
        'token' => $result['token'],
        'expires_at' => $result['expires_at']
    ], 'Giriş başarılı');
}

/**
 * Çıkış işlemi
 */
function handleLogout(): void {
    $token = Auth::getTokenFromHeader();

    if ($token) {
        Auth::logout($token);
    }

    successResponse(null, 'Çıkış yapıldı');
}

/**
 * Oturum kontrolü
 */
function handleCheck(): void {
    $token = Auth::getTokenFromHeader();

    if (!$token) {
        errorResponse('Token bulunamadı', 401);
    }

    $user = Auth::validateToken($token);

    if (!$user) {
        errorResponse('Oturum süresi dolmuş', 401);
    }

    successResponse(['user' => $user], 'Oturum geçerli');
}

/**
 * Şifre değiştirme
 */
function handleChangePassword(): void {
    $user = Auth::requireAuth();
    $input = getJsonInput();

    validateRequired($input, ['current_password', 'new_password']);

    Auth::changePassword($user['id'], $input['current_password'], $input['new_password']);

    successResponse(null, 'Şifre başarıyla değiştirildi');
}

/**
 * Kullanıcı yönetimi (sadece admin)
 */
function handleUsers(): void {
    $method = $_SERVER['REQUEST_METHOD'];
    $user = Auth::requireAdmin();
    $pdo = getDB();

    switch ($method) {
        case 'GET':
            // Kullanıcı listesi
            $stmt = $pdo->query("SELECT id, username, full_name, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC");
            successResponse($stmt->fetchAll());
            break;

        case 'POST':
            // Yeni kullanıcı oluştur
            $input = getJsonInput();
            validateRequired($input, ['username', 'password', 'full_name']);

            $role = $input['role'] ?? ROLE_CASHIER;
            $userId = Auth::createUser($input['username'], $input['password'], $input['full_name'], $role);

            successResponse(['id' => $userId], 'Kullanıcı oluşturuldu', 201);
            break;

        case 'PUT':
            // Kullanıcı güncelle
            $input = getJsonInput();
            validateRequired($input, ['id']);

            $updates = [];
            $params = [];

            if (isset($input['full_name'])) {
                $updates[] = 'full_name = ?';
                $params[] = sanitizeString($input['full_name'], 100);
            }
            if (isset($input['role']) && in_array($input['role'], [ROLE_ADMIN, ROLE_CASHIER])) {
                $updates[] = 'role = ?';
                $params[] = $input['role'];
            }
            if (isset($input['is_active'])) {
                $updates[] = 'is_active = ?';
                $params[] = $input['is_active'] ? 1 : 0;
            }

            if (empty($updates)) {
                errorResponse('Güncellenecek alan yok', 400);
            }

            $params[] = getPositiveInt($input['id']);
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            successResponse(null, 'Kullanıcı güncellendi');
            break;

        case 'DELETE':
            // Kullanıcı sil
            $input = getJsonInput();
            validateRequired($input, ['id']);

            $userId = getPositiveInt($input['id']);

            // Kendini silemesin
            if ($userId === $user['id']) {
                errorResponse('Kendinizi silemezsiniz', 400);
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            if ($stmt->rowCount() === 0) {
                errorResponse('Kullanıcı bulunamadı', 404);
            }

            successResponse(null, 'Kullanıcı silindi');
            break;

        default:
            errorResponse('Desteklenmeyen metod', 405);
    }
}
