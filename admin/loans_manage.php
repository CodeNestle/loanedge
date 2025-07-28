<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) { go('login.php'); }

// Approve/Reject form handling (Phase1 simple)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = (int)($_POST['loan_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($loan_id > 0 && in_array($action, ['approve','reject'], true)) {
        if ($action === 'approve') {
            $stmt = $mysqli->prepare("UPDATE loans SET status='approved', approved_date=NOW() WHERE id=?");
        } else {
            $stmt = $mysqli->prepare("UPDATE loans SET status='rejected', approved_date=NULL WHERE id=?");
        }
        $stmt->bind_param('i', $loan_id);
        $stmt->execute();
        $stmt->close();
    }
}

$sql = "SELECT 
            l.id,
            u.name AS user_name,
            u.account_number AS account_number,   -- NEW
            lt.loan_name,
            l.amount,
            l.status,
            l.applied_date,
            l.approved_date
        FROM loans l
        JOIN users u ON l.user_id=u.id
        JOIN loan_types lt ON l.loan_type_id=lt.id
        ORDER BY l.status='pending' DESC, l.id DESC"; // Pending first
$res = $mysqli->query($sql);
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
<div class="container w-full max-w-6xl bg-white p-8 rounded-xl shadow-lg fade-in mt-10 mb-10">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Loan Applications</h1>
    <div class="nav flex flex-wrap justify-center items-center gap-4 mb-8">
        <a href="dashboard.php"
           class="inline-block px-6 py-3 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Back to Dashboard
        </a>
        <a href="logout.php"
           class="inline-block px-6 py-3 bg-red-600 text-white text-base font-semibold no-underline rounded-lg
                  transition-all duration-250 hover:bg-red-700 hover:shadow-md hover:scale-105">
            Logout
        </a>
    </div>
    
    <?php if (empty($rows)): ?>
        <p class="text-center text-lg text-gray-600 mt-8">No loan applications found.</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tl-lg">ID</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">User</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Account No</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Type</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Amount</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Applied</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider rounded-tr-lg">Approved</th>
                        <th class="py-3 px-4 bg-blue-600 text-white text-left text-sm font-semibold uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr class="border-b border-gray-200 last:border-b-0 hover:bg-blue-50 transition-colors duration-150">
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['id']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['user_name']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800 font-mono"><?php echo h($r['account_number']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['loan_name']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800">₹<?php echo h(number_format($r['amount'], 2)); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800 capitalize"><?php echo h($r['status']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['applied_date']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800"><?php echo h($r['approved_date']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-800">
                            <?php if ($r['status'] === 'pending'): ?>
                                <form method="post" class="inline-block">
                                    <input type="hidden" name="loan_id" value="<?php echo h($r['id']); ?>">
                                    <button type="submit" name="action" value="approve"
                                            class="bg-green-500 text-white px-4 py-2 rounded-md text-sm font-medium
                                                   transition-all duration-200 hover:bg-green-600 hover:shadow-md">
                                        Approve
                                    </button>
                                </form>
                                <form method="post" class="inline-block ml-2">
                                    <input type="hidden" name="loan_id" value="<?php echo h($r['id']); ?>">
                                    <button type="submit" name="action" value="reject"
                                            class="bg-red-500 text-white px-4 py-2 rounded-md text-sm font-medium
                                                   transition-all duration-200 hover:bg-red-600 hover:shadow-md">
                                        Reject
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-gray-500">--</span>
                            <?php endif; ?>
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
