<?php
session_start(); // セッションの開始

// データベース接続情報
require '../db-connect/db-connect.php';

// データベース接続とSQL実行
$pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// セッションからユーザーIDを取得
if (!isset($_SESSION['user_id'])) {
    throw new Exception("ログインユーザーが無効です。");
}
$user_id = $_SESSION['user_id'];

// 年度の取得（デフォルトは今年）
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$sql = "SELECT DATE_FORMAT(daily, '%Y-%m') AS month, SUM(outgo) AS total_outgo 
    FROM DailySpend 
    WHERE user_id = :user_id AND YEAR(daily) = :year 
    GROUP BY month 
    ORDER BY month";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// JSONとして返す
header('Content-Type: application/json');
echo json_encode($data);
?>
