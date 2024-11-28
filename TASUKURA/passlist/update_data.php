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

// ランダムな文字列の生成
function generateRandomString($length) {
    // 指定した長さ分のランダムなバイト列を生成
    $randomBytes = openssl_random_pseudo_bytes($length);
    // Base64エンコードして文字列化
    return base64_encode($randomBytes);
}

// パスワードの暗号化
function encryption($passtxt){
    // ランダムな32バイトの文字列を生成し、キーを作成
    $key = generateRandomString(32); 
    // SHA-256でハッシュ化してAES-256で使用できる形式に変換
    $hashedKey = hash('sha256', $key, true); 
    
    // 初期化ベクトル（IV）の生成（16バイト）
    $iv = openssl_random_pseudo_bytes(16);

    // AES-256-CBCを使用してパスワードを暗号化
    $ciphertext = openssl_encrypt($passtxt, 'aes-256-cbc', $hashedKey, 0, $iv);

    $combined = base64_encode($iv . $ciphertext);

    // 暗号化に使ったキー（ハッシュ済み）とIVを保存
    // 後で復号に使うため、キーとIVは必ず保持しておく必要がある
    return [
        'key' => base64_encode($hashedKey), // Base64エンコードして返す
        'combined' => $combined
    ];
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
                $cypher = encryption($passtxt);
                $passtxt = $cypher['combined'];
                $key = $cypher['key'];

                $stmt = $pdo->prepare("UPDATE PassList SET url = ?, passName = ?, passtxt = ?, arcaneKey = ? WHERE pass_id = ? AND user_id = ?");
                $stmt->execute([$url, $passName, $passtxt, $key, $pass_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE PassList SET url = ?, passName = ? WHERE pass_id = ? AND user_id = ?");
                $stmt->execute([$url, $passName, $pass_id, $user_id]);
            }
            echo json_encode(['success' => true, 'message' => '更新しました']);
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
                $cypher = encryption($passtxt);
                $passtxt = $cypher['combined'];
                $key = $cypher['key'];

                $stmt = $pdo->prepare("INSERT INTO PassList (user_id, url, passName, passtxt, arcaneKey) VALUES (?,?,?,?,?)");
                $stmt->execute([$user_id, $url, $passName, $passtxt, $key]);
                echo json_encode(['success' => true, 'message' => '登録しました']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
