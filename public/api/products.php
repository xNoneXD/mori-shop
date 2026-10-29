<?php
require dirname(__DIR__, 2) . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $category = trim($_GET['category'] ?? '');
    $showAll = ($_GET['all'] ?? '') === '1';
    if ($showAll) requireAdmin();
    $sql = 'SELECT p.id, p.category_id, p.name, p.slug, p.description, p.price, p.stock, p.image_path, p.is_active, c.name AS category, c.slug AS category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE ' . ($showAll ? '1=1' : 'p.is_active = 1');
    $params = [];
    if ($category !== '') { $sql .= ' AND c.slug = ?'; $params[] = $category; }
    $sql .= ' ORDER BY p.created_at DESC';
    $stmt = db()->prepare($sql); $stmt->execute($params);
    jsonResponse(['products' => $stmt->fetchAll()]);
}
requireAdmin(); requireCsrf(); $data = requestData();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($data['name'] ?? '')); $categoryId = filter_var($data['category_id'] ?? null, FILTER_VALIDATE_INT); $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_INT); $stock = filter_var($data['stock'] ?? 0, FILTER_VALIDATE_INT);
    if ($name === '' || !$categoryId || $price === false || $price < 0 || $stock === false || $stock < 0) jsonResponse(['error' => '商品欄位不正確'], 422);
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . bin2hex(random_bytes(3));
    $stmt = db()->prepare('INSERT INTO products (category_id, name, slug, description, price, stock, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$categoryId, $name, $slug, trim((string)($data['description'] ?? '')), $price, $stock, trim((string)($data['image_path'] ?? '')) ?: null]);
    jsonResponse(['id' => (int)db()->lastInsertId(), 'message' => '商品已建立'], 201);
}
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT); $name = trim((string)($data['name'] ?? '')); $categoryId = filter_var($data['category_id'] ?? null, FILTER_VALIDATE_INT); $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_INT); $stock = filter_var($data['stock'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $name === '' || !$categoryId || $price === false || $price < 0 || $stock === false || $stock < 0) jsonResponse(['error' => '商品欄位不正確'], 422);
    $stmt = db()->prepare('UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image_path = ?, is_active = ? WHERE id = ?');
    $stmt->execute([$categoryId, $name, trim((string)($data['description'] ?? '')), $price, $stock, trim((string)($data['image_path'] ?? '')) ?: null, !empty($data['is_active']) ? 1 : 0, $id]);
    jsonResponse(['message' => '商品已更新']);
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) jsonResponse(['error' => '商品資料不正確'], 422);
    db()->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([$id]);
    jsonResponse(['message' => '商品已下架']);
}
jsonResponse(['error' => '僅接受 GET、POST、PATCH 或 DELETE'], 405);
