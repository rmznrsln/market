<?php
/**
 * Hasta Detay ve Tedavi Sayfası
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = Database::getInstance();
$user = getCurrentUser();

$patientId = (int)($_GET['id'] ?? 0);
if ($patientId <= 0) {
    redirect('index.php');
}

// Hasta bilgilerini getir
$patient = $db->fetch("SELECT * FROM patients WHERE id = ?", [$patientId]);
if (!$patient) {
    redirect('index.php');
}

// Tedavi bilgilerini getir
$treatment = $db->fetch(
    "SELECT * FROM treatments WHERE patient_id = ? ORDER BY created_at DESC LIMIT 1",
    [$patientId]
);

// Tedavi türlerini getir
$treatmentTypes = $db->fetchAll("SELECT * FROM treatment_types WHERE is_active = 1 ORDER BY name_tr");

// Form gönderimi
$message = null;
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'save_treatment':
            $treatmentData = [
                'patient_id' => $patientId,
                'doctor_notes' => sanitize($_POST['doctor_notes'] ?? ''),
                'nurse_notes' => sanitize($_POST['nurse_notes'] ?? ''),
                'diagnosis' => sanitize($_POST['diagnosis'] ?? ''),
                'treatment_type' => sanitize($_POST['treatment_type'] ?? ''),
                'price' => (float)($_POST['price'] ?? 0),
                'currency' => $_POST['currency'] ?? 'TRY',
                'status' => 'in_progress',
                'started_at' => date('Y-m-d H:i:s')
            ];

            if ($treatment) {
                // Güncelle
                $db->update('treatments', $treatmentData, 'id = ?', [$treatment['id']]);
            } else {
                // Yeni kayıt
                $db->insert('treatments', $treatmentData);
            }

            // Hasta durumunu güncelle
            $db->update('patients', ['status' => 'in_treatment'], 'id = ?', [$patientId]);

            $message = 'Tedavi bilgileri kaydedildi.';

            // Sayfayı yenile
            redirect("patient.php?id=$patientId&saved=1");
            break;

        case 'complete_treatment':
            if ($treatment) {
                $db->update('treatments', [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$treatment['id']]);
            }

            $db->update('patients', ['status' => 'completed'], 'id = ?', [$patientId]);

            $message = 'Tedavi tamamlandı.';
            redirect('index.php');
            break;

        case 'cancel':
            if ($treatment) {
                $db->update('treatments', ['status' => 'cancelled'], 'id = ?', [$treatment['id']]);
            }
            $db->update('patients', ['status' => 'cancelled'], 'id = ?', [$patientId]);
            redirect('index.php');
            break;
    }

    // Verileri yeniden yükle
    $patient = $db->fetch("SELECT * FROM patients WHERE id = ?", [$patientId]);
    $treatment = $db->fetch(
        "SELECT * FROM treatments WHERE patient_id = ? ORDER BY created_at DESC LIMIT 1",
        [$patientId]
    );
}

if (isset($_GET['saved'])) {
    $message = 'Tedavi bilgileri kaydedildi.';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasta Detay - <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0e3055;
            --secondary-color: #65bdc2;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            margin: 0;
            min-height: 100vh;
        }

        /* Sidebar - aynı stiller */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a4a7a 100%);
            color: white;
            padding: 20px 0;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 18px;
            margin: 10px 0 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary-color);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 24px;
            color: var(--primary-color);
            margin: 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--secondary-color);
            color: white;
        }

        /* Cards */
        .info-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--secondary-color);
        }

        /* Patient Header */
        .patient-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .patient-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #4aa8ad 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: 600;
        }

        .patient-details h2 {
            font-size: 24px;
            color: var(--primary-color);
            margin: 0 0 5px;
        }

        .patient-details p {
            color: #666;
            margin: 0;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .info-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .info-item label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }

        .info-item span {
            font-size: 15px;
            color: var(--primary-color);
            font-weight: 500;
        }

        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
            font-size: 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(101, 189, 194, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
        }

        /* Buttons */
        .btn-primary-custom {
            background: var(--secondary-color);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: #4aa8ad;
            color: white;
        }

        .btn-success-custom {
            background: #28a745;
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
        }

        .btn-success-custom:hover {
            background: #218838;
            color: white;
        }

        .btn-danger-custom {
            background: #dc3545;
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
        }

        /* Status Badge */
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-waiting { background: #fff3cd; color: #856404; }
        .status-in_treatment { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }

        /* Price Input */
        .price-input-group {
            display: flex;
            gap: 10px;
        }

        .price-input-group input {
            flex: 1;
        }

        .price-input-group select {
            width: 100px;
        }

        /* Alert */
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h2,
            .sidebar-menu li a span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .patient-header {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Health Info Highlight */
        .health-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 16px;
            border-radius: 0 10px 10px 0;
            margin-bottom: 10px;
        }

        .health-warning label {
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-clinic-medical fa-2x"></i>
            <h2>Klinik Yönetim</h2>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    <span>Panel</span>
                </a>
            </li>
            <li>
                <a href="patients.php">
                    <i class="fas fa-users"></i>
                    <span>Tüm Hastalar</span>
                </a>
            </li>
            <li>
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Raporlar</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Çıkış</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
            <span class="status-badge status-<?= $patient['status'] ?>">
                <?php
                $statusLabels = [
                    'waiting' => 'Bekliyor',
                    'in_treatment' => 'Tedavide',
                    'completed' => 'Tamamlandı',
                    'cancelled' => 'İptal'
                ];
                echo $statusLabels[$patient['status']] ?? $patient['status'];
                ?>
            </span>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Sol Kolon - Hasta Bilgileri -->
            <div class="col-lg-5">
                <!-- Hasta Başlık -->
                <div class="info-card">
                    <div class="patient-header">
                        <div class="patient-avatar">
                            <?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?>
                        </div>
                        <div class="patient-details">
                            <h2><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
                            <p><i class="fas fa-clock"></i> Kayıt: <?= formatDateTime($patient['created_at']) ?></p>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <label>Telefon</label>
                            <span><i class="fas fa-phone"></i> <?= htmlspecialchars($patient['phone']) ?></span>
                        </div>
                        <div class="info-item">
                            <label>E-posta</label>
                            <span><?= htmlspecialchars($patient['email'] ?: '-') ?></span>
                        </div>
                        <div class="info-item">
                            <label>Uyruk</label>
                            <span><i class="fas fa-globe"></i> <?= $patient['nationality'] ? getNationalityText($patient['nationality']) : '-' ?></span>
                        </div>
                        <div class="info-item">
                            <label>Doğum Tarihi</label>
                            <span><?= $patient['birth_date'] ? formatDate($patient['birth_date']) : '-' ?></span>
                        </div>
                        <div class="info-item">
                            <label>Cinsiyet</label>
                            <span><?= getGenderText($patient['gender']) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Nasıl Ulaştı</label>
                            <span><?= getHowFoundText($patient['how_found']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Adres -->
                <?php if ($patient['address']): ?>
                <div class="info-card">
                    <h4 class="card-title"><i class="fas fa-map-marker-alt"></i> Adres</h4>
                    <p style="margin:0;"><?= nl2br(htmlspecialchars($patient['address'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Sağlık Bilgileri -->
                <div class="info-card">
                    <h4 class="card-title"><i class="fas fa-heartbeat"></i> Sağlık Bilgileri</h4>

                    <?php if ($patient['chronic_diseases']): ?>
                    <div class="health-warning">
                        <label><i class="fas fa-exclamation-triangle"></i> Kronik Hastalıklar</label>
                        <p style="margin:0;"><?= nl2br(htmlspecialchars($patient['chronic_diseases'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="info-grid">
                        <div class="info-item">
                            <label>Sigara Kullanımı</label>
                            <span><?= getSmokingText($patient['smoking']) ?></span>
                        </div>
                    </div>

                    <?php if ($patient['medications']): ?>
                    <div class="info-item" style="margin-top:16px;">
                        <label>Kullandığı İlaçlar</label>
                        <span><?= nl2br(htmlspecialchars($patient['medications'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($patient['past_surgeries']): ?>
                    <div class="info-item" style="margin-top:16px;">
                        <label>Geçirdiği Ameliyatlar</label>
                        <span><?= nl2br(htmlspecialchars($patient['past_surgeries'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($patient['additional_notes']): ?>
                    <div class="info-item" style="margin-top:16px;">
                        <label>Ek Notlar (Hasta)</label>
                        <span><?= nl2br(htmlspecialchars($patient['additional_notes'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sağ Kolon - Tedavi -->
            <div class="col-lg-7">
                <div class="info-card">
                    <h4 class="card-title"><i class="fas fa-stethoscope"></i> Tedavi Bilgileri</h4>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="save_treatment">

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Tedavi Türü</label>
                                <select name="treatment_type" class="form-select" id="treatmentType">
                                    <option value="">- Seçiniz -</option>
                                    <?php foreach ($treatmentTypes as $type): ?>
                                    <option value="<?= htmlspecialchars($type['name_tr']) ?>"
                                            data-price="<?= $type['base_price'] ?>"
                                            <?= ($treatment['treatment_type'] ?? '') === $type['name_tr'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type['name_tr']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="other">Diğer</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fiyat</label>
                                <div class="price-input-group">
                                    <input type="number" name="price" class="form-control" id="priceInput"
                                           value="<?= $treatment['price'] ?? '' ?>" step="0.01" placeholder="0.00">
                                    <select name="currency" class="form-select">
                                        <option value="TRY" <?= ($treatment['currency'] ?? 'TRY') === 'TRY' ? 'selected' : '' ?>>₺</option>
                                        <option value="USD" <?= ($treatment['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>$</option>
                                        <option value="EUR" <?= ($treatment['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>€</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teşhis</label>
                            <textarea name="diagnosis" class="form-control" rows="3"
                                      placeholder="Teşhis ve bulgular..."><?= htmlspecialchars($treatment['diagnosis'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user-md"></i> Doktor Notları</label>
                            <?php if ($user['role'] === 'nurse'): ?>
                            <textarea class="form-control" rows="4" disabled
                                      style="background-color: #f8f9fa;"><?= htmlspecialchars($treatment['doctor_notes'] ?? '') ?></textarea>
                            <input type="hidden" name="doctor_notes" value="<?= htmlspecialchars($treatment['doctor_notes'] ?? '') ?>">
                            <small class="text-muted"><i class="fas fa-lock"></i> Doktor notlarını sadece doktorlar düzenleyebilir</small>
                            <?php else: ?>
                            <textarea name="doctor_notes" class="form-control" rows="4"
                                      placeholder="Doktor notları..."><?= htmlspecialchars($treatment['doctor_notes'] ?? '') ?></textarea>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-user-nurse"></i> Hemşire Notları</label>
                            <textarea name="nurse_notes" class="form-control" rows="3"
                                      placeholder="Hemşire notları..."><?= htmlspecialchars($treatment['nurse_notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-3 flex-wrap">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-save"></i> Kaydet
                            </button>

                            <?php if ($patient['status'] !== 'completed'): ?>
                            <button type="submit" name="action" value="complete_treatment" class="btn btn-success-custom"
                                    onclick="return confirm('Tedaviyi tamamlamak istediğinize emin misiniz?')">
                                <i class="fas fa-check"></i> Tedaviyi Tamamla
                            </button>
                            <?php endif; ?>

                            <?php if ($patient['status'] === 'waiting'): ?>
                            <button type="submit" name="action" value="cancel" class="btn btn-danger-custom"
                                    onclick="return confirm('Hastayı iptal etmek istediğinize emin misiniz?')">
                                <i class="fas fa-times"></i> İptal Et
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <?php if ($treatment && $treatment['started_at']): ?>
                <div class="info-card">
                    <h4 class="card-title"><i class="fas fa-history"></i> Tedavi Geçmişi</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Başlangıç</label>
                            <span><?= formatDateTime($treatment['started_at']) ?></span>
                        </div>
                        <?php if ($treatment['completed_at']): ?>
                        <div class="info-item">
                            <label>Tamamlanma</label>
                            <span><?= formatDateTime($treatment['completed_at']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <label>Durum</label>
                            <span><?= getStatusBadge($treatment['status']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tedavi türü seçilince fiyatı otomatik doldur
        document.getElementById('treatmentType').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const price = selected.dataset.price;
            if (price) {
                document.getElementById('priceInput').value = price;
            }
        });
    </script>
</body>
</html>
