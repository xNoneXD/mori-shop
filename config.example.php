<?php
// 複製本檔為 config.php，並依本機 MySQL 設定調整。
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'mori_shop',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'upload_dir' => __DIR__ . '/public/uploads',
    'max_upload_bytes' => 2 * 1024 * 1024,
];
