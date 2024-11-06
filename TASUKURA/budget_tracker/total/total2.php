<?php
session_start(); // セッションの開始

// データベース接続情報
require '../../db-connect/db-connect.php';

// データベース接続とSQL実行
$pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// セッションからユーザーIDを取得
if (!isset($_SESSION['user_id'])) {
    throw new Exception("ログインユーザーが無効です。");
}
$user_id = $_SESSION['user_id'];

// 年と月のパラメータを取得
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : null;

if ($month) {
    // 月ごとの詳細データを取得
    $sql = "SELECT content, ingo, daily 
            FROM DailyIncome 
            WHERE user_id = :user_id AND YEAR(daily) = :year AND MONTH(daily) = :month
            ORDER BY daily";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // 年ごとの月別収入合計を取得
    $sql = "SELECT DATE_FORMAT(daily, '%Y-%m') AS month, SUM(ingo) AS total_ingo 
            FROM DailyIncome 
            WHERE user_id = :user_id AND YEAR(daily) = :year 
            GROUP BY month 
            ORDER BY month";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// JSONとして返す
header('Content-Type: application/json');
echo json_encode($data);
?>
