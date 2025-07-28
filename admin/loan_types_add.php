<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) { go('login.php'); }

$name = '';
$rate = '';
$duration = '';
$is_active = 1;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $rate     = trim($_POST['rate'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') $errors[] = 'Name required';
    if ($rate === '' || !is_numeric($rate)) $errors[] = 'Valid rate required';
    if ($duration <= 0) $errors[] = 'Duration must be positive';

    if (!$errors) {
        $stmt = $mysqli->prepare('INSERT INTO loan_types (loan_name, interest_rate, duration_months, is_active) VALUES (?,?,?,?)');
        $stmt->bind_param('sdii', $name, $rate, $duration, $is_active);
        if ($stmt->execute()) {
            go('loan_types.php');
        } else {
            $errors[] = 'Insert failed: ' . $stmt->error;
        }
        $stmt->close();
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
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Add Loan Type</h1>
    <?php if ($errors): ?>
        <div class="msg bg-red-100 border border-red-300 text-red-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
            <?php echo implode('<br>', array_map('h', $errors)); ?>
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
            <label for="rate" class="block font-medium mb-2 text-gray-700 text-base">Interest Rate (%)</label>
            <input type="text" id="rate" name="rate" value="<?php echo h($rate); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                          focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-4">
            <label for="duration" class="block font-medium mb-2 text-gray-700 text-base">Duration (months)</label>
            <input type="number" id="duration" name="duration" value="<?php echo h($duration); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                          focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-6 flex items-center">
            <input type="checkbox" id="is_active" name="is_active" <?php if ($is_active) echo 'checked'; ?>
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-gray-700 text-base font-medium">Active</label>
        </div>
        <div class="flex justify-between gap-4">
            <button type="submit"
                    class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer
                           transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                Save
            </button>
            <a href="loan_types.php"
               class="w-full p-3 text-center bg-gray-300 text-gray-800 border-none rounded-lg text-lg font-semibold no-underline cursor-pointer
                      transition-all duration-250 hover:bg-gray-400 hover:shadow-md hover:scale-105">
                Cancel
            </a>
        </div>
    </form>
    <div class="nav mt-6 text-center">
        <a href="loan_types.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                  transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Back to Loan Types
        </a>
        <a href="logout.php"
           class="inline-block px-5 py-2 text-base no-underline text-red-600 border border-red-600 rounded-lg
                  transition-all duration-200 hover:bg-red-600 hover:text-white">
            Logout
        </a>
    </div>
</div>
</body>
</html>
