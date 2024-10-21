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
    // データベースエラー時の処理
    echo "データベースエラー: " . $e->getMessage();
    exit;

} catch (Exception $e) {
    // その他のエラー時の処理
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
    <link rel="stylesheet" href="css/home.css"> <!-- スタイルシートのリンク -->
    <link rel="stylesheet" href="../header/css/header.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQueryの読み込み -->
</head>
<body>

    <div class="container">
    <header>
    <div class="header-content">
        <h1>家計簿管理アプリ</h1>
        <div class="btn">
            <a href="total.php" target="_self">
                <img src="../images/total.png" alt="集計" style="width:90px;">
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

    <div class="form-container">
    <section class="budget-section">
    <h2>予算の設定</h2>
    <form id="budget-form">
        <input type="number" name="budget" id="budget" placeholder="月の予算" required>
        <button type="submit">更新する</button>
    </form>
    <p id="update-result"></p> <!-- 更新結果を表示する場所 -->
</section>



<script>
    $(document).ready(function() {
        $('#budget-form').on('submit', function(e) {
            e.preventDefault(); // フォームのデフォルトの送信を無効化

            // フォームデータを取得
            let budget = $('#budget').val();

            // AJAXリクエストを送信してデータを更新
            $.ajax({
                url: 'update.php', // サーバーサイドの処理ファイル
                type: 'POST',      // POSTメソッドで送信
                data: { budget: budget }, // フォームデータを送信
                success: function(response) {
                    // サーバーからのレスポンスを処理
                    $('#update-result').text(response); // サーバーのレスポンスを表示

                    // 更新されたbudgetをページに反映
                    $('#budget-text').text(`¥${budget}`);
                    updateBarChart(); // バーの表示を更新
                },
                error: function() {
                    // エラーが発生した場合の処理
                    $('#update-result').text('更新に失敗しました。');
                }
            });
        });

        // バーグラフの更新処理
        function updateBarChart() {
            let budget = parseInt($('#budget-text').text().replace('¥', '')); // 現在の予算を取得
            let moutgo = parseInt($('#used-text').text().replace('¥', '')); // 現在の支出額を取得
            if (budget > 0) {
                $('#used-amount').css('width', `${(moutgo / budget) * 100}%`);
            }
        }
    });
</script>


        <section class="expense-input">
            <h2>支出の入力</h2>
            <form id="expense-form">
                <input type="text" id="expense-description" placeholder="支出の内容" required>
                <input type="text" id="expense-amount" placeholder="金額" required >
                <input type="date" id="expense-date" required>
                
                <input type="number" id="num_id" placeholder="識別番号" required> <!-- num_id用のフィールド -->
                
                <button type="submit" class="button">支出を追加</button>
            </form>
        </section>
    </div>
    <script>
        $(document).ready(function() {
            // 支出フォームの送信処理
            const today = new Date().toISOString().split('T')[0];
            $('#expense-date').val(today);



            $('#expense-form').on('submit', function(e) {
                e.preventDefault(); // デフォルトのフォーム送信を防ぐ

                // フォームデータを取得
                let description = $('#expense-description').val();
                let amount = $('#expense-amount').val();
                let date = $('#expense-date').val();
                let num_id = $('#num_id').val();  // 識別番号

                // AJAXリクエストを送信
                $.ajax({
                    url: 'insert_expense.php', // データベース挿入処理をするPHPファイル
                    type: 'POST',
                    data: {
                        description: description,
                        amount: amount,
                        date: date,
                        num_id: num_id
                    },
                    success: function(response) {
                        // 成功時の処理
                        alert(response); // 成功メッセージを表示
                    },
                    error: function() {
                        // エラー時の処理
                        alert('データの送信に失敗しました。');
                    }
                });
            });
        });
    </script>


    <footer>
        <p>2024 家計簿管理アプリ</p>
    </footer>
</body>
</html>
