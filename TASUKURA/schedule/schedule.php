<?php
session_start();
require '../db-connect/db-connect.php'; // Include the database connection file

// セッションからuser_idを取得
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    die('ログインユーザーが無効です');
}

// 最大のitemNoを取得して+1
$result = $mysqli->query("SELECT MAX(itemNo) AS max_itemNo FROM Managements");
$row = $result->fetch_assoc();
$itemNo = $row['max_itemNo'] + 1;

// フォームからタイトルと開始時刻・終了時刻を取得（例としてPOSTを想定）
if (isset($_POST['title']) && isset($_POST['starttime']) && isset($_POST['endtime'])) {
    $title = $_POST['title'];
    $starttime = $_POST['starttime'];
    $endtime = $_POST['endtime'];
} else {
    die('タイトル、開始時刻、終了時刻を入力してください');
}

// 現在の日時を取得
$inputdate = date('Y-m-d H:i:s');

// SQLインサートクエリ
$sql = "INSERT INTO Managements (user_id, itemNo, status, title, content, inputdate, starttime, endtime, checks) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

// プリペアドステートメントの作成
$stmt = $mysqli->prepare($sql);
$status = 's';
$content = '';  // contentは空にする
$checks = '';   // checksも空にする

// パラメータをバインド
$stmt->bind_param("iisssssss", $user_id, $itemNo, $status, $title, $content, $inputdate, $starttime, $endtime, $checks);

// クエリを実行
if ($stmt->execute()) {
    echo "データが正常に挿入されました";
} else {
    echo "エラー: " . $stmt->error;
}

// ステートメントと接続を閉じる
$stmt->close();
$mysqli->close();
?>