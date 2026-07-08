<?php
session_start();
include 'includes/dbex.php';
include 'includes/twig_init.php';
include 'includes/get_user_data.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $n1 = rand(1, 10);
    $n2 = rand(1, 10);
    $_SESSION['captcha_answer'] = $n1 + $n2;
    $_SESSION['captcha_n1']     = $n1;
    $_SESSION['captcha_n2']     = $n2;
} else {
    $n1 = $_SESSION['captcha_n1'] ?? 1;
    $n2 = $_SESSION['captcha_n2'] ?? 1;
}

$error   = '';
$success = false;

if (isset($_POST['register'])) {

    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security check failed. Please try again.";
    } else {
        $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $captcha  = trim($_POST['captcha']);

        if (strlen($username) < 2 || strlen($username) > 50) {
            $error = "Username must be between 2 and 50 characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($captcha != $_SESSION['captcha_answer']) {
            $error = "Captcha answer is incorrect. Please try again.";
        } else {
            $check = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "An account with this email already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt   = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed);

                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = "Registration failed. Please try again.";
                }
                $stmt->close();
            }
            $check->close();
        }
    }

    $n1 = rand(1, 10);
    $n2 = rand(1, 10);
    $_SESSION['captcha_answer'] = $n1 + $n2;
    $_SESSION['captcha_n1']     = $n1;
    $_SESSION['captcha_n2']     = $n2;
}

$csrf_token = get_csrf_token();

echo $twig->render('register.html.twig', [
    'error'      => $error,
    'success'    => $success,
    'captcha_q'  => $n1 . ' + ' . $n2,
    'csrf_token' => $csrf_token,
]);
