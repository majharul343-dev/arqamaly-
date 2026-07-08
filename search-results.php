<?php
session_start();
include 'includes/auth_check.php';
include 'includes/dbex.php';

$user_id    = $_SESSION['user_id'];
$name       = isset($_GET['name'])       ? trim($_GET['name'])       : '';
$category   = isset($_GET['category'])   ? trim($_GET['category'])   : '';
$date_from  = isset($_GET['date_from'])  ? trim($_GET['date_from'])  : '';
$date_to    = isset($_GET['date_to'])    ? trim($_GET['date_to'])    : '';
$amount_min = isset($_GET['amount_min']) ? trim($_GET['amount_min']) : '';
$amount_max = isset($_GET['amount_max']) ? trim($_GET['amount_max']) : '';

$sql    = "SELECT * FROM expense_tracker WHERE user_id = ?";
$types  = "i";
$params = [$user_id];

if ($name !== '') {
    $sql .= " AND name LIKE ?";
    $types  .= "s";
    $params[] = "%" . $name . "%";
}
if ($category !== '') {
    $sql .= " AND category = ?";
    $types  .= "s";
    $params[] = $category;
}
if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $sql .= " AND date_created >= ?";
    $types  .= "s";
    $params[] = $date_from;
}
if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $sql .= " AND date_created <= ?";
    $types  .= "s";
    $params[] = $date_to;
}
if ($amount_min !== '' && is_numeric($amount_min)) {
    $sql .= " AND amount >= ?";
    $types  .= "d";
    $params[] = (float)$amount_min;
}
if ($amount_max !== '' && is_numeric($amount_max)) {
    $sql .= " AND amount <= ?";
    $types  .= "d";
    $params[] = (float)$amount_max;
}

$sql .= " ORDER BY date_created DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result();

if ($results->num_rows > 0) {
    $grand_total = 0;
    $rows = [];
    while ($row = $results->fetch_assoc()) {
        $rows[]       = $row;
        $grand_total += (float)$row['amount'];
    }

    echo '<div class="expense-table">';
    echo '<table class="table table-hover mb-0" id="resultsTable">';
    echo '<thead><tr><th>#</th><th>Name</th><th>Amount</th><th>Category</th><th>Date</th></tr></thead><tbody>';

    foreach ($rows as $r) {
        $cat = htmlspecialchars($r['category'], ENT_QUOTES, 'UTF-8');
        echo '<tr>';
        echo '<td class="text-muted small">'  . htmlspecialchars($r['id'],           ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="fw-semibold">'        . htmlspecialchars($r['name'],         ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td><strong>&pound;'             . number_format((float)$r['amount'], 2)                    . '</strong></td>';
        echo '<td><span class="cat-badge cat-' . $cat . '">' . $cat                                       . '</span></td>';
        echo '<td class="text-muted small">'  . htmlspecialchars($r['date_created'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '<tfoot><tr><td colspan="2" class="text-end fw-bold">Total</td>';
    echo '<td colspan="3" class="fw-bold">&pound;' . number_format($grand_total, 2) . '</td></tr></tfoot>';
    echo '</table></div>';
} else {
    echo '<div class="alert alert-info">No expenses found matching your search.</div>';
}
