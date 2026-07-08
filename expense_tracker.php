<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/twig_init.php';
include 'includes/get_user_data.php';

header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net; img-src 'self' data:; connect-src 'self'; font-src 'self' https://cdn.jsdelivr.net;");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

$user_id = $_SESSION['user_id'];
$ud      = get_user_data($mysqli, $user_id);

$stmt = $mysqli->prepare("SELECT * FROM expense_tracker WHERE user_id = ? ORDER BY date_created DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result   = $stmt->get_result();
$expenses = [];
$total    = 0;
while ($row = $result->fetch_assoc()) {
    $expenses[] = $row;
    $total += (float)$row['amount'];
}
$stmt->close();

$flash_success = '';
if (isset($_GET['added']))   $flash_success = "Expense added successfully!";
elseif (isset($_GET['updated'])) $flash_success = "Expense updated successfully!";
elseif (isset($_GET['deleted'])) $flash_success = "Expense deleted.";

$csrf_token = get_csrf_token();

echo $twig->render('expense_list.html.twig', [
    'logged_in'     => true,
    'username'      => $_SESSION['username'],
    'budget'        => $ud['budget'],
    'total_spent'   => $ud['total_spent'],
    'expenses'      => $expenses,
    'total'         => $total,
    'flash_success' => $flash_success,
    'csrf_token'    => $csrf_token,
]);
