-- IT Envanter Test Verileri
USE it_envanter;

-- Departmanlar
INSERT INTO departments (name, description) VALUES
('Bilgi Teknolojileri', 'IT departmanı - Yazılım ve donanım yönetimi'),
('İnsan Kaynakları', 'İK süreçleri ve personel yönetimi'),
('Finans', 'Muhasebe ve finans operasyonları'),
('Pazarlama', 'Pazarlama ve reklam faaliyetleri'),
('Satış', 'Satış ve müşteri ilişkileri'),
('Üretim', 'Üretim ve operasyon yönetimi'),
('Ar-Ge', 'Araştırma ve geliştirme'),
('Lojistik', 'Depo ve lojistik operasyonları'),
('Kalite Kontrol', 'Kalite güvence ve kontrol'),
('Yönetim', 'Üst yönetim ve idari işler');

-- Cihaz Kategorileri
INSERT INTO device_categories (name, description, icon) VALUES
('Dizüstü Bilgisayar', 'Laptop ve notebook cihazlar', 'fa-laptop'),
('Masaüstü Bilgisayar', 'Desktop PC ve workstation', 'fa-desktop'),
('Monitör', 'Ekran ve display cihazları', 'fa-tv'),
('Yazıcı', 'Yazıcı ve tarayıcı cihazları', 'fa-print'),
('Telefon', 'Cep telefonu ve akıllı telefonlar', 'fa-mobile'),
('Tablet', 'Tablet bilgisayarlar', 'fa-tablet'),
('Ağ Cihazı', 'Router, switch ve modemler', 'fa-network-wired'),
('Sunucu', 'Server ve storage sistemleri', 'fa-server'),
('Projeksiyon', 'Projeksiyon ve sunum cihazları', 'fa-video'),
('Diğer', 'Diğer IT ekipmanları', 'fa-box');

-- Çalışanlar (30 çalışan)
INSERT INTO employees (employee_no, first_name, last_name, email, phone, department_id, position, hire_date, status) VALUES
('EMP001', 'Ahmet', 'Yılmaz', 'ahmet.yilmaz@sirket.com', '0532-111-2233', 1, 'IT Müdürü', '2020-01-15', 'active'),
('EMP002', 'Mehmet', 'Kaya', 'mehmet.kaya@sirket.com', '0533-222-3344', 1, 'Sistem Yöneticisi', '2020-03-20', 'active'),
('EMP003', 'Fatma', 'Demir', 'fatma.demir@sirket.com', '0534-333-4455', 1, 'Yazılım Geliştirici', '2021-06-01', 'active'),
('EMP004', 'Ayşe', 'Çelik', 'ayse.celik@sirket.com', '0535-444-5566', 2, 'İK Müdürü', '2019-08-10', 'active'),
('EMP005', 'Ali', 'Öztürk', 'ali.ozturk@sirket.com', '0536-555-6677', 2, 'İK Uzmanı', '2021-02-15', 'active'),
('EMP006', 'Zeynep', 'Arslan', 'zeynep.arslan@sirket.com', '0537-666-7788', 3, 'Finans Müdürü', '2018-11-01', 'active'),
('EMP007', 'Mustafa', 'Şahin', 'mustafa.sahin@sirket.com', '0538-777-8899', 3, 'Muhasebeci', '2020-07-20', 'active'),
('EMP008', 'Elif', 'Yıldız', 'elif.yildiz@sirket.com', '0539-888-9900', 4, 'Pazarlama Müdürü', '2019-04-05', 'active'),
('EMP009', 'Hasan', 'Aydın', 'hasan.aydin@sirket.com', '0540-999-0011', 4, 'Dijital Pazarlama Uzmanı', '2021-09-12', 'active'),
('EMP010', 'Emine', 'Koç', 'emine.koc@sirket.com', '0541-000-1122', 5, 'Satış Müdürü', '2018-05-18', 'active'),
('EMP011', 'Osman', 'Kurt', 'osman.kurt@sirket.com', '0542-111-2234', 5, 'Satış Temsilcisi', '2022-01-10', 'active'),
('EMP012', 'Hatice', 'Özdemir', 'hatice.ozdemir@sirket.com', '0543-222-3345', 5, 'Satış Temsilcisi', '2022-03-25', 'active'),
('EMP013', 'İbrahim', 'Aksoy', 'ibrahim.aksoy@sirket.com', '0544-333-4456', 6, 'Üretim Müdürü', '2017-09-01', 'active'),
('EMP014', 'Merve', 'Polat', 'merve.polat@sirket.com', '0545-444-5567', 6, 'Üretim Mühendisi', '2020-11-15', 'active'),
('EMP015', 'Burak', 'Erdoğan', 'burak.erdogan@sirket.com', '0546-555-6678', 7, 'Ar-Ge Müdürü', '2019-02-28', 'active'),
('EMP016', 'Seda', 'Güneş', 'seda.gunes@sirket.com', '0547-666-7789', 7, 'Ar-Ge Mühendisi', '2021-04-10', 'active'),
('EMP017', 'Emre', 'Kılıç', 'emre.kilic@sirket.com', '0548-777-8890', 7, 'Ar-Ge Mühendisi', '2021-08-05', 'active'),
('EMP018', 'Gizem', 'Acar', 'gizem.acar@sirket.com', '0549-888-9901', 8, 'Lojistik Müdürü', '2018-12-20', 'active'),
('EMP019', 'Serkan', 'Korkmaz', 'serkan.korkmaz@sirket.com', '0550-999-0012', 8, 'Depo Sorumlusu', '2020-06-08', 'active'),
('EMP020', 'Deniz', 'Yalçın', 'deniz.yalcin@sirket.com', '0551-000-1123', 9, 'Kalite Müdürü', '2019-07-15', 'active'),
('EMP021', 'Canan', 'Aslan', 'canan.aslan@sirket.com', '0552-111-2235', 9, 'Kalite Uzmanı', '2021-05-20', 'active'),
('EMP022', 'Tolga', 'Özer', 'tolga.ozer@sirket.com', '0553-222-3346', 10, 'Genel Müdür', '2015-01-01', 'active'),
('EMP023', 'Pınar', 'Çetin', 'pinar.cetin@sirket.com', '0554-333-4457', 10, 'Genel Müdür Yardımcısı', '2016-06-15', 'active'),
('EMP024', 'Volkan', 'Doğan', 'volkan.dogan@sirket.com', '0555-444-5568', 1, 'Network Uzmanı', '2020-09-01', 'active'),
('EMP025', 'Sibel', 'Tan', 'sibel.tan@sirket.com', '0556-555-6679', 1, 'Helpdesk Uzmanı', '2022-02-14', 'active'),
('EMP026', 'Kemal', 'Yavuz', 'kemal.yavuz@sirket.com', '0557-666-7780', 4, 'Grafik Tasarımcı', '2021-10-01', 'active'),
('EMP027', 'Burcu', 'Karaca', 'burcu.karaca@sirket.com', '0558-777-8891', 3, 'Finans Uzmanı', '2022-04-18', 'active'),
('EMP028', 'Cem', 'Bulut', 'cem.bulut@sirket.com', '0559-888-9902', 5, 'Bölge Satış Müdürü', '2019-03-10', 'active'),
('EMP029', 'Aslı', 'Tekin', 'asli.tekin@sirket.com', '0560-999-0013', 2, 'İK Asistanı', '2023-01-05', 'active'),
('EMP030', 'Murat', 'Özkan', 'murat.ozkan@sirket.com', '0561-000-1124', 6, 'Vardiya Amiri', '2020-04-22', 'active');

