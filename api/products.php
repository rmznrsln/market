<?php
/**
 * Ürün API'si
 * GET    - Tüm ürünleri listele veya barkod ile ara
 * POST   - Yeni ürün ekle
 * PUT    - Ürün güncelle
 * DELETE - Ürün sil
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

switch ($method) {
    case 'GET':
        // Barkod ile ara veya tüm ürünleri listele
        if (isset($_GET['barcode'])) {
            $barcode = trim($_GET['barcode']);
            $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
            $stmt->execute([$barcode]);
            $product = $stmt->fetch();

            if ($product) {
                jsonResponse($product);
            } else {
                jsonResponse(['error' => 'Ürün bulunamadı'], 404);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
            $products = $stmt->fetchAll();
            jsonResponse($products);
        }
        break;

    case 'POST':
        // Yeni ürün ekle
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['barcode']) || empty($input['name']) || !isset($input['price'])) {
            jsonResponse(['error' => 'Barkod, ürün adı ve fiyat zorunludur'], 400);
        }

        $barcode = trim($input['barcode']);
        $name = trim($input['name']);
        $price = floatval($input['price']);

        if ($price < 0) {
            jsonResponse(['error' => 'Fiyat negatif olamaz'], 400);
        }

        // Barkod kontrolü
        $stmt = $pdo->prepare("SELECT id FROM products WHERE barcode = ?");
        $stmt->execute([$barcode]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Bu barkod zaten kayıtlı'], 409);
        }

        $stmt = $pdo->prepare("INSERT INTO products (barcode, name, price) VALUES (?, ?, ?)");
        $stmt->execute([$barcode, $name, $price]);

        $id = $pdo->lastInsertId();
        jsonResponse([
            'success' => true,
            'message' => 'Ürün başarıyla eklendi',
            'id' => $id
        ], 201);
        break;

    case 'PUT':
        // Ürün güncelle
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id'])) {
            jsonResponse(['error' => 'Ürün ID zorunludur'], 400);
        }

        $id = intval($input['id']);
        $updates = [];
        $params = [];

        if (!empty($input['barcode'])) {
            // Barkod benzersizlik kontrolü
            $stmt = $pdo->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
            $stmt->execute([trim($input['barcode']), $id]);
            if ($stmt->fetch()) {
                jsonResponse(['error' => 'Bu barkod başka bir ürüne ait'], 409);
            }
            $updates[] = "barcode = ?";
            $params[] = trim($input['barcode']);
        }

        if (!empty($input['name'])) {
            $updates[] = "name = ?";
            $params[] = trim($input['name']);
        }

        if (isset($input['price'])) {
            $price = floatval($input['price']);
            if ($price < 0) {
                jsonResponse(['error' => 'Fiyat negatif olamaz'], 400);
            }
            $updates[] = "price = ?";
            $params[] = $price;
        }

        if (empty($updates)) {
            jsonResponse(['error' => 'Güncellenecek alan bulunamadı'], 400);
        }

        $params[] = $id;
        $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            jsonResponse(['error' => 'Ürün bulunamadı'], 404);
        }

        jsonResponse(['success' => true, 'message' => 'Ürün güncellendi']);
        break;

    case 'DELETE':
        // Ürün sil
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id'])) {
            jsonResponse(['error' => 'Ürün ID zorunludur'], 400);
        }

        $id = intval($input['id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(['error' => 'Ürün bulunamadı'], 404);
        }

        jsonResponse(['success' => true, 'message' => 'Ürün silindi']);
        break;

    default:
        jsonResponse(['error' => 'Desteklenmeyen metod'], 405);
}
