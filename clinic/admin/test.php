<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. PHP calisiyor<br>";

require_once __DIR__ . '/../config/database.php';
echo "2. Database config yuklendi<br>";

try {
    $db = Database::getInstance();
    echo "3. Veritabani baglantisi basarili<br>";

    // Tabloları kontrol et
    $tables = $db->fetchAll("SHOW TABLES");
    echo "4. Tablolar:<br>";
    echo "<pre>";
    print_r($tables);
    echo "</pre>";

    // Users tablosunu kontrol et
    $users = $db->fetchAll("SELECT * FROM users");
    echo "5. Users:<br>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";

} catch (Exception $e) {
    echo "HATA: " . $e->getMessage();
}
