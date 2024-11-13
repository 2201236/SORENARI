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

// 日付パラメータが指定されているか確認
if (isset($_GET['date'])) {
    $date = $_GET['date'];

    try {
        // スケジュールを取得するSQL文を準備
        $stmt = $pdo->prepare("
            SELECT itemNo, title, starttime, endtime
            FROM Managements
            WHERE :date BETWEEN DATE(starttime) AND DATE(endtime)
        ");
        $stmt->bindParam(':date', $date, PDO::PARAM_STR); // 日付の型を指定
        $stmt->execute();

        // スケジュールを取得してJSONで出力
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($schedules);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'スケジュールの取得に失敗しました: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => '日付が指定されていません']);
}
