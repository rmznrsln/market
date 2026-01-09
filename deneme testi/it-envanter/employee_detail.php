<?php
$pageTitle = "Çalışan Detayı - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$employeeId = $_GET['id'] ?? 0;

// Çalışan bilgilerini çek
$employee = $db->fetch("
    SELECT e.*, d.name as department_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE e.id = ?
", [$employeeId]);

if (!$employee) {
    echo '<div class="alert alert-danger">Calisan bulunamadi!</div>';
    require_once 'includes/footer.php';
    exit;
}

// Zimmetli cihazlar
$assignedDevices = $db->fetchAll("
    SELECT d.*, dc.name as category_name, dc.icon as category_icon, a.assigned_date
    FROM assignments a
    JOIN devices d ON a.device_id = d.id
    LEFT JOIN device_categories dc ON d.category_id = dc.id
    WHERE a.employee_id = ?
    ORDER BY a.assigned_date DESC
", [$employeeId]);

// Zimmet geçmişi
$assignmentHistory = $db->fetchAll("
    SELECT ah.*, d.asset_tag, d.name as device_name, d.brand, d.model
    FROM assignment_history ah
    JOIN devices d ON ah.device_id = d.id
    WHERE ah.employee_id = ?
    ORDER BY ah.assigned_date DESC
", [$employeeId]);

// Durum etiketleri
$statusLabels = [
    'active' => ['Aktif', 'status-active'],
    'inactive' => ['Pasif', 'status-inactive'],
    'on_leave' => ['Izinde', 'status-on_leave']
];
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user me-2"></i><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="employees.php">Calisanlar</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($employee['employee_no']) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="employee_edit.php?id=<?= $employee['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i> Duzenle
        </a>
        <a href="assignment_add.php?employee_id=<?= $employee['id'] ?>" class="btn btn-success">
            <i class="fas fa-laptop me-1"></i> Cihaz Zimmetle
        </a>
    </div>
</div>

<div class="row">
    <!-- Çalışan Bilgileri -->
    <div class="col-lg-4 mb-4">
        <div class="card detail-card">
            <div class="detail-header">
                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Calisan Bilgileri</h5>
            </div>
            <div class="detail-body">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)) ?>
                    </div>
                    <h5 class="mt-3 mb-1"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h5>
                    <p class="text-muted mb-0"><?= htmlspecialchars($employee['position'] ?? '-') ?></p>
                </div>

                <div class="row detail-row">
                    <div class="col-5 detail-label">Sicil No</div>
                    <div class="col-7 detail-value"><strong><?= htmlspecialchars($employee['employee_no']) ?></strong></div>
                </div>
                <div class="row detail-row">
                    <div class="col-5 detail-label">E-posta</div>
                    <div class="col-7 detail-value">
                        <a href="mailto:<?= htmlspecialchars($employee['email']) ?>">
                            <?= htmlspecialchars($employee['email']) ?>
                        </a>
                    </div>
                </div>
                <div class="row detail-row">
                    <div class="col-5 detail-label">Telefon</div>
                    <div class="col-7 detail-value"><?= htmlspecialchars($employee['phone'] ?? '-') ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-5 detail-label">Departman</div>
                    <div class="col-7 detail-value"><?= htmlspecialchars($employee['department_name'] ?? '-') ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-5 detail-label">Ise Giris</div>
                    <div class="col-7 detail-value">
                        <?= $employee['hire_date'] ? date('d.m.Y', strtotime($employee['hire_date'])) : '-' ?>
                    </div>
                </div>
                <div class="row detail-row">
                    <div class="col-5 detail-label">Durum</div>
                    <div class="col-7 detail-value">
                        <span class="badge badge-status <?= $statusLabels[$employee['status']][1] ?? '' ?>">
                            <?= $statusLabels[$employee['status']][0] ?? $employee['status'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- İstatistikler -->
        <div class="card content-card mt-4">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Zimmet Istatistikleri
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h3 class="text-primary"><?= count($assignedDevices) ?></h3>
                        <small class="text-muted">Aktif Zimmet</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-secondary"><?= count($assignmentHistory) ?></h3>
                        <small class="text-muted">Gecmis Zimmet</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zimmetli Cihazlar -->
    <div class="col-lg-8 mb-4">
        <div class="card content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-laptop me-2"></i>Zimmetli Cihazlar</span>
                <span class="badge bg-primary"><?= count($assignedDevices) ?> cihaz</span>
            </div>
            <div class="card-body">
                <?php if (empty($assignedDevices)): ?>
                    <p class="text-muted text-center mb-0">Bu calisana zimmetli cihaz bulunmuyor.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Demirbas No</th>
                                    <th>Cihaz</th>
                                    <th>Kategori</th>
                                    <th>Zimmet Tarihi</th>
                                    <th>Islem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignedDevices as $device): ?>
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
                                    <td>
                                        <i class="fas <?= htmlspecialchars($device['category_icon'] ?? 'fa-box') ?> me-1"></i>
                                        <?= htmlspecialchars($device['category_name'] ?? '-') ?>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($device['assigned_date'])) ?></td>
                                    <td>
                                        <a href="assignment_return.php?device_id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-undo"></i> Iade Al
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Zimmet Geçmişi -->
        <?php if (!empty($assignmentHistory)): ?>
        <div class="card content-card mt-4">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Zimmet Gecmisi
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Cihaz</th>
                                <th>Zimmet Tarihi</th>
                                <th>Iade Tarihi</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignmentHistory as $history): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($history['asset_tag']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($history['brand'] . ' ' . $history['model']) ?></small>
                                </td>
                                <td><?= date('d.m.Y', strtotime($history['assigned_date'])) ?></td>
                                <td><?= $history['returned_date'] ? date('d.m.Y', strtotime($history['returned_date'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $conditionLabels = [
                                        'good' => ['Iyi', 'bg-success'],
                                        'damaged' => ['Hasarli', 'bg-danger'],
                                        'needs_repair' => ['Tamir Gerekli', 'bg-warning']
                                    ];
                                    $cond = $history['return_condition'];
                                    ?>
                                    <span class="badge <?= $conditionLabels[$cond][1] ?? 'bg-secondary' ?>">
                                        <?= $conditionLabels[$cond][0] ?? $cond ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
