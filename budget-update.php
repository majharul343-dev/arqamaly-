<?php
ob_start();
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Security check failed.']);
    exit();
}

include 'includes/dbex.php';

$budget  = filter_var($_POST['budget'] ?? '', FILTER_VALIDATE_FLOAT);
$user_id = (int)$_SESSION['user_id'];

if ($budget === false || $budget < 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Please enter a valid amount.']);
    exit();
}

$stmt = $mysqli->prepare("UPDATE users SET budget = ? WHERE id = ?");
$stmt->bind_param("di", $budget, $user_id);

if ($stmt->execute()) {
    ob_end_clean();
    echo json_encode(['success' => true]);
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $mysqli->error]);
}
$stmt->close();
