<?php
/**
 * Market Satış Sistemi - Satış API
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

setCorsHeaders();
setJsonHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Tüm işlemler için auth gerekli
    $currentUser = Auth::requireAuth();
    $pdo = getDB();

    switch ($method) {
        case 'GET':
            handleGet($pdo, $currentUser);
            break;

        case 'POST':
            handlePost($pdo, $currentUser);
            break;

        default:
            errorResponse('Desteklenmeyen metod', 405);
    }
} catch (Exception $e) {
    $status = http_response_code() ?: 500;
    if ($status === 200) $status = 500;
    errorResponse($e->getMessage(), $status);
}

/**
 * Satış listele veya detay getir
 */
function handleGet(PDO $pdo, array $currentUser): void {
    // Tek satış detayı
    if (isset($_GET['id'])) {
        $id = getPositiveInt($_GET['id']);

        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name as cashier_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();

        if (!$sale) {
            errorResponse('Satış bulunamadı', 404);
        }

        // Satış kalemleri
        $stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
        $stmt->execute([$id]);
        $sale['items'] = $stmt->fetchAll();

        successResponse($sale);
    }

    // Satış listesi (pagination ile)
    $pagination = getPaginationParams();

    // Filtreler
    $whereClause = '1=1';
    $params = [];

    // Tarih filtresi
    if (isset($_GET['date_from'])) {
        $whereClause .= ' AND s.created_at >= ?';
        $params[] = $_GET['date_from'] . ' 00:00:00';
    }
    if (isset($_GET['date_to'])) {
        $whereClause .= ' AND s.created_at <= ?';
        $params[] = $_GET['date_to'] . ' 23:59:59';
    }

    // Kullanıcı filtresi (admin tüm satışları görebilir)
    if (isset($_GET['user_id']) && $currentUser['role'] === ROLE_ADMIN) {
        $whereClause .= ' AND s.user_id = ?';
        $params[] = getPositiveInt($_GET['user_id']);
    } elseif ($currentUser['role'] !== ROLE_ADMIN) {
        // Kasiyer sadece kendi satışlarını görebilir
        $whereClause .= ' AND s.user_id = ?';
        $params[] = $currentUser['id'];
    }

    // Toplam sayı
    $countSql = "SELECT COUNT(*) FROM sales s WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();

    // Veri çek
    $sql = "
        SELECT s.*, u.full_name as cashier_name
        FROM sales s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE $whereClause
        ORDER BY s.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $params[] = $pagination['limit'];
    $params[] = $pagination['offset'];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sales = $stmt->fetchAll();

    // İstatistikler (bugünün satışları)
    $statsParams = [];
    $statsWhere = 's.created_at >= CURDATE()';
    if ($currentUser['role'] !== ROLE_ADMIN) {
        $statsWhere .= ' AND s.user_id = ?';
        $statsParams[] = $currentUser['id'];
    }

    $statsStmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_count,
            COALESCE(SUM(total_amount), 0) as total_amount
        FROM sales s
        WHERE $statsWhere
    ");
    $statsStmt->execute($statsParams);
    $todayStats = $statsStmt->fetch();

    successResponse([
        'items' => $sales,
        'pagination' => createPaginationMeta($totalItems, $pagination['page'], $pagination['limit']),
        'today_stats' => [
            'count' => (int) $todayStats['total_count'],
            'amount' => (float) $todayStats['total_amount']
        ]
    ]);
}

/**
 * Yeni satış kaydet
 */
function handlePost(PDO $pdo, array $currentUser): void {
    $input = getJsonInput();
    validateRequired($input, ['items']);

    if (!is_array($input['items']) || empty($input['items'])) {
        errorResponse('Sepet boş olamaz', 400);
    }

    $paymentMethod = $input['payment_method'] ?? 'cash';
    if (!in_array($paymentMethod, ['cash', 'card', 'other'])) {
        $paymentMethod = 'cash';
    }

    Database::beginTransaction();

    try {
        $totalAmount = 0;
        $validatedItems = [];

        // Ürünleri doğrula ve toplam hesapla
        foreach ($input['items'] as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                throw new Exception('Geçersiz ürün bilgisi');
            }

            $productId = getPositiveInt($item['product_id']);
            $quantity = getPositiveInt($item['quantity']);

            if ($productId <= 0 || $quantity <= 0) {
                throw new Exception('Geçersiz ürün ID veya miktar');
            }

            // Ürünü getir
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Ürün bulunamadı: ID $productId");
            }

            $unitPrice = (float) $product['price'];
            $itemTotal = $unitPrice * $quantity;
            $totalAmount += $itemTotal;

            $validatedItems[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'product_barcode' => $product['barcode'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal
            ];
        }

        // Satış kaydı oluştur
        $stmt = $pdo->prepare("
            INSERT INTO sales (user_id, total_amount, payment_method)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$currentUser['id'], $totalAmount, $paymentMethod]);
        $saleId = (int) $pdo->lastInsertId();

        // Satış kalemlerini kaydet
        $stmt = $pdo->prepare("
            INSERT INTO sale_items (sale_id, product_id, product_name, product_barcode, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($validatedItems as $item) {
            $stmt->execute([
                $saleId,
                $item['product_id'],
                $item['product_name'],
                $item['product_barcode'],
                $item['quantity'],
                $item['unit_price'],
                $item['total_price']
            ]);
        }

        Database::commit();

        successResponse([
            'sale_id' => $saleId,
            'total_amount' => $totalAmount,
            'item_count' => count($validatedItems),
            'payment_method' => $paymentMethod,
            'cashier' => $currentUser['full_name']
        ], 'Satış kaydedildi', 201);

    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
}
