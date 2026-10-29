<?php
require dirname(__DIR__, 2) . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'me';
if ($action === 'me') jsonResponse(['user' => currentUser(), 'csrfToken' => csrfToken()]);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => '僅接受 POST'], 405);
requireCsrf();
$data = requestData();

if ($action === 'register') {
    $name = trim((string)($data['name'] ?? ''));
    $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = (string)($data['password'] ?? '');
    if (mb_strlen($name) < 2 || !$email || strlen($password) < 8) jsonResponse(['error' => '姓名、有效 Email 與至少 8 碼密碼皆為必填'], 422);
    $exists = db()->prepare('SELECT id FROM users WHERE email = ?'); $exists->execute([$email]);
    if ($exists->fetch()) jsonResponse(['error' => '此 Email 已註冊'], 409);
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    session_regenerate_id(true); $_SESSION['user_id'] = (int)db()->lastInsertId();
    jsonResponse(['message' => '註冊成功', 'user' => currentUser(), 'csrfToken' => csrfToken()], 201);
}
if ($action === 'login') {
    $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL); $password = (string)($data['password'] ?? '');
    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?'); $stmt->execute([$email]); $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) jsonResponse(['error' => 'Email 或密碼不正確'], 401);
    session_regenerate_id(true); $_SESSION['user_id'] = (int)$user['id'];
    jsonResponse(['message' => '登入成功', 'user' => currentUser(), 'csrfToken' => csrfToken()]);
}
if ($action === 'logout') { $_SESSION = []; session_destroy(); jsonResponse(['message' => '已登出']); }
jsonResponse(['error' => '找不到功能'], 404);
