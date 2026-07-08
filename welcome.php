<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/twig_init.php';
include 'includes/get_user_data.php';

$user_id = $_SESSION['user_id'];
$ud      = get_user_data($mysqli, $user_id);

$stmt = $mysqli->prepare("SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as cnt FROM expense_tracker WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$all_time_total = (float)$res['total'];
$expense_count  = (int)$res['cnt'];
$stmt->close();

$stmt2 = $mysqli->prepare(
    "SELECT category, SUM(amount) as total
     FROM expense_tracker
     WHERE user_id = ?
       AND MONTH(date_created) = MONTH(CURDATE())
       AND YEAR(date_created)  = YEAR(CURDATE())
     GROUP BY category
     ORDER BY total DESC"
);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
$categories = [];
while ($row = $res2->fetch_assoc()) {
    $categories[] = $row;
}
$stmt2->close();

$csrf_token = get_csrf_token();

echo $twig->render('welcome.html.twig', [
    'logged_in'      => true,
    'username'       => $_SESSION['username'],
    'budget'         => $ud['budget'],
    'total_spent'    => $ud['total_spent'],
    'all_time_total' => $all_time_total,
    'expense_count'  => $expense_count,
    'categories'     => $categories,
    'csrf_token'     => $csrf_token,
]);
