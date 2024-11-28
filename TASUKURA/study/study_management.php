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

<?php
// 科目ごとの今月の学習時間を取得
$query_subjects = $pdo->prepare("
    SELECT subject_name, SEC_TO_TIME(SUM(TIME_TO_SEC(study_time))) AS total_time
    FROM Study
    WHERE user_id = :user_id AND study_date BETWEEN :start_of_month AND :today
    GROUP BY subject_name
");
$query_subjects->execute(['user_id' => $user_id, 'start_of_month' => $start_of_month, 'today' => $today]);
$subject_times = $query_subjects->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学習管理システム</title>
    <link rel="stylesheet" href="./css/study_style.css">
</head>
<body>
<header>
<?php
require '../header/header2.php';
?>
</header>

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

<div class="subject-time-button-container">
    <button id="show-subject-times">科目ごとの学習時間を表示</button>
</div>

<div class="tab-container">
    <div class="tab active" onclick="showTab('stopwatchTab')">ストップウォッチで時間を保存する</div>
    <div class="tab" onclick="showTab('manualInputTab')">入力して時間を保存する</div>
</div>

<div id="stopwatchTab" class="content active">
    <form method="POST" action="start_stopwatch.php" class="form-container" id="stopwatch-form">
        <button type="submit" id="reset-button" class="reset-button">時間をリセット</button>

        <div class="subject-container">
            <label for="subject">科目を入力：</label>
            <input type="text" name="subject" id="subject" placeholder="科目名を入力してください">
        </div>

        <div id="error-message" style="color: red; display: none;">科目を入力してください。</div>
            <input type="hidden" name="elapsed_time" id="elapsed_time" value="0">

        <div class="horizontal-container">
            <div class="stopwatch">
                <div id="time-display">00:00:00</div>
                <button type="button" id="start-button" class="start-button">開始</button>
                <button type="button" id="stop-button" class="stop-button" disabled>停止</button>
            </div>
        </div>
        <button type="submit" id="save-time-button">時間を保存</button>
    </form>
</div>

<div id="manualInputTab" class="content">
    <form method="POST" action="save_time.php" id="manual-input-form">
        <div class="centered-container">
            <div class="form-container">
                <div class="subject-container">
                    <label for="manual-subject">科目を入力：</label>
                    <input type="text" id="manual-subject" name="manual_subject" placeholder="科目を入力してください">
                </div>

                <!-- エラーメッセージを表示する場所 -->
                <div id="manual-error-message" style="color: red; display: none;">科目が入力されていません。</div>

                <div class="horizontal-container">
                    <label for="hours">時間：</label>
                    <select id="hours" name="hours">
                        <?php for ($i = 0; $i < 24; $i++) echo "<option value='$i'>$i</option>"; ?>
                    </select> 時
                    <select id="minutes" name="minutes">
                        <?php for ($i = 0; $i < 60; $i+=15) echo "<option value='$i'>$i</option>"; ?>
                    </select> 分
                    <select id="seconds" name="seconds">
                        <?php for ($i = 0; $i < 60; $i+=15) echo "<option value='$i'>$i</option>"; ?>
                    </select> 秒
                </div>
                <button type="submit" id="save-time-button">時間を保存</button>
            </div>
        </div>
    </form>
</div>

<button id="toggle-calendar-button">カレンダーを表示</button>

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
<button id="scrollToTop">上に戻る</button>

<div id="popup" class="popup">
    <div class="popup-content">
        <span class="close" id="close-popup">&times;</span>
        <h2 id="popup-date">日付の詳細</h2>
        <ul id="popup-details"></ul>
        <div class="popup-actions">
            <!-- 編集ボタン -->
            <form action="study_edit_page.php" method="GET">
                <input type="hidden" name="date" id="edit-date" value="">
                <button type="submit" class="edit-button">編集</button>
            </form>
        </div>
    </div>
</div>

<div id="subject-popup" class="popup">
    <div class="popup-content">
        <span class="close" id="close-subject-popup">&times;</span>
        <h2>科目ごとの今月の学習時間</h2>
        <ul id="subject-details">
            <?php foreach ($subject_times as $subject): ?>
                <li><?php echo htmlspecialchars($subject['subject_name']) . ': ' . htmlspecialchars($subject['total_time']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script src="./js/study.js"></script>
</body>
</html>
