<?php
require '../db-connect/db-connect.php';

session_start();

// ユーザーがログインしていない場合、ログイン画面へリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/logininput.php");
    exit;
}

// セッションからユーザーIDを取得
$user_id = $_SESSION['user_id'];

// 今日、今週、今月の学習時間を計算
$today = date('Y-m-d');
$start_of_week = date('Y-m-d', strtotime('monday this week'));
$start_of_month = date('Y-m-01');

// 今日の学習時間
$query_today = $pdo->prepare("
    SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(study_time))) AS total_time 
    FROM Study 
    WHERE user_id = :user_id AND study_date = :today
");
$query_today->execute(['user_id' => $user_id, 'today' => $today]);
$today_time = $query_today->fetchColumn() ?: '00:00:00';

// 今週の学習時間
$query_week = $pdo->prepare("
    SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(study_time))) AS total_time 
    FROM Study 
    WHERE user_id = :user_id AND study_date BETWEEN :start_of_week AND :today
");
$query_week->execute(['user_id' => $user_id, 'start_of_week' => $start_of_week, 'today' => $today]);
$week_time = $query_week->fetchColumn() ?: '00:00:00';

// 今月の学習時間
$query_month = $pdo->prepare("
    SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(study_time))) AS total_time 
    FROM Study 
    WHERE user_id = :user_id AND study_date BETWEEN :start_of_month AND :today
");
$query_month->execute(['user_id' => $user_id, 'start_of_month' => $start_of_month, 'today' => $today]);
$month_time = $query_month->fetchColumn() ?: '00:00:00';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学習管理システム</title>
    <link rel="stylesheet" href="./css/study_style.css">
    <link rel="stylesheet" href="../header/css/header.css">
</head>
<body>

<?php
require '../header/header.php';
?>

<div class="time-container">
    <div class="time-box">
        <h3>今日の学習時間</h3>
        <div><?php echo $today_time; ?></div>
    </div>
    <div class="time-box">
        <h3>今週の学習時間</h3>
        <div><?php echo $week_time; ?></div>
    </div>
    <div class="time-box">
        <h3>今月の学習時間</h3>
        <div><?php echo $month_time; ?></div>
    </div>
</div>

<div class="stopwatch-container">
    <form method="POST" action="start_stopwatch.php" class="form-container" id="stopwatch-form">
        <label for="subject">科目を入力：</label>
        <input type="text" name="subject" id="subject" placeholder="科目名を入力してください">
        <div id="error-message" style="color: red; display: none;">科目を入力してください。</div>
        <input type="hidden" name="elapsed_time" id="elapsed_time" value="0">

        <div class="stopwatch">
            <div id="time-display">00:00:00</div>
            <button type="button" id="start-button">開始</button>
            <button type="button" id="stop-button" disabled>停止</button>
        </div>

        <button type="submit" id="save-time-button">時間を保存</button>
        <button type="button" id="reset-button">リセット</button>
    </form>
</div>

<div id="calendar">
    <h2>カレンダー</h2>
    <div class="controls">
        <span class="arrow" id="prev-month">&#10094;</span>
        <label for="year-select">年: </label>
        <select id="year-select"></select>
        <label for="month-select">月: </label>
        <select id="month-select">
            <option value="0">1月</option>
            <option value="1">2月</option>
            <option value="2">3月</option>
            <option value="3">4月</option>
            <option value="4">5月</option>
            <option value="5">6月</option>
            <option value="6">7月</option>
            <option value="7">8月</option>
            <option value="8">9月</option>
            <option value="9">10月</option>
            <option value="10">11月</option>
            <option value="11">12月</option>
        </select>
        <span class="arrow" id="next-month">&#10095;</span>
        <button id="generate-button">更新</button>
    </div>
    <table id="calendar-table">
        <thead>
            <tr>
                <th>日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th>土</th>
            </tr>
        </thead>
        <tbody id="calendar-body"></tbody>
    </table>
</div>

<div id="popup" class="popup">
    <div class="popup-content">
        <span class="close" id="close-popup">&times;</span>
        <h2 id="popup-date">日付の詳細</h2>
        <ul id="popup-details"></ul>
    </div>
</div>

<script src="./js/study.js"></script>
<script>
    document.getElementById("stopwatch-form").addEventListener("submit", function(event) {
        var subjectInput = document.getElementById("subject");
        var errorMessage = document.getElementById("error-message");
        
        // 科目が入力されているかチェック
        if (subjectInput.value.trim() === "") {
            // エラーメッセージを表示し、フォームの送信を停止
            errorMessage.style.display = "block";
            event.preventDefault();
        } else {
            // エラーメッセージを非表示にしてフォームを送信
            errorMessage.style.display = "none";
        }
    });
</script>
</body>
</html>
