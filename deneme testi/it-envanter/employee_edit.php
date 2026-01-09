<?php
$pageTitle = "Çalışan Düzenle - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$employeeId = $_GET['id'] ?? 0;
$employee = $db->fetch("SELECT * FROM employees WHERE id = ?", [$employeeId]);

if (!$employee) {
    echo '<div class="alert alert-danger">Calisan bulunamadi!</div>';
    require_once 'includes/footer.php';
    exit;
}

$departments = $db->fetchAll("SELECT * FROM departments ORDER BY name");
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'employee_no' => trim($_POST['employee_no']),
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']) ?: null,
        'department_id' => $_POST['department_id'] ?: null,
        'position' => trim($_POST['position']) ?: null,
        'hire_date' => $_POST['hire_date'] ?: null,
        'status' => $_POST['status']
    ];

    try {
        $db->update('employees', $data, 'id = ?', [$employeeId]);
        $message = 'Calisan basariyla guncellendi!';
        $messageType = 'success';
        $employee = array_merge($employee, $data);
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-user-edit me-2"></i>Calisan Duzenle</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="employees.php">Calisanlar</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($employee['employee_no']) ?></li>
        </ol>
    </nav>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card content-card">
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">Kisisel Bilgiler</h5>

                    <div class="mb-3">
                        <label class="form-label">Sicil No <span class="text-danger">*</span></label>
                        <input type="text" name="employee_no" class="form-control" required
                               value="<?= htmlspecialchars($employee['employee_no']) ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ad <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required
                                   value="<?= htmlspecialchars($employee['first_name']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required
                                   value="<?= htmlspecialchars($employee['last_name']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-posta <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= htmlspecialchars($employee['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3">Is Bilgileri</h5>

                    <div class="mb-3">
                        <label class="form-label">Departman</label>
                        <select name="department_id" class="form-select">
                            <option value="">Secin...</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= $employee['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pozisyon</label>
                        <input type="text" name="position" class="form-control"
                               value="<?= htmlspecialchars($employee['position'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ise Giris Tarihi</label>
                        <input type="date" name="hire_date" class="form-control"
                               value="<?= $employee['hire_date'] ?? '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $employee['status'] == 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= $employee['status'] == 'inactive' ? 'selected' : '' ?>>Pasif</option>
                            <option value="on_leave" <?= $employee['status'] == 'on_leave' ? 'selected' : '' ?>>Izinde</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="employee_detail.php?id=<?= $employeeId ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Geri
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guncelle
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
