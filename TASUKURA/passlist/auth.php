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

// POSTデータの受け取り
$mailaddress = $_POST['user_id'];
$password = $_POST['passtxt'];

// データベース接続
$pdo = connectDB();

if (!$_SESSION['is_logged_in']) {
    $stmt = $pdo->prepare("SELECT user_id, name, password FROM Users WHERE mailaddress = ?");
    $stmt->execute([$mailaddress]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && (password_verify($password, $result['password']) || $password === $result['password'])) {
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['name'] = htmlspecialchars($result['name']);
        $_SESSION['limited_session']['status'] = false;
        $_SESSION['is_logged_in'] = true;

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else if (!$_SESSION['limited_session']['status']) {
    $stmt = $pdo->prepare("SELECT password FROM Users WHERE user_id = ?");
    $stmt->execute([$mailaddress]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && (password_verify($password, $result['password']) || $password === $result['password'])) {
        $_SESSION['limited_session']['status'] = true;
        $_SESSION['limited_session']['counter'] = 60; // 60秒
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else if ($_SESSION['limited_session']['status']) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}