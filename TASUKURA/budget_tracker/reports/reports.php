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
function getMonthlyData($pdo) {
    $query = "
        WITH monthly_expenses AS (
            SELECT 
                DATE_FORMAT(spend_date, '%Y-%m') as month,
                SUM(amount) as total_expense
            FROM daily_spend
            WHERE spend_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(spend_date, '%Y-%m')
        ),
        monthly_income AS (
            SELECT 
                DATE_FORMAT(income_date, '%Y-%m') as month,
                SUM(amount) as total_income
            FROM daily_income
            WHERE income_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(income_date, '%Y-%m')
        )
        SELECT 
            COALESCE(me.month, mi.month) as month,
            COALESCE(mi.total_income, 0) as income,
            COALESCE(me.total_expense, 0) as expense,
            COALESCE(mi.total_income, 0) - COALESCE(me.total_expense, 0) as balance
        FROM monthly_expenses me
        RIGHT OUTER JOIN monthly_income mi ON me.month = mi.month
        ORDER BY month DESC
        LIMIT 6";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}

function getCategoryData($pdo) {
    $query = "
        SELECT 
            category,
            SUM(amount) as total_amount
        FROM daily_spend
        WHERE spend_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
        GROUP BY category
        ORDER BY total_amount DESC";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}

// データの取得
$monthlyData = getMonthlyData($pdo);
$categoryData = getCategoryData($pdo);

// JSONデータの準備
$monthlyDataJson = json_encode(array_reverse($monthlyData));
$categoryDataJson = json_encode($categoryData);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家計簿レポート</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">家計簿レポート</h1>

        <!-- 収支推移グラフ -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">月次収支推移</h2>
            <canvas id="monthlyChart" height="300"></canvas>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- カテゴリ別支出グラフ -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">カテゴリ別支出（今月）</h2>
                <canvas id="categoryPieChart" height="300"></canvas>
            </div>

            <!-- 支出バランスグラフ -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">支出バランス（今月）</h2>
                <canvas id="categoryBarChart" height="300"></canvas>
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

        // 月次収支推移グラフ
        const monthlyData = <?php echo $monthlyDataJson; ?>;
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: '収入',
                    data: monthlyData.map(item => item.income),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: '支出',
                    data: monthlyData.map(item => item.expense),
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
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
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // カテゴリ別支出グラフ（円グラフ）
        const categoryData = <?php echo $categoryDataJson; ?>;
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.total_amount),
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)'
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

        // カテゴリ別支出グラフ（棒グラフ）
        new Chart(document.getElementById('categoryBarChart'), {
            type: 'bar',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    label: '支出額',
                    data: categoryData.map(item => item.total_amount),
                    backgroundColor: 'rgb(75, 192, 192)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
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
                                return formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>