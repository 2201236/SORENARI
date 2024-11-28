<?php
session_start();
require_once '../../db-connect/db-connect.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインが必要です。");
    }

    $user_id = $_SESSION['user_id'];
    $id = $_POST['id'] ?? null;
    $type = $_POST['type'] ?? '';
    $content = $_POST['content'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $daily = $_POST['daily'] ?? '';
    $category = $_POST['category'] ?? '';

    if (!$id || !$type || !$content || !$amount || !$daily || !$category) {
        throw new Exception("必要なデータが不足しています。");
    }

    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // トランザクション開始
    $pdo->beginTransaction();

    try {
        if ($type === 'income') {
            $stmt = $pdo->prepare("
                UPDATE DailyIncome 
                SET content = :content, 
                    ingo = :amount, 
                    daily = :daily, 
                    category = :category 
                WHERE id = :id AND user_id = :user_id
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE DailySpend 
                SET content = :content, 
                    outgo = :amount, 
                    daily = :daily, 
                    category = :category 
                WHERE id = :id AND user_id = :user_id
            ");
        }

        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
        $stmt->bindParam(':daily', $daily, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        $success = $stmt->execute();

        if ($success) {
            $pdo->commit();
            $transactionType = ($type === 'income') ? '収入' : '支出';
            $formattedAmount = number_format($amount) . '円';
            ?>
            <!DOCTYPE html>
            <html lang="ja">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>更新完了</title>
                <link rel="stylesheet" href="../../assets/css/style.css">
                <meta http-equiv="refresh" content="3;url=transactions.php">
                <style>
                    .success-container {
                        max-width: 600px;
                        margin: 100px auto;
                        text-align: center;
                        padding: 2rem;
                        background-color: #fff;
                        border-radius: 10px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .success-icon {
                        color: #4CAF50;
                        font-size: 48px;
                        margin-bottom: 1rem;
                    }
                    .success-message {
                        margin-bottom: 1.5rem;
                        color: #333;
                    }
                    .transaction-details {
                        margin-bottom: 1rem;
                        color: #666;
                    }
                    .redirect-message {
                        color: #666;
                        font-size: 0.9rem;
                    }
                    .loading-dots {
                        display: inline-block;
                    }
                    .loading-dots:after {
                        content: '...';
                        animation: dots 1.5s steps(4, end) infinite;
                    }
                    @keyframes dots {
                        0%, 20% { content: ''; }
                        40% { content: '.'; }
                        60% { content: '..'; }
                        80%, 100% { content: '...'; }
                    }
                </style>
            </head>
            <body>
                <div class="success-container">
                    <div class="success-icon">✓</div>
                    <h1 class="success-message">更新が完了しました</h1>
                    <div class="transaction-details">
                        <p><?php echo htmlspecialchars($content); ?>（<?php echo $transactionType; ?>）</p>
                        <p><?php echo $formattedAmount; ?></p>
                        <p><?php echo htmlspecialchars($daily); ?></p>
                    </div>
                    <p class="redirect-message">
                        取引一覧ページに移動します<span class="loading-dots"></span>
                    </p>
                </div>
            </body>
            </html>
            <?php
            exit;
        } else {
            throw new Exception("更新に失敗しました。");
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $e->getMessage()
    ];
    header('Location: edit_transaction.php?type=' . urlencode($type) . '&id=' . urlencode($id));
    exit;
}
?>