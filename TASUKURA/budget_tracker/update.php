<?php
session_start(); // セッションの開始

// データベース接続情報
require '../db-connect/db-connect.php';

try {
    // PDO接続
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // セッションからユーザーIDを取得
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // フォームからbudgetの値を取得
    if (isset($_POST['budget'])) {
        $budget = $_POST['budget'];

        // データベースのbudgetを更新
        $stmt = $pdo->prepare("UPDATE Bank SET budget = :budget WHERE user_id = :user_id");
        $stmt->bindParam(':budget', $budget, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // 成功した場合のレスポンス
        echo "予算が正常に更新されました。";
    } else {
        throw new Exception("予算の入力が無効です。");
    }

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
}
?>
