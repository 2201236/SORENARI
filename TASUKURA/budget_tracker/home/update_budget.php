<?php
session_start();
require_once '../../db-connect/db-connect.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインが必要です。");
    }

    if (!isset($_POST['budget']) || !is_numeric($_POST['budget'])) {
        throw new Exception("無効な予算額です。");
    }

    $user_id = $_SESSION['user_id'];
    $budget = (int)$_POST['budget'];

    if ($budget < 0) {
        throw new Exception("予算額は0以上である必要があります。");
    }

    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE Bank SET budget = :budget WHERE user_id = :user_id");
    $stmt->bindParam(':budget', $budget, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>