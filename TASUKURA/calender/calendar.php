<?php 
session_start(); // セッションを開始

// セッションに保存されている全ての情報を出力する
if (!empty($_SESSION)) {
    echo '<pre>';
    print_r($_SESSION); // セッションの全てのデータを表示
    echo '</pre>';
} else {
    echo "セッションにデータがありません。";
}
?>
<!DOCTYPE html> 
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>全画面カレンダー</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div id="calendar">
    <h2 style="display: inline;">カレンダー</h2>
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
        <tbody id="calendar-body">
        </tbody>
    </table>
</div>

<!-- 外部JavaScriptファイルをリンク -->
<script src="js/calendar.js"></script>

</body>
</html>