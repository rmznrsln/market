<?php
/**
 * Admin Şifre Sıfırlama Scripti
 * Kullandıktan sonra bu dosyayı silin!
 */

require_once __DIR__ . '/../config/database.php';

$newPassword = 'admin123'; // Yeni şifre
$username = 'admin';       // Kullanıcı adı

try {
    $db = Database::getInstance();

    // Mevcut kullanıcıyı kontrol et
    $user = $db->fetch("SELECT * FROM users WHERE username = ?", [$username]);

    if ($user) {
        // Şifreyi güncelle
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $db->update('users', ['password' => $hashedPassword], 'username = ?', [$username]);
        echo "Sifre basariyla guncellendi!<br>";
        echo "Kullanici: " . $username . "<br>";
        echo "Yeni sifre: " . $newPassword . "<br>";
    } else {
        // Kullanıcı yoksa oluştur
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $db->insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'name' => 'Admin',
            'email' => 'admin@clinic.com',
            'role' => 'admin',
            'is_active' => 1
        ]);
        echo "Yeni admin kullanicisi olusturuldu!<br>";
        echo "Kullanici: " . $username . "<br>";
        echo "Sifre: " . $newPassword . "<br>";
    }

    echo "<br><strong>UYARI: Bu dosyayi hemen silin!</strong>";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
