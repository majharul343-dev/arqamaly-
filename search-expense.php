<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';
include 'includes/twig_init.php';
include 'includes/get_user_data.php';

$user_id    = $_SESSION['user_id'];
$ud         = get_user_data($mysqli, $user_id);
$csrf_token = get_csrf_token();

echo $twig->render('search.html.twig', [
    'logged_in'   => true,
    'username'    => $_SESSION['username'],
    'budget'      => $ud['budget'],
    'total_spent' => $ud['total_spent'],
    'csrf_token'  => $csrf_token,
]);
