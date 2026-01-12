<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Raporlar ve İstatistikler Sayfası
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = Database::getInstance();
$user = getCurrentUser();

// Tarih filtresi
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Ayın başı
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Bugün

// Genel İstatistikler
$generalStats = $db->fetch("
    SELECT
        COUNT(*) as total_patients,
        SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_count,
        SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_count,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting_count,
        SUM(CASE WHEN status = 'in_treatment' THEN 1 ELSE 0 END) as treatment_count
    FROM patients
    WHERE DATE(created_at) BETWEEN ? AND ?
", [$startDate, $endDate]);

// Uyruk bazlı istatistikler
$nationalityStats = $db->fetchAll("
    SELECT
        COALESCE(NULLIF(nationality, ''), 'Belirtilmemiş') as nationality,
        COUNT(*) as count
    FROM patients
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY nationality
    ORDER BY count DESC
    LIMIT 10
", [$startDate, $endDate]);

// Tedavi türü bazlı istatistikler
$treatmentStats = $db->fetchAll("
    SELECT
        COALESCE(NULLIF(t.treatment_type, ''), 'Belirtilmemiş') as treatment_type,
        COUNT(*) as count,
        SUM(t.price) as total_revenue,
        AVG(t.price) as avg_price
    FROM treatments t
    JOIN patients p ON t.patient_id = p.id
    WHERE t.status = 'completed'
    AND DATE(t.completed_at) BETWEEN ? AND ?
    GROUP BY t.treatment_type
    ORDER BY count DESC
", [$startDate, $endDate]);

// Günlük kayıt istatistikleri (son 30 gün)
$dailyStats = $db->fetchAll("
    SELECT
        DATE(created_at) as date,
        COUNT(*) as count
    FROM patients
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

// Nasıl ulaştı istatistikleri
$howFoundStats = $db->fetchAll("
    SELECT
        how_found,
        COUNT(*) as count
    FROM patients
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY how_found
    ORDER BY count DESC
", [$startDate, $endDate]);

// Toplam gelir
$totalRevenue = $db->fetch("
    SELECT
        SUM(CASE WHEN currency = 'TRY' THEN price ELSE 0 END) as total_try,
        SUM(CASE WHEN currency = 'USD' THEN price ELSE 0 END) as total_usd,
        SUM(CASE WHEN currency = 'EUR' THEN price ELSE 0 END) as total_eur
    FROM treatments
    WHERE status = 'completed'
    AND DATE(completed_at) BETWEEN ? AND ?
", [$startDate, $endDate]);

// Chart.js için veri hazırlama
$nationalityLabels = array_column($nationalityStats, 'nationality');
$nationalityCounts = array_column($nationalityStats, 'count');

$treatmentLabels = array_column($treatmentStats, 'treatment_type');
$treatmentCounts = array_column($treatmentStats, 'count');
$treatmentRevenues = array_column($treatmentStats, 'total_revenue');

$dailyDates = array_column($dailyStats, 'date');
$dailyCounts = array_column($dailyStats, 'count');

$howFoundLabels = array_map(function($item) {
    $map = [
        'social_media' => 'Sosyal Medya',
        'website' => 'Web Sitesi',
        'reference' => 'Referans',
        'recommendation' => 'Tavsiye',
        'other' => 'Diğer'
    ];
    return $map[$item['how_found']] ?? $item['how_found'];
}, $howFoundStats);
$howFoundCounts = array_column($howFoundStats, 'count');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Klinik Yönetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
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
        }

        /* Sidebar */
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
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0;
        }

        /* Date Filter */
        .date-filter {
            display: flex;
            gap: 10px;
            align-items: center;
            background: white;
            padding: 10px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .date-filter input {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .date-filter button {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }

        .date-filter .btn-excel {
            background: #217346;
        }

        .date-filter .btn-excel:hover {
            background: #1e6b3e;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
        }

        .stat-card i {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0;
        }

        .stat-card p {
            color: #666;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .stat-card.total { border-top: 4px solid var(--secondary-color); }
        .stat-card.total i { color: var(--secondary-color); }

        .stat-card.completed { border-top: 4px solid #28a745; }
        .stat-card.completed i { color: #28a745; }

        .stat-card.revenue { border-top: 4px solid #ffc107; }
        .stat-card.revenue i { color: #ffc107; }

        .stat-card.male { border-top: 4px solid #007bff; }
        .stat-card.male i { color: #007bff; }

        .stat-card.female { border-top: 4px solid #e83e8c; }
        .stat-card.female i { color: #e83e8c; }

        /* Chart Cards */
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-title i {
            color: var(--secondary-color);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
        }

        .data-table tr:hover {
            background: #f8f9fa;
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
            .date-filter {
                flex-wrap: wrap;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Print styles */
        @media print {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }

            .date-filter button {
                display: none;
            }
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
                <a href="reports.php" class="active">
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
            <h1><i class="fas fa-chart-bar"></i> Raporlar ve İstatistikler</h1>

            <form class="date-filter" method="GET">
                <label>Başlangıç:</label>
                <input type="date" name="start_date" value="<?= $startDate ?>">
                <label>Bitiş:</label>
                <input type="date" name="end_date" value="<?= $endDate ?>">
                <button type="submit"><i class="fas fa-filter"></i> Filtrele</button>
                <button type="button" onclick="window.print()"><i class="fas fa-print"></i></button>
                <button type="button" onclick="exportAllToExcel()" class="btn-excel"><i class="fas fa-file-excel"></i> Excel</button>
            </form>
        </div>

        <!-- Genel İstatistikler -->
        <div class="stats-grid">
            <div class="stat-card total">
                <i class="fas fa-users"></i>
                <h3><?= $generalStats['total_patients'] ?? 0 ?></h3>
                <p>Toplam Hasta</p>
            </div>

            <div class="stat-card completed">
                <i class="fas fa-check-circle"></i>
                <h3><?= $generalStats['completed_count'] ?? 0 ?></h3>
                <p>Tamamlanan</p>
            </div>

            <div class="stat-card revenue">
                <i class="fas fa-lira-sign"></i>
                <h3><?= number_format($totalRevenue['total_try'] ?? 0, 0, ',', '.') ?></h3>
                <p>Toplam Gelir (₺)</p>
            </div>

            <div class="stat-card male">
                <i class="fas fa-mars"></i>
                <h3><?= $generalStats['male_count'] ?? 0 ?></h3>
                <p>Erkek</p>
            </div>

            <div class="stat-card female">
                <i class="fas fa-venus"></i>
                <h3><?= $generalStats['female_count'] ?? 0 ?></h3>
                <p>Kadın</p>
            </div>
        </div>

        <div class="row">
            <!-- Günlük Kayıtlar Grafiği -->
            <div class="col-lg-8">
                <div class="chart-card">
                    <h4 class="chart-title"><i class="fas fa-chart-line"></i> Son 30 Gün - Günlük Kayıtlar</h4>
                    <div class="chart-container">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Cinsiyet Dağılımı -->
            <div class="col-lg-4">
                <div class="chart-card">
                    <h4 class="chart-title"><i class="fas fa-chart-pie"></i> Cinsiyet Dağılımı</h4>
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Uyruk Dağılımı -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <h4 class="chart-title"><i class="fas fa-globe"></i> Uyruğa Göre Dağılım</h4>
                    <div class="chart-container">
                        <canvas id="nationalityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Nasıl Ulaştı -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <h4 class="chart-title"><i class="fas fa-bullhorn"></i> Nasıl Ulaştı</h4>
                    <div class="chart-container">
                        <canvas id="howFoundChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tedavi İstatistikleri -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h4 class="chart-title"><i class="fas fa-procedures"></i> Tedaviye Göre Dağılım</h4>
                    <div class="chart-container">
                        <canvas id="treatmentChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="chart-card">
                    <h4 class="chart-title"><i class="fas fa-money-bill-wave"></i> Tedavi Gelirleri</h4>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tedavi</th>
                                <th>Adet</th>
                                <th>Toplam</th>
                                <th>Ortalama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($treatmentStats as $stat): ?>
                            <tr>
                                <td><?= htmlspecialchars($stat['treatment_type']) ?></td>
                                <td><?= $stat['count'] ?></td>
                                <td><?= formatMoney($stat['total_revenue']) ?></td>
                                <td><?= formatMoney($stat['avg_price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($treatmentStats)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center; color:#999;">Veri bulunamadı</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Uyruk Detaylı Tablo -->
        <div class="chart-card">
            <h4 class="chart-title"><i class="fas fa-table"></i> Uyruk Detayları</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Uyruk</th>
                        <th>Hasta Sayısı</th>
                        <th>Oran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalNationality = array_sum(array_column($nationalityStats, 'count'));
                    foreach ($nationalityStats as $stat):
                        $percentage = $totalNationality > 0 ? round(($stat['count'] / $totalNationality) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['nationality']) ?></td>
                        <td><?= $stat['count'] ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="flex:1; height:8px; background:#e0e0e0; border-radius:4px; overflow:hidden;">
                                    <div style="width:<?= $percentage ?>%; height:100%; background:var(--secondary-color);"></div>
                                </div>
                                <span><?= $percentage ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Renk paleti
        const colors = [
            '#65bdc2', '#0e3055', '#28a745', '#ffc107', '#dc3545',
            '#17a2b8', '#6610f2', '#e83e8c', '#fd7e14', '#20c997'
        ];

        // Günlük kayıtlar grafiği
        new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($dailyDates) ?>,
                datasets: [{
                    label: 'Kayıt Sayısı',
                    data: <?= json_encode($dailyCounts) ?>,
                    borderColor: '#65bdc2',
                    backgroundColor: 'rgba(101, 189, 194, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Cinsiyet grafiği
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: {
                labels: ['Erkek', 'Kadın', 'Diğer'],
                datasets: [{
                    data: [
                        <?= $generalStats['male_count'] ?? 0 ?>,
                        <?= $generalStats['female_count'] ?? 0 ?>,
                        <?= ($generalStats['total_patients'] ?? 0) - ($generalStats['male_count'] ?? 0) - ($generalStats['female_count'] ?? 0) ?>
                    ],
                    backgroundColor: ['#007bff', '#e83e8c', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Uyruk grafiği
        new Chart(document.getElementById('nationalityChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($nationalityLabels) ?>,
                datasets: [{
                    label: 'Hasta Sayısı',
                    data: <?= json_encode($nationalityCounts) ?>,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Nasıl ulaştı grafiği
        new Chart(document.getElementById('howFoundChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($howFoundLabels) ?>,
                datasets: [{
                    data: <?= json_encode($howFoundCounts) ?>,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Tedavi grafiği
        new Chart(document.getElementById('treatmentChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($treatmentLabels) ?>,
                datasets: [{
                    label: 'Tedavi Sayısı',
                    data: <?= json_encode($treatmentCounts) ?>,
                    backgroundColor: '#65bdc2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Excel Export Fonksiyonu
        function exportAllToExcel() {
            const wb = XLSX.utils.book_new();

            // 1. Genel İstatistikler
            const generalData = [
                ['Genel İstatistikler', ''],
                ['Tarih Aralığı', '<?= $startDate ?> - <?= $endDate ?>'],
                ['', ''],
                ['Metrik', 'Değer'],
                ['Toplam Hasta', <?= $generalStats['total_patients'] ?? 0 ?>],
                ['Erkek', <?= $generalStats['male_count'] ?? 0 ?>],
                ['Kadın', <?= $generalStats['female_count'] ?? 0 ?>],
                ['Bekleyen', <?= $generalStats['waiting_count'] ?? 0 ?>],
                ['Tedavide', <?= $generalStats['treatment_count'] ?? 0 ?>],
                ['Tamamlanan', <?= $generalStats['completed_count'] ?? 0 ?>],
                ['', ''],
                ['Toplam Gelir (TRY)', <?= $totalRevenue['total_try'] ?? 0 ?>],
                ['Toplam Gelir (USD)', <?= $totalRevenue['total_usd'] ?? 0 ?>],
                ['Toplam Gelir (EUR)', <?= $totalRevenue['total_eur'] ?? 0 ?>]
            ];
            const wsGeneral = XLSX.utils.aoa_to_sheet(generalData);
            XLSX.utils.book_append_sheet(wb, wsGeneral, 'Genel İstatistikler');

            // 2. Günlük Kayıtlar
            const dailyData = [
                ['Tarih', 'Kayıt Sayısı'],
                <?php foreach ($dailyStats as $stat): ?>
                ['<?= $stat['date'] ?>', <?= $stat['count'] ?>],
                <?php endforeach; ?>
            ];
            const wsDaily = XLSX.utils.aoa_to_sheet(dailyData);
            XLSX.utils.book_append_sheet(wb, wsDaily, 'Günlük Kayıtlar');

            // 3. Uyruk Dağılımı
            const nationalityData = [
                ['Uyruk', 'Hasta Sayısı', 'Oran (%)'],
                <?php
                $totalNat = array_sum(array_column($nationalityStats, 'count'));
                foreach ($nationalityStats as $stat):
                    $pct = $totalNat > 0 ? round(($stat['count'] / $totalNat) * 100, 1) : 0;
                ?>
                ['<?= addslashes($stat['nationality']) ?>', <?= $stat['count'] ?>, <?= $pct ?>],
                <?php endforeach; ?>
            ];
            const wsNationality = XLSX.utils.aoa_to_sheet(nationalityData);
            XLSX.utils.book_append_sheet(wb, wsNationality, 'Uyruk Dağılımı');

            // 4. Nasıl Ulaştı
            const howFoundData = [
                ['Kaynak', 'Hasta Sayısı'],
                <?php
                $howFoundMap = [
                    'social_media' => 'Sosyal Medya',
                    'website' => 'Web Sitesi',
                    'reference' => 'Referans',
                    'recommendation' => 'Tavsiye',
                    'other' => 'Diğer'
                ];
                foreach ($howFoundStats as $stat):
                    $label = $howFoundMap[$stat['how_found']] ?? $stat['how_found'];
                ?>
                ['<?= addslashes($label) ?>', <?= $stat['count'] ?>],
                <?php endforeach; ?>
            ];
            const wsHowFound = XLSX.utils.aoa_to_sheet(howFoundData);
            XLSX.utils.book_append_sheet(wb, wsHowFound, 'Nasıl Ulaştı');

            // 5. Tedavi İstatistikleri
            const treatmentData = [
                ['Tedavi Türü', 'Adet', 'Toplam Gelir', 'Ortalama Fiyat'],
                <?php foreach ($treatmentStats as $stat): ?>
                ['<?= addslashes($stat['treatment_type']) ?>', <?= $stat['count'] ?>, <?= $stat['total_revenue'] ?? 0 ?>, <?= round($stat['avg_price'] ?? 0, 2) ?>],
                <?php endforeach; ?>
            ];
            const wsTreatment = XLSX.utils.aoa_to_sheet(treatmentData);
            XLSX.utils.book_append_sheet(wb, wsTreatment, 'Tedavi İstatistikleri');

            // Excel dosyasını indir
            const fileName = 'Klinik_Rapor_<?= $startDate ?>_<?= $endDate ?>.xlsx';
            XLSX.writeFile(wb, fileName);
        }
    </script>
</body>
</html>
