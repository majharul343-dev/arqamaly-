<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/twig_init.php';
include 'includes/get_user_data.php';

$user_id = $_SESSION['user_id'];
$ud      = get_user_data($mysqli, $user_id);

$error      = '';
$csrf_token = get_csrf_token();

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'db')         $error = "A database error occurred. Please try again.";
    elseif ($_GET['error'] === 'validation') $error = "Please fill in all fields correctly.";
}

echo $twig->render('add_expense.html.twig', [
    'logged_in'   => true,
    'username'    => $_SESSION['username'],
    'budget'      => $ud['budget'],
    'total_spent' => $ud['total_spent'],
    'error'       => $error,
    'today'       => date('Y-m-d'),
    'csrf_token'  => $csrf_token,
]);
