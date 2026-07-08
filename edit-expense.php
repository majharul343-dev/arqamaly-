<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/get_user_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: expense_tracker.php");
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

$user_id      = $_SESSION['user_id'];
$id           = (int)$_POST['id'];
$name         = htmlspecialchars(trim($_POST['name']),     ENT_QUOTES, 'UTF-8');
$amount       = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
$category     = htmlspecialchars(trim($_POST['category']), ENT_QUOTES, 'UTF-8');
$date_created = trim($_POST['date_created']);

if ($id <= 0 || empty($name) || $amount === false || $amount <= 0 || empty($category) || empty($date_created)) {
    header("Location: expense_tracker.php");
    exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_created)) {
    header("Location: expense_tracker.php");
    exit();
}

$stmt = $mysqli->prepare(
    "UPDATE expense_tracker SET name=?, amount=?, category=?, date_created=? WHERE id=? AND user_id=?"
);
$stmt->bind_param("sdssii", $name, $amount, $category, $date_created, $id, $user_id);

header("Location: expense_tracker.php?" . ($stmt->execute() ? "updated=1" : "error=update"));
exit();
