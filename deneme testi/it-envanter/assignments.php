<?php
$pageTitle = "Zimmetler - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

// Aktif zimmetleri çek
$assignments = $db->fetchAll("
    SELECT
        a.*,
        d.asset_tag, d.name as device_name, d.brand, d.model,
        dc.name as category_name, dc.icon as category_icon,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        e.employee_no,
        dep.name as department
    FROM assignments a
    JOIN devices d ON a.device_id = d.id
    LEFT JOIN device_categories dc ON d.category_id = dc.id
    JOIN employees e ON a.employee_id = e.id
    LEFT JOIN departments dep ON e.department_id = dep.id
    ORDER BY a.assigned_date DESC
");
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-clipboard-list me-2"></i>Zimmetler</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Zimmetler</li>
            </ol>
        </nav>
    </div>
    <a href="assignment_add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Yeni Zimmet
    </a>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-primary me-3">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <p class="stat-label">Toplam Aktif Zimmet</p>
                    <h3 class="stat-number"><?= count($assignments) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assignments Table -->
<div class="table-container">
    <table id="assignmentsTable" class="table table-hover">
        <thead>
            <tr>
                <th>Cihaz</th>
                <th>Kategori</th>
                <th>Calisan</th>
                <th>Departman</th>
                <th>Zimmet Tarihi</th>
                <th>Notlar</th>
                <th>Islemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $a): ?>
            <tr>
                <td>
                    <a href="device_detail.php?id=<?= $a['device_id'] ?>">
                        <strong><?= htmlspecialchars($a['asset_tag']) ?></strong>
                    </a><br>
                    <small class="text-muted"><?= htmlspecialchars($a['brand'] . ' ' . $a['model']) ?></small>
                </td>
                <td>
                    <i class="fas <?= htmlspecialchars($a['category_icon'] ?? 'fa-box') ?> me-1"></i>
                    <?= htmlspecialchars($a['category_name'] ?? '-') ?>
                </td>
                <td>
                    <a href="employee_detail.php?id=<?= $a['employee_id'] ?>">
                        <?= htmlspecialchars($a['employee_name']) ?>
                    </a><br>
                    <small class="text-muted"><?= htmlspecialchars($a['employee_no']) ?></small>
                </td>
                <td><?= htmlspecialchars($a['department'] ?? '-') ?></td>
                <td><?= date('d.m.Y', strtotime($a['assigned_date'])) ?></td>
                <td>
                    <?php if ($a['notes']): ?>
                        <span title="<?= htmlspecialchars($a['notes']) ?>">
                            <?= htmlspecialchars(substr($a['notes'], 0, 30)) ?>...
                        </span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="action-buttons">
                    <a href="assignment_return.php?device_id=<?= $a['device_id'] ?>" class="btn btn-sm btn-outline-info" title="Iade Al">
                        <i class="fas fa-undo"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#assignmentsTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        pageLength: 25,
        order: [[4, 'desc']]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
