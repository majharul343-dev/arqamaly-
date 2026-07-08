<?php
// Entry point — redirect to login or dashboard
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
} else {
    header("Location: login.php");
}
exit();
