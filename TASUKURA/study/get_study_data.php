<?php
require '../db-connect/db-connect.php';
session_start();

// ユーザーがログインしているか確認
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 現在の月の全データを取得
$year = $_GET['year'];
$month = $_GET['month'] + 1; // JavaScriptの月は0始まりなので+1

$query = $pdo->prepare("
    SELECT 
        study_date, 
        subject_name, 
        SEC_TO_TIME(SUM(TIME_TO_SEC(study_time))) AS total_time 
    FROM Study 
    WHERE user_id = :user_id 
      AND YEAR(study_date) = :year 
      AND MONTH(study_date) = :month
    GROUP BY study_date, subject_name
    ORDER BY study_date, subject_name
");
$query->execute(['user_id' => $user_id, 'year' => $year, 'month' => $month]);

$data = $query->fetchAll(PDO::FETCH_ASSOC);

// JSON形式で返す
header('Content-Type: application/json');
echo json_encode($data);
?>
