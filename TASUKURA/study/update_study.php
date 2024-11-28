<?php
require '../db-connect/db-connect.php';
session_start();

// ユーザーがログインしていない場合リダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/logininput.php");
    exit;
}

// ユーザーIDを取得
$user_id = $_SESSION['user_id'];
$date = $_POST['date'] ?? null;
$subject_names = $_POST['subject_name'] ?? [];
$study_times = $_POST['study_time'] ?? [];

// 日付が正しいかを検証
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo "<script>alert('無効な日付が指定されています。'); window.location.href='study_management.php';</script>";
    exit;
}

// トランザクションを開始
$pdo->beginTransaction();

try {
    foreach ($subject_names as $subject_id => $subject_name) {
        $study_time = $study_times[$subject_id] ?? null;
    
        if (!$study_time) {
            throw new Exception("学習時間が指定されていません。");
        }
    
        // データを更新
        $query = $pdo->prepare("
            UPDATE Study 
            SET subject_name = :subject_name, study_time = :study_time 
            WHERE user_id = :user_id AND study_date = :date AND subject = :subject_id
        ");
        $query->execute([
            'subject_name' => $subject_name,
            'study_time' => $study_time,
            'user_id' => $user_id,
            'date' => $date,
            'subject_id' => $subject_id,
        ]);
    }

    // トランザクションをコミット
    $pdo->commit();

    // アラートを表示して元の画面にリダイレクト
    echo "<script>alert('データが更新されました。'); window.location.href='study_management.php';</script>";
} catch (Exception $e) {
    // エラー時はロールバックし、エラーをアラートで表示
    $pdo->rollBack();
    echo "<script>alert('データ更新中にエラーが発生しました: " . addslashes($e->getMessage()) . "'); window.location.href='study_management.php';</script>";
}
?>
