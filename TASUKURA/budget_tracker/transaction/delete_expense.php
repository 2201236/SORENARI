<?php
session_start();
require_once '../../db-connect/db-connect.php';

header('Content-Type: application/json');

try {
    // セッションチェック
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('ログインが必要です。');
    }
    $user_id = $_SESSION['user_id'];

    // POSTデータの取得
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['id'])) {
        throw new Exception('必要なデータが不足しています。');
    }

    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    
    if ($id === false) {
        throw new Exception('入力データが不正です。');
    }

    // データベース接続
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // トランザクション開始
    $pdo->beginTransaction();

    // ユーザーの支出データであることを確認
    $stmt = $pdo->prepare("
        SELECT id FROM DailySpend 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    if (!$stmt->fetch()) {
        throw new Exception('不正なアクセスです。');
    }

    // 支出データの削除
    $stmt = $pdo->prepare("
        DELETE FROM DailySpend 
        WHERE id = :id AND user_id = :user_id
    ");

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    $stmt->execute();

    // トランザクションのコミット
    $pdo->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // トランザクションのロールバック
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}