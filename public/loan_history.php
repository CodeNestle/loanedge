<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    go('login.php');
}
$user_id = (int)$_SESSION['user_id'];

// Fetch total loans closed
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM loans WHERE user_id=? AND status='closed'");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($total_closed);
$stmt->fetch();
$stmt->close();

// Fetch total amount paid
$stmt = $mysqli->prepare("SELECT IFNULL(SUM(amount_paid),0) FROM loan_payments WHERE loan_id IN (SELECT id FROM loans WHERE user_id=?)");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($total_paid);
$stmt->fetch();
$stmt->close();

// Fetch all loan history
$sql = "SELECT l.id, lt.loan_name, l.amount, l.interest, l.status, l.applied_date, l.approved_date, l.updated_at
        FROM loans l
        JOIN loan_types lt ON lt.id = l.loan_type_id
        WHERE l.user_id=?
        ORDER BY l.id DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
    /* Custom styles for animations or specific overrides if needed */
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

<body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-start min-h-screen p-8">
<div class="container max-w-4xl w-full bg-white p-8 rounded-xl shadow-lg fade-in mt-10 mb-10">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Loan History</h1>

    <div class="summary-box bg-gray-50 border border-gray-200 p-6 rounded-lg mb-8 shadow-sm">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Summary</h3>
        <p class="text-lg text-gray-700 mb-2"><strong>Total Loans Closed:</strong> <span class="font-bold text-blue-700"><?php echo h($total_closed); ?></span></p>
        <p class="text-lg text-gray-700"><strong>Total Amount Paid:</strong> <span class="font-bold text-green-700">₹<?php echo h(number_format($total_paid, 2)); ?></span></p>
    </div>

    <?php if (empty($rows)): ?>
        <p class="text-center text-lg text-gray-600 mt-8">No loan history available.</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tl-lg">ID</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Type</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Principal</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Interest</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Applied</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Approved</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tr-lg">Last Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr class="border-b border-gray-200 last:border-b-0 hover:bg-blue-50 transition-colors duration-150">
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['id']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['loan_name']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800">₹<?php echo h(number_format($r['amount'], 2)); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800">₹<?php echo h(number_format($r['interest'], 2)); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800 capitalize"><?php echo h($r['status']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['applied_date']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['approved_date']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['updated_at']); ?></td>
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
            Dashboard
        </a>
    </div>
</div>
</body>
</html>
