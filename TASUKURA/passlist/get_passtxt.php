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
$pass_id = isset($_POST['pass_id']) ? $_POST['pass_id'] : null;
if (!$pass_id) {
    echo json_encode(['success' => false, 'error' => '無効なID']);
    exit;
}

// データベース接続
$pdo = connectDB();

try {
    // パスワードを取得
    $stmt = $pdo->prepare("SELECT passtxt, arcaneKey FROM PassList WHERE pass_id = ? AND user_id = ?");
    $stmt->execute([$pass_id, $_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $key = base64_decode($result['arcaneKey']);
        $combined = base64_decode($result['passtxt']);

        $iv = substr($combined, 0, 16);
        $ciphertext = substr($combined, 16);

        $decryptionPasstxt = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);

        echo json_encode(['success' => true, 'passtxt' => $decryptionPasstxt]);
    } else {
        echo json_encode(['success' => false, 'error' => 'パスワードが見つかりません']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DBエラー: ' . $e->getMessage()]);
}
