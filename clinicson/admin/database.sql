-- Hasta Bilgi Formu Veritabanı
-- MySQL Database Schema

CREATE DATABASE IF NOT EXISTS clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinic_db;

-- Hastalar tablosu
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    nationality VARCHAR(50),
    birth_date DATE,
    gender ENUM('male', 'female', 'other', 'not_specified') DEFAULT 'not_specified',
    chronic_diseases TEXT,
    smoking ENUM('yes', 'no', 'quit') DEFAULT 'no',
    medications TEXT,
    past_surgeries TEXT,
    how_found ENUM('social_media', 'website', 'reference', 'recommendation', 'other') DEFAULT 'other',
    additional_notes TEXT,
    language VARCHAR(5) DEFAULT 'tr',
    status ENUM('waiting', 'in_treatment', 'completed', 'cancelled') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tedavi/Muayene tablosu
CREATE TABLE IF NOT EXISTS treatments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_notes TEXT,
    nurse_notes TEXT,
    diagnosis TEXT,
    treatment_type VARCHAR(200),
    price DECIMAL(10, 2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'TRY',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Tedavi türleri tablosu
CREATE TABLE IF NOT EXISTS treatment_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_tr VARCHAR(200) NOT NULL,
    name_en VARCHAR(200),
    name_ru VARCHAR(200),
    name_ar VARCHAR(200),
    base_price DECIMAL(10, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Örnek tedavi türleri
INSERT INTO treatment_types (name_tr, name_en, name_ru, name_ar, base_price) VALUES
('Rinoplasti', 'Rhinoplasty', 'Ринопластика', 'عملية تجميل الأنف', 50000.00),
('Yüz Germe', 'Facelift', 'Подтяжка лица', 'شد الوجه', 80000.00),
('Liposuction', 'Liposuction', 'Липосакция', 'شفط الدهون', 45000.00),
('Meme Estetiği', 'Breast Surgery', 'Маммопластика', 'جراحة الثدي', 60000.00),
('Karın Germe', 'Tummy Tuck', 'Абдоминопластика', 'شد البطن', 55000.00),
('Botox', 'Botox', 'Ботокс', 'البوتوكس', 5000.00),
('Dolgu', 'Filler', 'Филлеры', 'الفيلر', 4000.00),
('Saç Ekimi', 'Hair Transplant', 'Пересадка волос', 'زراعة الشعر', 35000.00),
('Göz Kapağı Estetiği', 'Blepharoplasty', 'Блефаропластика', 'جراحة الجفون', 25000.00),
('Kulak Estetiği', 'Otoplasty', 'Отопластика', 'تجميل الأذن', 20000.00);

-- Kullanıcılar (Doktor/Hemşire) tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'doctor', 'nurse') DEFAULT 'nurse',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Varsayılan admin kullanıcısı (şifre: admin123)
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin'),
('doktor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Feridun Elmas', 'doctor'),
('hemsire', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hemşire', 'nurse');

-- İstatistikler için view
CREATE OR REPLACE VIEW patient_statistics AS
SELECT
    COUNT(*) as total_patients,
    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_count,
    SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting_count,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
FROM patients;

-- Uyruk bazlı istatistikler için view
CREATE OR REPLACE VIEW nationality_statistics AS
SELECT
    nationality,
    COUNT(*) as patient_count
FROM patients
WHERE nationality IS NOT NULL AND nationality != ''
GROUP BY nationality
ORDER BY patient_count DESC;

-- Tedavi bazlı istatistikler için view
CREATE OR REPLACE VIEW treatment_statistics AS
SELECT
    t.treatment_type,
    COUNT(*) as treatment_count,
    SUM(t.price) as total_revenue,
    AVG(t.price) as avg_price
FROM treatments t
WHERE t.status = 'completed'
GROUP BY t.treatment_type
ORDER BY treatment_count DESC;
