<?php
session_start(); // セッションの開始

// データベース接続情報
require '../../db-connect/db-connect.php';

try {
    // PDOのエラーモードを例外に設定
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // セッションからユーザーIDを取得
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // Bankテーブルからデータがあるか確認
    $stmt = $pdo->prepare("SELECT 1 FROM Bank WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $exists = $stmt->fetchColumn();


     // データが存在しない場合は挿入する
     if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO Bank (user_id, budget, moutgo, youtgo, lyoutgo) VALUES (:user_id, :budget, :moutgo, :youtgo, :lyoutgo)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':budget', 1000, PDO::PARAM_INT);
        $stmt->bindValue(':moutgo', 0, PDO::PARAM_INT);
        $stmt->bindValue(':youtgo', 0, PDO::PARAM_INT);
        $stmt->bindValue(':lyoutgo', 0, PDO::PARAM_INT);
        $stmt->execute();

        // データ挿入後にページを更新
        echo "<script>location.reload();</script>";
        exit;
    }


    // Bankテーブルからbudgetとmoutgoを取得
    $stmt = $pdo->prepare("SELECT budget, moutgo FROM Bank WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $bankData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bankData) {
        throw new Exception("ユーザーの家計簿データが見つかりません。");
    }

    $budget = $bankData['budget'];
    $moutgo = $bankData['moutgo'];

    // 現在の月の支出合計をDailySpendテーブルから取得
    $stmt = $pdo->prepare("SELECT SUM(outgo) AS total_outgo FROM DailySpend WHERE user_id = :user_id AND MONTH(daily) = MONTH(CURRENT_DATE())");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $dailySpendData = $stmt->fetch(PDO::FETCH_ASSOC);

    $monthly_outgo = $dailySpendData['total_outgo'] ?? 0; // 結果がない場合は0を使用

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
    <title>家計簿管理 ダッシュボード</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="../../header/css/header2.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<button class="back-button" onclick="window.history.back()">← </button>

    <!-- トグルメニューボタン -->
    <button class="menu-toggle" id="menuToggle"> ☰</button>

    <!-- メニュー -->
    <nav class="menu" id="menu">
        <ul>
            <li><a href="../../home/home.php" class="menu-item">🏠 ホーム</a></li>
            <li><a href="../../calendar/calendar.php" class="menu-item">📅 カレンダー</a></li>
            <li><a href="../../study/study_management.php" class="menu-item">📖 学習管理</a></li>
            <li><a href="../../budget_tracker/home/home.php" class="menu-item">🏦 家計簿</a></li>
            <li><a href="#" class="menu-item">🗂️ 共有ボード</a></li>
            <li><a href="../../passlist/passlist.php" class="menu-item">🗝 パスワード</a></li>
            <li><a href="#" class="menu-item">⚙ 設定</a></li>
            <li><a href="../../logout/logoutinput.php" class="menu-item">👋 ログアウト</a></li>
        </ul>
    </nav>

    <script src="../../header/js/menu.js"></script>

<div class="container">
    <header>
        <div class="header-content">
            <h1>家計簿管理</h1>
            <div class="btn">
                <a href="../total/total.html" target="_self">
                    <img src="../../images/total.png" alt="集計" style="width:70px;">
                </a>
                <a href="../save/save.php" target="_self">
                    <img src="../../images/bank.png" alt="集計" style="width:70px;">
                </a>
            </div>
        </div>
    </header>
    
    <div class="bar-chart">
        <div class="bar">
            <div class="bar-inner" id="used-amount" style="width: <?php echo ($monthly_outgo / $budget) * 100; ?>%;"></div>
        </div>
        <div class="bar-labels">
            <span id="used-text">¥<?php echo number_format($monthly_outgo); ?></span> / <span id="budget-text">¥<?php echo number_format($budget); ?></span>
        </div>
    </div>
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
            </section>

            <section class="expense-input">
                <h2>支出の入力</h2>
                <form id="expense-form">
                    <input type="text" id="expense-description" placeholder="支出の内容" required>
                    <input type="text" id="expense-amount" placeholder="金額" required>
                    <input type="date" id="expense-date" required>
                  
                    <button type="submit" class="button">支出を追加</button>
                </form>
            </section>

            <section class="income-input" style="display: none;">
                <h2>収入の入力</h2>
                <form id="income-form">
                    <input type="text" id="income-description" placeholder="収入の内容" required>
                    <input type="text" id="income-amount" placeholder="金額" required>
                    <input type="date" id="income-date" required>
                    
                    <button type="submit" class="button">収入を追加</button>
                </form>
            </section>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const today = new Date();
        // yyyy-mm-dd の形式に変換
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0'); // 月は0始まりなので+1
        const dd = String(today.getDate()).padStart(2, '0');
        const formattedDate = `${yyyy}-${mm}-${dd}`;
        
        // テキストボックスに今日の日付を設定
        document.getElementById('expense-date').value = formattedDate;
        document.getElementById('income-date').value = formattedDate;

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

            let monthly_outgo = <?php echo $monthly_outgo; ?>;

            $('#budget-form').on('submit', function(e) {
                e.preventDefault();
                let budget = $('#budget').val();

                if (parseInt(budget) < monthly_outgo) {
                alert('設定した予算が現在の支出額より少なくなっています。適切な予算を設定してください。');
                return; // 送信処理を中断
            }

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
                    setTimeout(function() {
                        location.reload();
                    }, 3000);  // 3000ミリ秒（3秒）後にリロード
                } 
            }

            $('#expense-form').on('submit', function(e) {
                e.preventDefault();
                let description = $('#expense-description').val();
                let amount = $('#expense-amount').val();
                let date = $('#expense-date').val();
              

                 // PHPから取得した budget と monthly_outgo をJavaScriptで整数変換
                let budget = Number(<?php echo json_encode($budget); ?>);
                let current_outgo = Number(<?php echo json_encode($monthly_outgo); ?>) + Number(amount);

                console.log("Current Outgo:", current_outgo, "Budget:", budget, "Amount:", amount); // デバッグ用ログ

                if (current_outgo > budget) {
                    alert('予算を超えています');
                    return; // フォーム送信を停止
                }
                
                $.ajax({
                    url: 'insert_expense.php',
                    type: 'POST',
                    data: {
                        description: description,
                        amount: amount,
                        date: date,
                       
                    },
                    success: function(response) {
                        setTimeout(function() {
                        location.reload(); // 3秒後にページをリロード
                    }, 3000);
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
                

                $.ajax({
                    url: 'insert_income.php',
                    type: 'POST',
                    data: {
                        description: description,
                        amount: amount,
                        date: date,
                       
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