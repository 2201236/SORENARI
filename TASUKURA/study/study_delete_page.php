<?php
require '../db-connect/db-connect.php';
session_start();

// ユーザーがログインしていない場合リダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/logininput.php");
    exit;
}

// ユーザーIDと削除対象の日付を取得
$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? null;

// 日付部分を正規表現で抽出
if ($date) {
    preg_match('/\d{4}-\d{2}-\d{2}/', $date, $matches);
    $date = $matches[0] ?? null; // 日付部分だけ取得
}

// 日付が正しい形式か確認
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo "無効な日付が指定されています。";
    exit;
}

// データベースから該当データを取得
$query = $pdo->prepare("
    SELECT subject, subject_name, study_time 
    FROM Study 
    WHERE user_id = :user_id AND study_date = :date
");
$query->execute(['user_id' => $user_id, 'date' => $date]);
$study_data = $query->fetchAll(PDO::FETCH_ASSOC);

if (!$study_data) {
    echo "該当するデータが見つかりません。";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>削除確認</title>
</head>
<body>
    <h1>削除確認</h1>
    <?php foreach ($study_data as $data): ?>
        <div>
            <h3>科目名: <?php echo htmlspecialchars($data['subject_name']); ?></h3>
            <p>学習時間: <?php echo htmlspecialchars($data['study_time']); ?></p>
            <form action="delete_subject.php" method="POST">
                <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($data['subject']); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                <button type="submit" onclick="return confirm('この科目を削除しますか？');">削除</button>
            </form>
        </div>
    <?php endforeach; ?>
    <a href="study_management.php">戻る</a>
</body>
</html>
