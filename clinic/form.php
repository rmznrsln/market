<?php
/**
 * Hasta Bilgi Formu
 * Tablet için optimize edilmiş form sayfası
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';

$lang = getLanguage();
$translations = loadLanguage($lang);
$direction = getDirection();
$isRTL = isRTL();

// Form gönderildi mi?
$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        try {
            $db = Database::getInstance();

            $data = [
                'first_name' => sanitize($_POST['first_name'] ?? ''),
                'last_name' => sanitize($_POST['last_name'] ?? ''),
                'address' => sanitize($_POST['address'] ?? ''),
                'phone' => sanitize($_POST['phone'] ?? ''),
                'email' => sanitize($_POST['email'] ?? ''),
                'nationality' => sanitize($_POST['nationality'] ?? ''),
                'birth_date' => $_POST['birth_date'] ?? null,
                'gender' => $_POST['gender'] ?? 'not_specified',
                'chronic_diseases' => sanitize($_POST['chronic_diseases'] ?? ''),
                'smoking' => $_POST['smoking'] ?? 'no',
                'medications' => sanitize($_POST['medications'] ?? ''),
                'past_surgeries' => sanitize($_POST['past_surgeries'] ?? ''),
                'how_found' => $_POST['how_found'] ?? 'other',
                'additional_notes' => sanitize($_POST['additional_notes'] ?? ''),
                'language' => $lang,
                'status' => 'waiting'
            ];

            $db->insert('patients', $data);
            $success = true;
        } catch (Exception $e) {
            $error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

// Çeviri fonksiyonu için kısayol
function t($key) {
    return __($key);
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $direction ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= t('patient_form') ?> - <?= t('app_name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <?php if ($isRTL): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <?php endif; ?>
    <style>
        :root {
            --primary-color: #0e3055;
            --secondary-color: #65bdc2;
            --accent-color: #f8f9fa;
            --border-radius: 12px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4a7a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .form-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 10px 0;
        }

        .form-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .form-body {
            padding: 30px;
        }

        .section-title {
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

        .section-title i {
            color: var(--secondary-color);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: var(--border-radius);
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(101, 189, 194, 0.2);
        }

        .form-control::placeholder {
            color: #aaa;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #4aa8ad 100%);
            border: none;
            color: white;
            padding: 16px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #4aa8ad 0%, var(--secondary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(101, 189, 194, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link:hover {
            color: var(--secondary-color);
        }

        /* Success Screen */
        .success-screen {
            text-align: center;
            padding: 60px 30px;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #4aa8ad 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        .success-icon i {
            font-size: 50px;
            color: white;
        }

        .success-title {
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .success-message {
            color: #666;
            font-size: 16px;
            max-width: 400px;
            margin: 0 auto 30px;
        }

        .btn-new-form {
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border-radius: var(--border-radius);
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-new-form:hover {
            background: #1a4a7a;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-body {
                padding: 20px;
            }

            .form-header {
                padding: 20px;
            }

            .form-header h1 {
                font-size: 20px;
            }
        }

        /* Radio/Checkbox styling */
        .form-check {
            padding: 12px 16px;
            margin-bottom: 8px;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .form-check:hover {
            border-color: var(--secondary-color);
            background: rgba(101, 189, 194, 0.05);
        }

        .form-check-input:checked ~ .form-check-label {
            color: var(--primary-color);
            font-weight: 500;
        }

        .form-check-input:checked {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            animation: fadeIn 0.5s ease-out;
        }

        .required-star {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if ($success): ?>
        <!-- Başarı Ekranı -->
        <div class="success-screen">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="success-title"><?= t('thank_you') ?></h2>
            <p class="success-message"><?= t('form_success') ?></p>
            <a href="index.php" class="btn-new-form">
                <i class="fas fa-home"></i> <?= t('back') ?>
            </a>
        </div>
        <?php else: ?>
        <!-- Form Header -->
        <div class="form-header">
            <h1><i class="fas fa-clipboard-list"></i> <?= t('patient_form') ?></h1>
            <p><?= t('welcome_message') ?></p>
        </div>

        <!-- Form Body -->
        <div class="form-body">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-<?= $isRTL ? 'right' : 'left' ?>"></i> <?= t('back') ?>
            </a>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="patientForm">
                <?= csrf_field() ?>

                <!-- Kişisel Bilgiler -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> <?= t('personal_info') ?>
                    </h3>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= t('first_name') ?> <span class="required-star">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="<?= t('placeholder_name') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= t('last_name') ?> <span class="required-star">*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="<?= t('placeholder_surname') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= t('birth_date') ?></label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= t('nationality') ?></label>
                            <input type="text" name="nationality" class="form-control" placeholder="<?= t('placeholder_nationality') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t('gender') ?></label>
                        <select name="gender" class="form-select">
                            <option value="not_specified"><?= t('please_select') ?></option>
                            <option value="male"><?= t('gender_male') ?></option>
                            <option value="female"><?= t('gender_female') ?></option>
                            <option value="other"><?= t('gender_other') ?></option>
                            <option value="not_specified"><?= t('gender_not_specified') ?></option>
                        </select>
                    </div>
                </div>

                <!-- İletişim Bilgileri -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-address-card"></i> <?= t('contact_info') ?>
                    </h3>

                    <div class="mb-3">
                        <label class="form-label"><?= t('address') ?></label>
                        <textarea name="address" class="form-control" rows="2" placeholder="<?= t('placeholder_address') ?>"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= t('phone') ?> <span class="required-star">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="<?= t('placeholder_phone') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= t('email') ?></label>
                            <input type="email" name="email" class="form-control" placeholder="<?= t('placeholder_email') ?>">
                        </div>
                    </div>
                </div>

                <!-- Sağlık Bilgileri -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-heartbeat"></i> <?= t('health_info') ?>
                    </h3>

                    <div class="mb-3">
                        <label class="form-label"><?= t('chronic_diseases') ?></label>
                        <textarea name="chronic_diseases" class="form-control" rows="2" placeholder="<?= t('placeholder_chronic') ?>"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t('smoking') ?></label>
                        <select name="smoking" class="form-select">
                            <option value="no"><?= t('smoking_no') ?></option>
                            <option value="yes"><?= t('smoking_yes') ?></option>
                            <option value="quit"><?= t('smoking_quit') ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t('medications') ?></label>
                        <textarea name="medications" class="form-control" rows="2" placeholder="<?= t('placeholder_medications') ?>"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t('past_surgeries') ?></label>
                        <textarea name="past_surgeries" class="form-control" rows="2" placeholder="<?= t('placeholder_surgeries') ?>"></textarea>
                    </div>
                </div>

                <!-- Ek Bilgiler -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i> <?= t('additional_notes') ?>
                    </h3>

                    <div class="mb-3">
                        <label class="form-label"><?= t('how_found') ?></label>
                        <select name="how_found" class="form-select">
                            <option value="other"><?= t('please_select') ?></option>
                            <option value="social_media"><?= t('how_social_media') ?></option>
                            <option value="website"><?= t('how_website') ?></option>
                            <option value="reference"><?= t('how_reference') ?></option>
                            <option value="recommendation"><?= t('how_recommendation') ?></option>
                            <option value="other"><?= t('how_other') ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t('additional_notes') ?></label>
                        <textarea name="additional_notes" class="form-control" rows="3" placeholder="<?= t('placeholder_notes') ?>"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> <?= t('submit') ?>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
