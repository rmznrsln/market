<?php
/**
 * Dil Seçim Ekranı - Hasta Formu
 * Tablet için optimize edilmiş hasta karşılama ekranı
 */

require_once __DIR__ . '/includes/helpers.php';

// Dil seçildiyse form sayfasına yönlendir
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    redirect('form.php');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Op. Dr. Feridun Elmas - Hasta Bilgi Formu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0e3055;
            --secondary-color: #65bdc2;
            --accent-color: #f8f9fa;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4a7a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }

        .welcome-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            padding: 50px;
            text-align: center;
        }

        .logo-section {
            margin-bottom: 40px;
        }

        .logo-section img {
            max-width: 200px;
            height: auto;
        }

        .logo-section h1 {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 600;
            margin-top: 20px;
        }

        .welcome-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 300;
        }

        .welcome-subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 50px;
        }

        .language-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .language-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 25px 30px;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            background: white;
            color: #333;
            font-size: 20px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .language-btn:hover {
            border-color: var(--secondary-color);
            background: var(--secondary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(101, 189, 194, 0.3);
        }

        .language-btn .flag {
            font-size: 36px;
        }

        .language-btn .lang-name {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .language-btn .lang-native {
            font-size: 20px;
            font-weight: 600;
        }

        .language-btn .lang-english {
            font-size: 14px;
            opacity: 0.7;
        }

        .footer-note {
            margin-top: 50px;
            color: #999;
            font-size: 14px;
        }

        .footer-note i {
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .welcome-container {
                padding: 30px;
            }

            .language-grid {
                grid-template-columns: 1fr;
            }

            .welcome-title {
                font-size: 26px;
            }

            .language-btn {
                padding: 20px;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-container {
            animation: fadeInUp 0.6s ease-out;
        }

        .language-btn {
            animation: fadeInUp 0.6s ease-out;
        }

        .language-btn:nth-child(1) { animation-delay: 0.1s; }
        .language-btn:nth-child(2) { animation-delay: 0.15s; }
        .language-btn:nth-child(3) { animation-delay: 0.2s; }
        .language-btn:nth-child(4) { animation-delay: 0.25s; }
        .language-btn:nth-child(5) { animation-delay: 0.3s; }
        .language-btn:nth-child(6) { animation-delay: 0.35s; }
        .language-btn:nth-child(7) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="logo-section">
            <i class="fas fa-clinic-medical fa-4x" style="color: var(--secondary-color);"></i>
            <h1>Op. Dr. Feridun Elmas</h1>
            <p style="color: var(--secondary-color); font-size: 16px;">Plastik ve Estetik Cerrahi Kliniği</p>
        </div>

        <h2 class="welcome-title">Hoş Geldiniz / Welcome</h2>
        <p class="welcome-subtitle">Lütfen dilinizi seçiniz / Please select your language</p>

        <div class="language-grid">
            <a href="?lang=tr" class="language-btn">
                <span class="flag">🇹🇷</span>
                <span class="lang-name">
                    <span class="lang-native">Türkçe</span>
                    <span class="lang-english">Turkish</span>
                </span>
            </a>

            <a href="?lang=en" class="language-btn">
                <span class="flag">🇬🇧</span>
                <span class="lang-name">
                    <span class="lang-native">English</span>
                    <span class="lang-english">English</span>
                </span>
            </a>

            <a href="?lang=ru" class="language-btn">
                <span class="flag">🇷🇺</span>
                <span class="lang-name">
                    <span class="lang-native">Русский</span>
                    <span class="lang-english">Russian</span>
                </span>
            </a>

            <a href="?lang=ar" class="language-btn">
                <span class="flag">🇸🇦</span>
                <span class="lang-name">
                    <span class="lang-native">العربية</span>
                    <span class="lang-english">Arabic</span>
                </span>
            </a>

            <a href="?lang=de" class="language-btn">
                <span class="flag">🇩🇪</span>
                <span class="lang-name">
                    <span class="lang-native">Deutsch</span>
                    <span class="lang-english">German</span>
                </span>
            </a>

            <a href="?lang=fr" class="language-btn">
                <span class="flag">🇫🇷</span>
                <span class="lang-name">
                    <span class="lang-native">Français</span>
                    <span class="lang-english">French</span>
                </span>
            </a>

            <a href="?lang=it" class="language-btn">
                <span class="flag">🇮🇹</span>
                <span class="lang-name">
                    <span class="lang-native">Italiano</span>
                    <span class="lang-english">Italian</span>
                </span>
            </a>
        </div>

        <p class="footer-note">
            <i class="fas fa-lock"></i> Bilgileriniz güvenle saklanmaktadır
        </p>
    </div>
</body>
</html>
