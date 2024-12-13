<?php
session_start();

// データベース接続情報
$host = 'mysql310.phy.lolipop.lan';
$dbname = 'LAA1517469-taskura';
$username = 'LAA1517469';
$password = '1234';

try {
    // PDOインスタンスの作成
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続失敗: " . $e->getMessage());
}

// 日付とユーザーIDが指定されているか確認
if (isset($_GET['date']) && isset($_SESSION['user_id'])) {
    $date = $_GET['date'];
    $user_id = $_SESSION['user_id']; // セッションからユーザーIDを取得

    // 日付の形式をチェック
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        try {
            // ユーザーIDと日付でスケジュールを絞り込むSQL文を準備
            $stmt = $pdo->prepare("
                SELECT itemNo, title, starttime, endtime
                FROM Managements
                WHERE user_id = :user_id 
                AND DATE(starttime) <= :date AND DATE(endtime) >= :date
            ");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // ユーザーIDの型を指定
            $stmt->bindParam(':date', $date, PDO::PARAM_STR); // 日付の型を指定
            $stmt->execute();

            // スケジュールを取得してJSONで出力
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode($schedules);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'スケジュールの取得に失敗しました: ' . $e->getMessage()]);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => '無効な日付形式です']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => '日付またはユーザーIDが指定されていません']);
}