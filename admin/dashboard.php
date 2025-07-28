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
    /* Additional styles for clickable stat cards */
    .stat-card {
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(-100%);
        transition: transform 0.3s ease-out;
        z-index: 0;
    }
    .stat-card:hover::before {
        transform: translateX(0);
    }
    .stat-card > * {
        position: relative;
        z-index: 1;
    }
</style>
</head>
<body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-start min-h-screen p-8">

<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) {
    go('login.php');
}

$empid = $_SESSION['admin_empid'] ?? '';

// Quick stats counts
// total users
$users_total = $mysqli->query('SELECT COUNT(*) AS c FROM users')->fetch_assoc()['c'] ?? 0;
// total loans
$loans_total = $mysqli->query('SELECT COUNT(*) AS c FROM loans')->fetch_assoc()['c'] ?? 0;
// pending loans
$loans_pending = $mysqli->query("SELECT COUNT(*) AS c FROM loans WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
?>

<div class="container w-full max-w-4xl bg-white p-8 rounded-xl shadow-lg fade-in mt-10 mb-10 bg-opacity-95">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-2">Admin Dashboard</h1>
    <?php if ($empid): ?>
    <p class="text-center text-lg text-gray-600 mb-6">Employee ID: <strong class="font-semibold text-gray-800"><?php echo h($empid); ?></strong></p>
    <?php endif; ?>

    <p class="text-center text-lg text-gray-600 mb-8">Welcome Admin!</p>
    <div class="nav flex flex-wrap justify-center items-center gap-4 mb-8">
        <a href="loan_types.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                   transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Loan Types
        </a>
        <a href="loans_manage.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                   transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Loan Applications
        </a>
        <a href="users_list.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                   transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Users
        </a>
        <a href="loan_apply.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                   transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Apply Loan (for Users)
        </a>
        <a href="logout.php"
           class="inline-block px-6 py-3 bg-red-600 text-white text-base font-semibold no-underline rounded-lg
                   transition-all duration-250 hover:bg-red-700 hover:shadow-md hover:scale-105">
            Logout
        </a>
    </div>
    <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Quick Stats</h2>
    <ul class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Users Card - Now clickable -->
        <li>
            <a href="users_list.php" 
               class="stat-card p-6 bg-gray-50 border border-gray-200 rounded-lg text-xl font-semibold text-gray-800 shadow-sm
                      flex flex-col items-center justify-center border-l-4 border-green-500 transition-transform duration-200 hover:scale-105 hover:shadow-md hover:bg-green-50 hover:border-green-600">
                <span class="text-4xl font-extrabold text-green-600 mb-2"><?php echo h($users_total); ?></span>
                Total Users
            </a>
        </li>
        <!-- Total Loans Card - Now clickable -->
        <li>
            <a href="loans_manage.php" 
               class="stat-card p-6 bg-gray-50 border border-blue-200 rounded-lg text-xl font-semibold text-gray-800 shadow-sm
                      flex flex-col items-center justify-center border-l-4 border-blue-500 transition-transform duration-200 hover:scale-105 hover:shadow-md hover:bg-blue-50 hover:border-blue-600">
                <span class="text-4xl font-extrabold text-blue-600 mb-2"><?php echo h($loans_total); ?></span>
                Total Loans
            </a>
        </li>
        <!-- Pending Loans Card - Now clickable -->
        <li>
            <a href="loans_manage.php?status=pending" 
               class="stat-card p-6 bg-gray-50 border border-gray-200 rounded-lg text-xl font-semibold text-gray-800 shadow-sm
                      flex flex-col items-center justify-center border-l-4 border-orange-500 transition-transform duration-200 hover:scale-105 hover:shadow-md hover:bg-orange-50 hover:border-orange-600">
                <span class="text-4xl font-extrabold text-orange-600 mb-2"><?php echo h($loans_pending); ?></span>
                Pending Loans
            </a>
        </li>
    </ul>
</div>
</body>
</html>


<body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-start min-h-screen p-8">
