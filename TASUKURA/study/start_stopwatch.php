<?php
require '../db-connect/db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $user_id = 1;  // 仮のユーザーID

    // 'elapsed_time' がセットされているか確認し、なければデフォルトで0を設定
    $elapsed_time = isset($_POST['elapsed_time']) ? $_POST['elapsed_time'] : 0;

    // 経過時間をhh:mm:ss形式に変換
    $hours = floor($elapsed_time / 3600);
    $minutes = floor(($elapsed_time % 3600) / 60);
    $seconds = $elapsed_time % 60;
    $formatted_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

    // 今日の日付
    $today = date('Y-m-d');

    // データベースに挿入
    $stmt = $pdo->prepare("
        INSERT INTO Study (subject, user_id, study_date, study_time) 
        VALUES (:subject, :user_id, :today, :study_time)
    ");
    $stmt->execute([
        'subject' => $subject,
        'user_id' => $user_id,
        'today' => $today,
        'study_time' => $formatted_time
    ]);

    // 出力が行われる前にリダイレクトを実行
    if (!headers_sent()) {
        header('Location: study_management.php');
        exit();  // header()後にスクリプトの実行を停止
    } else {
        // デバッグ: Headersが送信されている場合の処理
        echo "Headers already sent. Cannot redirect.";
    }
}
?>
