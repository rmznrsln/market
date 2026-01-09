<?php
$pageTitle = "Çalışanlar - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

// Filtreler
$departmentFilter = $_GET['department'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Departmanları çek
$departments = $db->fetchAll("SELECT * FROM departments ORDER BY name");

// Çalışanları çek
$sql = "
    SELECT e.*, d.name as department_name,
           (SELECT COUNT(*) FROM assignments a WHERE a.employee_id = e.id) as device_count
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE 1=1
";
$params = [];

if ($departmentFilter) {
    $sql .= " AND e.department_id = ?";
    $params[] = $departmentFilter;
}

if ($statusFilter) {
    $sql .= " AND e.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY e.last_name, e.first_name";
$employees = $db->fetchAll($sql, $params);

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
        <h1><i class="fas fa-users me-2"></i>Calisanlar</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Calisanlar</li>
            </ol>
        </nav>
    </div>
    <a href="employee_add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Yeni Calisan Ekle
    </a>
</div>

<!-- Filters -->
<div class="card content-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Departman</label>
                <select name="department" class="form-select">
                    <option value="">Tum Departmanlar</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $departmentFilter == $dept['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
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
                <a href="employees.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Temizle
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Employees Table -->
<div class="table-container">
    <table id="employeesTable" class="table table-hover">
        <thead>
            <tr>
                <th>Sicil No</th>
                <th>Ad Soyad</th>
                <th>E-posta</th>
                <th>Departman</th>
                <th>Pozisyon</th>
                <th>Durum</th>
                <th>Zimmetli Cihaz</th>
                <th>Islemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $emp): ?>
            <tr>
                <td><strong><?= htmlspecialchars($emp['employee_no']) ?></strong></td>
                <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                <td><a href="mailto:<?= htmlspecialchars($emp['email']) ?>"><?= htmlspecialchars($emp['email']) ?></a></td>
                <td><?= htmlspecialchars($emp['department_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($emp['position'] ?? '-') ?></td>
                <td>
                    <span class="badge badge-status <?= $statusLabels[$emp['status']][1] ?? '' ?>">
                        <?= $statusLabels[$emp['status']][0] ?? $emp['status'] ?>
                    </span>
                </td>
                <td>
                    <span class="badge bg-primary rounded-pill"><?= $emp['device_count'] ?></span>
                </td>
                <td class="action-buttons">
                    <a href="employee_detail.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-info" title="Detay">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="employee_edit.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-warning" title="Duzenle">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="assignment_add.php?employee_id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-success" title="Cihaz Zimmetle">
                        <i class="fas fa-laptop"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#employeesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        pageLength: 25,
        order: [[1, 'asc']]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
