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

    // 選択された月（デフォルトは現在の月）
    $selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

    // 月次総支出
    $stmt = $pdo->prepare("
        SELECT SUM(outgo) as total_expense
        FROM DailySpend 
        WHERE user_id = :user_id 
        AND DATE_FORMAT(daily, '%Y-%m') = :selected_month
        AND outgo > 0
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':selected_month', $selected_month);
    $stmt->execute();
    $monthly_total = $stmt->fetch(PDO::FETCH_ASSOC)['total_expense'] ?? 0;

    // カテゴリー別支出
    $stmt = $pdo->prepare("
        SELECT category, SUM(outgo) as category_total
        FROM DailySpend 
        WHERE user_id = :user_id 
        AND DATE_FORMAT(daily, '%Y-%m') = :selected_month
        AND outgo > 0
        GROUP BY category
        ORDER BY category_total DESC
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':selected_month', $selected_month);
    $stmt->execute();
    $category_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 日別支出トレンド
    $stmt = $pdo->prepare("
        SELECT daily, SUM(outgo) as daily_total
        FROM DailySpend 
        WHERE user_id = :user_id 
        AND DATE_FORMAT(daily, '%Y-%m') = :selected_month
        AND outgo > 0
        GROUP BY daily
        ORDER BY daily
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':selected_month', $selected_month);
    $stmt->execute();
    $daily_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 予算情報の取得
    $stmt = $pdo->prepare("SELECT budget FROM Bank WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $budget = $stmt->fetch(PDO::FETCH_ASSOC)['budget'] ?? 0;

} catch (Exception $e) {
    error_log($e->getMessage());
    exit('エラーが発生しました。');
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支出レポート | 家計簿管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">支出レポート</h1>
                <div class="flex items-center space-x-4">
                    <select id="monthSelector" class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php
                        for ($i = 0; $i < 12; $i++) {
                            $date = date('Y-m', strtotime("-$i months"));
                            $selected = $date === $selected_month ? 'selected' : '';
                            echo "<option value=\"$date\" $selected>" . date('Y年n月', strtotime($date)) . "</option>";
                        }
                        ?>
                    </select>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <?php
        require '../../header/header2.php';
    ?>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- 概要カード -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stat-card bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">月間支出合計</h3>
                <p class="text-2xl font-bold text-gray-800">¥<?php echo number_format($monthly_total); ?></p>
                <div class="mt-2 text-sm text-<?php echo $monthly_total > $budget ? 'red' : 'green'; ?>-600">
                    <?php echo $monthly_total > $budget ? '予算超過' : '予算内'; ?>
                </div>
            </div>

            <div class="stat-card bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">予算残高</h3>
                <p class="text-2xl font-bold text-gray-800">¥<?php echo number_format($budget - $monthly_total); ?></p>
                <div class="mt-2 text-sm text-gray-500">
                    予算: ¥<?php echo number_format($budget); ?>
                </div>
            </div>

            <div class="stat-card bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">1日平均支出</h3>
                <p class="text-2xl font-bold text-gray-800">
                    ¥<?php echo number_format($monthly_total / date('t', strtotime($selected_month))); ?>
                </p>
                <div class="mt-2 text-sm text-gray-500">
                    集計期間: <?php echo date('n', strtotime($selected_month)); ?>月
                </div>
            </div>
        </div>

        <!-- グラフとカテゴリー別支出 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- カテゴリー別支出グラフ -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">カテゴリー別支出</h2>
                <div class="h-64">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- 日別支出トレンド -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">支出トレンド</h2>
                <div class="h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- カテゴリー別詳細 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">カテゴリー別詳細</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                カテゴリー
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                支出額
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                割合
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($category_expenses as $expense): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($expense['category']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ¥<?php echo number_format($expense['category_total']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo number_format(($expense['category_total'] / $monthly_total) * 100, 1); ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // 月選択の処理
        document.getElementById('monthSelector').addEventListener('change', function() {
            window.location.href = window.location.pathname + '?month=' + this.value;
        });

        // カテゴリー別支出グラフ
        const categoryData = <?php echo json_encode($category_expenses); ?>;
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.category_total),
                    backgroundColor: [
                        '#4F46E5', '#7C3AED', '#EC4899', '#EF4444', '#F59E0B',
                        '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // 支出トレンドグラフ
        const trendData = <?php echo json_encode($daily_expenses); ?>;
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendData.map(item => item.daily),
                datasets: [{
                    label: '日別支出',
                    data: trendData.map(item => item.daily_total),
                    borderColor: '#3B82F6',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>