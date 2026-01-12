<?php
/**
 * Admin Giriş Sayfası
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

// Zaten giriş yapmışsa panele yönlendir
if (isLoggedIn()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        try {
            $db = Database::getInstance();
            $user = $db->fetch(
                "SELECT * FROM users WHERE username = ? AND is_active = 1",
                [$username]
            );

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                redirect('index.php');
            } else {
                $error = 'Kullanıcı adı veya şifre hatalı.';
            }
        } catch (Exception $e) {
            $error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Klinik Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0e3055;
            --secondary-color: #65bdc2;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4a7a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
        }

        .login-header {
            background: var(--secondary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .login-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 24px;
            margin: 0;
        }

        .login-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
            font-size: 16px;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(101, 189, 194, 0.2);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #1a4a7a;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 10px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }

        .back-link:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-md"></i>
            <h1>Klinik Yönetim Paneli</h1>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label">Kullanıcı Adı</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Kullanıcı adınızı girin" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Şifre</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Şifrenizi girin" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </button>
            </form>

            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
            </a>
        </div>
    </div>
</body>
</html>
