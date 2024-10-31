<?php
session_start();
header('Content-Type: application/json');

if (!$_SESSION['limited_session']) {
    echo json_encode(['success' => false, 'error' => 'セッション期限が切れています']);
}

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
        echo json_encode(['success' => false, 'error' => "接続エラー: " . $e->getMessage()]);
        exit;
    }
}

// POSTデータの検証
function validatePostData($field) {
    return isset($_POST[$field]) ? $_POST[$field] : null;
}

// データベース接続
$pdo = connectDB();

// リクエストがPOSTか確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // pass_idの検証
    $pass_id = validatePostData('pass_id');
    
    // pass_idがnullでないか確認
    if (!$pass_id) {
        echo json_encode(['success' => false, 'error' => '無効なIDです']);
        exit;
    }

    // 削除処理
    try {
        // セッションからユーザーIDを取得
        $user_id = $_SESSION['user_id'];

        // データベースから削除
        $stmt = $pdo->prepare("DELETE FROM PassList WHERE pass_id = ? AND user_id = ?");
        $stmt->execute([$pass_id, $user_id]);

        // 削除が成功したか確認
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => '削除に失敗しました。該当データが存在しないか、権限がありません。']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => '削除中にエラーが発生しました: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => '無効なリクエストメソッドです']);
}
