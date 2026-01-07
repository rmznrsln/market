<?php
/**
 * Market Satış Sistemi - Veritabanı Bağlantı Sınıfı
 */

require_once __DIR__ . '/../config/config.php';

class Database {
    private static ?PDO $instance = null;

    /**
     * Singleton PDO instance döndür
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]);
            } catch (PDOException $e) {
                error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
                throw new Exception('Veritabanı bağlantısı kurulamadı');
            }
        }

        return self::$instance;
    }

    /**
     * Bağlantıyı kapat
     */
    public static function close(): void {
        self::$instance = null;
    }

    /**
     * Transaction başlat
     */
    public static function beginTransaction(): bool {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Transaction onayla
     */
    public static function commit(): bool {
        return self::getInstance()->commit();
    }

    /**
     * Transaction geri al
     */
    public static function rollback(): bool {
        return self::getInstance()->rollBack();
    }
}

/**
 * Kısa erişim fonksiyonu
 */
function getDB(): PDO {
    return Database::getInstance();
}
