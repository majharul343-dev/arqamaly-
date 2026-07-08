<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/get_user_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-expense-form.php");
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

$user_id      = $_SESSION['user_id'];
$name         = htmlspecialchars(trim($_POST['name']),     ENT_QUOTES, 'UTF-8');
$amount       = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
$category     = htmlspecialchars(trim($_POST['category']), ENT_QUOTES, 'UTF-8');
$date_created = trim($_POST['date_created']);

if (empty($name) || $amount === false || $amount <= 0 || empty($category) || empty($date_created)) {
    header("Location: add-expense-form.php?error=validation");
    exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_created)) {
    header("Location: add-expense-form.php?error=validation");
    exit();
}

$stmt = $mysqli->prepare(
    "INSERT INTO expense_tracker (user_id, name, amount, category, date_created) VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("isdss", $user_id, $name, $amount, $category, $date_created);

if ($stmt->execute()) {
    header("Location: expense_tracker.php?added=1");
} else {
    header("Location: add-expense-form.php?error=db");
}
exit();
