<?php
session_start();
require '../../db-connect/db-connect.php';

try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM Savings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貯金ダッシュボード</title>
    <link rel="stylesheet" href="css/save.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>貯金ダッシュボード</h1>
        </header>

        <section class="savings-list">
            <h2>貯金リスト</h2>
            <div class="card-container">
                <?php foreach ($savings as $index => $saving): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><?= htmlspecialchars($saving['goal'], ENT_QUOTES, 'UTF-8') ?></h3>
                        </div>
                        <div class="card-body">
                            <p><strong>貯金額:</strong> ¥<?= number_format($saving['amount']) ?></p>
                            <p><strong>目標額:</strong> ¥<?= number_format($saving['goal']) ?></p>
                            <p><strong>日付:</strong> <?= htmlspecialchars($saving['date'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if (!empty($saving['description'])): ?>
                                <p><strong>説明:</strong> <?= htmlspecialchars($saving['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <canvas id="chart-<?= $index ?>" width="150" height="150"></canvas>
                        </div>
                    </div>
                    <script>
                        const ctx<?= $index ?> = document.getElementById('chart-<?= $index ?>').getContext('2d');
                        const goal<?= $index ?> = <?= $saving['goal'] ?>;
                        const amount<?= $index ?> = <?= $saving['amount'] ?>;
                        const progress<?= $index ?> = Math.min((amount<?= $index ?> / goal<?= $index ?>) * 100, 100);

                        new Chart(ctx<?= $index ?>, {
                            type: 'doughnut',
                            data: {
                                labels: ['進捗', '残り'],
                                datasets: [{
                                    data: [progress<?= $index ?>, 100 - progress<?= $index ?>],
                                    backgroundColor: ['#0040ff', '#E0E0E0'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                return tooltipItem.label + ': ' + tooltipItem.raw.toFixed(1) + '%';
                                            }
                                        }
                                    }
                                },
                                cutout: '75%'
                            },
                            plugins: [{
                                id: 'centerText',
                                afterDraw: function(chart) {
                                    const width = chart.width;
                                    const height = chart.height;
                                    const ctx = chart.ctx;
                                    
                                    ctx.restore();
                                    
                                    // テキストのスタイル設定
                                    const fontSize = height / 4;
                                    ctx.font = `bold ${fontSize}px Arial`;
                                    ctx.textBaseline = 'middle';
                                    ctx.textAlign = 'center';
                                    ctx.fillStyle = '#333';
                                    
                                    // プログレス値の取得と表示
                                    const progress = chart.data.datasets[0].data[0];
                                    const text = `${Math.round(progress)}%`;
                                    
                                    // テキストを描画
                                    ctx.fillText(text, width / 2, height / 2);
                                    
                                    ctx.save();
                                }
                            }]
                        });
                    </script>
                <?php endforeach; ?>
            </div>
        </section>

        <footer>
            <a href="save_form.html">＋ 新しい目標を追加</a>
        </footer>
    </div>
</body>
</html>