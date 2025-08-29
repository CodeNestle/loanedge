<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    go('login.php');
}
$user_id = (int)$_SESSION['user_id'];

function accrue_monthly_interest(mysqli $db, array $loan): array {
    $loan_id    = (int)$loan['id'];
    $principal  = (float)$loan['amount'];
    $interest   = (float)$loan['interest'];
    $rate_yr    = (float)$loan['interest_rate'];

    $base_str = $loan['last_interest_calc'] ?: $loan['applied_date'];
    $base_dt    = new DateTime($base_str);
    $now_dt     = new DateTime();
    $days   = (int)$base_dt->diff($now_dt)->days;
    $months = intdiv($days, 30);

    if ($months > 0 && $principal > 0) {
        $monthly_pct = $rate_yr / 12.0;
        $add_int    = ($principal * $monthly_pct / 100.0) * $months;
        $new_int    = $interest + $add_int;

        $new_last_dt = clone $base_dt;
        $new_last_dt->modify('+' . $months . ' month');
        $new_last    = $new_last_dt->format('Y-m-d');

        // FIXED: Corrected the number of parameters and question marks
        $stmt = $db->prepare("UPDATE loans SET interest=?, last_interest_calc=? WHERE id=?");
        $stmt->bind_param('dsi', $new_int, $new_last, $loan_id);
        $stmt->execute();
        $stmt->close();

        $loan['interest'] = $new_int;
        $loan['last_interest_calc'] = $new_last;
    }
    return $loan;
}

