<?php require '../header/header.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家計簿管理アプリ ダッシュボード</title>
    <link rel="stylesheet" href="css/home.css"> <!-- スタイルシートのリンク -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQueryの読み込み -->
</head>
<body>
    <div class="container">
    <header>
    <div class="header-content">
        <h1>家計簿管理アプリ</h1>
        <div class="btn">
            <a href="save.php" target="_self">
                <img src="../images/bank.png" alt="貯金" style="width:75px;">
            </a>
            <a href="total.php" target="_self">
                <img src="../images/total.png" alt="集計" style="width:90px;">
            </a>
        </div>
    </div>
    </header>


        <div class="bar-chart">
            <div class="bar">
                <div class="bar-inner" id="used-amount" style="width: 0;"></div>
            </div>
            <div class="bar-labels">
                <span id="used-text">¥0</span> / <span id="budget-text">¥0</span>
            </div>
        </div>

        <div class="form-container">
            <section class="budget-section">
                <h2>予算の設定</h2>
                <input type="number" id="budget" placeholder="月の予算" required>
            </section>

            <section class="expense-input">
                <h2>支出の入力</h2>
                <form id="expense-form">
                    <input type="text" id="expense-description" placeholder="支出の内容" required>
                    <input type="text" id="expense-amount" placeholder="金額" required readonly>
                    <input type="date" id="expense-date" required>
                    <button type="submit" class="button">支出を追加</button>
                </form>
            </section>
        </div>

        <div class="calculator" id="calculator">
            <div class="row">
                <button>7</button>
                <button>8</button>
                <button>9</button>
            </div>
            <div class="row">
                <button>4</button>
                <button>5</button>
                <button>6</button>
            </div>
            <div class="row">
                <button>1</button>
                <button>2</button>
                <button>3</button>
            </div>
            <div class="row">
                <button>0</button>
                <button class="clear">C</button>
                <button class="enter">OK</button>
            </div>
        </div>

        <footer>
            <p>
                2024 家計簿管理アプリ 
            </p>
        </footer>
    </div>

    <script>
        $(document).ready(function() {
            let budget = 0;
            let totalExpense = 0;

            // 予算の設定
            $('#budget').on('change', function() {
                budget = parseInt($(this).val());
                $('#budget-text').text(`¥${budget}`);
                updateBarChart();
            });

            // 金額入力ボックスをクリックしたときに電卓を表示
            $('#expense-amount').on('click', function() {
                const offset = $(this).offset();
                $('#calculator').css({
                    top: offset.top + $(this).outerHeight(),
                    left: offset.left
                }).toggle();
            });

            // 電卓ボタンのイベント
            $('#calculator button').on('click', function() {
                const value = $(this).text();
                if (value === 'OK') {
                    $('#calculator').hide();
                } else if (value === 'C') {
                    $('#expense-amount').val('');
                } else {
                    $('#expense-amount').val(function(index, oldValue) {
                        return oldValue + value; // ボタンの値を金額に追加
                    });
                }
            });

            // 支出の追加機能
            $('#expense-form').on('submit', function(e) {
                e.preventDefault();

                const amount = parseInt($('#expense-amount').val());
                totalExpense += amount;

                $('#used-amount').css('width', `${(totalExpense / budget) * 100}%`);
                $('#used-text').text(`¥${totalExpense}`);
                $('#expense-form')[0].reset();
                $('#calculator').hide(); // フォームを送信後に電卓を隠す
            });

            function updateBarChart() {
                if (budget > 0) {
                    $('#used-amount').css('width', `${(totalExpense / budget) * 100}%`);
                }
            }
        });
    </script>
</body>
</html>
