<?php
session_start();
header('Content-Type: application/json');

// 定数を定義
define('SERVER', 'mysql310.phy.lolipop.lan');
define('DBNAME', 'LAA1517469-taskura');
define('USER', 'LAA1517469');
define('PASS', '1234');

// データベース接続を関数化
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

// POSTデータの検証を関数化
function validatePostData($field) {
    return isset($_POST[$field]) ? $_POST[$field] : null;
}

// データベース接続
$pdo = connectDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTフィールドの検証
    $pass_id = validatePostData('pass_id'); // 新規登録はNULL
    $url = validatePostData('url');
    $passName = validatePostData('passName');
    $passtxt = validatePostData('passtxt') ?: ''; // 空値を挿入
    $user_id = $_SESSION['user_id'];

    if ($pass_id != null) {
        // 更新処理
        try {
            if (!empty($passtxt)) {
                $stmt = $pdo->prepare("UPDATE PassList SET url = ?, passName = ?, passtxt = ? WHERE pass_id = ? AND user_id = ?");
                $stmt->execute([$url, $passName, $passtxt, $pass_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE PassList SET url = ?, passName = ? WHERE pass_id = ? AND user_id = ?");
                $stmt->execute([$url, $passName, $pass_id]);
            }
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        // 新規登録処理
        try {
            if (empty($passtxt)) {
                echo json_encode(['success' => false, 'error' => 'パスワードが空です']);
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO PassList (user_id, url, passName, passtxt) VALUES (?,?,?,?)");
                $stmt->execute([$user_id, $url, $passName, $passtxt]);
                echo json_encode(['success' => true]);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
