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

    // パラメータの取得
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';

    if (!$type || !$id) {
        throw new Exception("無効なパラメータです。");
    }

    // データの取得
    if ($type === 'income') {
        $stmt = $pdo->prepare("
            SELECT id, content, ingo as amount, daily, category
            FROM DailyIncome 
            WHERE id = :id AND user_id = :user_id
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT id, content, outgo as amount, daily, category
            FROM DailySpend 
            WHERE id = :id AND user_id = :user_id
        ");
    }

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        throw new Exception("取引が見つかりません。");
    }

    // カテゴリーの定義
    $incomeCategories = ['給与', 'ボーナス', '副業',  'その他'];
    $expenseCategories = ['食費', '日用品', '交通費', '住居費', '光熱費', '通信費', '娯楽費', '医療費', '教育費', 'その他'];
    $categories = $type === 'income' ? $incomeCategories : $expenseCategories;

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
    <title>取引の編集 | 家計簿管理</title>
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
        .category-icon {
            transition: all 0.2s ease;
        }
        /* カテゴリーアイコンの色設定 */
        .icon-salary { color: #3B82F6; }      /* 給与: 青 */
        .icon-bonus { color: #10B981; }       /* ボーナス: 緑 */
        .icon-sidework { color: #8B5CF6; }    /* 副業: 紫 */
        .icon-interest { color: #F59E0B; }    /* 利息: オレンジ */
        .icon-food { color: #EF4444; }        /* 食費: 赤 */
        .icon-daily { color: #8B5CF6; }       /* 日用品: 紫 */
        .icon-transport { color: #3B82F6; }   /* 交通費: 青 */
        .icon-housing { color: #10B981; }     /* 住居費: 緑 */
        .icon-utility { color: #F59E0B; }     /* 光熱費: オレンジ */
        .icon-communication { color: #EC4899; } /* 通信費: ピンク */
        .icon-entertainment { color: #8B5CF6; } /* 娯楽費: 紫 */
        .icon-medical { color: #EF4444; }     /* 医療費: 赤 */
        .icon-education { color: #3B82F6; }   /* 教育費: 青 */
        .icon-other { color: #6B7280; }       /* その他: グレー */
    </style>
</head>
<?php
        require '../../header/header2.php';
    ?>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">取引を編集</h1>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form id="editForm" method="POST" action="update_transaction.php" class="space-y-6">
                <input type="hidden" name="id" value="<?= $transaction['id'] ?>">
                <input type="hidden" name="type" value="<?= $type ?>">

                <!-- 金額入力 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">金額</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">¥</span>
                        <input type="number" 
                               name="amount" 
                               required
                               value="<?= $transaction['amount'] ?>"
                               class="w-full pl-8 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- メモ入力 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">内容</label>
                    <input type="text" 
                           name="content" 
                           required
                           value="<?= htmlspecialchars($transaction['content']) ?>"
                           class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- 日付選択 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">日付</label>
                    <input type="date" 
                           name="daily" 
                           required
                           value="<?= $transaction['daily'] ?>"
                           class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- カテゴリー選択 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">カテゴリー</label>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                        <?php foreach ($categories as $category): ?>
                            <?php $iconInfo = getCategoryIcon($category); ?>
                            <button type="button"
                                    class="category-button flex flex-col items-center px-4 py-3 border rounded-lg hover:shadow-sm <?= $transaction['category'] === $category ? 'active' : '' ?>"
                                    data-category="<?= $category ?>">
                                <i class="fas <?= $iconInfo['icon'] ?> mb-2 text-xl category-icon <?= $iconInfo['class'] ?>"></i>
                                <span class="text-sm font-medium"><?= $category ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="category" id="selectedCategory" value="<?= $transaction['category'] ?>">
                </div>

                <!-- ボタン -->
                <div class="flex justify-end space-x-4">
                    <button type="button" 
                            onclick="window.history.back()" 
                            class="px-6 py-3 border rounded-lg hover:bg-gray-50 transition">
                        キャンセル
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        更新する
                    </button>
                </div>
            </form>
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
            document.getElementById('editForm').addEventListener('submit', function(e) {
                if (!selectedCategoryInput.value) {
                    e.preventDefault();
                    alert('カテゴリーを選択してください。');
                    return;
                }
            });
        });
    </script>

    <?php
    // カテゴリーアイコンを取得する関数
    function getCategoryIcon($category) {
        $icons = [
            // 収入カテゴリー
            '給与' => ['icon' => 'fa-briefcase', 'class' => 'icon-salary'],
            'ボーナス' => ['icon' => 'fa-gift', 'class' => 'icon-bonus'],
            '副業' => ['icon' => 'fa-laptop-house', 'class' => 'icon-sidework'],
            '利息' => ['icon' => 'fa-chart-line', 'class' => 'icon-interest'],
            
            // 支出カテゴリー
            '食費' => ['icon' => 'fa-utensils', 'class' => 'icon-food'],
            '日用品' => ['icon' => 'fa-shopping-basket', 'class' => 'icon-daily'],
            '交通費' => ['icon' => 'fa-train', 'class' => 'icon-transport'],
            '住居費' => ['icon' => 'fa-home', 'class' => 'icon-housing'],
            '光熱費' => ['icon' => 'fa-bolt', 'class' => 'icon-utility'],
            '通信費' => ['icon' => 'fa-wifi', 'class' => 'icon-communication'],
            '娯楽費' => ['icon' => 'fa-gamepad', 'class' => 'icon-entertainment'],
            '医療費' => ['icon' => 'fa-hospital', 'class' => 'icon-medical'],
            '教育費' => ['icon' => 'fa-book', 'class' => 'icon-education'],
            'その他' => ['icon' => 'fa-ellipsis-h', 'class' => 'icon-other']
        ];
        
        $default = ['icon' => 'fa-ellipsis-h', 'class' => 'icon-other'];
        return $icons[$category] ?? $default;
    }
    ?>
</body>
</html>