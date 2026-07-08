<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/get_user_data.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: expense_tracker.php");
    exit();
}

verify_csrf($_GET['csrf_token'] ?? '');

$user_id = $_SESSION['user_id'];
$id      = (int)$_GET['id'];

$stmt = $mysqli->prepare("DELETE FROM expense_tracker WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$stmt->close();

header("Location: expense_tracker.php?deleted=1");
exit();
