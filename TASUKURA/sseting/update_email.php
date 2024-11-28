<?php
session_start();
require '../db-connect/db-connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_email = $_POST['current_email'];
$new_email = $_POST['new_email'];

try {
    $stmt = $pdo->prepare('SELECT mailaddress FROM Users WHERE user_id = :user_id');
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user && $user['mailaddress'] === $current_email) {
        $update_stmt = $pdo->prepare('UPDATE Users SET mailaddress = :new_email WHERE user_id = :user_id');
        $update_stmt->bindParam(':new_email', $new_email);
        $update_stmt->bindParam(':user_id', $user_id);
        $update_stmt->execute();
        
        echo "<script>
                alert('メールアドレスが更新されました。');
                window.location.href = 'sseting_select.php';
              </script>";
    } else {
        echo "<script>
                alert('現在のメールアドレスが一致しません。');
                window.history.back();
              </script>";
    }
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>
