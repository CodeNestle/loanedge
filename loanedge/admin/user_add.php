<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) { go('login.php'); }

$errors = [];
$success = '';
$name = $email = $phone = $password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Name, Email, and Password are required.';
    } else {
        // Duplicate email check (optional but safer)
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already exists.';
        }
        $stmt->close();

        if (!$errors) {
            // Use global helper (already defined in helpers.php)
            $account_number = generate_account_number($mysqli);
            if ($account_number === null) {
                $errors[] = 'Could not generate account number. Try again.';
            } else {
                // Insert user
                $stmt = $mysqli->prepare(
                    'INSERT INTO users (name, email, phone, password, account_number) VALUES (?,?,?,?,?)'
                );
                $stmt->bind_param('sssss', $name, $email, $phone, $password, $account_number);
                if ($stmt->execute()) {
                    $success = "User added! Account Number: $account_number";
                    // clear form
                    $name = $email = $phone = $password = '';
                } else {
                    $errors[] = 'DB error: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add User - Admin - LoanEdge</title>
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
<div class="container w-full max-w-md bg-white p-8 rounded-xl shadow-lg fade-in">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Add New User</h1>
    <div class="nav flex flex-wrap justify-center items-center gap-4 mb-8">
        <a href="dashboard.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Back to Dashboard
        </a>
        <a href="users_list.php"
           class="inline-block px-6 py-3 bg-gray-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-gray-700 hover:shadow-md hover:scale-105">
            User List
        </a>
        <a href="logout.php"
           class="inline-block px-6 py-3 bg-red-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-red-700 hover:shadow-md hover:scale-105">
            Logout
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="msg bg-red-100 border border-red-300 text-red-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
            <?php echo implode('<br>', array_map('h',$errors)); ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="msg bg-green-100 border border-green-300 text-green-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
            <?php echo h($success); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-4">
            <label for="name" class="block font-medium mb-2 text-gray-700 text-base">Name</label>
            <input type="text" id="name" name="name" value="<?php echo h($name); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                          focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-4">
            <label for="email" class="block font-medium mb-2 text-gray-700 text-base">Email</label>
            <input type="email" id="email" name="email" value="<?php echo h($email); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                          focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-4">
            <label for="phone" class="block font-medium mb-2 text-gray-700 text-base">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo h($phone); ?>"
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                          focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-6">
            <label for="password" class="block font-medium mb-2 text-gray-700 text-base">Password</label>
            <input type="text" id="password" name="password" value="<?php echo h($password); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                          focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <button type="submit"
                class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer
                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Add User
        </button>
    </form>
</div>
</body>
</html>
