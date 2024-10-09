<?php
include 'db-connect.php';

// ユーザーID（仮）
$user_id = 1;

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
</head>
<body>

<!-- 青枠部分：学習時間 -->
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

<!-- 赤枠部分：ストップウォッチと科目選択 -->
<div class="stopwatch-container">
    <form method="POST" action="start_stopwatch.php" class="form-container">
        <label for="subject">科目を入力：</label>
        <input type="text" name="subject" id="subject" placeholder="科目名を入力してください">

        <div class="stopwatch">
            <!-- ストップウォッチ本体 -->
            <div id="time-display">00:00:00</div>
            <button type="button" id="start-button">開始</button>
            <button type="button" id="stop-button" disabled>停止</button>
        </div>
        
        <!-- 時間を保存ボタン -->
        <button type="submit" id="save-time-button">時間を保存</button>
    </form>
</div>

<!-- カレンダー部分 -->
<div id="calendar">
    <h2>カレンダー</h2>
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


<script>
let startTime, interval;

document.getElementById('start-button').addEventListener('click', function() {
    startTime = Date.now();
    interval = setInterval(updateStopwatch, 1000);
    document.getElementById('start-button').disabled = true; // 開始ボタンを無効化
    document.getElementById('stop-button').disabled = false; // 停止ボタンを有効化
});

document.getElementById('stop-button').addEventListener('click', function() {
    clearInterval(interval); // ストップウォッチを停止
    document.getElementById('start-button').disabled = false; // 開始ボタンを有効化
    document.getElementById('stop-button').disabled = true; // 停止ボタンを無効化
});

function updateStopwatch() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const hours = String(Math.floor(elapsed / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
    const seconds = String(elapsed % 60).padStart(2, '0');
    document.getElementById('time-display').textContent = `${hours}:${minutes}:${seconds}`; // 表示を更新
}
</script>

<script>
  // カレンダー生成関数
  function generateCalendar(year, month) {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = ''; // カレンダーを初期化

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    let date = 1;

    for (let i = 0; i < 6; i++) {
      const row = document.createElement('tr');

      for (let j = 0; j < 7; j++) {
        if (i === 0 && j < firstDay.getDay()) {
          const cell = document.createElement('td');
          row.appendChild(cell); // 空セルを追加
        } else if (date > lastDay.getDate()) {
          break; // 日付が月の最終日を超えたら終了
        } else {
          const cell = document.createElement('td');
          cell.textContent = date;

          // 今日の日付を強調表示
          if (year === new Date().getFullYear() && month === new Date().getMonth() && date === new Date().getDate()) {
            cell.classList.add('today'); // クラス today を追加して強調
          }

          // セルクリック時のアラート（オプション）
          cell.addEventListener('click', () => {
            alert(`Clicked on ${cell.textContent}`);
          });

          row.appendChild(cell);
          date++;
        }
      }
      calendarBody.appendChild(row); // 行をカレンダーに追加
    }
  }

  // 現在の月のカレンダーを生成
  const today = new Date();
  generateCalendar(today.getFullYear(), today.getMonth());
</script>

</body>
</html>

