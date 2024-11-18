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

    // 表示する年月を取得（GETパラメータがない場合は現在の年月）
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

    // 月初めと月末の日付を取得
    $start_date = sprintf('%04d-%02d-01', $year, $month);
    $end_date = date('Y-m-t', strtotime($start_date));

    // 支出データを取得
    $stmt = $pdo->prepare("
        SELECT 
            daily,
            SUM(outgo) as daily_outgo,
            COUNT(*) as outgo_count,
            GROUP_CONCAT(
                CONCAT(content, ': ¥', FORMAT(outgo, 0))
                SEPARATOR '\n'
            ) as outgo_details
        FROM DailySpend 
        WHERE user_id = :user_id 
        AND daily BETWEEN :start_date AND :end_date
        GROUP BY daily
    ");
    
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    
    $daily_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $expense_data = [];
    foreach ($daily_expenses as $expense) {
        $expense_data[date('j', strtotime($expense['daily']))] = $expense;
    }

    // 収入データを取得
    $stmt = $pdo->prepare("
        SELECT 
            daily,
            SUM(ingo) as daily_income,
            COUNT(*) as income_count,
            GROUP_CONCAT(
                CONCAT(content, ': ¥', FORMAT(ingo, 0))
                SEPARATOR '\n'
            ) as income_details
        FROM DailyIncome 
        WHERE user_id = :user_id 
        AND daily BETWEEN :start_date AND :end_date
        GROUP BY daily
    ");
    
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    
    $daily_incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $income_data = [];
    foreach ($daily_incomes as $income) {
        $income_data[date('j', strtotime($income['daily']))] = $income;
    }

    // 月の合計を計算
    $monthly_outgo = array_sum(array_column($daily_expenses, 'daily_outgo'));
    $monthly_income = array_sum(array_column($daily_incomes, 'daily_income'));

    // カレンダーの日付を生成
    $first_day = new DateTime($start_date);
    $first_weekday = (int)$first_day->format('w');
    $days_in_month = (int)date('t', strtotime($start_date));
    
    // 前月・次月のリンク用の日付を計算
    $prev_month = new DateTime($start_date);
    $prev_month->modify('-1 month');
    $next_month = new DateTime($start_date);
    $next_month->modify('+1 month');

} catch (Exception $e) {
    error_log($e->getMessage());
    exit('エラーが発生しました。');
}

// JSONデータを生成（JavaScript用）
$calendar_data = [
    'expense_data' => $expense_data,
    'income_data' => $income_data
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カレンダー | 家計簿管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/calendar.css">
</head>
<body class="bg-gray-50">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">家計簿カレンダー</h1>
                <div class="flex items-center space-x-4">
                    <!-- 月選択 -->
                    <div class="flex items-center space-x-2">
                        <a href="?year=<?= $prev_month->format('Y') ?>&month=<?= $prev_month->format('m') ?>" 
                           class="p-2 hover:bg-gray-100 rounded-full">
                            <i class="fas fa-chevron-left text-gray-600"></i>
                        </a>
                        <span class="font-medium text-gray-800">
                            <?= $year ?>年<?= $month ?>月
                        </span>
                        <a href="?year=<?= $next_month->format('Y') ?>&month=<?= $next_month->format('m') ?>" 
                           class="p-2 hover:bg-gray-100 rounded-full">
                            <i class="fas fa-chevron-right text-gray-600"></i>
                        </a>
                    </div>
                    <button onclick="window.history.back()" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- 月次サマリー -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">支出合計</h2>
                    <span class="transaction-dot expense-dot"></span>
                </div>
                <p class="text-3xl font-bold text-red-600">
                    ¥<?= number_format($monthly_outgo) ?>
                </p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">収入合計</h2>
                    <span class="transaction-dot income-dot"></span>
                </div>
                <p class="text-3xl font-bold text-green-600">
                    ¥<?= number_format($monthly_income) ?>
                </p>
            </div>
        </div>

        <!-- カレンダー -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <!-- 曜日ヘッダー -->
            <div class="grid grid-cols-7 gap-px bg-gray-200 text-center">
                <div class="bg-white py-2 font-medium text-red-600">日</div>
                <div class="bg-white py-2 font-medium">月</div>
                <div class="bg-white py-2 font-medium">火</div>
                <div class="bg-white py-2 font-medium">水</div>
                <div class="bg-white py-2 font-medium">木</div>
                <div class="bg-white py-2 font-medium">金</div>
                <div class="bg-white py-2 font-medium text-blue-600">土</div>
            </div>

            <!-- カレンダー本体 -->
            <div id="calendar-grid" class="grid grid-cols-7 gap-px bg-gray-200">
                <!-- JavaScriptで動的に生成 -->
            </div>
        </div>
    </main>

    <!-- 日別詳細モーダル -->
    <div id="dayDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800" id="modalDate"></h3>
                    <button onclick="closeDayDetails()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="modalContent" class="space-y-6">
                    <!-- 支出セクション -->
                    <div class="expense-section hidden">
                        <h4 class="text-red-600 font-medium mb-2">支出</h4>
                        <div id="expenseDetails" class="space-y-2"></div>
                    </div>
                    <!-- 収入セクション -->
                    <div class="income-section hidden">
                        <h4 class="text-green-600 font-medium mb-2">収入</h4>
                        <div id="incomeDetails" class="space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PHP から JavaScript 変数へデータを渡す
        const calendarData = <?= json_encode($calendar_data) ?>;
        const currentYear = <?= $year ?>;
        const currentMonth = <?= $month ?>;
        const firstWeekday = <?= $first_weekday ?>;
        const daysInMonth = <?= $days_in_month ?>;
    </script>
    <script>
        // カレンダー関連の処理を管理するクラス
class BudgetCalendar {
    constructor(calendarData, currentYear, currentMonth, firstWeekday, daysInMonth) {
        this.calendarData = calendarData;
        this.currentYear = currentYear;
        this.currentMonth = currentMonth;
        this.firstWeekday = firstWeekday;
        this.daysInMonth = daysInMonth;
        this.calendarGrid = document.getElementById('calendar-grid');
        this.dayDetailsModal = document.getElementById('dayDetailsModal');
        this.modalDate = document.getElementById('modalDate');
        this.expenseSection = document.querySelector('.expense-section');
        this.incomeSection = document.querySelector('.income-section');
        this.expenseDetails = document.getElementById('expenseDetails');
        this.incomeDetails = document.getElementById('incomeDetails');

        this.initialize();
    }

    initialize() {
        this.generateCalendar();
        this.setupEventListeners();
    }

    generateCalendar() {
        this.calendarGrid.innerHTML = '';
        
        // 月初めまでの空白を追加
        for (let i = 0; i < this.firstWeekday; i++) {
            this.calendarGrid.appendChild(this.createEmptyCell());
        }
        
        // 日付セルを追加
        for (let day = 1; day <= this.daysInMonth; day++) {
            this.calendarGrid.appendChild(this.createDateCell(day));
        }
        
        // 月末の空白を追加
        const remainingCells = 7 - ((this.firstWeekday + this.daysInMonth) % 7);
        if (remainingCells < 7) {
            for (let i = 0; i < remainingCells; i++) {
                this.calendarGrid.appendChild(this.createEmptyCell());
            }
        }
    }

    createEmptyCell() {
        const cell = document.createElement('div');
        cell.className = 'bg-white p-2 calendar-cell';
        return cell;
    }

    createDateCell(day) {
        const cell = document.createElement('div');
        const date = this.formatDate(day);
        const isToday = date === new Date().toISOString().split('T')[0];
        
        cell.className = `bg-white p-2 calendar-cell relative cursor-pointer ${isToday ? 'today' : ''}`;
        cell.onclick = () => this.showDayDetails(date, day);
        
        // 日付表示
        cell.appendChild(this.createDateDisplay(day));
        
        // 取引データの表示
        const transactionsDiv = this.createTransactionsDisplay(day);
        if (transactionsDiv) {
            cell.appendChild(transactionsDiv);
        }
        
        return cell;
    }

    createDateDisplay(day) {
        const dateDisplay = document.createElement('div');
        dateDisplay.className = 'flex justify-between items-start';
        
        const dayNum = document.createElement('span');
        const weekday = (this.firstWeekday + day - 1) % 7;
        dayNum.className = weekday === 0 ? 'text-red-600' : (weekday === 6 ? 'text-blue-600' : '');
        dayNum.textContent = day;
        
        dateDisplay.appendChild(dayNum);
        return dateDisplay;
    }

    createTransactionsDisplay(day) {
        const hasExpense = this.calendarData.expense_data[day];
        const hasIncome = this.calendarData.income_data[day];
        
        if (!hasExpense && !hasIncome) return null;

        const transactionsDiv = document.createElement('div');
        transactionsDiv.className = 'mt-2 space-y-1 text-sm';
        
        if (hasExpense) {
            transactionsDiv.appendChild(this.createTransactionIndicator('expense', hasExpense.daily_outgo));
        }
        
        if (hasIncome) {
            transactionsDiv.appendChild(this.createTransactionIndicator('income', hasIncome.daily_income));
        }
        
        return transactionsDiv;
    }

    createTransactionIndicator(type, amount) {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-1';
        
        const isExpense = type === 'expense';
        div.innerHTML = `
            <span class="transaction-dot ${isExpense ? 'expense-dot' : 'income-dot'}"></span>
            <span class="${isExpense ? 'text-red-600' : 'text-green-600'}">
                ¥${this.numberWithCommas(amount)}
            </span>
        `;
        
        return div;
    }

    showDayDetails(date, day) {
        const expenseData = this.calendarData.expense_data[day];
        const incomeData = this.calendarData.income_data[day];
        
        // 日付表示を設定
        this.modalDate.textContent = new Date(date).toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long'
        });

        // 支出セクションの表示/非表示
        if (expenseData) {
            this.expenseSection.classList.remove('hidden');
            this.displayTransactionDetails(expenseData, 'expense');
        } else {
            this.expenseSection.classList.add('hidden');
        }

        // 収入セクションの表示/非表示
        if (incomeData) {
            this.incomeSection.classList.remove('hidden');
            this.displayTransactionDetails(incomeData, 'income');
        } else {
            this.incomeSection.classList.add('hidden');
        }

        // モーダルを表示
        this.dayDetailsModal.classList.remove('hidden');
    }

    displayTransactionDetails(data, type) {
        const container = type === 'expense' ? this.expenseDetails : this.incomeDetails;
        const details = data[type === 'expense' ? 'outgo_details' : 'income_details'];
        
        container.innerHTML = details.split('\n').map(detail => `
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span>${detail.split(': ')[0]}</span>
                <span class="${type === 'expense' ? 'text-red-600' : 'text-green-600'}">
                    ${detail.split(': ')[1]}
                </span>
            </div>
        `).join('');
    }

    closeDayDetails() {
        this.dayDetailsModal.classList.add('hidden');
    }

    setupEventListeners() {
        // モーダル外クリックでモーダルを閉じる
        this.dayDetailsModal.addEventListener('click', (e) => {
            if (e.target === this.dayDetailsModal) {
                this.closeDayDetails();
            }
        });

        // ESCキーでモーダルを閉じる
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDayDetails();
            }
        });
    }

    formatDate(day) {
        return `${this.currentYear}-${String(this.currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
}

// カレンダーの初期化
document.addEventListener('DOMContentLoaded', function() {
    const budgetCalendar = new BudgetCalendar(
        calendarData,
        currentYear,
        currentMonth,
        firstWeekday,
        daysInMonth
    );

    // グローバルスコープに必要な関数を公開
    window.closeDayDetails = () => budgetCalendar.closeDayDetails();
});
    </script>
</body>
</html>