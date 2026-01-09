<?php
$pageTitle = "Raporlar - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$reportType = $_GET['type'] ?? 'category';

// Kategori Raporu
$categoryReport = $db->fetchAll("
    SELECT dc.name as category, dc.icon,
           COUNT(d.id) as total,
           SUM(CASE WHEN d.status = 'available' THEN 1 ELSE 0 END) as available,
           SUM(CASE WHEN d.status = 'assigned' THEN 1 ELSE 0 END) as assigned,
           SUM(CASE WHEN d.status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
           SUM(d.purchase_price) as total_value
    FROM device_categories dc
    LEFT JOIN devices d ON dc.id = d.category_id
    GROUP BY dc.id
    ORDER BY total DESC
");

// Departman Raporu
$departmentReport = $db->fetchAll("
    SELECT dep.name as department,
           COUNT(DISTINCT e.id) as employee_count,
           COUNT(a.id) as device_count,
           SUM(d.purchase_price) as total_value
    FROM departments dep
    LEFT JOIN employees e ON dep.id = e.department_id
    LEFT JOIN assignments a ON e.id = a.employee_id
    LEFT JOIN devices d ON a.device_id = d.id
    GROUP BY dep.id
    ORDER BY device_count DESC
");

// Garanti Raporu
$warrantyReport = $db->fetchAll("
    SELECT d.*, dc.name as category_name,
           DATEDIFF(d.warranty_end_date, CURDATE()) as days_remaining,
           CONCAT(e.first_name, ' ', e.last_name) as assigned_to
    FROM devices d
    LEFT JOIN device_categories dc ON d.category_id = dc.id
    LEFT JOIN assignments a ON d.id = a.device_id
    LEFT JOIN employees e ON a.employee_id = e.id
    WHERE d.warranty_end_date IS NOT NULL
    ORDER BY d.warranty_end_date ASC
");
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-chart-bar me-2"></i>Raporlar</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Raporlar</li>
        </ol>
    </nav>
</div>

<!-- Report Type Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $reportType === 'category' ? 'active' : '' ?>" href="?type=category">
            <i class="fas fa-th-large me-1"></i> Kategori Raporu
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $reportType === 'department' ? 'active' : '' ?>" href="?type=department">
            <i class="fas fa-building me-1"></i> Departman Raporu
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $reportType === 'warranty' ? 'active' : '' ?>" href="?type=warranty">
            <i class="fas fa-shield-alt me-1"></i> Garanti Durumu
        </a>
    </li>
</ul>

<?php if ($reportType === 'category'): ?>
<!-- Kategori Raporu -->
<div class="card content-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-th-large me-2"></i>Kategori Bazli Envanter Raporu</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i> Yazdir
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="text-center">Toplam</th>
                        <th class="text-center">Mevcut</th>
                        <th class="text-center">Zimmetli</th>
                        <th class="text-center">Bakimda</th>
                        <th class="text-end">Toplam Deger</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grandTotal = 0;
                    $grandValue = 0;
                    foreach ($categoryReport as $row):
                        $grandTotal += $row['total'];
                        $grandValue += $row['total_value'] ?? 0;
                    ?>
                    <tr>
                        <td>
                            <i class="fas <?= htmlspecialchars($row['icon'] ?? 'fa-box') ?> me-2 text-primary"></i>
                            <?= htmlspecialchars($row['category']) ?>
                        </td>
                        <td class="text-center"><strong><?= $row['total'] ?></strong></td>
                        <td class="text-center">
                            <span class="badge bg-success"><?= $row['available'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?= $row['assigned'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning"><?= $row['maintenance'] ?></span>
                        </td>
                        <td class="text-end">
                            <?= number_format($row['total_value'] ?? 0, 2, ',', '.') ?> TL
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td>TOPLAM</td>
                        <td class="text-center"><?= $grandTotal ?></td>
                        <td colspan="3"></td>
                        <td class="text-end"><?= number_format($grandValue, 2, ',', '.') ?> TL</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php elseif ($reportType === 'department'): ?>
<!-- Departman Raporu -->
<div class="card content-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-building me-2"></i>Departman Bazli Envanter Raporu</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i> Yazdir
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Departman</th>
                        <th class="text-center">Calisan Sayisi</th>
                        <th class="text-center">Zimmetli Cihaz</th>
                        <th class="text-end">Cihaz Degeri</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departmentReport as $row): ?>
                    <tr>
                        <td><i class="fas fa-building me-2 text-secondary"></i><?= htmlspecialchars($row['department']) ?></td>
                        <td class="text-center"><?= $row['employee_count'] ?></td>
                        <td class="text-center"><span class="badge bg-primary"><?= $row['device_count'] ?></span></td>
                        <td class="text-end"><?= number_format($row['total_value'] ?? 0, 2, ',', '.') ?> TL</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Garanti Raporu -->
<div class="card content-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-shield-alt me-2"></i>Garanti Durumu Raporu</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i> Yazdir
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="warrantyTable">
                <thead>
                    <tr>
                        <th>Demirbas No</th>
                        <th>Cihaz</th>
                        <th>Kategori</th>
                        <th>Garanti Bitis</th>
                        <th>Durum</th>
                        <th>Zimmetli</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warrantyReport as $device): ?>
                    <tr>
                        <td>
                            <a href="device_detail.php?id=<?= $device['id'] ?>">
                                <strong><?= htmlspecialchars($device['asset_tag']) ?></strong>
                            </a>
                        </td>
                        <td>
                            <?= htmlspecialchars($device['name']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($device['brand'] . ' ' . $device['model']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($device['category_name'] ?? '-') ?></td>
                        <td><?= date('d.m.Y', strtotime($device['warranty_end_date'])) ?></td>
                        <td>
                            <?php
                            $days = $device['days_remaining'];
                            if ($days < 0):
                            ?>
                                <span class="badge bg-dark">Suresi Dolmus</span>
                            <?php elseif ($days <= 30): ?>
                                <span class="badge bg-danger"><?= $days ?> gun</span>
                            <?php elseif ($days <= 90): ?>
                                <span class="badge bg-warning"><?= $days ?> gun</span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= $days ?> gun</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($device['assigned_to'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#warrantyTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        pageLength: 25,
        order: [[3, 'asc']]
    });
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
