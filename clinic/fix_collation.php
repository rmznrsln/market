<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Veritabanı collation'ını değiştir
    $pdo->exec("ALTER DATABASE `markacra_clinic_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Veritabani collation guncellendi<br>";

    // Sadece tabloları al (view'ları hariç tut)
    $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        try {
            $pdo->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Tablo guncellendi: $table<br>";
        } catch (Exception $e) {
            echo "Tablo hatasi ($table): " . $e->getMessage() . "<br>";
        }
    }

    echo "<br><strong>Tamamlandi! Artik reports.php calisacak.</strong>";
    echo "<br><br><a href='admin/reports.php'>Reports sayfasina git</a>";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
