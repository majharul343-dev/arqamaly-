<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: expense_tracker.php");
    exit();
}

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

$error = '';

if (isset($_POST['login'])) {

    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security check failed. Please try again.";
    } else {
        $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $captcha  = trim($_POST['captcha']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($captcha != $_SESSION['captcha_answer']) {
            $error = "Captcha answer is incorrect. Please try again.";
        } else {
            $stmt = $mysqli->prepare("SELECT id, username, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $username, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']  = $id;
                    $_SESSION['username'] = $username;
                    header("Location: expense_tracker.php");
                    exit();
                } else {
                    $error = "Incorrect password. Please try again.";
                }
            } else {
                $error = "No account found with that email address.";
            }

            $stmt->close();
        }
    }

    $n1 = rand(1, 10);
    $n2 = rand(1, 10);
    $_SESSION['captcha_answer'] = $n1 + $n2;
    $_SESSION['captcha_n1']     = $n1;
    $_SESSION['captcha_n2']     = $n2;
}

$csrf_token = get_csrf_token();

echo $twig->render('login.html.twig', [
    'error'      => $error,
    'captcha_q'  => $n1 . ' + ' . $n2,
    'csrf_token' => $csrf_token,
]);
