<?php
session_start(); // セッション開始

// セッションからユーザーIDを取得
$user_id = $_SESSION['user_id'];

// フォームからのデータを取得
$task = $_POST['task'];
$due_date = $_POST['due_date'];
$due_time = $_POST['due_time'];

// データベース接続設定
$host = 'mysql310.phy.lolipop.lan';
$dbname = 'LAA1517469-taskura';
$username = 'LAA1517469';
$password = '1234';

try {
    // データベース接続
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続失敗: " . $e->getMessage());
}

// Todoをデータベースに追加
$stmt = $pdo->prepare("INSERT INTO Todos (user_id, task, due_date, due_time) VALUES (:user_id, :task, :due_date, :due_time)");
$stmt->execute([
    'user_id' => $user_id,
    'task' => $task,
    'due_date' => $due_date,
    'due_time' => $due_time
]);

// リダイレクトして元のページに戻る
header('Location: todo.php');
exit;
?>
