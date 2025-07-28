<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) { go('login.php'); }

// Query with account_number
$res = $mysqli->query('SELECT id, name, email, phone, account_number, created_at FROM users ORDER BY id DESC');
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users - Admin - LoanEdge</title>
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
<div class="container w-full max-w-5xl bg-white p-8 rounded-xl shadow-lg fade-in mt-10 mb-10">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Registered Users</h1>
    <div class="nav flex flex-wrap justify-center items-center gap-4 mb-8">
        <a href="dashboard.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Back to Dashboard
        </a>
        <a href="user_add.php"
           class="inline-block px-6 py-3 bg-green-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-green-700 hover:shadow-md hover:scale-105">
            Add User
        </a>
        <a href="logout.php"
           class="inline-block px-6 py-3 bg-red-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-red-700 hover:shadow-md hover:scale-105">
            Logout
        </a>
    </div>
    
    <?php if (empty($rows)): ?>
        <p class="text-center text-lg text-gray-600 mt-8">No registered users found.</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tl-lg">ID</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Name</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Email</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Phone</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Account No</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tr-lg">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr class="border-b border-gray-200 last:border-b-0 hover:bg-blue-50 transition-colors duration-150">
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['id']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['name']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['email']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['phone']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800 font-mono"><?php echo h($r['account_number']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
