<?php
/**
 * Market Satış Sistemi
 * API ve Frontend tek dosyada
 */

// Veritabanı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'market_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO bağlantısı
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// JSON yanıt
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// API İstekleri
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $method = $_SERVER['REQUEST_METHOD'];
    $pdo = getDB();

    // CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($method === 'OPTIONS') exit(0);

    // === ÜRÜN API ===
    if ($action === 'products') {
        switch ($method) {
            case 'GET':
                if (isset($_GET['barcode'])) {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
                    $stmt->execute([trim($_GET['barcode'])]);
                    $product = $stmt->fetch();
                    if ($product) jsonResponse($product);
                    else jsonResponse(['error' => 'Ürün bulunamadı'], 404);
                } else {
                    $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
                    jsonResponse($stmt->fetchAll());
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['barcode']) || empty($input['name']) || !isset($input['price'])) {
                    jsonResponse(['error' => 'Barkod, ürün adı ve fiyat zorunludur'], 400);
                }
                $barcode = trim($input['barcode']);
                $name = trim($input['name']);
                $price = floatval($input['price']);

                $stmt = $pdo->prepare("SELECT id FROM products WHERE barcode = ?");
                $stmt->execute([$barcode]);
                if ($stmt->fetch()) {
                    jsonResponse(['error' => 'Bu barkod zaten kayıtlı'], 409);
                }

                $stmt = $pdo->prepare("INSERT INTO products (barcode, name, price) VALUES (?, ?, ?)");
                $stmt->execute([$barcode, $name, $price]);
                jsonResponse(['success' => true, 'message' => 'Ürün eklendi', 'id' => $pdo->lastInsertId()], 201);
                break;

            case 'DELETE':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['id'])) jsonResponse(['error' => 'ID zorunlu'], 400);

                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([intval($input['id'])]);
                if ($stmt->rowCount() === 0) jsonResponse(['error' => 'Ürün bulunamadı'], 404);
                jsonResponse(['success' => true, 'message' => 'Ürün silindi']);
                break;
        }
    }

    // === SATIŞ API ===
    if ($action === 'sales') {
        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $id = intval($_GET['id']);
                    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
                    $stmt->execute([$id]);
                    $sale = $stmt->fetch();
                    if (!$sale) jsonResponse(['error' => 'Satış bulunamadı'], 404);

                    $stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
                    $stmt->execute([$id]);
                    $sale['items'] = $stmt->fetchAll();
                    jsonResponse($sale);
                } else {
                    $stmt = $pdo->query("SELECT * FROM sales ORDER BY created_at DESC LIMIT 50");
                    jsonResponse($stmt->fetchAll());
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['items'])) jsonResponse(['error' => 'Sepet boş'], 400);

                $pdo->beginTransaction();
                try {
                    $totalAmount = 0;
                    foreach ($input['items'] as $item) {
                        $totalAmount += floatval($item['total_price']);
                    }

                    $stmt = $pdo->prepare("INSERT INTO sales (total_amount) VALUES (?)");
                    $stmt->execute([$totalAmount]);
                    $saleId = $pdo->lastInsertId();

                    $stmt = $pdo->prepare(
                        "INSERT INTO sale_items (sale_id, product_id, product_name, product_barcode, quantity, unit_price, total_price)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    foreach ($input['items'] as $item) {
                        $stmt->execute([
                            $saleId,
                            intval($item['product_id']),
                            $item['product_name'],
                            $item['product_barcode'],
                            intval($item['quantity']),
                            floatval($item['unit_price']),
                            floatval($item['total_price'])
                        ]);
                    }

                    $pdo->commit();
                    jsonResponse(['success' => true, 'sale_id' => $saleId, 'total_amount' => $totalAmount], 201);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    jsonResponse(['error' => 'Satış kaydedilemedi'], 500);
                }
                break;
        }
    }

    jsonResponse(['error' => 'Geçersiz istek'], 400);
}

// HTML Sayfası
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Satış Sistemi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Market Satış Sistemi</h1>
            <nav>
                <button class="tab-btn active" data-tab="sales">Satış</button>
                <button class="tab-btn" data-tab="products">Ürün Yönetimi</button>
                <button class="tab-btn" data-tab="history">Satış Geçmişi</button>
            </nav>
        </header>

        <!-- Satış Ekranı -->
        <section id="sales" class="tab-content active">
            <div class="sales-container">
                <div class="barcode-section">
                    <h2>Barkod Okut</h2>
                    <div class="input-group">
                        <input type="text" id="barcodeInput" placeholder="Barkod okutun veya girin..." autofocus>
                        <button id="addToCartBtn">Ekle</button>
                    </div>
                    <div id="productInfo" class="product-info hidden">
                        <span id="foundProductName"></span>
                        <span id="foundProductPrice"></span>
                    </div>
                </div>

                <div class="cart-section">
                    <h2>Sepet</h2>
                    <div class="cart-items" id="cartItems">
                        <p class="empty-cart">Sepet boş</p>
                    </div>
                    <div class="cart-total">
                        <span>Toplam:</span>
                        <span id="cartTotal">0.00 TL</span>
                    </div>
                    <button id="completeSaleBtn" class="complete-btn" disabled>Satışı Tamamla</button>
                </div>
            </div>
        </section>

        <!-- Ürün Yönetimi -->
        <section id="products" class="tab-content">
            <div class="product-form">
                <h2>Yeni Ürün Ekle</h2>
                <form id="productForm">
                    <div class="form-group">
                        <label for="productBarcode">Barkod</label>
                        <input type="text" id="productBarcode" required>
                    </div>
                    <div class="form-group">
                        <label for="productName">Ürün Adı</label>
                        <input type="text" id="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Fiyat (TL)</label>
                        <input type="number" id="productPrice" step="0.01" min="0" required>
                    </div>
                    <button type="submit" class="submit-btn">Ürün Ekle</button>
                </form>
            </div>

            <div class="product-list">
                <h2>Kayıtlı Ürünler</h2>
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>Barkod</th>
                            <th>Ürün Adı</th>
                            <th>Fiyat</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody id="productsBody">
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Satış Geçmişi -->
        <section id="history" class="tab-content">
            <h2>Satış Geçmişi</h2>
            <table id="salesTable">
                <thead>
                    <tr>
                        <th>Satış No</th>
                        <th>Tarih</th>
                        <th>Toplam</th>
                        <th>Detay</th>
                    </tr>
                </thead>
                <tbody id="salesBody">
                </tbody>
            </table>
        </section>

        <!-- Satış Detay Modal -->
        <div id="saleModal" class="modal hidden">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h2>Satış Detayı</h2>
                <div id="saleDetails"></div>
            </div>
        </div>

        <!-- Bildirim -->
        <div id="notification" class="notification hidden"></div>
    </div>

    <script src="assets/app.js"></script>
</body>
</html>
