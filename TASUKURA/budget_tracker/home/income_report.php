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
}

// データ取得用の関数
function getMonthlyIncomeData($pdo) {
    $query = "
        SELECT 
            DATE_FORMAT(income_date, '%Y-%m') as month,
            SUM(amount) as total_income,
            COUNT(*) as transaction_count
        FROM daily_income
        WHERE income_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(income_date, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}

function getIncomeCategoryData($pdo) {
    $query = "
        SELECT 
            category,
            SUM(amount) as total_amount,
            COUNT(*) as transaction_count,
            AVG(amount) as average_amount
        FROM daily_income
        WHERE income_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
        GROUP BY category
        ORDER BY total_amount DESC";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}

function getTopIncomeTransactions($pdo) {
    $query = "
        SELECT 
            content,
            amount,
            income_date,
            category
        FROM daily_income
        WHERE income_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
        ORDER BY amount DESC
        LIMIT 5";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}

// データの取得
$monthlyData = getMonthlyIncomeData($pdo);
$categoryData = getIncomeCategoryData($pdo);
$topTransactions = getTopIncomeTransactions($pdo);

// JSONデータの準備
$monthlyDataJson = json_encode(array_reverse($monthlyData));
$categoryDataJson = json_encode($categoryData);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>収入レポート</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-green-700">収入レポート</h1>

        <!-- 月次収入推移グラフ -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">月次収入推移</h2>
            <canvas id="monthlyChart" height="300"></canvas>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- カテゴリ別収入グラフ -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">カテゴリ別収入（今月）</h2>
                <canvas id="categoryPieChart" height="300"></canvas>
            </div>

            <!-- カテゴリ別平均収入グラフ -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">カテゴリ別平均収入（今月）</h2>
                <canvas id="categoryBarChart" height="300"></canvas>
            </div>
        </div>

        <!-- 今月のトップ収入 -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">今月のトップ収入</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-green-50">
                            <th class="px-4 py-2 text-left">内容</th>
                            <th class="px-4 py-2 text-right">金額</th>
                            <th class="px-4 py-2 text-left">カテゴリ</th>
                            <th class="px-4 py-2 text-left">日付</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topTransactions as $transaction): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($transaction['content']); ?></td>
                            <td class="px-4 py-2 text-right">¥<?php echo number_format($transaction['amount']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($transaction['category']); ?></td>
                            <td class="px-4 py-2"><?php echo date('Y/m/d', strtotime($transaction['income_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // 金額のフォーマット関数
        const formatCurrency = (value) => {
            return new Intl.NumberFormat('ja-JP', {
                style: 'currency',
                currency: 'JPY'
            }).format(value);
        };

        // 月次収入推移グラフ
        const monthlyData = <?php echo $monthlyDataJson; ?>;
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: '収入',
                    data: monthlyData.map(item => item.total_income),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '収入: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // カテゴリ別収入グラフ（円グラフ）
        const categoryData = <?php echo $categoryDataJson; ?>;
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.total_amount),
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(45, 212, 191)',
                        'rgb(56, 189, 248)',
                        'rgb(168, 85, 247)',
                        'rgb(251, 146, 60)',
                        'rgb(236, 72, 153)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // カテゴリ別平均収入グラフ（棒グラフ）
        new Chart(document.getElementById('categoryBarChart'), {
            type: 'bar',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    label: '平均収入額',
                    data: categoryData.map(item => item.average_amount),
                    backgroundColor: 'rgb(34, 197, 94)',
                    borderColor: 'rgb(21, 128, 61)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '平均: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>