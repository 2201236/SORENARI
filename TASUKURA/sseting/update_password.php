<?php
session_start();
require '../db-connect/db-connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];

try {
    $stmt = $pdo->prepare('SELECT password FROM Users WHERE user_id = :user_id');
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['password'])) {
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare('UPDATE Users SET password = :new_password WHERE user_id = :user_id');
        $update_stmt->bindParam(':new_password', $new_password_hashed);
        $update_stmt->bindParam(':user_id', $user_id);
        $update_stmt->execute();
        
        echo "<script>
                alert('パスワードが更新されました。');
                window.location.href = 'sseting_select.php';
              </script>";
    } else {
        echo "<script>
                alert('現在のパスワードが一致しません。');
                window.history.back();
              </script>";
    }
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>
