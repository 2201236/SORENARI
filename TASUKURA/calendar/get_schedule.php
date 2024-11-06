<?php
session_start();
$host = 'mysql310.phy.lolipop.lan';
$dbname = 'LAA1517469-taskura';
$username = 'LAA1517469';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続失敗: " . $e->getMessage());
}

if (isset($_GET['date'])) {
    $date = $_GET['date'];

    try {
        $stmt = $pdo->prepare("
            SELECT itemNo, title, starttime, endtime, content
            FROM Managements
            WHERE :date BETWEEN DATE(starttime) AND DATE(endtime) AND status = 's'
        ");
        $stmt->bindParam(':date', $date);
        $stmt->execute();

        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($schedules);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'スケジュールの取得に失敗しました: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => '日付が指定されていません']);
}
