<?php
require '../db-connect/db-connect.php';
session_start();

// ユーザーがログインしていない場合リダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/logininput.php");
    exit;
}

// ユーザーIDと選択された日付を取得
$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? null;

// 日付が正しいかを検証
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo "無効な日付が指定されています。";
    exit;
}

// 選択した日付の全科目データを集計して取得
$query = $pdo->prepare("
    SELECT subject, 
           subject_name, 
           SEC_TO_TIME(SUM(TIME_TO_SEC(study_time))) AS total_time 
    FROM Study 
    WHERE user_id = :user_id AND study_date = :date 
    GROUP BY subject, subject_name
");
$query->execute(['user_id' => $user_id, 'date' => $date]);
$study_data = $query->fetchAll(PDO::FETCH_ASSOC);

if (!$study_data) {
    echo "<script>
        alert('該当するデータが見つかりません。');
        window.location.href = './study_management.php'; // リダイレクト先を指定してください
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編集画面</title>
    <link rel="stylesheet" href="./css/study_edit_style.css">
</head>
<body>
<header>
<?php
require '../header/header2.php';
?>
</header>

    <h1 class="h">編集画面</h1>
    <form action="update_study.php" method="POST" class="update-form">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">

        <?php foreach ($study_data as $data): ?>
            <div>
                <input type="hidden" name="subject_id[<?php echo $data['subject']; ?>]" value="<?php echo htmlspecialchars($data['subject']); ?>">
                <label for="subject_name_<?php echo $data['subject']; ?>">科目名：</label>
                <input type="text" 
                    name="subject_name[<?php echo $data['subject']; ?>]" 
                    id="subject_name_<?php echo $data['subject']; ?>" 
                    value="<?php echo htmlspecialchars($data['subject_name']); ?>" required>
                <br><br>
                <label for="study_time_<?php echo $data['subject']; ?>">学習時間：</label>
                <input type="time" 
                    name="study_time[<?php echo $data['subject']; ?>]" 
                    id="study_time_<?php echo $data['subject']; ?>" 
                    value="<?php echo htmlspecialchars($data['total_time']); ?>" required>
                <br><br>
            </div>
        <?php endforeach; ?>

        <button type="submit">更新</button>
    </form>

    <!-- 削除フォームを別に作成 -->
    <?php foreach ($study_data as $data): ?>
        <form action="delete_subject.php" method="POST" class="delete-form">
            <div>
                <label for="subject_name_display_<?php echo $data['subject']; ?>">科目名：</label>
                <input type="text" 
                    name="subject_name_display[<?php echo $data['subject']; ?>]" 
                    value="<?php echo htmlspecialchars($data['subject_name']); ?>" 
                    readonly>
            </div>
            <div>
                <label for="study_time_display_<?php echo $data['subject']; ?>">学習時間：</label>
                <input type="time" 
                    name="study_time_display[<?php echo $data['subject']; ?>]" 
                    value="<?php echo htmlspecialchars($data['total_time']); ?>" 
                    readonly>
            </div>
            <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($data['subject']); ?>">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
            <button type="submit" onclick="return confirm('本当に削除しますか？')">削除</button>
        </form>
    <?php endforeach; ?>
</body>
</html>
