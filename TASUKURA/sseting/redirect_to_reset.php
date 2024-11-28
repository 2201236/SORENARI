<?php
session_start();

// 選択されたオプションを取得
$reset_option = $_POST['reset_option'] ?? '';

// 選択に応じてリダイレクト
if ($reset_option === 'email') {
    header('Location: reset_email.php');
} elseif ($reset_option === 'password') {
    header('Location: reset_password.php');
} else {
    echo "無効な選択です。";
}
exit;
?>
