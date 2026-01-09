<?php
$pageTitle = "Dashboard - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

// İstatistikleri çek
$stats = $db->fetch("SELECT * FROM v_dashboard_stats");

// Kategori bazlı cihaz sayıları
$categoryStats = $db->fetchAll("
    SELECT dc.name, dc.icon, COUNT(d.id) as count
    FROM device_categories dc
    LEFT JOIN devices d ON dc.id = d.category_id
    GROUP BY dc.id
    ORDER BY count DESC
");

// Departman bazlı zimmet sayıları
$deptStats = $db->fetchAll("
    SELECT dep.name, COUNT(a.id) as count
    FROM departments dep
    LEFT JOIN employees e ON dep.id = e.department_id
    LEFT JOIN assignments a ON e.id = a.employee_id
    GROUP BY dep.id
    ORDER BY count DESC
    LIMIT 5
");

// Son zimmetler
$recentAssignments = $db->fetchAll("
    SELECT
        d.asset_tag, d.name as device_name, d.brand, d.model,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        dep.name as department,
        a.assigned_date
    FROM assignments a
    JOIN devices d ON a.device_id = d.id
    JOIN employees e ON a.employee_id = e.id
    LEFT JOIN departments dep ON e.department_id = dep.id
    ORDER BY a.assigned_date DESC
    LIMIT 10
");

// Garantisi yaklaşan cihazlar
$warrantyExpiring = $db->fetchAll("
    SELECT d.asset_tag, d.name, d.brand, d.model, d.warranty_end_date,
           DATEDIFF(d.warranty_end_date, CURDATE()) as days_remaining
    FROM devices d
    WHERE d.warranty_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
    ORDER BY d.warranty_end_date ASC
    LIMIT 5
");
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Ana Sayfa</li>
            </ol>
        </nav>
    </div>
    <div class="quick-actions">
        <a href="device_add.php" class="btn btn-primary quick-action-btn">
            <i class="fas fa-plus"></i> Yeni Cihaz
        </a>
        <a href="assignment_add.php" class="btn btn-success quick-action-btn">
            <i class="fas fa-clipboard-check"></i> Zimmet Olustur
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-primary me-3">
                    <i class="fas fa-laptop"></i>
                </div>
                <div>
                    <p class="stat-label">Toplam Cihaz</p>
                    <h3 class="stat-number"><?= number_format($stats['total_devices']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-success me-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="stat-label">Mevcut</p>
                    <h3 class="stat-number"><?= number_format($stats['available_devices']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-info me-3">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <p class="stat-label">Zimmetli</p>
                    <h3 class="stat-number"><?= number_format($stats['assigned_devices']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-warning me-3">
                    <i class="fas fa-tools"></i>
                </div>
                <div>
                    <p class="stat-label">Bakimda</p>
                    <h3 class="stat-number"><?= number_format($stats['maintenance_devices']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-secondary me-3">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="stat-label">Calisanlar</p>
                    <h3 class="stat-number"><?= number_format($stats['active_employees']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-danger me-3">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <p class="stat-label">Aktif Zimmet</p>
                    <h3 class="stat-number"><?= number_format($stats['total_assignments']) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-pie me-2"></i>Kategori Dagilimi</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-bar me-2"></i>Departman Bazli Zimmetler</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i>Son Zimmetler</span>
                <a href="assignments.php" class="btn btn-sm btn-outline-primary">Tumunu Gor</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Cihaz</th>
                                <th>Calisan</th>
                                <th>Departman</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAssignments as $assignment): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($assignment['asset_tag']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($assignment['brand'] . ' ' . $assignment['model']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($assignment['employee_name']) ?></td>
                                <td><?= htmlspecialchars($assignment['department'] ?? '-') ?></td>
                                <td><?= date('d.m.Y', strtotime($assignment['assigned_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card content-card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Garantisi Yaklasan Cihazlar
            </div>
            <div class="card-body">
                <?php if (empty($warrantyExpiring)): ?>
                    <p class="text-muted text-center mb-0">90 gun icinde garantisi bitecek cihaz yok.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($warrantyExpiring as $device): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($device['asset_tag']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($device['brand'] . ' ' . $device['model']) ?></small>
                                </div>
                                <span class="badge <?= $device['days_remaining'] < 30 ? 'bg-danger' : 'bg-warning' ?>">
                                    <?= $device['days_remaining'] ?> gun
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kategori Özeti -->
        <div class="card content-card mt-4">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Kategori Ozeti
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($categoryStats as $cat): ?>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas <?= htmlspecialchars($cat['icon']) ?> me-2 text-primary"></i>
                            <?= htmlspecialchars($cat['name']) ?>
                        </span>
                        <span class="badge bg-primary rounded-pill"><?= $cat['count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Kategori grafiği verileri
const categoryData = {
    labels: [<?= implode(',', array_map(fn($c) => "'" . addslashes($c['name']) . "'", $categoryStats)) ?>],
    datasets: [{
        data: [<?= implode(',', array_column($categoryStats, 'count')) ?>],
        backgroundColor: [
            '#667eea', '#11998e', '#f093fb', '#4facfe', '#ff416c',
            '#ffc107', '#6f42c1', '#20c997', '#fd7e14', '#6c757d'
        ]
    }]
};

// Departman grafiği verileri
const deptData = {
    labels: [<?= implode(',', array_map(fn($d) => "'" . addslashes($d['name']) . "'", $deptStats)) ?>],
    datasets: [{
        label: 'Zimmet Sayisi',
        data: [<?= implode(',', array_column($deptStats, 'count')) ?>],
        backgroundColor: '#667eea'
    }]
};

document.addEventListener('DOMContentLoaded', function() {
    // Kategori Pie Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Departman Bar Chart
    new Chart(document.getElementById('departmentChart'), {
        type: 'bar',
        data: deptData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