function fetch_user_loans(mysqli $db, int $user_id): array {
    $sql = "SELECT
                l.id, l.amount, l.interest, l.status, l.applied_date, l.last_interest_calc,
                lt.loan_name, lt.interest_rate,
                u.account_number
            FROM loans l
            JOIN loan_types lt ON lt.id = l.loan_type_id
            JOIN users u ON u.id = l.user_id
            WHERE l.user_id=? AND l.status IN ('approved')
            ORDER BY l.applied_date DESC, l.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function fetch_one_loan(mysqli $db, int $user_id, int $loan_id): ?array {
    $sql = "SELECT
                l.id, l.loan_type_id, l.amount, l.interest, l.status,
                l.applied_date, l.last_interest_calc,
                lt.loan_name, lt.interest_rate,
                u.account_number
            FROM loans l
            JOIN loan_types lt ON lt.id = l.loan_type_id
            JOIN users u ON u.id = l.user_id
            WHERE l.user_id=? AND l.id=? AND l.status IN ('approved')
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ii', $user_id, $loan_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

$selected_loan_id = isset($_GET['loan_id']) ? (int)$_GET['loan_id'] : 0;
$errors   = [];
$success = [];

if ($selected_loan_id > 0) {
    $loan = fetch_one_loan($mysqli, $user_id, $selected_loan_id);
    if (!$loan) {
        $errors[] = 'Loan not found or not payable.';
    } else {
        $loan = accrue_monthly_interest($mysqli, $loan);
        $principal = (float)$loan['amount'];
        $interest  = (float)$loan['interest'];
        $total_due = $principal + $interest;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pay = (float)($_POST['pay_amount'] ?? 0);
            if ($pay <= 0) {
                $errors[] = 'Enter valid amount.';
            } elseif ($pay > $total_due) {
                $errors[] = "You entered ₹" . number_format($pay, 2) . " but total due is ₹" . number_format($total_due, 2) . ".";
            } else {
                $paid_interest = min($pay, $interest);
                $interest -= $paid_interest;
                $remain = $pay - $paid_interest;
                $paid_principal = min($remain, $principal);
                $principal -= $paid_principal;
                $new_status = ($principal <= 0.01 && $interest <= 0.01) ? 'closed' : $loan['status'];
                $stmt = $mysqli->prepare("UPDATE loans SET amount=?, interest=?, status=? WHERE id=?");
                $stmt->bind_param('ddsi', $principal, $interest, $new_status, $loan['id']);
                $stmt->execute();
                $stmt->close();

                $remark = "Paid Interest ₹" . number_format($paid_interest, 2) . ", Principal ₹" . number_format($paid_principal, 2);
                $stmt = $mysqli->prepare("INSERT INTO loan_payments (loan_id, amount_paid, remarks) VALUES (?,?,?)");
                $stmt->bind_param('ids', $loan['id'], $pay, $remark);
                $stmt->execute();
                $stmt->close();

                $success[] = "Payment ₹" . number_format($pay, 2) . " successful! Interest ₹" . number_format($paid_interest, 2) . ", Principal ₹" . number_format($paid_principal, 2) . ".";
                $success[] = "Remaining Due: ₹" . number_format(($principal + $interest), 2);
                if ($new_status === 'closed') $success[] = "Loan fully closed.";
                $loan['amount']   = $principal;
                $loan['interest']= $interest;
                $loan['status']   = $new_status;
                $total_due       = $principal + $interest;
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LoanEdge - A digital solution of loan management</title>
        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
            .fade-in {
                animation: fadeIn 0.5s ease-out forwards;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-center min-h-screen p-8">
        <div class="container max-w-md w-full bg-white p-8 rounded-xl shadow-lg fade-in">
            <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Pay Loan</h1>
            <?php if ($success): ?><div class="msg bg-green-100 border border-green-300 text-green-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center"><?php echo implode('<br>', array_map('h',$success)); ?></div><?php endif; ?>
            <?php if ($errors): ?><div class="msg bg-red-100 border border-red-300 text-red-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center"><?php echo implode('<br>', array_map('h',$errors)); ?></div><?php endif; ?>

            <?php if (!empty($loan) && !$errors): ?>
                <div class="space-y-2 mb-6 text-gray-700 text-base">
                    <p><strong>Account Number:</strong> <?php echo h($loan['account_number']); ?></p>
                    <p><strong>Loan:</strong> <?php echo h($loan['loan_name']); ?> (#<?php echo h($loan['id']); ?>)</p>
                    <p><strong>Applied:</strong> <?php echo h($loan['applied_date']); ?></p>
                    <p><strong>Principal:</strong> ₹<?php echo h(number_format($loan['amount'],2)); ?></p>
                    <p><strong>Interest:</strong> ₹<?php echo h(number_format($loan['interest'],2)); ?></p>
                    <p class="text-xl font-bold text-gray-900"><strong>Total Due:</strong> ₹<?php echo h(number_format($total_due,2)); ?></p>
                </div>

                <?php if ($loan['status'] !== 'closed'): ?>
                    <form method="post" class="mt-6">
                        <input type="number" step="0.01" name="pay_amount" placeholder="Enter amount (₹)" required
                               class="w-full p-3 border border-gray-300 rounded-lg text-base
                                       focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200 mb-4">
                        <button type="submit"
                                class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer
                                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                            Pay
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-center text-lg font-semibold text-green-700 mt-6">Loan Fully Closed.</p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="nav mt-8 text-center space-x-4">
                <a href="pay_loan.php"
                   class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                           transition-all duration-200 hover:bg-blue-600 hover:text-white">
                    Back to Loans
                </a>
                <a href="dashboard.php"
                   class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                           transition-all duration-200 hover:bg-blue-600 hover:text-white">
                    Dashboard
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$loans = fetch_user_loans($mysqli, $user_id);
foreach ($loans as $i => $ln) {
    $loans[$i] = accrue_monthly_interest($mysqli, $ln);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LoanEdge - A digital solution of loan management</title>
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Inter', sans-serif;
    }
    .fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-center min-h-screen p-8">
<div class="container max-w-4xl w-full bg-white p-8 rounded-xl shadow-lg fade-in">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Your Active Loans</h1>
    <?php if (!$loans): ?>
        <p class="text-center text-lg text-gray-600 mt-8">No active loans.</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tl-lg">ID</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Account</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Loan Type</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Applied</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Principal</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Interest</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Total Due</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tr-lg">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $ln): ?>
                        <?php $total = $ln['amount'] + $ln['interest']; ?>
                        <tr class="border-b border-gray-200 last:border-b-0 hover:bg-blue-50 transition-colors duration-150">
                            <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['id']); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['account_number']); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['loan_name']); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['applied_date']); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-800">₹<?php echo h(number_format($ln['amount'],2)); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-800">₹<?php echo h(number_format($ln['interest'],2)); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-800">₹<?php echo h(number_format($total,2)); ?></td>
                            <td class="py-3 px-4 text-sm">
                                <a href="pay_loan.php?loan_id=<?php echo h($ln['id']); ?>"
                                   class="inline-block bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-medium no-underline
                                           transition-all duration-200 hover:bg-blue-600 hover:shadow-md">
                                    Pay
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <div class="nav mt-8 text-center">
        <a href="dashboard.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                   transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Back to Dashboard
        </a>
    </div>
</div>
</body>
</html>
