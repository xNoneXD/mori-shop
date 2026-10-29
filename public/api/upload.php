<?php
require dirname(__DIR__, 2) . '/src/bootstrap.php';
requireAdmin(); requireCsrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) jsonResponse(['error' => '請選擇圖片檔'], 422);
$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $config['max_upload_bytes']) jsonResponse(['error' => '上傳失敗或檔案超過 2MB'], 422);
$finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($file['tmp_name']); $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) jsonResponse(['error' => '僅接受 JPEG、PNG 或 WebP 圖片'], 422);
if (!is_dir($config['upload_dir'])) mkdir($config['upload_dir'], 0755, true);
$name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime]; $target = $config['upload_dir'] . '/' . $name;
if (!move_uploaded_file($file['tmp_name'], $target)) jsonResponse(['error' => '檔案儲存失敗'], 500);
jsonResponse(['message' => '上傳完成', 'path' => 'uploads/' . $name], 201);
