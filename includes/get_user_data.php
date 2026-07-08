<?php
// Returns budget and total spent this month for the logged-in user
function get_user_data($mysqli, $user_id) {
    $stmt = $mysqli->prepare("SELECT budget FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $budget = $user ? (float)$user['budget'] : 0;
    $stmt->close();

    $stmt2 = $mysqli->prepare(
        "SELECT COALESCE(SUM(amount), 0) as total
         FROM expense_tracker
         WHERE user_id = ?
           AND MONTH(date_created) = MONTH(CURDATE())
           AND YEAR(date_created)  = YEAR(CURDATE())"
    );
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $row = $res2->fetch_assoc();
    $total_spent = (float)$row['total'];
    $stmt2->close();

    return ['budget' => $budget, 'total_spent' => $total_spent];
}

// Returns the CSRF token (creates one if missing)
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verifies a CSRF token from a form submission
function verify_csrf($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die("Request blocked: invalid security token. Please go back and try again.");
    }
}
