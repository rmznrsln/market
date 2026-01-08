<?php
/**
 * Telegram Bildirim Sistemi
 */

// Telegram Bot Ayarlari
define('TELEGRAM_BOT_TOKEN', '8003064904:AAFBFcyTBGiCwXRKACAh8cdAlvBhsQpFQBA');
define('TELEGRAM_CHAT_ID', '8360566668');

/**
 * Telegram'a mesaj gonder
 */
function sendTelegramMessage(string $message, ?string $chatId = null): bool {
    $chatId = $chatId ?? TELEGRAM_CHAT_ID;

    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);

    try {
        $result = @file_get_contents($url, false, $context);
        return $result !== false;
    } catch (Exception $e) {
        error_log("Telegram mesaj hatasi: " . $e->getMessage());
        return false;
    }
}

/**
 * Yeni siparis bildirimi gonder
 */
function sendNewOrderNotification(array $order, array $items): bool {
    $message = "🛒 <b>YENİ SİPARİŞ!</b>\n\n";
    $message .= "📋 <b>Sipariş No:</b> {$order['order_no']}\n";
    $message .= "👤 <b>Müşteri:</b> {$order['customer_name']}\n";
    $message .= "📞 <b>Telefon:</b> {$order['customer_phone']}\n";
    $message .= "📍 <b>Adres:</b> {$order['customer_address']}\n";

    if (!empty($order['customer_note'])) {
        $message .= "📝 <b>Not:</b> {$order['customer_note']}\n";
    }

    $message .= "\n<b>Ürünler:</b>\n";
    $message .= "─────────────\n";

    $subtotal = 0;
    foreach ($items as $item) {
        $message .= "• {$item['product_name']} x{$item['quantity']} = " .
                    number_format($item['total_price'], 2) . " TL\n";
        $subtotal += $item['total_price'];
    }

    $message .= "─────────────\n";

    // Paket servis ucreti varsa goster
    $deliveryFee = isset($order['delivery_fee']) ? (float)$order['delivery_fee'] : 0;
    if ($deliveryFee > 0) {
        $message .= "📦 <b>Ara Toplam:</b> " . number_format($subtotal, 2) . " TL\n";
        $message .= "🚚 <b>Paket Servis:</b> " . number_format($deliveryFee, 2) . " TL\n";
        $message .= "─────────────\n";
    }

    $message .= "💰 <b>TOPLAM:</b> " . number_format($order['total_amount'], 2) . " TL\n";
    $message .= "\n⏰ " . date('d.m.Y H:i');

    return sendTelegramMessage($message);
}

/**
 * Siparis durumu degisikligi bildirimi
 */
function sendOrderStatusNotification(string $orderNo, string $status, string $customerName): bool {
    $statusLabels = [
        'pending' => '⏳ Bekliyor',
        'preparing' => '👨‍🍳 Hazırlanıyor',
        'ready' => '✅ Hazır',
        'completed' => '🎉 Tamamlandı',
        'cancelled' => '❌ İptal Edildi'
    ];

    $statusText = $statusLabels[$status] ?? $status;

    $message = "📦 <b>SİPARİŞ DURUMU DEĞİŞTİ</b>\n\n";
    $message .= "📋 <b>Sipariş:</b> {$orderNo}\n";
    $message .= "👤 <b>Müşteri:</b> {$customerName}\n";
    $message .= "📊 <b>Yeni Durum:</b> {$statusText}\n";
    $message .= "\n⏰ " . date('d.m.Y H:i');

    return sendTelegramMessage($message);
}
