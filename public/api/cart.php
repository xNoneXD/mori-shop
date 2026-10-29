<?php
require dirname(__DIR__, 2) . '/src/bootstrap.php';
$user = requireUser();

function cartFor(int $userId): array {
    $stmt = db()->prepare('SELECT ci.product_id, ci.quantity, p.name, p.price, p.stock, p.image_path FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ? AND p.is_active = 1');
    $stmt->execute([$userId]); $items = $stmt->fetchAll();
    $total = array_sum(array_map(fn($i) => $i['quantity'] * $i['price'], $items));
    return ['items' => $items, 'total' => $total];
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') jsonResponse(cartFor((int)$user['id']));
requireCsrf(); $data = requestData(); $productId = filter_var($data['product_id'] ?? null, FILTER_VALIDATE_INT);
if (!$productId) jsonResponse(['error' => '商品資料不正確'], 422);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = max(1, min(99, (int)($data['quantity'] ?? 1)));
    $product = db()->prepare('SELECT stock FROM products WHERE id = ? AND is_active = 1'); $product->execute([$productId]); $stock = $product->fetchColumn();
    if ($stock === false || $stock < $quantity) jsonResponse(['error' => '商品庫存不足'], 422);
    $stmt = db()->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), 99)');
    $stmt->execute([$user['id'], $productId, $quantity]); jsonResponse(cartFor((int)$user['id']), 201);
}
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $quantity = (int)($data['quantity'] ?? 0);
    if ($quantity < 1) { $stmt = db()->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?'); $stmt->execute([$user['id'], $productId]); }
    else { $stmt = db()->prepare('UPDATE cart_items SET quantity = LEAST(?, 99) WHERE user_id = ? AND product_id = ?'); $stmt->execute([$quantity, $user['id'], $productId]); }
    jsonResponse(cartFor((int)$user['id']));
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') { $stmt = db()->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?'); $stmt->execute([$user['id'], $productId]); jsonResponse(cartFor((int)$user['id'])); }
jsonResponse(['error' => '不支援的請求'], 405);
