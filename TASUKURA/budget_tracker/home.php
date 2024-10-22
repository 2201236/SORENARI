<?php
session_start(); // セッションの開始

// データベース接続情報
require '../db-connect/db-connect.php';

// PDO接続とデータ取得
try {
    // PDOのエラーモードを例外に設定
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // セッションからユーザーIDを取得
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // Bankテーブルからbudgetとmoutgoを取得
    $stmt = $pdo->prepare("SELECT budget, moutgo FROM Bank WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $bankData = $stmt->fetch(PDO::FETCH_ASSOC);

    // データが取得できない場合のエラーハンドリング
    if (!$bankData) {
        throw new Exception("ユーザーの家計簿データが見つかりません。");
    }

    // 取得したデータを変数に格納
    $budget = $bankData['budget'];
    $moutgo = $bankData['moutgo'];

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit;

} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家計簿管理アプリ ダッシュボード</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="../header/css/header.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
    <header>
        <div class="header-content">
            <h1>家計簿管理アプリ</h1>
            <div class="btn">
                <a href="total.php" target="_self">
                    <img src="../images/total.png" alt="集計" style="width:70px;">
                </a>
            </div>
        </div>
    </header>
    
    <div class="bar-chart">
        <div class="bar">
            <div class="bar-inner" id="used-amount" style="width: <?php echo ($moutgo / $budget) * 100; ?>%;"></div>
        </div>
        <div class="bar-labels">
            <span id="used-text">¥<?php echo number_format($moutgo); ?></span> / <span id="budget-text">¥<?php echo number_format($budget); ?></span>
        </div>
    </div>

    <!-- toggle-buttonsは右側に配置 -->
    <div class="toggle-buttons">
        <button id="show-expense-form" class="active">支出入力</button>
        <button id="show-income-form">収入入力</button>
    </div>

    <div class="form-container">
        <div class="form-cards">
            <section class="budget-section">
                <h2>予算の設定</h2>
                <form id="budget-form">
                    <input type="number" name="budget" id="budget" placeholder="月の予算" required>
                    <button type="submit">更新する</button>
                </form>
                <p id="update-result"></p>
            </section>

            <section class="expense-input">
                <h2>支出の入力</h2>
                <form id="expense-form">
                    <input type="text" id="expense-description" placeholder="支出の内容" required>
                    <input type="text" id="expense-amount" placeholder="金額" required>
                    <input type="date" id="expense-date" required>
                    <input type="number" id="num_id" placeholder="識別番号" required>
                    <button type="submit" class="button">支出を追加</button>
                </form>
            </section>

            <section class="income-input" style="display: none;">
                <h2>収入の入力</h2>
                <form id="income-form">
                    <input type="text" id="income-description" placeholder="収入の内容" required>
                    <input type="text" id="income-amount" placeholder="金額" required>
                    <input type="date" id="income-date" required>
                    <input type="number" id="income-num_id" placeholder="識別番号" required>
                    <button type="submit" class="button">収入を追加</button>
                </form>
            </section>
        </div>
    </div>



    <script>
        $(document).ready(function() {
            $('#show-expense-form').click(function() {
                $('.income-input').hide();
                $('.expense-input').show();
                $(this).addClass('active');
                $('#show-income-form').removeClass('active');
            });

            $('#show-income-form').click(function() {
                $('.expense-input').hide();
                $('.income-input').show();
                $(this).addClass('active');
                $('#show-expense-form').removeClass('active');
            });

            $('#budget-form').on('submit', function(e) {
                e.preventDefault();
                let budget = $('#budget').val();

                $.ajax({
                    url: 'update.php',
                    type: 'POST',
                    data: { budget: budget },
                    success: function(response) {
                        $('#update-result').text(response);
                        $('#budget-text').text(`¥${budget}`);
                        updateBarChart();
                    },
                    error: function() {
                        $('#update-result').text('更新に失敗しました。');
                    }
                });
            });

            function updateBarChart() {
                let budget = parseInt($('#budget-text').text().replace('¥', ''));
                let moutgo = parseInt($('#used-text').text().replace('¥', ''));
                if (budget > 0) {
                    $('#used-amount').css('width', `${(moutgo / budget) * 100}%`);
                }
            }

            $('#expense-form').on('submit', function(e) {
                e.preventDefault();
                let description = $('#expense-description').val();
                let amount = $('#expense-amount').val();
                let date = $('#expense-date').val();
                let num_id = $('#num_id').val();

                $.ajax({
                    url: 'insert_expense.php',
                    type: 'POST',
                    data: {
                        description: description,
                        amount: amount,
                        date: date,
                        num_id: num_id
                    },
                    success: function(response) {
                        alert(response);
                    },
                    error: function() {
                        alert('データの送信に失敗しました。');
                    }
                });
            });

            $('#income-form').on('submit', function(e) {
                e.preventDefault();
                let description = $('#income-description').val();
                let amount = $('#income-amount').val();
                let date = $('#income-date').val();
                let num_id = $('#income-num_id').val();

                $.ajax({
                    url: 'insert_income.php',
                    type: 'POST',
                    data: {
                        description: description,
                        amount: amount,
                        date: date,
                        num_id: num_id
                    },
                    success: function(response) {
                        alert(response);
                    },
                    error: function() {
                        alert('データの送信に失敗しました。');
                    }
                });
            });
        });
    </script>
</body>
</html>
