<?php
/**
 * Satış API'si
 * GET  - Satış geçmişini listele
 * POST - Yeni satış oluştur
 */

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
        // Satış geçmişi
        if (isset($_GET['id'])) {
            // Tek satış detayı
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
            $stmt->execute([$id]);
            $sale = $stmt->fetch();

            if (!$sale) {
                jsonResponse(['error' => 'Satış bulunamadı'], 404);
            }

            $stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
            $stmt->execute([$id]);
            $sale['items'] = $stmt->fetchAll();

            jsonResponse($sale);
        } else {
            // Tüm satışlar
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $stmt = $pdo->prepare("SELECT * FROM sales ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$limit]);
            $sales = $stmt->fetchAll();
            jsonResponse($sales);
        }
        break;

    case 'POST':
        // Yeni satış oluştur
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['items']) || !is_array($input['items'])) {
            jsonResponse(['error' => 'Sepet boş olamaz'], 400);
        }

        $pdo->beginTransaction();

        try {
            $totalAmount = 0;

            // Önce toplam tutarı hesapla
            foreach ($input['items'] as $item) {
                $totalAmount += floatval($item['total_price']);
            }

            // Satış kaydı oluştur
            $stmt = $pdo->prepare("INSERT INTO sales (total_amount) VALUES (?)");
            $stmt->execute([$totalAmount]);
            $saleId = $pdo->lastInsertId();

            // Satış detaylarını ekle
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

            jsonResponse([
                'success' => true,
                'message' => 'Satış başarıyla tamamlandı',
                'sale_id' => $saleId,
                'total_amount' => $totalAmount
            ], 201);

        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['error' => 'Satış kaydedilemedi: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Desteklenmeyen metod'], 405);
}
