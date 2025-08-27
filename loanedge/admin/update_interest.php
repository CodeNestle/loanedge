<?php
// admin/update_interest.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// OPTIONAL: protect with admin login (uncomment if you want auth)
// require_once __DIR__ . '/../includes/session.php';
// require_once __DIR__ . '/../includes/auth.php';
// if (!admin_is_logged_in()) { go('login.php'); }

echo "<h2>Monthly Interest Update</h2>";

/*
 * Get all active loans (pending/approved only)
 * Bring in loan type rate.
 * NOTE: last_interest_calc may be NULL; fallback to applied_date in PHP.
 */
$sql = "
    SELECT l.id, l.amount, l.interest, l.last_interest_calc, l.applied_date,
           l.status, lt.interest_rate, lt.loan_name
    FROM loans l
    JOIN loan_types lt ON lt.id = l.loan_type_id
    WHERE l.status IN ('pending','approved')
    ORDER BY l.id
";
$res = $mysqli->query($sql);
if (!$res) {
    die('Query error: ' . h($mysqli->error));
}

$updated = [];
while ($loan = $res->fetch_assoc()) {

    $loan_id     = (int)$loan['id'];
    $principal   = (float)$loan['amount'];        // remaining principal
    $interest    = (float)$loan['interest'];      // already accrued & unpaid
    $rate_annual = (float)$loan['interest_rate']; // % per year
    $base_str    = $loan['last_interest_calc'] ?: $loan['applied_date'];

    // Dates
    $base_dt = new DateTime($base_str);
    $now_dt  = new DateTime();

    // How many *full* 30-day periods since last calc
    $days   = (int)$base_dt->diff($now_dt)->days;
    $months = intdiv($days, 30);

    if ($months <= 0) {
        continue; // nothing to add
    }
    if ($principal <= 0) {
        continue; // no principal => no further interest
    }

    // Monthly simple interest
    $monthly_rate_pct = $rate_annual / 12.0;              // % per month
    $add_interest     = ($principal * $monthly_rate_pct / 100.0) * $months;
    if ($add_interest < 0) { $add_interest = 0; }

    $new_interest = $interest + $add_interest;

    // Update row: advance last_interest_calc by *months* months from base date
    // Use DATE_ADD(COALESCE(last_interest_calc, applied_date), INTERVAL ? MONTH)
    $stmt = $mysqli->prepare("
        UPDATE loans
        SET interest = ?, 
            last_interest_calc = DATE_ADD(COALESCE(last_interest_calc, applied_date), INTERVAL ? MONTH),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param('dii', $new_interest, $months, $loan_id);
    $stmt->execute();
    $stmt->close();

    $updated[] = [
        'id'           => $loan_id,
        'loan_name'    => $loan['loan_name'],
        'months'       => $months,
        'add_interest' => $add_interest,
        'new_interest' => $new_interest
    ];
}
$res->close();

if ($updated) {
    echo "<p>Interest updated for " . count($updated) . " loan(s):</p>";
    echo "<table border='1' cellpadding='6' cellspacing='0'>";
    echo "<tr><th>Loan ID</th><th>Name</th><th>Months Added</th><th>Interest Added</th><th>New Interest</th></tr>";
    foreach ($updated as $u) {
        echo "<tr>";
        echo "<td>" . h($u['id']) . "</td>";
        echo "<td>" . h($u['loan_name']) . "</td>";
        echo "<td>" . h($u['months']) . "</td>";
        echo "<td>₹" . h(number_format($u['add_interest'],2)) . "</td>";
        echo "<td>₹" . h(number_format($u['new_interest'],2)) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No loans needed interest update (less than 30 days since last calculation).</p>";
}

echo "<p><a href='dashboard.php'>Back to Admin Dashboard</a></p>";
?>
