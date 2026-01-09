<?php
$pageTitle = "Yeni Cihaz Ekle - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$categories = $db->fetchAll("SELECT * FROM device_categories ORDER BY name");
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'asset_tag' => trim($_POST['asset_tag']),
        'name' => trim($_POST['name']),
        'category_id' => $_POST['category_id'] ?: null,
        'brand' => trim($_POST['brand']),
        'model' => trim($_POST['model']),
        'serial_number' => trim($_POST['serial_number']) ?: null,
        'purchase_date' => $_POST['purchase_date'] ?: null,
        'purchase_price' => $_POST['purchase_price'] ?: null,
        'warranty_end_date' => $_POST['warranty_end_date'] ?: null,
        'status' => $_POST['status'],
        'specifications' => trim($_POST['specifications']),
        'notes' => trim($_POST['notes'])
    ];

    try {
        $db->insert('devices', $data);
        header('Location: devices.php?msg=added');
        exit;
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-plus-circle me-2"></i>Yeni Cihaz Ekle</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="devices.php">Cihazlar</a></li>
            <li class="breadcrumb-item active">Yeni Cihaz</li>
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
                    <h5 class="mb-3">Temel Bilgiler</h5>

                    <div class="mb-3">
                        <label class="form-label">Demirbas No <span class="text-danger">*</span></label>
                        <input type="text" name="asset_tag" class="form-control" required
                               placeholder="Ornek: IT-LAP-001">
                        <div class="invalid-feedback">Demirbas no zorunludur.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cihaz Adi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="Ornek: Dell Latitude 5520">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Secin...</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marka</label>
                            <input type="text" name="brand" class="form-control" placeholder="Ornek: Dell">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" placeholder="Ornek: Latitude 5520">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seri Numarasi</label>
                        <input type="text" name="serial_number" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="available">Mevcut</option>
                            <option value="maintenance">Bakimda</option>
                            <option value="retired">Emekli</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3">Satin Alma Bilgileri</h5>

                    <div class="mb-3">
                        <label class="form-label">Satin Alma Tarihi</label>
                        <input type="date" name="purchase_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Satin Alma Fiyati (TL)</label>
                        <input type="number" name="purchase_price" class="form-control" step="0.01" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Garanti Bitis Tarihi</label>
                        <input type="date" name="warranty_end_date" class="form-control">
                    </div>

                    <h5 class="mb-3 mt-4">Ek Bilgiler</h5>

                    <div class="mb-3">
                        <label class="form-label">Teknik Ozellikler</label>
                        <textarea name="specifications" class="form-control" rows="3"
                                  placeholder="Ornek: Intel i7-1165G7, 16GB RAM, 512GB SSD"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="devices.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Geri
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
