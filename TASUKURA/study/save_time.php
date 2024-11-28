<?php
// セッション開始とデータベース接続
session_start();
require '../db-connect/db-connect.php';

// POSTデータを取得
$manual_subject = isset($_POST['manual_subject']) ? trim($_POST['manual_subject']) : '';
$hours = isset($_POST['hours']) ? (int)$_POST['hours'] : 0;
$minutes = isset($_POST['minutes']) ? (int)$_POST['minutes'] : 0;
$seconds = isset($_POST['seconds']) ? (int)$_POST['seconds'] : 0;

// 秒数としての経過時間を計算
$elapsed_time = ($hours * 3600) + ($minutes * 60) + $seconds;

// ユーザーIDをセッションから取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 今日の日付を取得
$study_date = date('Y-m-d');

// hh:mm:ss 形式にフォーマット
$study_time = sprintf('%02d:%02d:%02d', floor($elapsed_time / 3600), floor(($elapsed_time % 3600) / 60), $elapsed_time % 60);

// 科目名とユーザーIDの確認
if (!empty($manual_subject) && $user_id > 0) {
    try {
        // SQLクエリの準備
        $stmt = $pdo->prepare("INSERT INTO Study (subject_name, user_id, study_date, study_time) 
                               VALUES (:subject_name, :user_id, :study_date, :study_time)");
        $stmt->bindParam(':subject_name', $manual_subject);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':study_date', $study_date);
        $stmt->bindParam(':study_time', $study_time);

        // データの挿入実行
        if ($stmt->execute()) {
            // データが正常に保存された場合に study_management.php へリダイレクト
            if (!headers_sent()) {
                header('Location: study_management.php');
                exit();
            } else {
                echo "Headers already sent. Cannot redirect.";
            }
        } else {
            // データ挿入に失敗した場合のアラート表示
            echo "<script>alert('データ保存に失敗しました。'); window.location.href = 'study_management.php';</script>";
        }
    } catch (Exception $e) {
        // 例外発生時のアラート表示
        echo "<script>alert('エラーが発生しました: " . addslashes($e->getMessage()) . "'); window.location.href = 'study_management.php';</script>";
    }
} else {
    // 科目名が入力されていないか、ユーザーIDが無効な場合のアラート表示
    echo "<script>alert('科目名またはユーザーIDが不正です。'); window.location.href = 'study_management.php';</script>";
}

?>
