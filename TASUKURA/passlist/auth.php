<?php
session_start();
header('Content-Type: application/json');

// DB接続設定
define('SERVER', 'mysql310.phy.lolipop.lan');
define('DBNAME', 'LAA1517469-taskura');
define('USER', 'LAA1517469');
define('PASS', '1234');

// データベース接続関数
function connectDB() {
    $dsn = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
    try {
        $pdo = new PDO($dsn, USER, PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => '接続エラー']);
        exit;
    }
}

// POSTデータの検証
$mailaddress = $_POST['user_id'];
$password = $_POST['passtxt'];

// データベース接続
$pdo = connectDB();

// パスワードをハッシュ化して取得
$stmt = $pdo->prepare("SELECT user_id, name FROM Users WHERE mailaddress = ? AND password = ?");
$stmt->execute([$mailaddress, $password]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    if (!isset($_SESSION)) {
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['name'] = htmlspecialchars($result['name']);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}