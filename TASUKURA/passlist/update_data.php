<?php
header('Content-Type: application/json');

const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1517469-taskura';
const USER ='LAA1517469';
const PASS ='1234';
$connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
$pdo=new PDO($connect, USER, PASS);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $user_id = $_POST['user_id'];
    $password = $_POST['password']; // 空であればパスワードは変更しないように後で処理

    // DB接続
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // パスワードが空でない場合のみ、パスワードも更新
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE your_table SET url = ?, passName = ?, password = ? WHERE id = ?");
            $stmt->execute([$url, $user_id, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE your_table SET url = ?, passName = ? WHERE id = ?");
            $stmt->execute([$url, $user_id, $id]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
