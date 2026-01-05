<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

switch ($method) {
    case 'GET':
        if (isset($_GET['barcode'])) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
            $stmt->execute([trim($_GET['barcode'])]);
            $product = $stmt->fetch();
            if ($product) {
                jsonResponse($product);
            } else {
                jsonResponse(['error' => 'Urun bulunamadi'], 404);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['barcode']) || empty($input['name']) || !isset($input['price'])) {
            jsonResponse(['error' => 'Barkod, urun adi ve fiyat zorunludur'], 400);
        }

        $barcode = trim($input['barcode']);
        $name = trim($input['name']);
        $price = floatval($input['price']);

        $stmt = $pdo->prepare("SELECT id FROM products WHERE barcode = ?");
        $stmt->execute([$barcode]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Bu barkod zaten kayitli'], 409);
        }

        $stmt = $pdo->prepare("INSERT INTO products (barcode, name, price) VALUES (?, ?, ?)");
        $stmt->execute([$barcode, $name, $price]);
        jsonResponse(['success' => true, 'message' => 'Urun eklendi', 'id' => $pdo->lastInsertId()], 201);
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id'])) {
            jsonResponse(['error' => 'ID zorunlu'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([intval($input['id'])]);
        if ($stmt->rowCount() === 0) {
            jsonResponse(['error' => 'Urun bulunamadi'], 404);
        }
        jsonResponse(['success' => true, 'message' => 'Urun silindi']);
        break;

    default:
        jsonResponse(['error' => 'Desteklenmeyen metod'], 405);
}
