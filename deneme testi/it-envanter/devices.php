<?php
$pageTitle = "Cihazlar - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

// Filtreler
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Kategorileri çek
$categories = $db->fetchAll("SELECT * FROM device_categories ORDER BY name");

// Cihazları çek
$sql = "
    SELECT d.*, dc.name as category_name, dc.icon as category_icon,
           CONCAT(e.first_name, ' ', e.last_name) as assigned_to,
           e.employee_no
    FROM devices d
    LEFT JOIN device_categories dc ON d.category_id = dc.id
    LEFT JOIN assignments a ON d.id = a.device_id
    LEFT JOIN employees e ON a.employee_id = e.id
    WHERE 1=1
";
$params = [];

if ($categoryFilter) {
    $sql .= " AND d.category_id = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter) {
    $sql .= " AND d.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY d.created_at DESC";
$devices = $db->fetchAll($sql, $params);

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
        <h1><i class="fas fa-laptop me-2"></i>Cihazlar</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Cihazlar</li>
            </ol>
        </nav>
    </div>
    <a href="device_add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Yeni Cihaz Ekle
    </a>
</div>

<!-- Filters -->
<div class="card content-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select">
                    <option value="">Tum Kategoriler</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tum Durumlar</option>
                    <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $statusFilter == $key ? 'selected' : '' ?>>
                        <?= $label[0] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i> Filtrele
                </button>
                <a href="devices.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Temizle
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Devices Table -->
<div class="table-container">
    <table id="devicesTable" class="table table-hover">
        <thead>
            <tr>
                <th>Demirbaş No</th>
                <th>Cihaz</th>
                <th>Kategori</th>
                <th>Marka/Model</th>
                <th>Seri No</th>
                <th>Durum</th>
                <th>Zimmetli</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($devices as $device): ?>
            <tr>
                <td><strong><?= htmlspecialchars($device['asset_tag']) ?></strong></td>
                <td><?= htmlspecialchars($device['name']) ?></td>
                <td>
                    <i class="fas <?= htmlspecialchars($device['category_icon'] ?? 'fa-box') ?> me-1 text-primary"></i>
                    <?= htmlspecialchars($device['category_name'] ?? '-') ?>
                </td>
                <td><?= htmlspecialchars($device['brand'] . ' ' . $device['model']) ?></td>
                <td><small><?= htmlspecialchars($device['serial_number'] ?? '-') ?></small></td>
                <td>
                    <span class="badge badge-status <?= $statusLabels[$device['status']][1] ?? '' ?>">
                        <?= $statusLabels[$device['status']][0] ?? $device['status'] ?>
                    </span>
                </td>
                <td>
                    <?php if ($device['assigned_to']): ?>
                        <a href="employee_detail.php?id=<?= $device['employee_no'] ?>">
                            <?= htmlspecialchars($device['assigned_to']) ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td class="action-buttons">
                    <a href="device_detail.php?id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-info" title="Detay">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="device_edit.php?id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-warning" title="Duzenle">
                        <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($device['status'] === 'available'): ?>
                    <a href="assignment_add.php?device_id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-success" title="Zimmetle">
                        <i class="fas fa-user-plus"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#devicesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
