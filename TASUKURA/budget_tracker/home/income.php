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

    // カテゴリーの取得
    $categories = [
        '給与', 'ボーナス', '副業', '利息', 'その他'
    ];

    // 最近の収入履歴を取得
    $stmt = $pdo->prepare("
        SELECT content, ingo, daily, category
        FROM DailyIncome 
        WHERE user_id = :user_id AND ingo > 0
        ORDER BY daily DESC 
        LIMIT 5
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $recentIncomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>収入記録 | 家計簿管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-button {
            transition: all 0.2s ease;
        }
        .category-button.active {
            background-color: #3B82F6;
            color: white;
        }
        .category-button.active i {
            color: white !important;
        }
        .category-button:hover:not(.active) {
            background-color: #EFF6FF;
            transform: translateY(-2px);
        }
        .input-group:focus-within {
            border-color: #3B82F6;
        }
        .category-icon {
            transition: all 0.2s ease;
        }
        .category-button:hover:not(.active) .category-icon {
            transform: scale(1.1);
        }
        .income-card {
            transition: all 0.2s ease;
        }
        .income-card:hover {
            transform: translateX(5px);
            background-color: #F9FAFB;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">収入を記録</h1>
               
            </div>
        </div>
    </header>
    <?php
        require '../../header/header_budget.php';
    ?>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-8">
        <!-- 収入入力フォーム -->
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition duration-200">
            <form id="incomeForm" method="POST" action="save_income.php" class="space-y-6">
                <!-- 金額入力 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">金額</label>
                    <div class="relative input-group">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">¥</span>
                        <input type="number" 
                               name="amount" 
                               required
                               class="w-full pl-8 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                               placeholder="1000">
                    </div>
                </div>

                <!-- 日付選択 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">日付</label>
                    <input type="date" 
                           name="date" 
                           required
                           value="<?php echo date('Y-m-d'); ?>"
                           class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- カテゴリー選択 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">カテゴリー</label>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="給与">
                            <i class="fas fa-briefcase mb-2 text-xl category-icon text-blue-500"></i>
                            <span class="text-sm font-medium">給与</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="ボーナス">
                            <i class="fas fa-gift mb-2 text-xl category-icon text-green-500"></i>
                            <span class="text-sm font-medium">ボーナス</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="副業">
                            <i class="fas fa-laptop-house mb-2 text-xl category-icon text-purple-500"></i>
                            <span class="text-sm font-medium">副業</span>
                        </button>
                        
                        <!-- <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="利息">
                            <i class="fas fa-chart-line mb-2 text-xl category-icon text-yellow-500"></i>
                            <span class="text-sm font-medium">利息</span>
                        </button>
                         -->
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="その他">
                            <i class="fas fa-ellipsis-h mb-2 text-xl category-icon text-gray-500"></i>
                            <span class="text-sm font-medium">その他</span>
                        </button>
                    </div>
                    <input type="hidden" name="category" id="selectedCategory">
                </div>

                <!-- メモ入力 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">メモ</label>
                    <input type="text" 
                           name="description" 
                           class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                           placeholder="収入の詳細を入力">
                </div>

                <!-- 送信ボタン -->
                <button type="submit" 
                        class="w-full bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-plus-circle"></i>
                    <span>収入を記録する</span>
                </button>
            </form>
        </div>

        <!-- 最近の収入履歴 -->
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition duration-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">最近の収入</h2>
            <?php if (empty($recentIncomes)): ?>
                <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                    <i class="fas fa-coins text-4xl mb-4"></i>
                    <p>収入履歴がありません</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentIncomes as $income): ?>
                        <div class="income-card flex items-center justify-between p-4 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="bg-green-100 p-3 rounded-full">
                                    <i class="fas fa-plus text-green-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">
                                        <?php echo htmlspecialchars($income['content']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($income['category']); ?> ・
                                        <?php echo date('Y/m/d', strtotime($income['daily'])); ?>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xl font-bold text-green-600">
                                +¥<?php echo number_format($income['ingo']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryButtons = document.querySelectorAll('.category-button');
            const selectedCategoryInput = document.getElementById('selectedCategory');

            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // 既存の選択を解除
                    categoryButtons.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    
                    // 新しい選択を適用
                    this.classList.add('active');
                    selectedCategoryInput.value = this.dataset.category;

                    // クリック時のアニメーション
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 100);
                });
            });

            // フォーム送信時の処理
            document.getElementById('incomeForm').addEventListener('submit', function(e) {
                if (!selectedCategoryInput.value) {
                    e.preventDefault();
                    alert('カテゴリーを選択してください。');
                    return;
                }
            });
        });
    </script>
</body>
</html>