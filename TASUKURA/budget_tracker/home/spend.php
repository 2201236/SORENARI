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

    // 最近の支出履歴を取得
    $stmt = $pdo->prepare("
        SELECT content, outgo, daily, category
        FROM DailySpend 
        WHERE user_id = :user_id AND outgo > 0
        ORDER BY daily DESC 
        LIMIT 5
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $recentExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>支出記録 | 家計簿管理</title>
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
        .expense-card {
            transition: all 0.2s ease;
        }
        .expense-card:hover {
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
                <h1 class="text-2xl font-bold text-gray-800">支出を記録</h1>
               
            </div>
        </div>
    </header>
    <?php
        require '../../header/header_budget.php';
    ?>
    <main class="max-w-4xl mx-auto px-4 py-8 space-y-8">
        <!-- 支出入力フォーム -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 hover:shadow-md transition duration-200">
            <form id="expenseForm" method="POST" action="save_spend.php" class="space-y-6">
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
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="食費">
                            <i class="fas fa-utensils mb-2 text-xl category-icon text-blue-500"></i>
                            <span class="text-sm font-medium">食費</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="日用品">
                            <i class="fas fa-shopping-basket mb-2 text-xl category-icon text-green-500"></i>
                            <span class="text-sm font-medium">日用品</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="交通費">
                            <i class="fas fa-train mb-2 text-xl category-icon text-red-500"></i>
                            <span class="text-sm font-medium">交通費</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="住居費">
                            <i class="fas fa-home mb-2 text-xl category-icon text-yellow-500"></i>
                            <span class="text-sm font-medium">住居費</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="光熱費">
                            <i class="fas fa-bolt mb-2 text-xl category-icon text-orange-500"></i>
                            <span class="text-sm font-medium">光熱費</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="通信費">
                            <i class="fas fa-wifi mb-2 text-xl category-icon text-purple-500"></i>
                            <span class="text-sm font-medium">通信費</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="娯楽費">
                            <i class="fas fa-gamepad mb-2 text-xl category-icon text-pink-500"></i>
                            <span class="text-sm font-medium">娯楽費</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="医療費">
                            <i class="fas fa-hospital mb-2 text-xl category-icon text-teal-500"></i>
                            <span class="text-sm font-medium">医療費</span>
                        </button>
                        
                        <button type="button"
                                class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm"
                                data-category="教育費">
                            <i class="fas fa-book mb-2 text-xl category-icon text-indigo-500"></i>
                            <span class="text-sm font-medium">教育費</span>
                        </button>
                        
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
                           placeholder="支出の詳細を入力">
                </div>

                <!-- 送信ボタン -->
                <button type="submit" 
                        class="w-full bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-plus-circle"></i>
                    <span>支出を記録する</span>
                </button>
            </form>
        </div>

        <!-- 最近の支出履歴 -->
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition duration-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">最近の支出</h2>
            <?php if (empty($recentExpenses)): ?>
                <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                    <i class="fas fa-receipt text-4xl mb-4"></i>
                    <p>支出履歴がありません</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentExpenses as $expense): ?>
                        <div class="expense-card flex items-center justify-between p-4 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="bg-red-100 p-3 rounded-full">
                                    <i class="fas fa-minus text-red-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">
                                        <?php echo htmlspecialchars($expense['content']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($expense['category']); ?> ・
                                        <?php echo date('Y/m/d', strtotime($expense['daily'])); ?>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xl font-bold text-red-600">
                                -¥<?php echo number_format($expense['outgo']); ?>
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
            document.getElementById('expenseForm').addEventListener('submit', function(e) {
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