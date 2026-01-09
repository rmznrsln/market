<?php
$pageTitle = "Arama Sonuçları - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$query = trim($_GET['q'] ?? '');

$devices = [];
$employees = [];

if ($query && strlen($query) >= 2) {
    $searchTerm = "%{$query}%";

    // Cihaz ara
    $devices = $db->fetchAll("
        SELECT d.*, dc.name as category_name
        FROM devices d
        LEFT JOIN device_categories dc ON d.category_id = dc.id
        WHERE d.asset_tag LIKE ? OR d.name LIKE ? OR d.serial_number LIKE ?
              OR d.brand LIKE ? OR d.model LIKE ?
        LIMIT 20
    ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);

    // Çalışan ara
    $employees = $db->fetchAll("
        SELECT e.*, d.name as department_name
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE e.employee_no LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?
              OR e.email LIKE ? OR CONCAT(e.first_name, ' ', e.last_name) LIKE ?
        LIMIT 20
    ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$statusLabels = [
    'available' => ['Mevcut', 'status-available'],
    'assigned' => ['Zimmetli', 'status-assigned'],
    'maintenance' => ['Bakimda', 'status-maintenance'],
    'retired' => ['Emekli', 'status-retired'],
    'lost' => ['Kayip', 'status-lost'],
    'active' => ['Aktif', 'status-active'],
    'inactive' => ['Pasif', 'status-inactive'],
    'on_leave' => ['Izinde', 'status-on_leave']
];
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-search me-2"></i>Arama Sonuclari</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Arama</li>
        </ol>
    </nav>
</div>

<!-- Search Form -->
<div class="card content-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="q" class="form-control form-control-lg"
                       placeholder="Cihaz, calisan veya seri no ara..."
                       value="<?= htmlspecialchars($query) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-search me-1"></i> Ara
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($query): ?>
    <?php if (empty($devices) && empty($employees)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            "<strong><?= htmlspecialchars($query) ?></strong>" icin sonuc bulunamadi.
        </div>
    <?php else: ?>
        <!-- Cihaz Sonuçları -->
        <?php if (!empty($devices)): ?>
        <div class="card content-card mb-4">
            <div class="card-header">
                <i class="fas fa-laptop me-2"></i>Cihazlar
                <span class="badge bg-primary ms-2"><?= count($devices) ?> sonuc</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Demirbas No</th>
                                <th>Cihaz</th>
                                <th>Kategori</th>
                                <th>Marka/Model</th>
                                <th>Durum</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($device['asset_tag']) ?></strong></td>
                                <td><?= htmlspecialchars($device['name']) ?></td>
                                <td><?= htmlspecialchars($device['category_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($device['brand'] . ' ' . $device['model']) ?></td>
                                <td>
                                    <span class="badge badge-status <?= $statusLabels[$device['status']][1] ?? '' ?>">
                                        <?= $statusLabels[$device['status']][0] ?? $device['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="device_detail.php?id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-info">
                                        Detay
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Çalışan Sonuçları -->
        <?php if (!empty($employees)): ?>
        <div class="card content-card">
            <div class="card-header">
                <i class="fas fa-users me-2"></i>Calisanlar
                <span class="badge bg-primary ms-2"><?= count($employees) ?> sonuc</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Sicil No</th>
                                <th>Ad Soyad</th>
                                <th>Departman</th>
                                <th>E-posta</th>
                                <th>Durum</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($emp['employee_no']) ?></strong></td>
                                <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                                <td><?= htmlspecialchars($emp['department_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($emp['email']) ?></td>
                                <td>
                                    <span class="badge badge-status <?= $statusLabels[$emp['status']][1] ?? '' ?>">
                                        <?= $statusLabels[$emp['status']][0] ?? $emp['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="employee_detail.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-info">
                                        Detay
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
<?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-search fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">Arama yapmak icin yukari alana bir terim girin</h4>
        <p class="text-muted">Cihaz adi, demirbas no, seri no, calisan adi veya sicil no ile arama yapabilirsiniz.</p>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
