<?php
// データベース接続
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

// リクエストされた日付
$date = $_GET['date'] ?? null;

if ($date) {
    // SQLで特定の日付のスケジュールを取得
    $stmt = $pdo->prepare("SELECT * FROM Managements WHERE DATE(starttime) = :date");
    $stmt->execute([':date' => $date]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // JSONとして結果を返す
    header('Content-Type: application/json');
    echo json_encode($schedules);
} else {
    echo json_encode(['error' => '日付が指定されていません。']);
}
?>
