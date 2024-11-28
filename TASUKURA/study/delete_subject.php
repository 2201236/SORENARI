<?php
require '../db-connect/db-connect.php';
session_start();

// ユーザーがログインしていない場合リダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/logininput.php");
    exit;
}

// POSTデータを取得
$subject_id = $_POST['subject_id'] ?? null;
$date = $_POST['date'] ?? null;

// 必要なデータがない場合はエラーメッセージ
if (!$subject_id || !$date) {
    echo "<script>alert('削除するデータが正しくありません。'); window.location.href='study_management.php';</script>";
    exit;
}

// ユーザーIDを取得
$user_id = $_SESSION['user_id'];

// 削除クエリ
$query = $pdo->prepare("
    DELETE FROM Study 
    WHERE user_id = :user_id AND study_date = :date AND subject = :subject_id
");
$query->execute([
    'user_id' => $user_id,
    'date' => $date,
    'subject_id' => $subject_id
]);

// 削除後のリダイレクト
echo "<script>alert('科目が削除されました。'); window.location.href='study_management.php';</script>";
exit;
