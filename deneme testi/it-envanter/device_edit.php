<?php
$pageTitle = "Cihaz Düzenle - IT Envanter Yönetim Sistemi";
require_once 'includes/header.php';

$deviceId = $_GET['id'] ?? 0;
$device = $db->fetch("SELECT * FROM devices WHERE id = ?", [$deviceId]);

if (!$device) {
    echo '<div class="alert alert-danger">Cihaz bulunamadi!</div>';
    require_once 'includes/footer.php';
    exit;
}

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
        $db->update('devices', $data, 'id = ?', [$deviceId]);
        $message = 'Cihaz basariyla guncellendi!';
        $messageType = 'success';
        $device = array_merge($device, $data);
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i>Cihaz Duzenle</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="devices.php">Cihazlar</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($device['asset_tag']) ?></li>
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
                               value="<?= htmlspecialchars($device['asset_tag']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cihaz Adi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= htmlspecialchars($device['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Secin...</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $device['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marka</label>
                            <input type="text" name="brand" class="form-control"
                                   value="<?= htmlspecialchars($device['brand'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control"
                                   value="<?= htmlspecialchars($device['model'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seri Numarasi</label>
                        <input type="text" name="serial_number" class="form-control"
                               value="<?= htmlspecialchars($device['serial_number'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="available" <?= $device['status'] == 'available' ? 'selected' : '' ?>>Mevcut</option>
                            <option value="assigned" <?= $device['status'] == 'assigned' ? 'selected' : '' ?>>Zimmetli</option>
                            <option value="maintenance" <?= $device['status'] == 'maintenance' ? 'selected' : '' ?>>Bakimda</option>
                            <option value="retired" <?= $device['status'] == 'retired' ? 'selected' : '' ?>>Emekli</option>
                            <option value="lost" <?= $device['status'] == 'lost' ? 'selected' : '' ?>>Kayip</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3">Satin Alma Bilgileri</h5>

                    <div class="mb-3">
                        <label class="form-label">Satin Alma Tarihi</label>
                        <input type="date" name="purchase_date" class="form-control"
                               value="<?= $device['purchase_date'] ?? '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Satin Alma Fiyati (TL)</label>
                        <input type="number" name="purchase_price" class="form-control" step="0.01" min="0"
                               value="<?= $device['purchase_price'] ?? '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Garanti Bitis Tarihi</label>
                        <input type="date" name="warranty_end_date" class="form-control"
                               value="<?= $device['warranty_end_date'] ?? '' ?>">
                    </div>

                    <h5 class="mb-3 mt-4">Ek Bilgiler</h5>

                    <div class="mb-3">
                        <label class="form-label">Teknik Ozellikler</label>
                        <textarea name="specifications" class="form-control" rows="3"><?= htmlspecialchars($device['specifications'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($device['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="device_detail.php?id=<?= $deviceId ?>" class="btn btn-secondary">
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
