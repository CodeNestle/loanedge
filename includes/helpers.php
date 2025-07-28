<?php
/**
 * Common helper functions
 * -----------------------
 * h()  : HTML escape
 * go() : Redirect & exit
 * generate_account_number() : Unique 5‑digit acct# for users
 * generate_employee_id()    : Optional helper for admins (EMP### style)
 */

/**
 * Escape output for HTML.
 */
function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a relative/absolute URL and stop script.
 * Use like: go('login.php'); or go('../admin/dashboard.php');
 */
function go($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Generate a unique 5-digit account number that does NOT already exist
 * in the users table. Returns string like "00427" or null on failure.
 *
 * @param mysqli $db
 * @param int $max_attempts
 * @return string|null
 */
function generate_account_number($db, $max_attempts = 20) {
    for ($i = 0; $i < $max_attempts; $i++) {
        // 00001–99999 (avoid 00000)
        $num = str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $stmt = $db->prepare('SELECT id FROM users WHERE account_number=? LIMIT 1');
        if (!$stmt) {
            break; // DB problem; bail out
        }
        $stmt->bind_param('s', $num);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            return $num;
        }
    }
    return null;
}

/**
 * OPTIONAL: Generate a unique employee ID for admin table.
 * Format: PREFIX + zero‑padded number (e.g., EMP001, EMP002…)
 * Not used yet in flow, but ready if needed.
 *
 * @param mysqli $db
 * @param string $prefix
 * @param int $width
 * @return string
 */
function generate_employee_id($db, $prefix = 'EMP', $width = 3) {
    // Find max numeric suffix from existing employee_ids starting with prefix
    $sql = "SELECT employee_id FROM admin WHERE employee_id LIKE CONCAT(?, '%')";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $prefix);
    $stmt->execute();
    $res = $stmt->get_result();
    $max = 0;
    while ($row = $res->fetch_assoc()) {
        $id = $row['employee_id'];
        $n  = (int)preg_replace('/\\D+/', '', $id); // strip non-digits
        if ($n > $max) $max = $n;
    }
    $stmt->close();
    $next = $max + 1;
    return $prefix . str_pad((string)$next, $width, '0', STR_PAD_LEFT);
}
