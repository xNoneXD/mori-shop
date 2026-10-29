# Mori 選物商店

以 PHP、MySQL 與原生 JavaScript 製作的購物網站專題，提供商品瀏覽、會員註冊與登入、購物車、建立訂單，以及管理員商品與分類管理功能。

## 功能

- 商品列表與分類瀏覽
- 會員註冊、登入與登出
- 會員購物車與結帳建立訂單
- 管理員商品、分類與訂單管理
- 管理員上傳商品圖片（JPEG、PNG、WebP，單張上限 2 MB）
- PDO prepared statements、密碼雜湊、CSRF token 與 session cookie 基本保護

## 使用技術

- PHP 8（需啟用 PDO MySQL；註冊功能亦需 mbstring）
- MySQL／MariaDB
- HTML、CSS、原生 JavaScript
- Apache（XAMPP）

## 本機安裝（XAMPP）

1. 將專案放入 `C:\xampp\htdocs\mori-shop`，啟動 Apache 與 MySQL。
2. 在 phpMyAdmin 匯入 `database/schema.sql`，建立資料庫與資料表。
3. 複製 `config.example.php` 為 `config.php`，依本機 MySQL 帳號密碼調整設定。`config.php` 已列入 Git 忽略規則，請勿提交真實密碼。
4. 開啟 [http://localhost/mori-shop/public/](http://localhost/mori-shop/public/) 使用商店；管理後台位於 [http://localhost/mori-shop/public/admin.php](http://localhost/mori-shop/public/admin.php)。

> 專案的網站入口位於 `public/`。若部署到正式環境，請將 Apache 網站根目錄指向此資料夾，並確保 `src/`、`database/` 及設定檔不對外公開。

## 建立管理員

先透過商店註冊一般帳號，再於 phpMyAdmin 執行下列 SQL，將該帳號升級為管理員（將 Email 換成註冊時使用的信箱）：

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

接著登入該帳號並開啟管理後台。

## 資料與安全提醒

- `database/schema.sql` 只建立資料表及商品分類，不含示範商品或管理員帳號。
- `public/uploads/` 是執行時存放商品圖片的位置；上傳內容會被 Git 忽略，目錄本身會保留。
- 請勿提交本機設定、資料庫備份、個人資料或正式環境上傳檔案。
- 此專題適合課程展示與本機開發；正式上線前應依部署環境檢視安全性、備份與錯誤處理設定。

## 專案結構

```text
mori-shop/
├── config.example.php   # 設定範本（複製為 config.php）
├── database/
│   └── schema.sql       # 資料庫結構與初始分類
├── public/              # Apache 網站入口、前端資源與 API
│   ├── api/
│   ├── uploads/         # 執行時上傳目錄
│   ├── admin.php
│   └── index.php
└── src/
    └── bootstrap.php    # Session、資料庫連線與共用驗證
```
