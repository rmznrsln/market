<?php
/**
 * Market Satis Sistemi - Paket Siparis API
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

setCorsHeaders();
setJsonHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $pdo = getDB();

    // Public endpoints (auth gerektirmeyen)
    if ($action === 'public-products') {
        handlePublicProducts($pdo);
        exit;
    }

    if ($action === 'create' && $method === 'POST') {
        handleCreateOrder($pdo);
        exit;
    }

    // Auth gerektiren endpoints
    $currentUser = Auth::requireAuth();

    switch ($method) {
        case 'GET':
            handleGet($pdo, $currentUser);
            break;

        case 'PUT':
            handleUpdate($pdo, $currentUser);
            break;

        case 'DELETE':
            handleDelete($pdo, $currentUser);
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
 * Public urun listesi (auth gerektirmez)
 */
function handlePublicProducts(PDO $pdo): void {
    $stmt = $pdo->query("SELECT id, barcode, name, price FROM products WHERE is_active = 1 ORDER BY name ASC");
    $products = $stmt->fetchAll();
    successResponse($products);
}

/**
 * Yeni siparis olustur (musteriler icin - auth gerektirmez)
 */
function handleCreateOrder(PDO $pdo): void {
    $input = getJsonInput();

    // Validasyon
    validateRequired($input, ['customer_name', 'customer_phone', 'customer_address', 'items']);

    $customerName = sanitizeString($input['customer_name'], 100);
    $customerPhone = sanitizeString($input['customer_phone'], 20);
    $customerAddress = trim($input['customer_address']);
    $customerNote = isset($input['customer_note']) ? trim($input['customer_note']) : '';

    if (strlen($customerName) < 2) {
        errorResponse('Gecerli bir isim girin', 400);
    }
    if (strlen($customerPhone) < 10) {
        errorResponse('Gecerli bir telefon numarasi girin', 400);
    }
    if (strlen($customerAddress) < 10) {
        errorResponse('Gecerli bir adres girin', 400);
    }
    if (!is_array($input['items']) || empty($input['items'])) {
        errorResponse('Sepet bos olamaz', 400);
    }

    Database::beginTransaction();

    try {
        $totalAmount = 0;
        $validatedItems = [];

        // Urunleri dogrula
        foreach ($input['items'] as $item) {
            if (!isset($item['barcode']) || !isset($item['quantity'])) {
                throw new Exception('Gecersiz urun bilgisi');
            }

            $barcode = sanitizeString($item['barcode'], 50);
            $quantity = getPositiveInt($item['quantity']);

            if ($quantity <= 0) {
                throw new Exception('Gecersiz miktar');
            }

            // Urunu getir
            $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ? AND is_active = 1");
            $stmt->execute([$barcode]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Urun bulunamadi: $barcode");
            }

            $unitPrice = (float) $product['price'];
            $itemTotal = $unitPrice * $quantity;
            $totalAmount += $itemTotal;

            $validatedItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'product_barcode' => $product['barcode'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal
            ];
        }

        // Siparis numarasi olustur
        $orderNo = 'PKT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Siparis kaydi olustur
        $stmt = $pdo->prepare("
            INSERT INTO package_orders (order_no, customer_name, customer_phone, customer_address, customer_note, total_amount)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderNo, $customerName, $customerPhone, $customerAddress, $customerNote, $totalAmount]);
        $orderId = (int) $pdo->lastInsertId();

        // Siparis kalemlerini kaydet
        $stmt = $pdo->prepare("
            INSERT INTO package_order_items (order_id, product_id, product_name, product_barcode, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($validatedItems as $item) {
            $stmt->execute([
                $orderId,
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
            'order_id' => $orderId,
            'order_no' => $orderNo,
            'total_amount' => $totalAmount,
            'item_count' => count($validatedItems)
        ], 'Siparis alindi', 201);

    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
}

/**
 * Siparis listele veya detay getir
 */
function handleGet(PDO $pdo, array $currentUser): void {
    // Tek siparis detayi
    if (isset($_GET['id'])) {
        $id = getPositiveInt($_GET['id']);

        $stmt = $pdo->prepare("
            SELECT o.*, u.full_name as completed_by_name
            FROM package_orders o
            LEFT JOIN users u ON o.completed_by = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            errorResponse('Siparis bulunamadi', 404);
        }

        // Siparis kalemleri
        $stmt = $pdo->prepare("SELECT * FROM package_order_items WHERE order_id = ?");
        $stmt->execute([$id]);
        $order['items'] = $stmt->fetchAll();

        successResponse($order);
    }

    // Siparis listesi
    $status = $_GET['status'] ?? '';
    $whereClause = '1=1';
    $params = [];

    // Durum filtresi
    if ($status && in_array($status, ['pending', 'preparing', 'ready', 'completed', 'cancelled'])) {
        $whereClause .= ' AND o.status = ?';
        $params[] = $status;
    }

    // Aktif siparisler (tamamlanmamis)
    if (isset($_GET['active']) && $_GET['active'] === '1') {
        $whereClause .= ' AND o.status IN ("pending", "preparing", "ready")';
    }

    // Tarih filtresi
    if (isset($_GET['date_from'])) {
        $whereClause .= ' AND o.created_at >= ?';
        $params[] = $_GET['date_from'] . ' 00:00:00';
    }
    if (isset($_GET['date_to'])) {
        $whereClause .= ' AND o.created_at <= ?';
        $params[] = $_GET['date_to'] . ' 23:59:59';
    }

    // Pagination
    $pagination = getPaginationParams();

    // Toplam sayi
    $countSql = "SELECT COUNT(*) FROM package_orders o WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();

    // Veri cek
    $sql = "
        SELECT o.*, u.full_name as completed_by_name
        FROM package_orders o
        LEFT JOIN users u ON o.completed_by = u.id
        WHERE $whereClause
        ORDER BY
            CASE o.status
                WHEN 'pending' THEN 1
                WHEN 'preparing' THEN 2
                WHEN 'ready' THEN 3
                ELSE 4
            END,
            o.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $params[] = $pagination['limit'];
    $params[] = $pagination['offset'];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Bekleyen siparis sayisi
    $pendingStmt = $pdo->query("SELECT COUNT(*) FROM package_orders WHERE status IN ('pending', 'preparing', 'ready')");
    $pendingCount = (int) $pendingStmt->fetchColumn();

    successResponse([
        'items' => $orders,
        'pagination' => createPaginationMeta($totalItems, $pagination['page'], $pagination['limit']),
        'pending_count' => $pendingCount
    ]);
}

/**
 * Siparis durumu guncelle
 */
function handleUpdate(PDO $pdo, array $currentUser): void {
    $input = getJsonInput();
    validateRequired($input, ['id']);

    $id = getPositiveInt($input['id']);

    // Siparisi getir
    $stmt = $pdo->prepare("SELECT * FROM package_orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        errorResponse('Siparis bulunamadi', 404);
    }

    // Durum guncelleme
    if (isset($input['status'])) {
        $newStatus = $input['status'];
        $validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];

        if (!in_array($newStatus, $validStatuses)) {
            errorResponse('Gecersiz durum', 400);
        }

        // Tamamlandi ise satisa donustur
        if ($newStatus === 'completed' && $order['status'] !== 'completed') {
            Database::beginTransaction();

            try {
                // Siparis kalemlerini al
                $stmt = $pdo->prepare("SELECT * FROM package_order_items WHERE order_id = ?");
                $stmt->execute([$id]);
                $items = $stmt->fetchAll();

                // Satis kaydi olustur
                $stmt = $pdo->prepare("
                    INSERT INTO sales (user_id, total_amount, payment_method)
                    VALUES (?, ?, 'cash')
                ");
                $stmt->execute([$currentUser['id'], $order['total_amount']]);
                $saleId = (int) $pdo->lastInsertId();

                // Satis kalemlerini kaydet
                $stmt = $pdo->prepare("
                    INSERT INTO sale_items (sale_id, product_id, product_name, product_barcode, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($items as $item) {
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

                // Siparis durumunu guncelle
                $stmt = $pdo->prepare("
                    UPDATE package_orders
                    SET status = 'completed', completed_by = ?, completed_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$currentUser['id'], $id]);

                Database::commit();

                successResponse([
                    'sale_id' => $saleId,
                    'message' => 'Siparis tamamlandi ve satisa eklendi'
                ], 'Siparis tamamlandi');

            } catch (Exception $e) {
                Database::rollback();
                throw $e;
            }
        } else {
            // Normal durum guncelleme
            $stmt = $pdo->prepare("UPDATE package_orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);

            successResponse(null, 'Siparis durumu guncellendi');
        }
    } else {
        errorResponse('Guncellenecek alan yok', 400);
    }
}

/**
 * Siparis iptal et
 */
function handleDelete(PDO $pdo, array $currentUser): void {
    $input = getJsonInput();
    validateRequired($input, ['id']);

    $id = getPositiveInt($input['id']);

    // Siparisi getir
    $stmt = $pdo->prepare("SELECT * FROM package_orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        errorResponse('Siparis bulunamadi', 404);
    }

    if ($order['status'] === 'completed') {
        errorResponse('Tamamlanmis siparis silinemez', 400);
    }

    // Siparisi iptal et (silmek yerine)
    $stmt = $pdo->prepare("UPDATE package_orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$id]);

    successResponse(null, 'Siparis iptal edildi');
}
