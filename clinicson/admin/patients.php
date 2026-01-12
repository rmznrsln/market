<?php
/**
 * Tüm Hastalar Listesi
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = Database::getInstance();
$user = getCurrentUser();

// Filtreler
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Sorgu oluştur
$where = "1=1";
$params = [];

if ($status) {
    $where .= " AND status = ?";
    $params[] = $status;
}

if ($search) {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Toplam kayıt sayısı
$totalCount = $db->fetch("SELECT COUNT(*) as count FROM patients WHERE $where", $params)['count'];
$totalPages = ceil($totalCount / $perPage);

// Hastaları getir
$patients = $db->fetchAll(
    "SELECT * FROM patients WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
    $params
);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tüm Hastalar - Klinik Yönetim</title>
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            flex-wrap: wrap;
        }

        .filter-bar input,
        .filter-bar select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 14px;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: var(--secondary-color);
            outline: none;
        }

        .filter-bar button {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }

        /* Table */
        .patients-table {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .patients-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .patients-table th,
        .patients-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .patients-table th {
            background: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 14px;
        }

        .patients-table tr:hover {
            background: #f8f9fa;
        }

        .patients-table tr {
            cursor: pointer;
        }

        .patient-name {
            font-weight: 600;
            color: var(--primary-color);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-waiting { background: #fff3cd; color: #856404; }
        .badge-in_treatment { background: #cce5ff; color: #004085; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 0 0 16px 16px;
        }

        .pagination {
            display: flex;
            gap: 5px;
            margin: 0;
        }

        .pagination a,
        .pagination span {
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            background: #f8f9fa;
        }

        .pagination a:hover {
            background: var(--secondary-color);
            color: white;
        }

        .pagination .active {
            background: var(--primary-color);
            color: white;
        }

        /* Responsive */
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

        @media (max-width: 768px) {
            .patients-table {
                overflow-x: auto;
            }

            .filter-bar {
                flex-direction: column;
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
                <a href="patients.php" class="active">
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
            <h1><i class="fas fa-users"></i> Tüm Hastalar</h1>
            <span class="text-muted">Toplam: <?= $totalCount ?> kayıt</span>
        </div>

        <!-- Filter Bar -->
        <form class="filter-bar" method="GET">
            <input type="text" name="search" placeholder="Ad, soyad, telefon veya e-posta ara..."
                   value="<?= htmlspecialchars($search) ?>" style="flex:1; min-width:200px;">

            <select name="status">
                <option value="">Tüm Durumlar</option>
                <option value="waiting" <?= $status === 'waiting' ? 'selected' : '' ?>>Bekleyen</option>
                <option value="in_treatment" <?= $status === 'in_treatment' ? 'selected' : '' ?>>Tedavide</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Tamamlanan</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>İptal</option>
            </select>

            <button type="submit"><i class="fas fa-search"></i> Ara</button>

            <?php if ($search || $status): ?>
            <a href="patients.php" class="btn btn-outline-secondary" style="padding:10px 20px; border-radius:8px;">
                <i class="fas fa-times"></i> Temizle
            </a>
            <?php endif; ?>
        </form>

        <!-- Patients Table -->
        <div class="patients-table">
            <table>
                <thead>
                    <tr>
                        <th>Hasta</th>
                        <th>Telefon</th>
                        <th>Uyruk</th>
                        <th>Durum</th>
                        <th>Kayıt Tarihi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                    <tr onclick="window.location='patient.php?id=<?= $patient['id'] ?>'">
                        <td>
                            <span class="patient-name">
                                <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                            </span>
                            <?php if ($patient['email']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($patient['email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($patient['phone']) ?></td>
                        <td><?= $patient['nationality'] ? getNationalityText($patient['nationality']) : '-' ?></td>
                        <td>
                            <?php
                            $statusLabels = [
                                'waiting' => 'Bekliyor',
                                'in_treatment' => 'Tedavide',
                                'completed' => 'Tamamlandı',
                                'cancelled' => 'İptal'
                            ];
                            ?>
                            <span class="badge badge-<?= $patient['status'] ?>">
                                <?= $statusLabels[$patient['status']] ?? $patient['status'] ?>
                            </span>
                        </td>
                        <td><?= formatDateTime($patient['created_at']) ?></td>
                        <td>
                            <a href="patient.php?id=<?= $patient['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                            <i class="fas fa-inbox fa-3x" style="margin-bottom:15px; display:block;"></i>
                            Kayıt bulunamadı
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <span class="text-muted">
                    Sayfa <?= $page ?> / <?= $totalPages ?>
                </span>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                       class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