-- Cihazlar (120 cihaz)
INSERT INTO devices (asset_tag, name, category_id, brand, model, serial_number, purchase_date, purchase_price, warranty_end_date, status, specifications) VALUES
-- Dizüstü Bilgisayarlar (30 adet)
('IT-LAP-001', 'Dell Latitude 5520', 1, 'Dell', 'Latitude 5520', 'DL5520-001-2023', '2023-01-15', 28500.00, '2026-01-15', 'assigned', 'Intel i7-1165G7, 16GB RAM, 512GB SSD'),
('IT-LAP-002', 'Dell Latitude 5520', 1, 'Dell', 'Latitude 5520', 'DL5520-002-2023', '2023-01-15', 28500.00, '2026-01-15', 'assigned', 'Intel i7-1165G7, 16GB RAM, 512GB SSD'),
('IT-LAP-003', 'Dell Latitude 5520', 1, 'Dell', 'Latitude 5520', 'DL5520-003-2023', '2023-01-15', 28500.00, '2026-01-15', 'assigned', 'Intel i7-1165G7, 16GB RAM, 512GB SSD'),
('IT-LAP-004', 'HP EliteBook 840', 1, 'HP', 'EliteBook 840 G8', 'HP840-001-2023', '2023-02-20', 32000.00, '2026-02-20', 'assigned', 'Intel i7-1185G7, 16GB RAM, 512GB SSD'),
('IT-LAP-005', 'HP EliteBook 840', 1, 'HP', 'EliteBook 840 G8', 'HP840-002-2023', '2023-02-20', 32000.00, '2026-02-20', 'assigned', 'Intel i7-1185G7, 16GB RAM, 512GB SSD'),
('IT-LAP-006', 'Lenovo ThinkPad T14', 1, 'Lenovo', 'ThinkPad T14 Gen 2', 'LNV-T14-001-2023', '2023-03-10', 29500.00, '2026-03-10', 'assigned', 'Intel i7-1165G7, 16GB RAM, 256GB SSD'),
('IT-LAP-007', 'Lenovo ThinkPad T14', 1, 'Lenovo', 'ThinkPad T14 Gen 2', 'LNV-T14-002-2023', '2023-03-10', 29500.00, '2026-03-10', 'assigned', 'Intel i7-1165G7, 16GB RAM, 256GB SSD'),
('IT-LAP-008', 'Lenovo ThinkPad X1 Carbon', 1, 'Lenovo', 'ThinkPad X1 Carbon Gen 9', 'LNV-X1C-001-2023', '2023-04-05', 45000.00, '2026-04-05', 'assigned', 'Intel i7-1185G7, 32GB RAM, 1TB SSD'),
('IT-LAP-009', 'Dell XPS 15', 1, 'Dell', 'XPS 15 9520', 'DL-XPS15-001-2023', '2023-05-12', 52000.00, '2026-05-12', 'assigned', 'Intel i9-12900HK, 32GB RAM, 1TB SSD'),
('IT-LAP-010', 'Dell XPS 15', 1, 'Dell', 'XPS 15 9520', 'DL-XPS15-002-2023', '2023-05-12', 52000.00, '2026-05-12', 'assigned', 'Intel i9-12900HK, 32GB RAM, 1TB SSD'),
('IT-LAP-011', 'HP ProBook 450', 1, 'HP', 'ProBook 450 G9', 'HP450-001-2023', '2023-06-01', 22000.00, '2026-06-01', 'assigned', 'Intel i5-1235U, 8GB RAM, 256GB SSD'),
('IT-LAP-012', 'HP ProBook 450', 1, 'HP', 'ProBook 450 G9', 'HP450-002-2023', '2023-06-01', 22000.00, '2026-06-01', 'assigned', 'Intel i5-1235U, 8GB RAM, 256GB SSD'),
('IT-LAP-013', 'HP ProBook 450', 1, 'HP', 'ProBook 450 G9', 'HP450-003-2023', '2023-06-01', 22000.00, '2026-06-01', 'assigned', 'Intel i5-1235U, 8GB RAM, 256GB SSD'),
('IT-LAP-014', 'Lenovo ThinkPad E14', 1, 'Lenovo', 'ThinkPad E14 Gen 4', 'LNV-E14-001-2023', '2023-07-15', 19500.00, '2026-07-15', 'assigned', 'Intel i5-1235U, 8GB RAM, 256GB SSD'),
('IT-LAP-015', 'Lenovo ThinkPad E14', 1, 'Lenovo', 'ThinkPad E14 Gen 4', 'LNV-E14-002-2023', '2023-07-15', 19500.00, '2026-07-15', 'assigned', 'Intel i5-1235U, 8GB RAM, 256GB SSD'),
('IT-LAP-016', 'Dell Latitude 3520', 1, 'Dell', 'Latitude 3520', 'DL3520-001-2023', '2023-08-20', 18000.00, '2026-08-20', 'assigned', 'Intel i5-1135G7, 8GB RAM, 256GB SSD'),
('IT-LAP-017', 'Dell Latitude 3520', 1, 'Dell', 'Latitude 3520', 'DL3520-002-2023', '2023-08-20', 18000.00, '2026-08-20', 'assigned', 'Intel i5-1135G7, 8GB RAM, 256GB SSD'),
('IT-LAP-018', 'MacBook Pro 14', 1, 'Apple', 'MacBook Pro 14" M2 Pro', 'APPLE-MBP14-001', '2023-09-01', 85000.00, '2026-09-01', 'assigned', 'Apple M2 Pro, 16GB RAM, 512GB SSD'),
('IT-LAP-019', 'MacBook Pro 14', 1, 'Apple', 'MacBook Pro 14" M2 Pro', 'APPLE-MBP14-002', '2023-09-01', 85000.00, '2026-09-01', 'assigned', 'Apple M2 Pro, 16GB RAM, 512GB SSD'),
('IT-LAP-020', 'MacBook Air M2', 1, 'Apple', 'MacBook Air M2', 'APPLE-MBA-001', '2023-10-10', 42000.00, '2026-10-10', 'assigned', 'Apple M2, 8GB RAM, 256GB SSD'),
('IT-LAP-021', 'HP EliteBook 850', 1, 'HP', 'EliteBook 850 G8', 'HP850-001-2024', '2024-01-05', 35000.00, '2027-01-05', 'available', 'Intel i7-1185G7, 16GB RAM, 512GB SSD'),
('IT-LAP-022', 'HP EliteBook 850', 1, 'HP', 'EliteBook 850 G8', 'HP850-002-2024', '2024-01-05', 35000.00, '2027-01-05', 'available', 'Intel i7-1185G7, 16GB RAM, 512GB SSD'),
('IT-LAP-023', 'Dell Precision 5570', 1, 'Dell', 'Precision 5570', 'DL-P5570-001', '2024-02-15', 72000.00, '2027-02-15', 'assigned', 'Intel i9-12900H, 64GB RAM, 2TB SSD'),
('IT-LAP-024', 'Lenovo ThinkPad P16', 1, 'Lenovo', 'ThinkPad P16', 'LNV-P16-001', '2024-03-01', 68000.00, '2027-03-01', 'assigned', 'Intel i7-12800HX, 32GB RAM, 1TB SSD'),
('IT-LAP-025', 'Dell Latitude 5540', 1, 'Dell', 'Latitude 5540', 'DL5540-001-2024', '2024-04-10', 30000.00, '2027-04-10', 'available', 'Intel i7-1365U, 16GB RAM, 512GB SSD'),
('IT-LAP-026', 'Dell Latitude 5540', 1, 'Dell', 'Latitude 5540', 'DL5540-002-2024', '2024-04-10', 30000.00, '2027-04-10', 'maintenance', 'Intel i7-1365U, 16GB RAM, 512GB SSD'),
('IT-LAP-027', 'HP ProBook 455', 1, 'HP', 'ProBook 455 G10', 'HP455-001-2024', '2024-05-20', 24000.00, '2027-05-20', 'assigned', 'AMD Ryzen 7 7730U, 16GB RAM, 512GB SSD'),
('IT-LAP-028', 'HP ProBook 455', 1, 'HP', 'ProBook 455 G10', 'HP455-002-2024', '2024-05-20', 24000.00, '2027-05-20', 'available', 'AMD Ryzen 7 7730U, 16GB RAM, 512GB SSD'),
('IT-LAP-029', 'Lenovo IdeaPad 5 Pro', 1, 'Lenovo', 'IdeaPad 5 Pro 16', 'LNV-IP5P-001', '2024-06-01', 26000.00, '2027-06-01', 'assigned', 'AMD Ryzen 7 5800H, 16GB RAM, 512GB SSD'),
('IT-LAP-030', 'ASUS ExpertBook', 1, 'ASUS', 'ExpertBook B5', 'ASUS-EB5-001', '2024-07-15', 28500.00, '2027-07-15', 'available', 'Intel i7-1255U, 16GB RAM, 512GB SSD'),

