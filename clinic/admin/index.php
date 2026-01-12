<?php
/**
 * Admin Panel - Ana Sayfa (Dashboard)
 * Bekleyen hastaların listesi
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = Database::getInstance();
$user = getCurrentUser();

// İstatistikleri getir
$stats = [
    'waiting' => $db->fetch("SELECT COUNT(*) as count FROM patients WHERE status = 'waiting'")['count'] ?? 0,
    'in_treatment' => $db->fetch("SELECT COUNT(*) as count FROM patients WHERE status = 'in_treatment'")['count'] ?? 0,
    'completed_today' => $db->fetch("SELECT COUNT(*) as count FROM patients WHERE status = 'completed' AND DATE(updated_at) = CURDATE()")['count'] ?? 0,
    'total_today' => $db->fetch("SELECT COUNT(*) as count FROM patients WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
];

// Bekleyen ve tedavideki hastaları getir
$patients = $db->fetchAll(
    "SELECT * FROM patients WHERE status IN ('waiting', 'in_treatment') ORDER BY
        CASE status
            WHEN 'in_treatment' THEN 1
            WHEN 'waiting' THEN 2
        END,
        created_at ASC"
);

// Durum güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $patientId = (int)($_POST['patient_id'] ?? 0);
    $action = $_POST['action'];

    if ($patientId > 0) {
        switch ($action) {
            case 'start_treatment':
                $db->update('patients', ['status' => 'in_treatment'], 'id = ?', [$patientId]);
                break;
            case 'cancel':
                $db->update('patients', ['status' => 'cancelled'], 'id = ?', [$patientId]);
                break;
        }
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Klinik Yönetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0e3055;
            --secondary-color: #65bdc2;
            --sidebar-width: 260px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            margin: 0;
            min-height: 100vh;
        }

        /* Sidebar */
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
            overflow-y: auto;
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

        .sidebar-header small {
            opacity: 0.7;
            font-size: 12px;
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

        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }

        .user-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }

        .user-info .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-info .user-role {
            font-size: 12px;
            opacity: 0.7;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0;
        }

        .page-header p {
            color: #666;
            margin: 5px 0 0;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.waiting { background: #fff3cd; color: #856404; }
        .stat-icon.treatment { background: #cce5ff; color: #004085; }
        .stat-icon.completed { background: #d4edda; color: #155724; }
        .stat-icon.total { background: #e2e3e5; color: #383d41; }

        .stat-info h3 {
            font-size: 28px;
            margin: 0;
            color: var(--primary-color);
        }

        .stat-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        /* Patient Cards */
        .patient-list {
            display: grid;
            gap: 16px;
        }

        .patient-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        .patient-card:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .patient-card.waiting {
            border-left-color: #ffc107;
        }

        .patient-card.in_treatment {
            border-left-color: #007bff;
        }

        .patient-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #4aa8ad 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .patient-info {
            flex-grow: 1;
        }

        .patient-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 5px;
        }

        .patient-meta {
            display: flex;
            gap: 16px;
            color: #666;
            font-size: 14px;
        }

        .patient-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .patient-actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 10px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-view {
            background: var(--secondary-color);
            color: white;
        }

        .btn-view:hover {
            background: #4aa8ad;
        }

        .btn-start {
            background: #28a745;
            color: white;
        }

        .btn-start:hover {
            background: #218838;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-waiting {
            background: #fff3cd;
            color: #856404;
        }

        .badge-treatment {
            background: #cce5ff;
            color: #004085;
        }

        /* Section Title */
        .section-title {
            font-size: 20px;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--secondary-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h2,
            .sidebar-header small,
            .sidebar-menu li a span,
            .user-info {
                display: none;
            }

            .sidebar-menu li a {
                justify-content: center;
                padding: 16px;
            }

            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .patient-card {
                flex-wrap: wrap;
            }

            .patient-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }

        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .refresh-indicator i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-clinic-medical fa-2x"></i>
            <h2>Klinik Yönetim</h2>
            <small>Dr. Feridun Elmas</small>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="index.php" class="active">
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
                <a href="settings.php">
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

        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="user-role"><?= ucfirst($user['role']) ?></div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-tachometer-alt"></i> Kontrol Paneli</h1>
            <p>Hoş geldiniz, <?= htmlspecialchars($user['full_name']) ?>!</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon waiting">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['waiting'] ?></h3>
                    <p>Bekleyen Hasta</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon treatment">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['in_treatment'] ?></h3>
                    <p>Tedavide</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['completed_today'] ?></h3>
                    <p>Bugün Tamamlanan</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_today'] ?></h3>
                    <p>Bugün Kayıt</p>
                </div>
            </div>
        </div>

        <!-- Patient List -->
        <h2 class="section-title">
            <i class="fas fa-list"></i> Aktif Hastalar
        </h2>

        <?php if (empty($patients)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Bekleyen hasta yok</h3>
            <p>Şu anda bekleyen veya tedavide hasta bulunmuyor.</p>
        </div>
        <?php else: ?>
        <div class="patient-list">
            <?php foreach ($patients as $patient): ?>
            <div class="patient-card <?= $patient['status'] ?>" onclick="window.location='patient.php?id=<?= $patient['id'] ?>'">
                <div class="patient-avatar">
                    <?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?>
                </div>

                <div class="patient-info">
                    <h3 class="patient-name">
                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                    </h3>
                    <div class="patient-meta">
                        <span><i class="fas fa-phone"></i> <?= htmlspecialchars($patient['phone']) ?></span>
                        <span><i class="fas fa-globe"></i> <?= htmlspecialchars($patient['nationality'] ?: '-') ?></span>
                        <span><i class="fas fa-clock"></i> <?= formatDateTime($patient['created_at']) ?></span>
                    </div>
                </div>

                <span class="badge-status <?= $patient['status'] === 'waiting' ? 'badge-waiting' : 'badge-treatment' ?>">
                    <?= $patient['status'] === 'waiting' ? 'Bekliyor' : 'Tedavide' ?>
                </span>

                <div class="patient-actions" onclick="event.stopPropagation();">
                    <a href="patient.php?id=<?= $patient['id'] ?>" class="btn-action btn-view">
                        <i class="fas fa-eye"></i> Görüntüle
                    </a>
                    <?php if ($patient['status'] === 'waiting'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                        <input type="hidden" name="action" value="start_treatment">
                        <button type="submit" class="btn-action btn-start">
                            <i class="fas fa-play"></i> Başlat
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Auto refresh -->
    <script>
        // Her 30 saniyede bir sayfayı yenile (yeni hastalar için)
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
