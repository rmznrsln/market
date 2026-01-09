-- IT Envanter Yönetim Sistemi Veritabanı
-- Oluşturulma Tarihi: 2026-01-08

DROP DATABASE IF EXISTS it_envanter;
CREATE DATABASE it_envanter CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE it_envanter;

-- Departmanlar Tablosu
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Çalışanlar Tablosu
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_no VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    department_id INT,
    position VARCHAR(100),
    hire_date DATE,
    status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Cihaz Kategorileri Tablosu
CREATE TABLE device_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fa-desktop'
) ENGINE=InnoDB;

-- Cihazlar Tablosu
CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_tag VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    brand VARCHAR(50),
    model VARCHAR(100),
    serial_number VARCHAR(100) UNIQUE,
    purchase_date DATE,
    purchase_price DECIMAL(10,2),
    warranty_end_date DATE,
    status ENUM('available', 'assigned', 'maintenance', 'retired', 'lost') DEFAULT 'available',
    specifications TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES device_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Zimmetler Tablosu (Aktif Zimmetler)
CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    employee_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    expected_return_date DATE,
    notes TEXT,
    assigned_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_device_assignment (device_id)
) ENGINE=InnoDB;

-- Zimmet Geçmişi Tablosu
CREATE TABLE assignment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    employee_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    returned_date DATE,
    return_condition ENUM('good', 'damaged', 'needs_repair') DEFAULT 'good',
    notes TEXT,
    assigned_by VARCHAR(100),
    returned_to VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Kullanıcılar Tablosu (Sistem Kullanıcıları)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'user', 'viewer') DEFAULT 'user',
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- İndeksler
CREATE INDEX idx_devices_status ON devices(status);
CREATE INDEX idx_devices_category ON devices(category_id);
CREATE INDEX idx_employees_department ON employees(department_id);
CREATE INDEX idx_employees_status ON employees(status);
CREATE INDEX idx_assignments_device ON assignments(device_id);
CREATE INDEX idx_assignments_employee ON assignments(employee_id);

-- Görünümler
CREATE VIEW v_device_assignments AS
SELECT
    d.id as device_id,
    d.asset_tag,
    d.name as device_name,
    d.brand,
    d.model,
    d.status as device_status,
    dc.name as category,
    e.id as employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    e.employee_no,
    dep.name as department,
    a.assigned_date,
    a.expected_return_date
FROM devices d
LEFT JOIN device_categories dc ON d.category_id = dc.id
LEFT JOIN assignments a ON d.id = a.device_id
LEFT JOIN employees e ON a.employee_id = e.id
LEFT JOIN departments dep ON e.department_id = dep.id;

-- Dashboard istatistikleri için görünüm
CREATE VIEW v_dashboard_stats AS
SELECT
    (SELECT COUNT(*) FROM devices) as total_devices,
    (SELECT COUNT(*) FROM devices WHERE status = 'available') as available_devices,
    (SELECT COUNT(*) FROM devices WHERE status = 'assigned') as assigned_devices,
    (SELECT COUNT(*) FROM devices WHERE status = 'maintenance') as maintenance_devices,
    (SELECT COUNT(*) FROM employees WHERE status = 'active') as active_employees,
    (SELECT COUNT(*) FROM assignments) as total_assignments;
