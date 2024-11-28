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

    // 収入履歴の取得
    $stmt = $pdo->prepare("
        SELECT id, content, ingo as amount, daily
        FROM DailyIncome
        WHERE user_id = :user_id
        ORDER BY daily DESC
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $incomeHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 支出履歴の取得
    $stmt = $pdo->prepare("
        SELECT id, content, outgo as amount, daily
        FROM DailySpend
        WHERE user_id = :user_id
        ORDER BY daily DESC
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $expenseHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>取引履歴 - 家計簿管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .transaction-card {
            transition: all 0.2s ease;
        }
        .transaction-card:hover {
            transform: translateX(5px);
            background-color: rgba(243, 244, 246, 0.8);
        }
        .modal {
            transition: opacity 0.3s ease-in-out;
        }
        .modal-content {
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ヘッダー部分は変更なし -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">取引履歴</h1>
            </div>
        </div>
    </header>
    <?php
        require '../../header/header2.php';
    ?>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <!-- タブ切り替え部分は変更なし -->
        <div class="bg-white rounded-xl shadow-sm mb-6">
            <div class="border-b">
                <div class="flex">
                    <button onclick="switchTab('income')" id="incomeTab" class="flex-1 px-6 py-4 text-center focus:outline-none relative">
                        <span class="text-lg font-medium text-gray-800">収入履歴</span>
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 transition-opacity duration-200"></div>
                    </button>
                    <button onclick="switchTab('expense')" id="expenseTab" class="flex-1 px-6 py-4 text-center focus:outline-none relative">
                        <span class="text-lg font-medium text-gray-800">支出履歴</span>
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 opacity-0 transition-opacity duration-200"></div>
                    </button>
                </div>
            </div>

            <!-- 収入履歴 -->
<div id="incomeContent" class="p-6">
    <?php if (empty($incomeHistory)): ?>
        <div class="flex flex-col items-center justify-center py-8 text-gray-500">
            <i class="fas fa-receipt text-4xl mb-4"></i>
            <p>収入の履歴がありません</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($incomeHistory as $income): ?>
                <div class="transaction-card flex justify-between items-center p-4 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-plus text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($income['content']) ?></p>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500"><?= $income['daily'] ?></span>
                                <?php if (!empty($income['category'])): ?>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                        <?= htmlspecialchars($income['category']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-xl font-bold text-green-600">
                            ¥<?= number_format($income['amount']) ?>
                        </span>
                        <div class="flex space-x-2">
                            <a href="edit_transaction.php?type=income&id=<?= $income['id'] ?>" 
                               class="p-2 text-gray-400 hover:text-blue-500 transition-colors duration-200">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="openDeleteModal('income', <?= $income['id'] ?>)" 
                                    class="p-2 text-gray-400 hover:text-red-500 transition-colors duration-200">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 支出履歴 -->
<div id="expenseContent" class="p-6 hidden">
    <?php if (empty($expenseHistory)): ?>
        <div class="flex flex-col items-center justify-center py-8 text-gray-500">
            <i class="fas fa-receipt text-4xl mb-4"></i>
            <p>支出の履歴がありません</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($expenseHistory as $expense): ?>
                <div class="transaction-card flex justify-between items-center p-4 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-minus text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($expense['content']) ?></p>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500"><?= $expense['daily'] ?></span>
                                <?php if (!empty($expense['category'])): ?>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                        <?= htmlspecialchars($expense['category']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-xl font-bold text-red-600">
                            ¥<?= number_format($expense['amount']) ?>
                        </span>
                        <div class="flex space-x-2">
                            <a href="edit_transaction.php?type=expense&id=<?= $expense['id'] ?>" 
                               class="p-2 text-gray-400 hover:text-blue-500 transition-colors duration-200">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="openDeleteModal('expense', <?= $expense['id'] ?>)" 
                                    class="p-2 text-gray-400 hover:text-red-500 transition-colors duration-200">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


    <!-- 削除確認モーダル -->
    <div id="deleteModal" class="modal fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="modal-content bg-white rounded-xl shadow-lg max-w-md w-full mx-4 transform scale-95 opacity-0">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">取引を削除</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <p class="text-gray-600 mb-6">この取引を削除してもよろしいですか？この操作は取り消せません。</p>

                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition duration-200">
                        キャンセル
                    </button>
                    <button onclick="deleteTransaction()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-200">
                        削除する
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- トースト通知 -->
    <div id="toast" class="fixed bottom-4 right-4 transform translate-y-full opacity-0 transition-all duration-500 ease-in-out z-50">
        <div class="bg-white rounded-lg shadow-lg p-4 flex items-center space-x-3">
            <div id="toastIcon" class="flex-shrink-0"></div>
            <div>
                <h4 id="toastTitle" class="font-medium text-gray-800"></h4>
                <p id="toastMessage" class="text-sm text-gray-600"></p>
            </div>
        </div>
    </div>
    <script src="transactions.js"></script>
</body>
</html>