-- Masaüstü Bilgisayarlar (20 adet)
('IT-PC-001', 'Dell OptiPlex 7090', 2, 'Dell', 'OptiPlex 7090', 'DO7090-001-2022', '2022-06-15', 22000.00, '2025-06-15', 'assigned', 'Intel i7-11700, 16GB RAM, 512GB SSD'),
('IT-PC-002', 'Dell OptiPlex 7090', 2, 'Dell', 'OptiPlex 7090', 'DO7090-002-2022', '2022-06-15', 22000.00, '2025-06-15', 'assigned', 'Intel i7-11700, 16GB RAM, 512GB SSD'),
('IT-PC-003', 'HP ProDesk 400', 2, 'HP', 'ProDesk 400 G7', 'HPD400-001-2022', '2022-08-20', 15000.00, '2025-08-20', 'assigned', 'Intel i5-10500, 8GB RAM, 256GB SSD'),
('IT-PC-004', 'HP ProDesk 400', 2, 'HP', 'ProDesk 400 G7', 'HPD400-002-2022', '2022-08-20', 15000.00, '2025-08-20', 'assigned', 'Intel i5-10500, 8GB RAM, 256GB SSD'),
('IT-PC-005', 'HP ProDesk 400', 2, 'HP', 'ProDesk 400 G7', 'HPD400-003-2022', '2022-08-20', 15000.00, '2025-08-20', 'assigned', 'Intel i5-10500, 8GB RAM, 256GB SSD'),
('IT-PC-006', 'Lenovo ThinkCentre M90', 2, 'Lenovo', 'ThinkCentre M90q', 'LNV-M90-001-2023', '2023-02-10', 18500.00, '2026-02-10', 'assigned', 'Intel i7-12700, 16GB RAM, 512GB SSD'),
('IT-PC-007', 'Lenovo ThinkCentre M90', 2, 'Lenovo', 'ThinkCentre M90q', 'LNV-M90-002-2023', '2023-02-10', 18500.00, '2026-02-10', 'assigned', 'Intel i7-12700, 16GB RAM, 512GB SSD'),
('IT-PC-008', 'Dell Precision 3660', 2, 'Dell', 'Precision 3660 Tower', 'DP3660-001-2023', '2023-04-15', 45000.00, '2026-04-15', 'assigned', 'Intel i9-12900K, 64GB RAM, 2TB SSD, RTX 3080'),
('IT-PC-009', 'Dell Precision 3660', 2, 'Dell', 'Precision 3660 Tower', 'DP3660-002-2023', '2023-04-15', 45000.00, '2026-04-15', 'assigned', 'Intel i9-12900K, 64GB RAM, 2TB SSD, RTX 3080'),
('IT-PC-010', 'HP Z2 Tower', 2, 'HP', 'Z2 Tower G9', 'HPZ2-001-2023', '2023-06-01', 38000.00, '2026-06-01', 'available', 'Intel i7-12700K, 32GB RAM, 1TB SSD'),
('IT-PC-011', 'HP EliteDesk 800', 2, 'HP', 'EliteDesk 800 G6', 'HPE800-001-2022', '2022-10-10', 20000.00, '2025-10-10', 'retired', 'Intel i7-10700, 16GB RAM, 512GB SSD'),
('IT-PC-012', 'Dell OptiPlex 5090', 2, 'Dell', 'OptiPlex 5090', 'DO5090-001-2023', '2023-08-20', 17500.00, '2026-08-20', 'available', 'Intel i5-11500, 8GB RAM, 256GB SSD'),
('IT-PC-013', 'Dell OptiPlex 5090', 2, 'Dell', 'OptiPlex 5090', 'DO5090-002-2023', '2023-08-20', 17500.00, '2026-08-20', 'available', 'Intel i5-11500, 8GB RAM, 256GB SSD'),
('IT-PC-014', 'Lenovo ThinkStation P350', 2, 'Lenovo', 'ThinkStation P350', 'LNV-P350-001', '2023-10-05', 55000.00, '2026-10-05', 'assigned', 'Intel i9-11900K, 64GB RAM, 2TB SSD, RTX A4000'),
('IT-PC-015', 'HP Z4 Workstation', 2, 'HP', 'Z4 G5', 'HPZ4-001-2024', '2024-01-15', 82000.00, '2027-01-15', 'assigned', 'Intel Xeon W-2235, 64GB RAM, 2TB SSD'),
('IT-PC-016', 'iMac 24"', 2, 'Apple', 'iMac 24" M1', 'APPLE-IMAC-001', '2023-05-20', 48000.00, '2026-05-20', 'assigned', 'Apple M1, 16GB RAM, 512GB SSD'),
('IT-PC-017', 'iMac 24"', 2, 'Apple', 'iMac 24" M1', 'APPLE-IMAC-002', '2023-05-20', 48000.00, '2026-05-20', 'assigned', 'Apple M1, 16GB RAM, 512GB SSD'),
('IT-PC-018', 'Mac Mini M2', 2, 'Apple', 'Mac Mini M2', 'APPLE-MINI-001', '2024-02-01', 28000.00, '2027-02-01', 'available', 'Apple M2, 16GB RAM, 512GB SSD'),
('IT-PC-019', 'Dell OptiPlex 7010', 2, 'Dell', 'OptiPlex 7010 Plus', 'DO7010-001-2024', '2024-03-15', 25000.00, '2027-03-15', 'available', 'Intel i7-13700, 16GB RAM, 512GB SSD'),
('IT-PC-020', 'HP ProDesk 600', 2, 'HP', 'ProDesk 600 G6', 'HPD600-001-2024', '2024-04-20', 19000.00, '2027-04-20', 'maintenance', 'Intel i5-12500, 16GB RAM, 512GB SSD'),

