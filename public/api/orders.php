<?php
require dirname(__DIR__, 2) . '/src/bootstrap.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(['error' => '僅接受 GET'], 405);
$sql = 'SELECT o.id, o.order_no, o.total_amount, o.status, o.recipient_name, o.recipient_phone, o.shipping_address, o.created_at, u.name AS customer_name, u.email AS customer_email, GROUP_CONCAT(CONCAT(oi.product_name, " ×", oi.quantity) ORDER BY oi.id SEPARATOR "、") AS items FROM orders o JOIN users u ON o.user_id = u.id JOIN order_items oi ON oi.order_id = o.id GROUP BY o.id ORDER BY o.created_at DESC';
jsonResponse(['orders' => db()->query($sql)->fetchAll()]);
