<?php
/**
 * IT Envanter Yönetim Sistemi - Kurulum Scripti
 * Bu dosya veritabanını oluşturur ve test verilerini yükler.
 */

echo "<h1>IT Envanter Yonetim Sistemi - Kurulum</h1>";
echo "<pre>";

// Veritabanı bağlantı bilgileri
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // MySQL'e bağlan
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "MySQL'e baglanildi.\n";

    // database.sql dosyasını oku ve çalıştır
    echo "Veritabani olusturuluyor...\n";
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $pdo->exec($sql);
    echo "Veritabani ve tablolar olusturuldu.\n";

    // seed.sql dosyasını oku ve çalıştır
    echo "Test verileri yukleniyor...\n";
    $seed = file_get_contents(__DIR__ . '/seed.sql');
    $pdo->exec($seed);
    echo "Test verileri yuklendi.\n";

    echo "\n========================================\n";
    echo "KURULUM TAMAMLANDI!\n";
    echo "========================================\n\n";
    echo "Simdi uygulamaya erisebilirsiniz:\n";
    echo "http://localhost/it-envanter/\n\n";
    echo "Varsayilan giris bilgileri:\n";
    echo "Kullanici: admin\n";
    echo "Sifre: password\n\n";
    echo "NOT: Bu dosyayi guvenlik icin silin veya yeniden adlandirin.\n";

} catch (PDOException $e) {
    echo "HATA: " . $e->getMessage() . "\n";
    echo "\nLutfen asagidakileri kontrol edin:\n";
    echo "1. MySQL/MariaDB servisinin calistigini\n";
    echo "2. Kullanici adi ve sifrenin dogru oldugunu\n";
    echo "3. Yeterli yetkilere sahip oldugunuzu\n";
}

echo "</pre>";
?>
