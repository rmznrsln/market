<?php
$pageTitle = "Zimmet İadesi - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$deviceId = $_GET['device_id'] ?? 0;

// Mevcut zimmet bilgisini çek
$assignment = $db->fetch("
    SELECT a.*, d.asset_tag, d.name as device_name, d.brand, d.model,
           CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_no,
           dep.name as department
    FROM assignments a
    JOIN devices d ON a.device_id = d.id
    JOIN employees e ON a.employee_id = e.id
    LEFT JOIN departments dep ON e.department_id = dep.id
    WHERE a.device_id = ?
", [$deviceId]);

if (!$assignment) {
    echo '<div class="alert alert-danger">Zimmet bulunamadi!</div>';
    require_once 'includes/footer.php';
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $returnCondition = $_POST['return_condition'];
    $notes = trim($_POST['notes']);
    $returnedTo = trim($_POST['returned_to']);

    try {
        // Geçmişe kaydet
        $db->insert('assignment_history', [
            'device_id' => $assignment['device_id'],
            'employee_id' => $assignment['employee_id'],
            'assigned_date' => $assignment['assigned_date'],
            'returned_date' => date('Y-m-d'),
            'return_condition' => $returnCondition,
            'notes' => $notes ?: $assignment['notes'],
            'assigned_by' => $assignment['assigned_by'],
            'returned_to' => $returnedTo ?: null
        ]);

        // Aktif zimmeti sil
        $db->delete('assignments', 'device_id = ?', [$deviceId]);

        // Cihaz durumunu güncelle
        $newStatus = $returnCondition === 'needs_repair' ? 'maintenance' : 'available';
        $db->update('devices', ['status' => $newStatus], 'id = ?', [$deviceId]);

        header('Location: assignments.php?msg=returned');
        exit;
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-undo me-2"></i>Zimmet Iadesi</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="assignments.php">Zimmetler</a></li>
            <li class="breadcrumb-item active">Iade</li>
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
                <!-- Mevcut Zimmet Bilgileri -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Mevcut Zimmet Bilgileri</h5>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Cihaz:</strong></p>
                            <p class="mb-2">
                                <?= htmlspecialchars($assignment['asset_tag']) ?> - <?= htmlspecialchars($assignment['device_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($assignment['brand'] . ' ' . $assignment['model']) ?></small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Calisan:</strong></p>
                            <p class="mb-2">
                                <?= htmlspecialchars($assignment['employee_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($assignment['employee_no']) ?> - <?= htmlspecialchars($assignment['department'] ?? '-') ?></small>
                            </p>
                        </div>
                    </div>
                    <p class="mb-0">
                        <strong>Zimmet Tarihi:</strong> <?= date('d.m.Y', strtotime($assignment['assigned_date'])) ?>
                    </p>
                </div>

                <form method="POST" class="mt-4">
                    <div class="mb-4">
                        <label class="form-label">Iade Durumu <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="return_condition"
                                           id="condGood" value="good" checked>
                                    <label class="form-check-label" for="condGood">
                                        <span class="badge bg-success me-1">Iyi</span>
                                        Cihaz iyi durumda
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="return_condition"
                                           id="condDamaged" value="damaged">
                                    <label class="form-check-label" for="condDamaged">
                                        <span class="badge bg-danger me-1">Hasarli</span>
                                        Cihazda hasar var
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="return_condition"
                                           id="condRepair" value="needs_repair">
                                    <label class="form-check-label" for="condRepair">
                                        <span class="badge bg-warning me-1">Tamir</span>
                                        Tamir gerekli
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            * "Tamir gerekli" secilirse cihaz otomatik olarak "Bakimda" durumuna alinir.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Iade Alan</label>
                        <input type="text" name="returned_to" class="form-control"
                               placeholder="Cihazi teslim alan kisi">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notlar</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Iade ile ilgili notlar..."><?= htmlspecialchars($assignment['notes'] ?? '') ?></textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="assignments.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Geri
                        </a>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-undo me-1"></i> Iadeyi Tamamla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card content-card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Islem Ozeti
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Bu islem sonucunda:
                </p>
                <ul class="text-muted small">
                    <li>Aktif zimmet sonlandirilacak</li>
                    <li>Zimmet gecmisine kayit eklenecek</li>
                    <li>Cihaz durumu guncellenecek</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
