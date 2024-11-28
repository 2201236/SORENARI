<?php
session_start();
require_once '../../db-connect/db-connect.php';

try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインが必要です。");
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

    // 予算と支出の取得
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

    // 収入と支出を合わせた最近の取引データを取得
    $stmt = $pdo->prepare("
        SELECT 
            content,
            outgo,
            0 AS income,
            daily,
            'expense' as type
        FROM DailySpend
        WHERE user_id = :user_id 
        UNION ALL
        SELECT 
            content,
            0 AS outgo,
            ingo AS income,
            daily,
            'income' as type
        FROM DailyIncome
        WHERE user_id = :user_id
        ORDER BY daily DESC
        LIMIT 5
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit;

} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}  

$remainingBudget = $budget - $monthly_outgo;
$percentageUsed = $budget > 0 ? ($monthly_outgo / $budget) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家計簿管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">家計簿管理</h1>
                </button>
            </div>
        </div>
    </header>
    <?php
        require '../../header/header_budget.php';
    ?>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-8">
        <!-- メインの予算カード -->
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition duration-200">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">今月の予算状況</h2>
                    <p class="text-sm text-gray-500 mt-1">予算: ¥<?= number_format($budget) ?></p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openBudgetModal()" class="p-2 rounded-full hover:bg-gray-100 transition duration-200" title="予算を設定">
                        <i class="fas fa-cog text-gray-600 hover:text-blue-500"></i>
                    </button>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-wallet text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <div class="flex justify-between items-baseline">
                    <span class="text-3xl font-bold text-gray-800">¥<?= number_format($monthly_outgo) ?></span>
                    <span class="text-lg text-gray-500">/ ¥<?= number_format($budget) ?></span>
                </div>

                <!-- プログレスバー -->
                <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="progress-bar absolute h-full rounded-full 
                        <?= $percentageUsed > 80 ? 'bg-red-500' : 'bg-blue-500' ?>"
                        style="width: <?= min($percentageUsed, 100) ?>%">
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">残り予算:</span>
                    <span class="text-lg font-semibold <?= $remainingBudget < 0 ? 'text-red-600' : 'text-green-600' ?>">
                        ¥<?= number_format($remainingBudget) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- クイックアクション -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="income.php" class="quick-action bg-white rounded-xl shadow-sm p-6 flex items-center space-x-4">
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-plus text-green-600 text-lg"></i>
                </div>
                <div>
                    <span class="font-medium text-gray-800 block">収入を記録</span>
                    <span class="text-sm text-gray-500">収入を追加する</span>
                </div>
            </a>

            <a href="spend.php" class="quick-action bg-white rounded-xl shadow-sm p-6 flex items-center space-x-4">
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-minus text-red-600 text-lg"></i>
                </div>
                <div>
                    <span class="font-medium text-gray-800 block">支出を記録</span>
                    <span class="text-sm text-gray-500">支出を追加する</span>
                </div>
            </a>
        </div>

        <!-- 最近の取引 -->
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition duration-200">
            <!-- 「最近の取引」のヘッダー部分を以下のように修正 -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">最近の取引</h2>
            <a href="../transaction/transactions.php" class="text-blue-500 hover:text-blue-600 text-sm flex items-center">
                すべて見る
                <i class="fas fa-chevron-right ml-1 text-xs"></i>
            </a>
            </div>
            <?php if (empty($recentTransactions)): ?>
                <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                    <i class="fas fa-receipt text-4xl mb-4"></i>
                    <p>取引履歴がありません</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="transaction-card flex justify-between items-center p-4 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <?php if ($transaction['type'] === 'income'): ?>
                                    <div class="bg-green-100 p-3 rounded-full">
                                        <i class="fas fa-plus text-green-600"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-red-100 p-3 rounded-full">
                                        <i class="fas fa-minus text-red-600"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($transaction['content']) ?></p>
                                    <span class="text-sm text-gray-500"><?= $transaction['daily'] ?></span>
                                </div>
                            </div>
                            <div>
                                <?php if ($transaction['type'] === 'income'): ?>
                                    <span class="text-xl font-bold text-green-600">
                                        ¥<?= number_format($transaction['income']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xl font-bold text-red-600">
                                        ¥<?= number_format($transaction['outgo']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 機能メニュー -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="calendar.php" class="menu-card bg-white rounded-xl shadow-sm p-6 flex flex-col items-center justify-center space-y-4 text-center">
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-calendar text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <span class="font-medium text-gray-800 block mb-2">カレンダー</span>
                    <p class="text-sm text-gray-500">日別の収支を確認</p>
                </div>
            </a>

            <a href="report.php" class="menu-card bg-white rounded-xl shadow-sm p-6 flex flex-col items-center justify-center space-y-4 text-center">
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-chart-pie text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <span class="font-medium text-gray-800 block mb-2">レポート</span>
                    <p class="text-sm text-gray-500">収支の分析と集計</p>
                </div>
            </a>
        </div>
    </main>
    
    <!-- 予算設定モーダル -->
    <div id="budgetModal" class="modal fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="modal-content bg-white rounded-xl shadow-lg max-w-md w-full mx-4 transform scale-95 opacity-0">
            <form id="budgetForm" action="update_budget.php" method="POST" class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">予算の設定</h3>
                    <button type="button" onclick="closeBudgetModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">
                            月間予算
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">¥</span>
                            <input
                                type="number"
                                id="budget"
                                name="budget"
                                value="<?= $budget ?>"
                                class="block w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                min="0"
                                step="1000"
                                required
                            >
                        </div>
                    </div>

                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        毎月の予算を設定します。この金額を基準に支出を管理します。
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="closeBudgetModal()"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition duration-200"
                    >
                        キャンセル
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200"
                    >
                        保存する
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function openBudgetModal() {
        const modal = document.getElementById('budgetModal');
        const modalContent = modal.querySelector('.modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('bg-opacity-50');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeBudgetModal() {
        const modal = document.getElementById('budgetModal');
        const modalContent = modal.querySelector('.modal-content');
        modal.classList.remove('bg-opacity-50');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // 予算フォームの送信をハンドル
    document.getElementById('budgetForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('update_budget.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('予算の更新に失敗しました。もう一度お試しください。');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('エラーが発生しました。もう一度お試しください。');
        });
    });
    </script>
</body>
</html>