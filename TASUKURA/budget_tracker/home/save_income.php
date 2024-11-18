<?php
session_start();
require_once '../../db-connect/db-connect.php';

// データベース接続
$pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);

// フォームからのデータ取得
$user_id = $_SESSION['user_id'];
$amount = $_POST['amount'];
$date = $_POST['date'];
$category = $_POST['category'];
$content = $_POST['description'];

// データ挿入のSQL（テーブル名を DailyIncome に修正）
$sql = "INSERT INTO DailyIncome (user_id, daily, ingo, category, content) VALUES (:user_id, :daily, :ingo, :category, :content)";
$stmt = $pdo->prepare($sql);

// パラメータをバインド
$stmt->bindValue(':user_id', $user_id);
$stmt->bindValue(':daily', $date);
$stmt->bindValue(':ingo', $amount);  // ingp カラム名を維持
$stmt->bindValue(':category', $category);
$stmt->bindValue(':content', $content);

// 実行
$stmt->execute();

// 完了ページへリダイレクト
header('Location: complete.php');
exit;
?>