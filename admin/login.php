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
<body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-center min-h-screen p-8">

<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';

$username = $empid = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $empid = trim($_POST['employee_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $empid === '' || $password === '') {
        $errors[] = 'Username, Employee ID & Password are required.';
    } else {
        // Step 1: Check if username exists
        $stmt_username = $mysqli->prepare('SELECT id, employee_id, password FROM admin WHERE username=? LIMIT 1');
        $stmt_username->bind_param('s', $username);
        $stmt_username->execute();
        $stmt_username->bind_result($aid, $db_empid, $db_pass);
        
        if ($stmt_username->fetch()) {
            // Username found, now check employee ID
            if ($empid === $db_empid) {
                // Employee ID matches, now check password
                if ($password === $db_pass) { // plain-text compare
                    $_SESSION['admin_id'] = $aid;
                    $_SESSION['admin_empid'] = $empid; // store for display
                    $stmt_username->close();
                    go('dashboard.php');
                } else {
                    $errors[] = 'Incorrect password.';
                }
            } else {
                $errors[] = 'Employee ID does not match the provided username.';
            }
        } else {
            $errors[] = 'Username not found.';
        }
        $stmt_username->close();
    }
}
?>
<div class="container w-full max-w-md bg-white p-8 rounded-xl shadow-lg fade-in">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Admin Login</h1>
    <?php if ($errors): ?>
        <div class="msg bg-red-100 border border-red-300 text-red-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
            <?php echo implode('<br>', array_map('h',$errors)); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-4">
            <label for="username" class="block font-medium mb-2 text-gray-700 text-base">Username</label>
            <input type="text" id="username" name="username" value="<?php echo h($username); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-4">
            <label for="employee_id" class="block font-medium mb-2 text-gray-700 text-base">Employee ID</label>
            <input type="text" id="employee_id" name="employee_id" value="<?php echo h($empid); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-6">
            <label for="password" class="block font-medium mb-2 text-gray-700 text-base">Password</label>
            <input type="password" id="password" name="password" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <button type="submit"
                class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer
                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Login
        </button>
    </form>
    <div class="nav mt-6 text-center space-x-4">
        <a href="../public/index.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                   transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Home
        </a>
        <a href="../public/login.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                   transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Customer Login
        </a>
    </div>
</div>
</body>
</html>
