<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';

$user_id     = $_SESSION['user_id'];
$term        = isset($_GET['term']) ? trim($_GET['term']) : '';
$suggestions = [];

if ($term !== '') {
    $like = "%" . $term . "%";
    $stmt = $mysqli->prepare("
        SELECT DISTINCT name AS val FROM expense_tracker WHERE user_id = ? AND name LIKE ?
        UNION
        SELECT DISTINCT category AS val FROM expense_tracker WHERE user_id = ? AND category LIKE ?
        ORDER BY val LIMIT 10
    ");
    $stmt->bind_param("isis", $user_id, $like, $user_id, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['val'];
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($suggestions);
