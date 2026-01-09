<?php
$pageTitle = "Yeni Zimmet - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

// Mevcut cihazları çek
$availableDevices = $db->fetchAll("
    SELECT d.*, dc.name as category_name
    FROM devices d
    LEFT JOIN device_categories dc ON d.category_id = dc.id
    WHERE d.status = 'available'
    ORDER BY d.asset_tag
");

// Aktif çalışanları çek
$employees = $db->fetchAll("
    SELECT e.*, d.name as department_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE e.status = 'active'
    ORDER BY e.last_name, e.first_name
");

$message = '';
$messageType = '';

// URL'den gelen ön seçimler
$preselectedDevice = $_GET['device_id'] ?? '';
$preselectedEmployee = $_GET['employee_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceId = $_POST['device_id'];
    $employeeId = $_POST['employee_id'];
    $assignedDate = $_POST['assigned_date'];
    $notes = trim($_POST['notes']);
    $assignedBy = trim($_POST['assigned_by']);

    try {
        // Zimmet kaydı oluştur
        $db->insert('assignments', [
            'device_id' => $deviceId,
            'employee_id' => $employeeId,
            'assigned_date' => $assignedDate,
            'notes' => $notes ?: null,
            'assigned_by' => $assignedBy ?: null
        ]);

        // Cihaz durumunu güncelle
        $db->update('devices', ['status' => 'assigned'], 'id = ?', [$deviceId]);

        header('Location: assignments.php?msg=created');
        exit;
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-clipboard-check me-2"></i>Yeni Zimmet Olustur</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="assignments.php">Zimmetler</a></li>
            <li class="breadcrumb-item active">Yeni Zimmet</li>
        </ol>
    </nav>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card content-card">
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label class="form-label">Cihaz Sec <span class="text-danger">*</span></label>
                        <select name="device_id" id="deviceSelect" class="form-select" required>
                            <option value="">-- Cihaz Secin --</option>
                            <?php foreach ($availableDevices as $device): ?>
                            <option value="<?= $device['id'] ?>"
                                    data-brand="<?= htmlspecialchars($device['brand']) ?>"
                                    data-model="<?= htmlspecialchars($device['model']) ?>"
                                    data-category="<?= htmlspecialchars($device['category_name'] ?? '-') ?>"
                                    <?= $preselectedDevice == $device['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($device['asset_tag'] . ' - ' . $device['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="deviceInfo" class="mt-2 p-3 bg-light rounded" style="display: none;">
                            <small>
                                <strong>Marka:</strong> <span id="deviceBrand"></span> |
                                <strong>Model:</strong> <span id="deviceModel"></span> |
                                <strong>Kategori:</strong> <span id="deviceCategory"></span>
                            </small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Calisan Sec <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employeeSelect" class="form-select" required>
                            <option value="">-- Calisan Secin --</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>"
                                    data-department="<?= htmlspecialchars($emp['department_name'] ?? '-') ?>"
                                    data-position="<?= htmlspecialchars($emp['position'] ?? '-') ?>"
                                    <?= $preselectedEmployee == $emp['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['employee_no'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="employeeInfo" class="mt-2 p-3 bg-light rounded" style="display: none;">
                            <small>
                                <strong>Departman:</strong> <span id="empDepartment"></span> |
                                <strong>Pozisyon:</strong> <span id="empPosition"></span>
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zimmet Tarihi <span class="text-danger">*</span></label>
                            <input type="date" name="assigned_date" class="form-control" required
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zimmetleyen</label>
                            <input type="text" name="assigned_by" class="form-control"
                                   placeholder="Zimmetleyen kisi adi">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notlar</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Zimmetle ilgili notlar..."></textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="assignments.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Geri
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Zimmeti Olustur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card content-card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Bilgi
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Zimmet olusturarak bir cihazi bir calisana teslim edebilirsiniz.
                </p>
                <ul class="text-muted small">
                    <li>Sadece "Mevcut" durumundaki cihazlar zimmetlenebilir</li>
                    <li>Sadece aktif calisanlara zimmet yapilabilir</li>
                    <li>Zimmet yapildiktan sonra cihaz durumu otomatik olarak "Zimmetli" olarak degisir</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('deviceSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('deviceInfo');

    if (this.value) {
        document.getElementById('deviceBrand').textContent = selected.dataset.brand || '-';
        document.getElementById('deviceModel').textContent = selected.dataset.model || '-';
        document.getElementById('deviceCategory').textContent = selected.dataset.category || '-';
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});

document.getElementById('employeeSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('employeeInfo');

    if (this.value) {
        document.getElementById('empDepartment').textContent = selected.dataset.department || '-';
        document.getElementById('empPosition').textContent = selected.dataset.position || '-';
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});

// Sayfa yüklendiğinde ön seçimleri göster
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('deviceSelect').dispatchEvent(new Event('change'));
    document.getElementById('employeeSelect').dispatchEvent(new Event('change'));
});
</script>

<?php require_once 'includes/footer.php'; ?>
