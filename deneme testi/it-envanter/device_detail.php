<?php
$pageTitle = "Cihaz Detayı - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$deviceId = $_GET['id'] ?? 0;

// Cihaz bilgilerini çek
$device = $db->fetch("
    SELECT d.*, dc.name as category_name, dc.icon as category_icon
    FROM devices d
    LEFT JOIN device_categories dc ON d.category_id = dc.id
    WHERE d.id = ?
", [$deviceId]);

if (!$device) {
    echo '<div class="alert alert-danger">Cihaz bulunamadi!</div>';
    require_once 'includes/footer.php';
    exit;
}

// Mevcut zimmet bilgisi
$currentAssignment = $db->fetch("
    SELECT a.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
           e.employee_no, e.email, dep.name as department
    FROM assignments a
    JOIN employees e ON a.employee_id = e.id
    LEFT JOIN departments dep ON e.department_id = dep.id
    WHERE a.device_id = ?
", [$deviceId]);

// Zimmet geçmişi
$assignmentHistory = $db->fetchAll("
    SELECT ah.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name
    FROM assignment_history ah
    JOIN employees e ON ah.employee_id = e.id
    WHERE ah.device_id = ?
    ORDER BY ah.assigned_date DESC
", [$deviceId]);

// Durum etiketleri
$statusLabels = [
    'available' => ['Mevcut', 'status-available'],
    'assigned' => ['Zimmetli', 'status-assigned'],
    'maintenance' => ['Bakimda', 'status-maintenance'],
    'retired' => ['Emekli', 'status-retired'],
    'lost' => ['Kayip', 'status-lost']
];
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas <?= htmlspecialchars($device['category_icon'] ?? 'fa-laptop') ?> me-2"></i><?= htmlspecialchars($device['asset_tag']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="devices.php">Cihazlar</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($device['asset_tag']) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="device_edit.php?id=<?= $device['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i> Duzenle
        </a>
        <?php if ($device['status'] === 'available'): ?>
        <a href="assignment_add.php?device_id=<?= $device['id'] ?>" class="btn btn-success">
            <i class="fas fa-user-plus me-1"></i> Zimmetle
        </a>
        <?php elseif ($device['status'] === 'assigned'): ?>
        <a href="assignment_return.php?device_id=<?= $device['id'] ?>" class="btn btn-info">
            <i class="fas fa-undo me-1"></i> Zimmeti Al
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Cihaz Bilgileri -->
    <div class="col-lg-6 mb-4">
        <div class="card detail-card">
            <div class="detail-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Cihaz Bilgileri</h5>
            </div>
            <div class="detail-body">
                <div class="row detail-row">
                    <div class="col-4 detail-label">Demirbas No</div>
                    <div class="col-8 detail-value"><strong><?= htmlspecialchars($device['asset_tag']) ?></strong></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Cihaz Adi</div>
                    <div class="col-8 detail-value"><?= htmlspecialchars($device['name']) ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Kategori</div>
                    <div class="col-8 detail-value">
                        <i class="fas <?= htmlspecialchars($device['category_icon'] ?? 'fa-box') ?> me-1"></i>
                        <?= htmlspecialchars($device['category_name'] ?? '-') ?>
                    </div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Marka</div>
                    <div class="col-8 detail-value"><?= htmlspecialchars($device['brand'] ?? '-') ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Model</div>
                    <div class="col-8 detail-value"><?= htmlspecialchars($device['model'] ?? '-') ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Seri No</div>
                    <div class="col-8 detail-value"><?= htmlspecialchars($device['serial_number'] ?? '-') ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Durum</div>
                    <div class="col-8 detail-value">
                        <span class="badge badge-status <?= $statusLabels[$device['status']][1] ?? '' ?>">
                            <?= $statusLabels[$device['status']][0] ?? $device['status'] ?>
                        </span>
                    </div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Ozellikler</div>
                    <div class="col-8 detail-value"><?= nl2br(htmlspecialchars($device['specifications'] ?? '-')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Satın Alma ve Garanti Bilgileri -->
    <div class="col-lg-6 mb-4">
        <div class="card detail-card mb-4">
            <div class="detail-header">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Satin Alma Bilgileri</h5>
            </div>
            <div class="detail-body">
                <div class="row detail-row">
                    <div class="col-4 detail-label">Satin Alma Tarihi</div>
                    <div class="col-8 detail-value">
                        <?= $device['purchase_date'] ? date('d.m.Y', strtotime($device['purchase_date'])) : '-' ?>
                    </div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Satin Alma Fiyati</div>
                    <div class="col-8 detail-value">
                        <?= $device['purchase_price'] ? number_format($device['purchase_price'], 2, ',', '.') . ' TL' : '-' ?>
                    </div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Garanti Bitis</div>
                    <div class="col-8 detail-value">
                        <?php if ($device['warranty_end_date']): ?>
                            <?= date('d.m.Y', strtotime($device['warranty_end_date'])) ?>
                            <?php
                            $daysRemaining = (strtotime($device['warranty_end_date']) - time()) / 86400;
                            if ($daysRemaining < 0): ?>
                                <span class="badge bg-danger ms-2">Suresi Dolmus</span>
                            <?php elseif ($daysRemaining < 90): ?>
                                <span class="badge bg-warning ms-2"><?= round($daysRemaining) ?> gun kaldi</span>
                            <?php else: ?>
                                <span class="badge bg-success ms-2">Aktif</span>
                            <?php endif; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mevcut Zimmet -->
        <?php if ($currentAssignment): ?>
        <div class="card detail-card">
            <div class="detail-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Mevcut Zimmet</h5>
            </div>
            <div class="detail-body">
                <div class="row detail-row">
                    <div class="col-4 detail-label">Calisan</div>
                    <div class="col-8 detail-value">
                        <a href="employee_detail.php?id=<?= $currentAssignment['employee_id'] ?>">
                            <?= htmlspecialchars($currentAssignment['employee_name']) ?>
                        </a>
                    </div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Sicil No</div>
                    <div class="col-8 detail-value"><?= htmlspecialchars($currentAssignment['employee_no']) ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Departman</div>
                    <div class="col-8 detail-value"><?= htmlspecialchars($currentAssignment['department'] ?? '-') ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Zimmet Tarihi</div>
                    <div class="col-8 detail-value"><?= date('d.m.Y', strtotime($currentAssignment['assigned_date'])) ?></div>
                </div>
                <div class="row detail-row">
                    <div class="col-4 detail-label">Notlar</div>
                    <div class="col-8 detail-value"><?= htmlspecialchars($currentAssignment['notes'] ?? '-') ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Zimmet Geçmişi -->
<?php if (!empty($assignmentHistory)): ?>
<div class="card content-card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>Zimmet Gecmisi
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Calisan</th>
                        <th>Zimmet Tarihi</th>
                        <th>Iade Tarihi</th>
                        <th>Iade Durumu</th>
                        <th>Notlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignmentHistory as $history): ?>
                    <tr>
                        <td><?= htmlspecialchars($history['employee_name']) ?></td>
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
                        <td><?= htmlspecialchars($history['notes'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
