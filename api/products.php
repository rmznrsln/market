<?php
/**
 * Market Satış Sistemi - Ürün API
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
            handleGet($pdo);
            break;

        case 'POST':
            handlePost($pdo);
            break;

        case 'PUT':
            handlePut($pdo);
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
 * Ürün listele veya ara
 */
function handleGet(PDO $pdo): void {
    // Barkod ile ara
    if (isset($_GET['barcode'])) {
        $barcode = trim($_GET['barcode']);
        $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ? AND is_active = 1");
        $stmt->execute([$barcode]);
        $product = $stmt->fetch();

        if (!$product) {
            errorResponse('Ürün bulunamadı', 404);
        }

        successResponse($product);
    }

    // ID ile ara
    if (isset($_GET['id'])) {
        $id = getPositiveInt($_GET['id']);
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            errorResponse('Ürün bulunamadı', 404);
        }

        successResponse($product);
    }

    // Arama
    $search = isset($_GET['search']) ? sanitizeString($_GET['search'], 100) : '';
    $showInactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] === '1';

    // Pagination
    $pagination = getPaginationParams();

    // Query oluştur
    $whereClause = $showInactive ? '1=1' : 'is_active = 1';
    $params = [];

    if ($search) {
        $whereClause .= ' AND (name LIKE ? OR barcode LIKE ?)';
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    // Toplam sayı
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $whereClause");
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();

    // Veri çek
    $sql = "SELECT * FROM products WHERE $whereClause ORDER BY name ASC LIMIT ? OFFSET ?";
    $params[] = $pagination['limit'];
    $params[] = $pagination['offset'];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    successResponse([
        'items' => $products,
        'pagination' => createPaginationMeta($totalItems, $pagination['page'], $pagination['limit'])
    ]);
}

/**
 * Yeni ürün ekle
 */
function handlePost(PDO $pdo): void {
    $input = getJsonInput();
    validateRequired($input, ['barcode', 'name', 'price']);

    $barcode = sanitizeString($input['barcode'], 50);
    $name = sanitizeString($input['name'], 255);
    $price = getPositiveFloat($input['price']);
    $stock = getPositiveInt($input['stock'] ?? 0);

    // Validasyonlar
    if (strlen($barcode) < 1) {
        errorResponse('Barkod boş olamaz', 400);
    }
    if (strlen($name) < 1) {
        errorResponse('Ürün adı boş olamaz', 400);
    }
    if ($price <= 0) {
        errorResponse('Fiyat sıfırdan büyük olmalıdır', 400);
    }

    // Barkod kontrolü
    $stmt = $pdo->prepare("SELECT id FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    if ($stmt->fetch()) {
        errorResponse('Bu barkod zaten kayıtlı', 409);
    }

    // Ekle
    $stmt = $pdo->prepare("INSERT INTO products (barcode, name, price, stock) VALUES (?, ?, ?, ?)");
    $stmt->execute([$barcode, $name, $price, $stock]);

    successResponse([
        'id' => (int) $pdo->lastInsertId(),
        'barcode' => $barcode,
        'name' => $name,
        'price' => $price,
        'stock' => $stock
    ], 'Ürün eklendi', 201);
}

/**
 * Ürün güncelle
 */
function handlePut(PDO $pdo): void {
    $input = getJsonInput();
    validateRequired($input, ['id']);

    $id = getPositiveInt($input['id']);

    // Ürün var mı kontrol et
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        errorResponse('Ürün bulunamadı', 404);
    }

    $updates = [];
    $params = [];

    if (isset($input['name'])) {
        $name = sanitizeString($input['name'], 255);
        if (strlen($name) < 1) {
            errorResponse('Ürün adı boş olamaz', 400);
        }
        $updates[] = 'name = ?';
        $params[] = $name;
    }

    if (isset($input['price'])) {
        $price = getPositiveFloat($input['price']);
        if ($price <= 0) {
            errorResponse('Fiyat sıfırdan büyük olmalıdır', 400);
        }
        $updates[] = 'price = ?';
        $params[] = $price;
    }

    if (isset($input['stock'])) {
        $updates[] = 'stock = ?';
        $params[] = getPositiveInt($input['stock']);
    }

    if (isset($input['is_active'])) {
        $updates[] = 'is_active = ?';
        $params[] = $input['is_active'] ? 1 : 0;
    }

    if (isset($input['barcode'])) {
        $barcode = sanitizeString($input['barcode'], 50);
        // Başka üründe kullanılıyor mu kontrol et
        $stmt = $pdo->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
        $stmt->execute([$barcode, $id]);
        if ($stmt->fetch()) {
            errorResponse('Bu barkod başka bir üründe kullanılıyor', 409);
        }
        $updates[] = 'barcode = ?';
        $params[] = $barcode;
    }

    if (empty($updates)) {
        errorResponse('Güncellenecek alan yok', 400);
    }

    $params[] = $id;
    $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    successResponse(null, 'Ürün güncellendi');
}

/**
 * Ürün sil (sadece admin)
 */
function handleDelete(PDO $pdo, array $currentUser): void {
    // Sadece admin silebilir
    if ($currentUser['role'] !== ROLE_ADMIN) {
        errorResponse('Bu işlem için yetkiniz yok', 403);
    }

    $input = getJsonInput();
    validateRequired($input, ['id']);

    $id = getPositiveInt($input['id']);

    // Satışta kullanılmış mı kontrol et
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sale_items WHERE product_id = ?");
    $stmt->execute([$id]);
    $usageCount = (int) $stmt->fetchColumn();

    if ($usageCount > 0) {
        // Tamamen silme yerine pasif yap
        $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            errorResponse('Ürün bulunamadı', 404);
        }

        successResponse(null, 'Ürün pasif duruma alındı (satış geçmişinde kullanıldığı için silinemedi)');
    } else {
        // Tamamen sil
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            errorResponse('Ürün bulunamadı', 404);
        }

        successResponse(null, 'Ürün silindi');
    }
}
