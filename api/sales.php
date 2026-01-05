<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
            $stmt->execute([$id]);
            $sale = $stmt->fetch();
            if (!$sale) {
                jsonResponse(['error' => 'Satis bulunamadi'], 404);
            }

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
        if (empty($input['items'])) {
            jsonResponse(['error' => 'Sepet bos'], 400);
        }

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
            jsonResponse(['error' => 'Satis kaydedilemedi'], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Desteklenmeyen metod'], 405);
}
