<?php
/**
 * Market Satış Sistemi - Kimlik Doğrulama Modülü
 */

require_once __DIR__ . '/database.php';

class Auth {
    private static ?array $currentUser = null;

    /**
     * Kullanıcı girişi yap
     */
    public static function login(string $username, string $password): array {
        $pdo = getDB();

        // Kullanıcıyı bul
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('Kullanıcı adı veya şifre hatalı');
        }

        // Şifre kontrolü
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception('Kullanıcı adı veya şifre hatalı');
        }

        // Eski oturumları temizle
        self::cleanExpiredSessions();

        // Yeni oturum oluştur
        $token = bin2hex(random_bytes(SESSION_TOKEN_LENGTH / 2));
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

        $stmt = $pdo->prepare("
            INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            $token,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            $expiresAt
        ]);

        // Son giriş zamanını güncelle
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Hassas bilgileri kaldır
        unset($user['password_hash']);

        return [
            'user' => $user,
            'token' => $token,
            'expires_at' => $expiresAt
        ];
    }

    /**
     * Kullanıcı çıkışı yap
     */
    public static function logout(string $token): bool {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->execute([$token]);
        self::$currentUser = null;
        return $stmt->rowCount() > 0;
    }

    /**
     * Token ile oturumu doğrula
     */
    public static function validateToken(string $token): ?array {
        $pdo = getDB();

        $stmt = $pdo->prepare("
            SELECT u.* FROM users u
            INNER JOIN sessions s ON u.id = s.user_id
            WHERE s.token = ? AND s.expires_at > NOW() AND u.is_active = 1
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            unset($user['password_hash']);
            self::$currentUser = $user;
        }

        return $user ?: null;
    }

    /**
     * Authorization header'dan token al
     */
    public static function getTokenFromHeader(): ?string {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Kimlik doğrulama gerektir (middleware)
     */
    public static function requireAuth(): array {
        $token = self::getTokenFromHeader();

        if (!$token) {
            http_response_code(401);
            throw new Exception('Oturum açmanız gerekiyor');
        }

        $user = self::validateToken($token);

        if (!$user) {
            http_response_code(401);
            throw new Exception('Oturum süresi dolmuş veya geçersiz');
        }

        return $user;
    }

    /**
     * Admin yetkisi gerektir
     */
    public static function requireAdmin(): array {
        $user = self::requireAuth();

        if ($user['role'] !== ROLE_ADMIN) {
            http_response_code(403);
            throw new Exception('Bu işlem için yetkiniz yok');
        }

        return $user;
    }

    /**
     * Mevcut kullanıcıyı al
     */
    public static function getCurrentUser(): ?array {
        return self::$currentUser;
    }

    /**
     * Şifre değiştir
     */
    public static function changePassword(int $userId, string $currentPassword, string $newPassword): bool {
        $pdo = getDB();

        // Mevcut şifreyi kontrol et
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            throw new Exception('Mevcut şifre hatalı');
        }

        // Yeni şifre validasyonu
        if (strlen($newPassword) < 6) {
            throw new Exception('Şifre en az 6 karakter olmalıdır');
        }

        // Şifreyi güncelle
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);

        // Tüm oturumları sonlandır (güvenlik için)
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
        $stmt->execute([$userId]);

        return true;
    }

    /**
     * Süresi dolmuş oturumları temizle
     */
    public static function cleanExpiredSessions(): int {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Yeni kullanıcı oluştur (sadece admin)
     */
    public static function createUser(string $username, string $password, string $fullName, string $role = ROLE_CASHIER): int {
        $pdo = getDB();

        // Kullanıcı adı kontrolü
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([trim($username)]);
        if ($stmt->fetch()) {
            throw new Exception('Bu kullanıcı adı zaten kullanılıyor');
        }

        // Validasyonlar
        if (strlen($username) < 3) {
            throw new Exception('Kullanıcı adı en az 3 karakter olmalıdır');
        }
        if (strlen($password) < 6) {
            throw new Exception('Şifre en az 6 karakter olmalıdır');
        }
        if (!in_array($role, [ROLE_ADMIN, ROLE_CASHIER])) {
            throw new Exception('Geçersiz rol');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, full_name, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([trim($username), $passwordHash, trim($fullName), $role]);

        return (int) $pdo->lastInsertId();
    }
}
