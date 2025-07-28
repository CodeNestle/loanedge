<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) { go('login.php'); }

$res = $mysqli->query('SELECT * FROM loan_types ORDER BY loan_name');
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
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
<div class="container w-full max-w-5xl bg-white p-8 rounded-xl shadow-lg fade-in mt-10 mb-10">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Loan Types</h1>
    <div class="nav flex flex-wrap justify-center items-center gap-4 mb-8">
        <a href="dashboard.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Back to Dashboard
        </a>
        <a href="loan_types_add.php"
           class="inline-block px-6 py-3 bg-green-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-green-700 hover:shadow-md hover:scale-105">
            + Add Loan Type
        </a>
        <a href="logout.php"
           class="inline-block px-6 py-3 bg-red-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-red-700 hover:shadow-md hover:scale-105">
            Logout
        </a>
    </div>
    
    <?php if (empty($rows)): ?>
        <p class="text-center text-lg text-gray-600 mt-8">No loan types found.</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tl-lg">ID</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Name</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Rate %</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Duration (m)</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Active?</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr class="border-b border-gray-200 last:border-b-0 hover:bg-blue-50 transition-colors duration-150">
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['id']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['loan_name']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['interest_rate']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['duration_months']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo $r['is_active'] ? 'Yes' : 'No'; ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800">
                            <a href="loan_types_edit.php?id=<?php echo h($r['id']); ?>"
                               class="text-blue-600 hover:text-blue-800 font-medium no-underline px-2 py-1 rounded-md transition-colors duration-150 hover:bg-blue-100">
                                Edit
                            </a> 
                            <span class="text-gray-400">|</span>
                            <a href="loan_types_delete.php?id=<?php echo h($r['id']); ?>" 
                               onclick="return confirm('Delete this loan type? All related loans will also be deleted!');"
                               class="text-red-600 hover:text-red-800 font-medium no-underline px-2 py-1 rounded-md transition-colors duration-150 hover:bg-red-100">
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
