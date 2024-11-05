<?php
session_start(); // セッション開始

// データベース接続設定
require '../../db-connect/db-connect.php';

try {
    // PDO接続
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // セッションからユーザーIDを取得
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // フォームデータを取得
    $content = $_POST['description'];  // 支出の内容
    $outgo = $_POST['amount'];         // 支出の金額
    $daily = $_POST['date'];           // 日付（YYYY-MM-DD形式）
    $num_id = $_POST['num_id'];        // 任意の識別子（番号など）

    // SQLクエリの準備
    $stmt = $pdo->prepare("INSERT INTO DailySpend (user_id, daily, num_id, outgo, content) VALUES (:user_id, :daily, :num_id, :outgo, :content)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':daily', $daily, PDO::PARAM_STR);
    $stmt->bindParam(':num_id', $num_id, PDO::PARAM_INT);
    $stmt->bindParam(':outgo', $outgo, PDO::PARAM_INT);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);

    // クエリの実行
    if ($stmt->execute()) {
        echo "支出データが正常に挿入されました。";
    } else {
        throw new Exception("支出データの挿入に失敗しました。");
    }

} catch (PDOException $e) {
    // データベースエラー時の処理
    echo "データベースエラー: " . $e->getMessage();
    exit;

} catch (Exception $e) {
    // その他のエラー時の処理
    echo "エラー: " . $e->getMessage();
    exit;
}
?>
