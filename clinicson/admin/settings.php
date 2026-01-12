<?php
/**
 * Ayarlar Sayfası
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = Database::getInstance();
$user = getCurrentUser();

$message = null;
$messageType = 'success';

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Şifre değiştirme (kendi şifresi)
    if ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $message = 'Tüm alanları doldurun.';
            $messageType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'Yeni şifreler eşleşmiyor.';
            $messageType = 'danger';
        } elseif (strlen($newPassword) < 6) {
            $message = 'Şifre en az 6 karakter olmalıdır.';
            $messageType = 'danger';
        } else {
            $dbUser = $db->fetch("SELECT password FROM users WHERE id = ?", [$user['id']]);
            if (password_verify($currentPassword, $dbUser['password'])) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $db->update('users', ['password' => $newHash], 'id = ?', [$user['id']]);
                $message = 'Şifreniz başarıyla değiştirildi.';
            } else {
                $message = 'Mevcut şifre yanlış.';
                $messageType = 'danger';
            }
        }
    }

    // Yeni kullanıcı ekleme (sadece admin)
    if ($_POST['action'] === 'add_user' && $user['role'] === 'admin') {
        $username = sanitize($_POST['username'] ?? '');
        $fullName = sanitize($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'nurse';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($fullName) || empty($password)) {
            $message = 'Tüm alanları doldurun.';
            $messageType = 'danger';
        } elseif (strlen($password) < 6) {
            $message = 'Şifre en az 6 karakter olmalıdır.';
            $messageType = 'danger';
        } else {
            // Kullanıcı adı kontrolü
            $existing = $db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
            if ($existing) {
                $message = 'Bu kullanıcı adı zaten kullanılıyor.';
                $messageType = 'danger';
            } else {
                $db->insert('users', [
                    'username' => $username,
                    'full_name' => $fullName,
                    'role' => $role,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'is_active' => 1
                ]);
                $message = 'Kullanıcı başarıyla eklendi.';
            }
        }
    }

    // Kullanıcı şifresini sıfırla (sadece admin)
    if ($_POST['action'] === 'reset_password' && $user['role'] === 'admin') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if ($userId <= 0 || empty($newPassword)) {
            $message = 'Geçersiz işlem.';
            $messageType = 'danger';
        } elseif (strlen($newPassword) < 6) {
            $message = 'Şifre en az 6 karakter olmalıdır.';
            $messageType = 'danger';
        } else {
            $db->update('users', [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT)
            ], 'id = ?', [$userId]);
            $message = 'Şifre başarıyla sıfırlandı.';
        }
    }

    // Kullanıcı durumunu değiştir (sadece admin)
    if ($_POST['action'] === 'toggle_user' && $user['role'] === 'admin') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0 && $userId !== $user['id']) {
            $targetUser = $db->fetch("SELECT is_active FROM users WHERE id = ?", [$userId]);
            if ($targetUser) {
                $newStatus = $targetUser['is_active'] ? 0 : 1;
                $db->update('users', ['is_active' => $newStatus], 'id = ?', [$userId]);
                $message = $newStatus ? 'Kullanıcı aktifleştirildi.' : 'Kullanıcı pasifleştirildi.';
            }
        }
    }

    // Kullanıcı sil (sadece admin)
    if ($_POST['action'] === 'delete_user' && $user['role'] === 'admin') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0 && $userId !== $user['id']) {
            $db->query("DELETE FROM users WHERE id = ?", [$userId]);
            $message = 'Kullanıcı silindi.';
        }
    }
}

// Kullanıcı listesi (admin için)
$allUsers = [];
if ($user['role'] === 'admin') {
    $allUsers = $db->fetchAll("SELECT * FROM users ORDER BY role, full_name");
}

// Tedavi türlerini getir
$treatmentTypes = $db->fetchAll("SELECT * FROM treatment_types ORDER BY name_tr");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Klinik Yönetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0e3055;
            --secondary-color: #65bdc2;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a4a7a 100%);
            color: white;
            padding: 20px 0;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 18px;
            margin: 10px 0 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary-color);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0 0 30px;
        }

        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .settings-card h4 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-card h4 i {
            color: var(--secondary-color);
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(101, 189, 194, 0.2);
        }

        .btn-save {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
        }

        .btn-save:hover {
            background: #4aa8ad;
            color: white;
        }

        .alert {
            border-radius: 10px;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h2,
            .sidebar-menu li a span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-clinic-medical fa-2x"></i>
            <h2>Klinik Yönetim</h2>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    <span>Panel</span>
                </a>
            </li>
            <li>
                <a href="patients.php">
                    <i class="fas fa-users"></i>
                    <span>Tüm Hastalar</span>
                </a>
            </li>
            <li>
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Raporlar</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="active">
                    <i class="fas fa-cog"></i>
                    <span>Ayarlar</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Çıkış</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-cog"></i> Ayarlar</h1>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <!-- Profil Bilgileri -->
                <div class="settings-card">
                    <h4><i class="fas fa-user"></i> Profil Bilgileri</h4>

                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" disabled>
                    </div>
                </div>

                <!-- Şifre Değiştir -->
                <div class="settings-card">
                    <h4><i class="fas fa-lock"></i> Şifre Değiştir</h4>

                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label class="form-label">Mevcut Şifre</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yeni Şifre</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yeni Şifre (Tekrar)</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>

                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save"></i> Şifreyi Değiştir
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <?php if ($user['role'] === 'admin'): ?>
                <!-- Kullanıcı Yönetimi (Sadece Admin) -->
                <div class="settings-card">
                    <h4><i class="fas fa-users-cog"></i> Kullanıcı Yönetimi</h4>

                    <!-- Yeni Kullanıcı Ekle -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="mb-3"><i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle</h6>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_user">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" name="full_name" class="form-control form-control-sm" placeholder="Ad Soyad" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="username" class="form-control form-control-sm" placeholder="Kullanıcı Adı" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="password" name="password" class="form-control form-control-sm" placeholder="Şifre (min 6)" required minlength="6">
                                </div>
                                <div class="col-md-6">
                                    <select name="role" class="form-select form-select-sm">
                                        <option value="nurse">Hemşire</option>
                                        <option value="doctor">Doktor</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="fas fa-plus"></i> Kullanıcı Ekle
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Kullanıcı Listesi -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kullanıcı</th>
                                    <th>Rol</th>
                                    <th>Durum</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($u['username']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $roleLabels = ['admin' => 'Admin', 'doctor' => 'Doktor', 'nurse' => 'Hemşire'];
                                        $roleColors = ['admin' => 'danger', 'doctor' => 'primary', 'nurse' => 'info'];
                                        ?>
                                        <span class="badge bg-<?= $roleColors[$u['role']] ?? 'secondary' ?>">
                                            <?= $roleLabels[$u['role']] ?? $u['role'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['id'] !== $user['id']): ?>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Şifre Sıfırla -->
                                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal<?= $u['id'] ?>" title="Şifre Sıfırla">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <!-- Aktif/Pasif -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="toggle_user">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn btn-outline-<?= $u['is_active'] ? 'secondary' : 'success' ?>" title="<?= $u['is_active'] ? 'Pasifleştir' : 'Aktifleştir' ?>">
                                                    <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                                                </button>
                                            </form>
                                            <!-- Sil -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Şifre Sıfırlama Modal -->
                                        <div class="modal fade" id="resetModal<?= $u['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-sm">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title">Şifre Sıfırla</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="reset_password">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <p class="small text-muted"><?= htmlspecialchars($u['full_name']) ?></p>
                                                            <input type="password" name="new_password" class="form-control" placeholder="Yeni Şifre" required minlength="6">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-warning btn-sm">Sıfırla</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted small">Siz</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tedavi Türleri -->
                <div class="settings-card">
                    <h4><i class="fas fa-procedures"></i> Tedavi Türleri</h4>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tedavi</th>
                                <th>Fiyat</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($treatmentTypes as $type): ?>
                            <tr>
                                <td><?= htmlspecialchars($type['name_tr']) ?></td>
                                <td><?= formatMoney($type['base_price']) ?></td>
                                <td>
                                    <?php if ($type['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sistem Bilgileri -->
                <div class="settings-card">
                    <h4><i class="fas fa-info-circle"></i> Sistem Bilgileri</h4>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted">PHP Sürümü</label>
                            <p class="mb-0"><?= phpversion() ?></p>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted">Sunucu</label>
                            <p class="mb-0"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
