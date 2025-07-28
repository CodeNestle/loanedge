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

$email = $account = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $account = trim($_POST['account_number'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $account === '' || $password === '') {
        $errors[] = 'Email, Account Number & Password are required.';
    } else {
        // Step 1: Check if email exists
        $stmt_email = $mysqli->prepare('SELECT id, account_number, password FROM users WHERE email=? LIMIT 1');
        $stmt_email->bind_param('s', $email);
        $stmt_email->execute();
        $stmt_email->bind_result($uid, $db_account, $db_pass);
        
        if ($stmt_email->fetch()) {
            // Email found, now check account number
            if ($account === $db_account) {
                // Account number matches, now check password
                if ($password === $db_pass) { // plain-text compare
                    $_SESSION['user_id'] = $uid;
                    $stmt_email->close();
                    go('dashboard.php');
                } else {
                    $errors[] = 'Incorrect password.';
                }
            } else {
                $errors[] = 'Account number does not match the provided email.';
            }
        } else {
            $errors[] = 'Email not found.';
        }
        $stmt_email->close();
    }
}
?>
<div class="container max-w-md w-full bg-white p-8 rounded-xl shadow-lg fade-in">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Customer Login</h1>
    <?php if ($errors): ?>
        <div class="msg bg-red-100 border border-red-300 text-red-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
            <?php echo implode('<br>', array_map('h',$errors)); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-4">
            <label for="email" class="block font-medium mb-2 text-gray-700 text-base">Email</label>
            <input type="email" id="email" name="email" value="<?php echo h($email); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-4">
            <label for="account_number" class="block font-medium mb-2 text-gray-700 text-base">Account Number</label>
            <input type="text" id="account_number" name="account_number" value="<?php echo h($account); ?>" maxlength="5" required
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
        <a href="register.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                   transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Register
        </a>
        <a href="index.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                   transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Home
        </a>
        <a href="../admin/login.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                   transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Admin
        </a>
    </div>
</div>
</body>
</html>
