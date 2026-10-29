<?php
require dirname(__DIR__, 2) . '/src/bootstrap.php';
$user = requireUser(); requireCsrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => '僅接受 POST'], 405);
$data = requestData(); $recipient = trim((string)($data['recipient_name'] ?? '')); $phone = trim((string)($data['recipient_phone'] ?? '')); $address = trim((string)($data['shipping_address'] ?? ''));
if ($recipient === '' || $phone === '' || $address === '') jsonResponse(['error' => '請填寫完整收件資料'], 422);
$pdo = db();
try {
    $pdo->beginTransaction();
    $itemsStmt = $pdo->prepare('SELECT ci.product_id, ci.quantity, p.name, p.price, p.stock FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ? FOR UPDATE'); $itemsStmt->execute([$user['id']]); $items = $itemsStmt->fetchAll();
    if (!$items) throw new RuntimeException('購物車沒有商品');
    $total = 0; foreach ($items as $item) { if ($item['quantity'] > $item['stock']) throw new RuntimeException("「{$item['name']}」庫存不足"); $total += $item['quantity'] * $item['price']; }
    $orderNo = 'M' . date('YmdHis') . random_int(100, 999);
    $order = $pdo->prepare('INSERT INTO orders (order_no, user_id, total_amount, recipient_name, recipient_phone, shipping_address) VALUES (?, ?, ?, ?, ?, ?)'); $order->execute([$orderNo, $user['id'], $total, $recipient, $phone, $address]); $orderId = (int)$pdo->lastInsertId();
    $line = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)'); $reduce = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
    foreach ($items as $item) { $line->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']]); $reduce->execute([$item['quantity'], $item['product_id']]); }
    $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$user['id']]); $pdo->commit(); jsonResponse(['message' => '訂單已建立', 'order_no' => $orderNo], 201);
} catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); jsonResponse(['error' => $e->getMessage()], 422); }
