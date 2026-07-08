<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/twig_init.php';
include 'includes/get_user_data.php';

$user_id = $_SESSION['user_id'];
$ud      = get_user_data($mysqli, $user_id);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: expense_tracker.php");
    exit();
}

$id   = (int)$_GET['id'];
$stmt = $mysqli->prepare("SELECT * FROM expense_tracker WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$expense = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$expense) {
    header("Location: expense_tracker.php");
    exit();
}

$csrf_token = get_csrf_token();

echo $twig->render('edit_expense.html.twig', [
    'logged_in'   => true,
    'username'    => $_SESSION['username'],
    'budget'      => $ud['budget'],
    'total_spent' => $ud['total_spent'],
    'expense'     => $expense,
    'csrf_token'  => $csrf_token,
]);
