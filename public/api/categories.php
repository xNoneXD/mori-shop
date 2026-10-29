<?php
require dirname(__DIR__, 2) . '/src/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') { jsonResponse(['categories' => db()->query('SELECT id, name, slug FROM categories ORDER BY id')->fetchAll()]); }
requireAdmin(); requireCsrf(); $data = requestData();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($data['name'] ?? '')); $slug = strtolower(trim((string)($data['slug'] ?? '')));
    if ($name === '' || !preg_match('/^[a-z0-9-]{2,80}$/', $slug)) jsonResponse(['error' => '請填寫分類名稱與英文 slug（小寫、數字、連字號）'], 422);
    try { db()->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)')->execute([$name, $slug]); jsonResponse(['message' => '分類已新增'], 201); } catch (PDOException) { jsonResponse(['error' => '分類 slug 已存在'], 409); }
}
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT); $name = trim((string)($data['name'] ?? '')); $slug = strtolower(trim((string)($data['slug'] ?? '')));
    if (!$id || $name === '' || !preg_match('/^[a-z0-9-]{2,80}$/', $slug)) jsonResponse(['error' => '分類資料不正確'], 422);
    try { db()->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, $id]); jsonResponse(['message' => '分類已更新']); } catch (PDOException) { jsonResponse(['error' => '分類 slug 已存在'], 409); }
}
jsonResponse(['error' => '不支援的請求'], 405);
