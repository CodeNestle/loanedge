<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

require_user_login();
$uid = user_id();

// Fetch user info
$stmt = $mysqli->prepare('SELECT name, email, phone FROM users WHERE id=? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);
$stmt->fetch();
$stmt->close();

// Fetch user loans basic list
$loans = [];
$sql = 'SELECT l.id, lt.loan_name, l.amount, l.status, l.applied_date, l.approved_date
        FROM loans l
        JOIN loan_types lt ON l.loan_type_id = lt.id
        WHERE l.user_id = ?
        ORDER BY l.id DESC';
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $loans[] = $row; }
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
<div class="container w-full max-w-4xl bg-white p-8 rounded-xl shadow-lg fade-in mt-10 mb-10">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-2">Hi <?php echo h($name); ?>!</h1>
    <p class="text-center text-lg text-gray-600 mb-6">Welcome to your dashboard...</p>
    
    <div class="nav flex flex-wrap justify-center items-center gap-4 mb-8">
        <a href="apply_loan.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Apply Loan
        </a>
        <a href="pay_loan.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Pay Loan
        </a>
        <a href="loan_history.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Loan History
        </a>
        <a href="logout.php"
           class="inline-block px-6 py-3 bg-red-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-red-700 hover:shadow-md hover:scale-105">
            Logout
        </a>
    </div>

    <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">Your Loans</h2>
    <?php if (empty($loans)): ?>
        <p class="text-center text-lg text-gray-600 mt-8">No loans yet.</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tl-lg">ID</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Type</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Amount</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Applied</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tr-lg">Approved</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $ln): ?>
                    <tr class="border-b border-gray-200 last:border-b-0 hover:bg-blue-50 transition-colors duration-150">
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['id']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['loan_name']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800">₹<?php echo h(number_format($ln['amount'], 2)); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800 capitalize"><?php echo h($ln['status']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['applied_date']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($ln['approved_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