-- Monitörler (25 adet)
('IT-MON-001', 'Dell UltraSharp U2722D', 3, 'Dell', 'U2722D', 'DLU2722D-001', '2023-01-20', 8500.00, '2026-01-20', 'assigned', '27 inç, 4K UHD, USB-C'),
('IT-MON-002', 'Dell UltraSharp U2722D', 3, 'Dell', 'U2722D', 'DLU2722D-002', '2023-01-20', 8500.00, '2026-01-20', 'assigned', '27 inç, 4K UHD, USB-C'),
('IT-MON-003', 'Dell UltraSharp U2722D', 3, 'Dell', 'U2722D', 'DLU2722D-003', '2023-01-20', 8500.00, '2026-01-20', 'assigned', '27 inç, 4K UHD, USB-C'),
('IT-MON-004', 'Dell UltraSharp U2722D', 3, 'Dell', 'U2722D', 'DLU2722D-004', '2023-01-20', 8500.00, '2026-01-20', 'assigned', '27 inç, 4K UHD, USB-C'),
('IT-MON-005', 'HP E24 G4', 3, 'HP', 'E24 G4', 'HPE24-001', '2022-08-15', 4200.00, '2025-08-15', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-006', 'HP E24 G4', 3, 'HP', 'E24 G4', 'HPE24-002', '2022-08-15', 4200.00, '2025-08-15', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-007', 'HP E24 G4', 3, 'HP', 'E24 G4', 'HPE24-003', '2022-08-15', 4200.00, '2025-08-15', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-008', 'HP E24 G4', 3, 'HP', 'E24 G4', 'HPE24-004', '2022-08-15', 4200.00, '2025-08-15', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-009', 'LG 27UK850-W', 3, 'LG', '27UK850-W', 'LG27UK-001', '2023-03-10', 7800.00, '2026-03-10', 'assigned', '27 inç, 4K UHD, HDR10'),
('IT-MON-010', 'LG 27UK850-W', 3, 'LG', '27UK850-W', 'LG27UK-002', '2023-03-10', 7800.00, '2026-03-10', 'assigned', '27 inç, 4K UHD, HDR10'),
('IT-MON-011', 'Samsung Odyssey G7', 3, 'Samsung', 'Odyssey G7 32"', 'SAM-G7-001', '2023-06-20', 12000.00, '2026-06-20', 'assigned', '32 inç, QHD, 240Hz, Curved'),
('IT-MON-012', 'Samsung Odyssey G7', 3, 'Samsung', 'Odyssey G7 32"', 'SAM-G7-002', '2023-06-20', 12000.00, '2026-06-20', 'assigned', '32 inç, QHD, 240Hz, Curved'),
('IT-MON-013', 'BenQ PD2700U', 3, 'BenQ', 'PD2700U', 'BNQ-PD27-001', '2023-09-05', 9500.00, '2026-09-05', 'available', '27 inç, 4K UHD, Tasarımcı Monitör'),
('IT-MON-014', 'Dell P2422H', 3, 'Dell', 'P2422H', 'DLP24-001', '2023-11-10', 4800.00, '2026-11-10', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-015', 'Dell P2422H', 3, 'Dell', 'P2422H', 'DLP24-002', '2023-11-10', 4800.00, '2026-11-10', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-016', 'Dell P2422H', 3, 'Dell', 'P2422H', 'DLP24-003', '2023-11-10', 4800.00, '2026-11-10', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-017', 'Dell P2422H', 3, 'Dell', 'P2422H', 'DLP24-004', '2023-11-10', 4800.00, '2026-11-10', 'assigned', '24 inç, FHD, IPS'),
('IT-MON-018', 'ASUS ProArt PA278CV', 3, 'ASUS', 'ProArt PA278CV', 'ASUS-PA27-001', '2024-01-15', 8200.00, '2027-01-15', 'available', '27 inç, QHD, IPS, USB-C'),
('IT-MON-019', 'ASUS ProArt PA278CV', 3, 'ASUS', 'ProArt PA278CV', 'ASUS-PA27-002', '2024-01-15', 8200.00, '2027-01-15', 'available', '27 inç, QHD, IPS, USB-C'),
('IT-MON-020', 'LG UltraWide 34WN80C', 3, 'LG', '34WN80C-B', 'LG34WN-001', '2024-02-20', 14500.00, '2027-02-20', 'assigned', '34 inç, QHD, UltraWide, USB-C'),
('IT-MON-021', 'LG UltraWide 34WN80C', 3, 'LG', '34WN80C-B', 'LG34WN-002', '2024-02-20', 14500.00, '2027-02-20', 'assigned', '34 inç, QHD, UltraWide, USB-C'),
('IT-MON-022', 'Dell S2722QC', 3, 'Dell', 'S2722QC', 'DLS27-001', '2024-03-10', 7200.00, '2027-03-10', 'available', '27 inç, 4K UHD, USB-C, Hoparlör'),
('IT-MON-023', 'HP Z27k G3', 3, 'HP', 'Z27k G3', 'HPZ27-001', '2024-04-05', 11000.00, '2027-04-05', 'available', '27 inç, 4K UHD, USB-C Hub'),
('IT-MON-024', 'ViewSonic VP2768a', 3, 'ViewSonic', 'VP2768a', 'VS-VP27-001', '2024-05-15', 6800.00, '2027-05-15', 'assigned', '27 inç, QHD, USB-C'),
('IT-MON-025', 'ViewSonic VP2768a', 3, 'ViewSonic', 'VP2768a', 'VS-VP27-002', '2024-05-15', 6800.00, '2027-05-15', 'assigned', '27 inç, QHD, USB-C'),

-- Yazıcılar (10 adet)
('IT-PRN-001', 'HP LaserJet Pro M404dn', 4, 'HP', 'LaserJet Pro M404dn', 'HP-LJP-001', '2022-05-10', 6500.00, '2025-05-10', 'assigned', 'Mono Lazer, Duplex, Network'),
('IT-PRN-002', 'HP LaserJet Pro M404dn', 4, 'HP', 'LaserJet Pro M404dn', 'HP-LJP-002', '2022-05-10', 6500.00, '2025-05-10', 'assigned', 'Mono Lazer, Duplex, Network'),
('IT-PRN-003', 'HP Color LaserJet Pro M454dw', 4, 'HP', 'Color LaserJet Pro M454dw', 'HP-CLJP-001', '2023-02-15', 12000.00, '2026-02-15', 'assigned', 'Renkli Lazer, Duplex, WiFi'),
('IT-PRN-004', 'HP Color LaserJet Pro M454dw', 4, 'HP', 'Color LaserJet Pro M454dw', 'HP-CLJP-002', '2023-02-15', 12000.00, '2026-02-15', 'assigned', 'Renkli Lazer, Duplex, WiFi'),
('IT-PRN-005', 'Canon imageCLASS MF445dw', 4, 'Canon', 'imageCLASS MF445dw', 'CAN-MF445-001', '2023-06-20', 9800.00, '2026-06-20', 'assigned', 'Mono Lazer MFP, Duplex, WiFi'),
('IT-PRN-006', 'Brother MFC-L8900CDW', 4, 'Brother', 'MFC-L8900CDW', 'BRO-L8900-001', '2023-09-10', 18500.00, '2026-09-10', 'assigned', 'Renkli Lazer MFP, Duplex, WiFi'),
('IT-PRN-007', 'Epson WorkForce Pro WF-4830', 4, 'Epson', 'WorkForce Pro WF-4830', 'EPS-WF4830-001', '2024-01-20', 8500.00, '2027-01-20', 'available', 'Renkli Inkjet MFP, WiFi'),
('IT-PRN-008', 'HP DesignJet T630', 4, 'HP', 'DesignJet T630', 'HP-DJ-T630-001', '2023-11-15', 28000.00, '2026-11-15', 'assigned', 'Plotter, A1, WiFi'),
('IT-PRN-009', 'Xerox VersaLink C405', 4, 'Xerox', 'VersaLink C405', 'XRX-C405-001', '2024-03-05', 22000.00, '2027-03-05', 'available', 'Renkli Lazer MFP, Duplex'),
('IT-PRN-010', 'Kyocera ECOSYS P3145dn', 4, 'Kyocera', 'ECOSYS P3145dn', 'KYO-P3145-001', '2024-05-10', 7200.00, '2027-05-10', 'assigned', 'Mono Lazer, Duplex, Network'),

-- Telefonlar (15 adet)
('IT-PHN-001', 'iPhone 14 Pro', 5, 'Apple', 'iPhone 14 Pro 256GB', 'APPLE-IP14P-001', '2023-03-15', 52000.00, '2025-03-15', 'assigned', '256GB, Space Black'),
('IT-PHN-002', 'iPhone 14 Pro', 5, 'Apple', 'iPhone 14 Pro 256GB', 'APPLE-IP14P-002', '2023-03-15', 52000.00, '2025-03-15', 'assigned', '256GB, Silver'),
('IT-PHN-003', 'iPhone 14', 5, 'Apple', 'iPhone 14 128GB', 'APPLE-IP14-001', '2023-04-20', 38000.00, '2025-04-20', 'assigned', '128GB, Blue'),
('IT-PHN-004', 'iPhone 14', 5, 'Apple', 'iPhone 14 128GB', 'APPLE-IP14-002', '2023-04-20', 38000.00, '2025-04-20', 'assigned', '128GB, Starlight'),
('IT-PHN-005', 'Samsung Galaxy S23 Ultra', 5, 'Samsung', 'Galaxy S23 Ultra', 'SAM-S23U-001', '2023-05-10', 48000.00, '2025-05-10', 'assigned', '256GB, Phantom Black'),
('IT-PHN-006', 'Samsung Galaxy S23 Ultra', 5, 'Samsung', 'Galaxy S23 Ultra', 'SAM-S23U-002', '2023-05-10', 48000.00, '2025-05-10', 'assigned', '256GB, Green'),
('IT-PHN-007', 'Samsung Galaxy S23', 5, 'Samsung', 'Galaxy S23', 'SAM-S23-001', '2023-06-15', 32000.00, '2025-06-15', 'assigned', '128GB, Cream'),
('IT-PHN-008', 'Samsung Galaxy S23', 5, 'Samsung', 'Galaxy S23', 'SAM-S23-002', '2023-06-15', 32000.00, '2025-06-15', 'assigned', '128GB, Lavender'),
('IT-PHN-009', 'iPhone 15 Pro', 5, 'Apple', 'iPhone 15 Pro 256GB', 'APPLE-IP15P-001', '2024-01-10', 62000.00, '2026-01-10', 'assigned', '256GB, Natural Titanium'),
('IT-PHN-010', 'iPhone 15 Pro', 5, 'Apple', 'iPhone 15 Pro 256GB', 'APPLE-IP15P-002', '2024-01-10', 62000.00, '2026-01-10', 'assigned', '256GB, Blue Titanium'),
('IT-PHN-011', 'Samsung Galaxy A54', 5, 'Samsung', 'Galaxy A54 5G', 'SAM-A54-001', '2024-02-20', 15000.00, '2026-02-20', 'available', '128GB, Awesome Graphite'),
('IT-PHN-012', 'Samsung Galaxy A54', 5, 'Samsung', 'Galaxy A54 5G', 'SAM-A54-002', '2024-02-20', 15000.00, '2026-02-20', 'available', '128GB, Awesome White'),
('IT-PHN-013', 'Xiaomi 13T Pro', 5, 'Xiaomi', '13T Pro', 'XIA-13TP-001', '2024-03-15', 24000.00, '2026-03-15', 'assigned', '256GB, Black'),
('IT-PHN-014', 'Google Pixel 8 Pro', 5, 'Google', 'Pixel 8 Pro', 'GOO-P8P-001', '2024-04-10', 42000.00, '2026-04-10', 'available', '128GB, Obsidian'),
('IT-PHN-015', 'iPhone 15', 5, 'Apple', 'iPhone 15 128GB', 'APPLE-IP15-001', '2024-05-20', 45000.00, '2026-05-20', 'available', '128GB, Pink'),

-- Tabletler (10 adet)
('IT-TAB-001', 'iPad Pro 12.9', 6, 'Apple', 'iPad Pro 12.9" M2', 'APPLE-IPADP-001', '2023-04-15', 42000.00, '2025-04-15', 'assigned', '256GB, WiFi, Space Gray'),
('IT-TAB-002', 'iPad Pro 12.9', 6, 'Apple', 'iPad Pro 12.9" M2', 'APPLE-IPADP-002', '2023-04-15', 42000.00, '2025-04-15', 'assigned', '256GB, WiFi, Silver'),
('IT-TAB-003', 'iPad Air', 6, 'Apple', 'iPad Air 5th Gen', 'APPLE-IPADA-001', '2023-06-20', 24000.00, '2025-06-20', 'assigned', '64GB, WiFi, Space Gray'),
('IT-TAB-004', 'iPad Air', 6, 'Apple', 'iPad Air 5th Gen', 'APPLE-IPADA-002', '2023-06-20', 24000.00, '2025-06-20', 'assigned', '64GB, WiFi, Blue'),
('IT-TAB-005', 'Samsung Galaxy Tab S9+', 6, 'Samsung', 'Galaxy Tab S9+', 'SAM-TS9P-001', '2023-09-10', 35000.00, '2025-09-10', 'assigned', '256GB, WiFi, Graphite'),
('IT-TAB-006', 'Samsung Galaxy Tab S9+', 6, 'Samsung', 'Galaxy Tab S9+', 'SAM-TS9P-002', '2023-09-10', 35000.00, '2025-09-10', 'available', '256GB, WiFi, Beige'),
('IT-TAB-007', 'Microsoft Surface Pro 9', 6, 'Microsoft', 'Surface Pro 9', 'MS-SP9-001', '2023-11-20', 48000.00, '2025-11-20', 'assigned', 'i7, 16GB, 256GB, Platinum'),
('IT-TAB-008', 'Microsoft Surface Pro 9', 6, 'Microsoft', 'Surface Pro 9', 'MS-SP9-002', '2023-11-20', 48000.00, '2025-11-20', 'assigned', 'i7, 16GB, 256GB, Graphite'),
('IT-TAB-009', 'iPad 10th Gen', 6, 'Apple', 'iPad 10th Gen', 'APPLE-IPAD10-001', '2024-02-15', 16000.00, '2026-02-15', 'available', '64GB, WiFi, Silver'),
('IT-TAB-010', 'Lenovo Tab P12 Pro', 6, 'Lenovo', 'Tab P12 Pro', 'LNV-TP12-001', '2024-04-20', 22000.00, '2026-04-20', 'available', '256GB, Storm Grey'),

-- Ağ Cihazları (5 adet)
('IT-NET-001', 'Cisco Catalyst 9200', 7, 'Cisco', 'Catalyst 9200-24P', 'CSC-C9200-001', '2022-09-15', 45000.00, '2025-09-15', 'assigned', '24 Port PoE+ Switch'),
('IT-NET-002', 'Cisco Catalyst 9200', 7, 'Cisco', 'Catalyst 9200-48P', 'CSC-C9200-002', '2022-09-15', 65000.00, '2025-09-15', 'assigned', '48 Port PoE+ Switch'),
('IT-NET-003', 'Ubiquiti UniFi Dream Machine', 7, 'Ubiquiti', 'UDM Pro', 'UBQ-UDMP-001', '2023-03-20', 18000.00, '2026-03-20', 'assigned', 'All-in-one Router/Controller'),
('IT-NET-004', 'Fortinet FortiGate 60F', 7, 'Fortinet', 'FortiGate 60F', 'FTN-FG60F-001', '2023-08-10', 25000.00, '2026-08-10', 'assigned', 'Next-Gen Firewall'),
('IT-NET-005', 'Ubiquiti UniFi AP U6 Pro', 7, 'Ubiquiti', 'U6 Pro', 'UBQ-U6P-001', '2024-01-25', 4500.00, '2027-01-25', 'assigned', 'WiFi 6 Access Point'),

-- Sunucular (5 adet)
('IT-SRV-001', 'Dell PowerEdge R750', 8, 'Dell', 'PowerEdge R750', 'DL-PE-R750-001', '2022-11-20', 185000.00, '2025-11-20', 'assigned', 'Xeon Gold 5318Y, 128GB RAM, 4x2TB SAS'),
('IT-SRV-002', 'Dell PowerEdge R750', 8, 'Dell', 'PowerEdge R750', 'DL-PE-R750-002', '2022-11-20', 185000.00, '2025-11-20', 'assigned', 'Xeon Gold 5318Y, 128GB RAM, 4x2TB SAS'),
('IT-SRV-003', 'HPE ProLiant DL380 Gen10', 8, 'HPE', 'ProLiant DL380 Gen10', 'HPE-DL380-001', '2023-04-15', 165000.00, '2026-04-15', 'assigned', 'Xeon Silver 4214R, 64GB RAM, 3x1.2TB SAS'),
('IT-SRV-004', 'Dell PowerEdge R650', 8, 'Dell', 'PowerEdge R650', 'DL-PE-R650-001', '2024-02-10', 145000.00, '2027-02-10', 'assigned', 'Xeon Gold 5317, 96GB RAM, 4x960GB SSD'),
('IT-SRV-005', 'Synology NAS RS1221+', 8, 'Synology', 'RS1221+', 'SYN-RS1221-001', '2023-07-20', 48000.00, '2026-07-20', 'assigned', '8-Bay NAS, 32GB RAM, 8x4TB HDD');

-- Zimmetler (Aktif Zimmetler - device_id'ye göre eşleştirme)
-- Dizüstü Bilgisayarlar
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(1, 1, '2023-01-20', 'IT Müdürü için ana bilgisayar', 'Sistem'),
(2, 2, '2023-01-22', 'Sistem yöneticisi için', 'Ahmet Yılmaz'),
(3, 3, '2023-01-25', 'Yazılım geliştirici için', 'Ahmet Yılmaz'),
(4, 4, '2023-03-01', 'İK Müdürü için', 'Ahmet Yılmaz'),
(5, 6, '2023-03-05', 'Finans Müdürü için', 'Ahmet Yılmaz'),
(6, 8, '2023-03-15', 'Pazarlama Müdürü için', 'Ahmet Yılmaz'),
(7, 10, '2023-03-20', 'Satış Müdürü için', 'Ahmet Yılmaz'),
(8, 22, '2023-04-10', 'Genel Müdür için premium laptop', 'Ahmet Yılmaz'),
(9, 15, '2023-05-20', 'Ar-Ge Müdürü için workstation laptop', 'Mehmet Kaya'),
(10, 16, '2023-05-25', 'Ar-Ge mühendisi için', 'Mehmet Kaya'),
(11, 11, '2023-06-10', 'Satış temsilcisi için', 'Mehmet Kaya'),
(12, 12, '2023-06-12', 'Satış temsilcisi için', 'Mehmet Kaya'),
(13, 19, '2023-06-15', 'Depo sorumlusu için', 'Mehmet Kaya'),
(14, 5, '2023-07-20', 'İK uzmanı için', 'Mehmet Kaya'),
(15, 7, '2023-07-25', 'Muhasebeci için', 'Mehmet Kaya'),
(16, 9, '2023-08-25', 'Dijital pazarlama uzmanı için', 'Mehmet Kaya'),
(17, 14, '2023-08-28', 'Üretim mühendisi için', 'Mehmet Kaya'),
(18, 23, '2023-09-05', 'Genel Müdür Yardımcısı için MacBook', 'Ahmet Yılmaz'),
(19, 17, '2023-09-10', 'Ar-Ge mühendisi için MacBook', 'Ahmet Yılmaz'),
(20, 26, '2023-10-15', 'Grafik tasarımcı için MacBook Air', 'Mehmet Kaya'),
(23, 24, '2024-02-20', 'Network uzmanı için workstation', 'Ahmet Yılmaz'),
(24, 13, '2024-03-10', 'Üretim Müdürü için', 'Mehmet Kaya'),
(27, 18, '2024-05-25', 'Lojistik Müdürü için', 'Mehmet Kaya'),
(29, 20, '2024-06-10', 'Kalite Müdürü için', 'Mehmet Kaya');

-- Masaüstü Bilgisayarlar zimmetleri
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(31, 25, '2022-06-20', 'Helpdesk masaüstü', 'Ahmet Yılmaz'),
(32, 29, '2022-06-25', 'İK asistanı için', 'Mehmet Kaya'),
(33, 27, '2022-08-25', 'Finans uzmanı için', 'Mehmet Kaya'),
(34, 21, '2022-08-28', 'Kalite uzmanı için', 'Mehmet Kaya'),
(35, 30, '2022-09-01', 'Vardiya amiri için', 'Mehmet Kaya'),
(36, 28, '2023-02-15', 'Bölge satış müdürü için', 'Mehmet Kaya'),
(37, 11, '2023-02-20', 'Satış temsilcisi ek bilgisayar', 'Mehmet Kaya'),
(38, 16, '2023-04-20', 'Ar-Ge mühendisi workstation', 'Ahmet Yılmaz'),
(39, 17, '2023-04-25', 'Ar-Ge mühendisi workstation', 'Ahmet Yılmaz'),
(44, 15, '2023-10-10', 'Ar-Ge Müdürü workstation', 'Ahmet Yılmaz'),
(45, 2, '2024-01-20', 'Sistem yöneticisi workstation', 'Ahmet Yılmaz'),
(46, 26, '2023-05-25', 'Grafik tasarımcı iMac', 'Mehmet Kaya'),
(47, 8, '2023-05-28', 'Pazarlama müdürü iMac', 'Mehmet Kaya');

-- Monitör zimmetleri
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(51, 1, '2023-01-25', 'IT Müdürü monitör', 'Sistem'),
(52, 2, '2023-01-28', 'Sistem yöneticisi monitör', 'Ahmet Yılmaz'),
(53, 3, '2023-02-01', 'Yazılım geliştirici monitör', 'Ahmet Yılmaz'),
(54, 22, '2023-02-05', 'Genel Müdür monitör', 'Ahmet Yılmaz'),
(55, 4, '2022-08-20', 'İK Müdürü monitör', 'Mehmet Kaya'),
(56, 6, '2022-08-22', 'Finans Müdürü monitör', 'Mehmet Kaya'),
(57, 7, '2022-08-25', 'Muhasebeci monitör', 'Mehmet Kaya'),
(58, 10, '2022-08-28', 'Satış Müdürü monitör', 'Mehmet Kaya'),
(59, 15, '2023-03-15', 'Ar-Ge Müdürü 4K monitör', 'Ahmet Yılmaz'),
(60, 16, '2023-03-18', 'Ar-Ge mühendisi 4K monitör', 'Ahmet Yılmaz'),
(61, 26, '2023-06-25', 'Grafik tasarımcı gaming monitör', 'Mehmet Kaya'),
(62, 9, '2023-06-28', 'Dijital pazarlama uzmanı monitör', 'Mehmet Kaya'),
(64, 5, '2023-11-15', 'İK uzmanı monitör', 'Mehmet Kaya'),
(65, 8, '2023-11-18', 'Pazarlama Müdürü monitör', 'Mehmet Kaya'),
(66, 11, '2023-11-20', 'Satış temsilcisi monitör', 'Mehmet Kaya'),
(67, 12, '2023-11-22', 'Satış temsilcisi monitör', 'Mehmet Kaya'),
(70, 23, '2024-02-25', 'GM Yardımcısı ultrawide monitör', 'Ahmet Yılmaz'),
(71, 17, '2024-02-28', 'Ar-Ge mühendisi ultrawide monitör', 'Ahmet Yılmaz'),
(74, 24, '2024-05-20', 'Network uzmanı monitör', 'Mehmet Kaya'),
(75, 25, '2024-05-22', 'Helpdesk uzmanı monitör', 'Mehmet Kaya');

-- Yazıcı zimmetleri (departmanlara)
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(76, 1, '2022-05-15', 'IT departmanı yazıcısı', 'Sistem'),
(77, 4, '2022-05-18', 'İK departmanı yazıcısı', 'Ahmet Yılmaz'),
(78, 6, '2023-02-20', 'Finans departmanı renkli yazıcı', 'Mehmet Kaya'),
(79, 8, '2023-02-25', 'Pazarlama departmanı renkli yazıcı', 'Mehmet Kaya'),
(80, 13, '2023-06-25', 'Üretim departmanı yazıcısı', 'Mehmet Kaya'),
(81, 22, '2023-09-15', 'Yönetim katı yazıcısı', 'Ahmet Yılmaz'),
(83, 15, '2023-11-20', 'Ar-Ge departmanı plotter', 'Ahmet Yılmaz'),
(85, 10, '2024-05-15', 'Satış departmanı yazıcısı', 'Mehmet Kaya');

-- Telefon zimmetleri
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(86, 22, '2023-03-20', 'Genel Müdür telefonu', 'Ahmet Yılmaz'),
(87, 23, '2023-03-22', 'GM Yardımcısı telefonu', 'Ahmet Yılmaz'),
(88, 10, '2023-04-25', 'Satış Müdürü telefonu', 'Mehmet Kaya'),
(89, 28, '2023-04-28', 'Bölge Satış Müdürü telefonu', 'Mehmet Kaya'),
(90, 6, '2023-05-15', 'Finans Müdürü telefonu', 'Mehmet Kaya'),
(91, 4, '2023-05-18', 'İK Müdürü telefonu', 'Mehmet Kaya'),
(92, 8, '2023-06-20', 'Pazarlama Müdürü telefonu', 'Mehmet Kaya'),
(93, 11, '2023-06-22', 'Satış temsilcisi telefonu', 'Mehmet Kaya'),
(94, 1, '2024-01-15', 'IT Müdürü yeni telefon', 'Sistem'),
(95, 15, '2024-01-18', 'Ar-Ge Müdürü telefonu', 'Ahmet Yılmaz'),
(98, 13, '2024-03-20', 'Üretim Müdürü telefonu', 'Mehmet Kaya');

-- Tablet zimmetleri
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(101, 22, '2023-04-20', 'Genel Müdür iPad Pro', 'Ahmet Yılmaz'),
(102, 23, '2023-04-22', 'GM Yardımcısı iPad Pro', 'Ahmet Yılmaz'),
(103, 10, '2023-06-25', 'Satış Müdürü iPad', 'Mehmet Kaya'),
(104, 28, '2023-06-28', 'Bölge Satış Müdürü iPad', 'Mehmet Kaya'),
(105, 8, '2023-09-15', 'Pazarlama Müdürü tablet', 'Mehmet Kaya'),
(107, 1, '2023-11-25', 'IT Müdürü Surface Pro', 'Sistem'),
(108, 15, '2023-11-28', 'Ar-Ge Müdürü Surface Pro', 'Ahmet Yılmaz');

-- Ağ cihazı zimmetleri (IT departmanına)
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(111, 1, '2022-09-20', 'Ana switch - Sunucu odası', 'Sistem'),
(112, 1, '2022-09-22', 'İkinci switch - Sunucu odası', 'Sistem'),
(113, 2, '2023-03-25', 'UDM Pro - Network merkezi', 'Ahmet Yılmaz'),
(114, 24, '2023-08-15', 'Firewall - Network güvenliği', 'Ahmet Yılmaz'),
(115, 24, '2024-01-30', 'WiFi 6 AP - Ofis', 'Ahmet Yılmaz');

-- Sunucu zimmetleri (IT departmanına)
INSERT INTO assignments (device_id, employee_id, assigned_date, notes, assigned_by) VALUES
(116, 2, '2022-11-25', 'Ana uygulama sunucusu', 'Ahmet Yılmaz'),
(117, 2, '2022-11-28', 'Veritabanı sunucusu', 'Ahmet Yılmaz'),
(118, 2, '2023-04-20', 'Yedek sunucu', 'Ahmet Yılmaz'),
(119, 2, '2024-02-15', 'Yeni uygulama sunucusu', 'Ahmet Yılmaz'),
(120, 2, '2023-07-25', 'NAS - Depolama', 'Ahmet Yılmaz');

-- Sistem Kullanıcıları
INSERT INTO users (username, password, full_name, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sistem Yöneticisi', 'admin@sirket.com', 'admin', 'active'),
('ahmet.yilmaz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmet Yılmaz', 'ahmet.yilmaz@sirket.com', 'admin', 'active'),
('mehmet.kaya', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mehmet Kaya', 'mehmet.kaya@sirket.com', 'user', 'active');
-- Not: Tüm şifreler "password" olarak ayarlanmıştır (bcrypt hash)

-- Zimmet Geçmişi (Örnek veriler)
INSERT INTO assignment_history (device_id, employee_id, assigned_date, returned_date, return_condition, notes, assigned_by, returned_to) VALUES
(21, 3, '2023-06-15', '2024-01-03', 'good', 'Yeni laptop ile değiştirildi', 'Mehmet Kaya', 'Ahmet Yılmaz'),
(22, 16, '2023-07-20', '2024-01-05', 'good', 'Yeni laptop ile değiştirildi', 'Mehmet Kaya', 'Ahmet Yılmaz'),
(41, 25, '2022-03-15', '2022-10-08', 'damaged', 'Ekran arızası nedeniyle emekli edildi', 'Ahmet Yılmaz', 'Mehmet Kaya'),
(26, 14, '2023-09-01', '2024-04-08', 'needs_repair', 'Bakıma alındı - SSD arızası', 'Mehmet Kaya', 'Ahmet Yılmaz');
