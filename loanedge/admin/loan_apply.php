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

    /* Modal specific styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.75);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease-out, visibility 0.3s ease-out;
    }
    .modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    .modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 0.75rem; /* rounded-xl */
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); /* shadow-2xl */
        max-width: 24rem; /* max-w-sm */
        width: 100%;
        text-align: center;
        transform: scale(0.9);
        transition: transform 0.3s ease-out;
    }
    .modal-overlay.show .modal-content {
        transform: scale(1);
    }
</style>
</head>
<body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-center min-h-screen p-8">

<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) {
    go('login.php');
}

// Fetch loan types list
$types = [];
$res = $mysqli->query("SELECT id, loan_name FROM loan_types WHERE is_active=1 ORDER BY loan_name");
if ($res) {
    $types = $res->fetch_all(MYSQLI_ASSOC);
    $res->close();
}

$account = '';
$amount  = '';
$type_id = '';
$errors  = [];
$success = '';
$applied_account_number = ''; // To store account number for popup
$applied_amount_for_popup = ''; // To store amount for popup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account = trim($_POST['account_number'] ?? '');
    $amount  = trim($_POST['amount'] ?? '');
    $type_id = (int)($_POST['loan_type_id'] ?? 0);

    if ($account === '' || $amount === '' || $type_id <= 0) {
        $errors[] = 'Account Number, Amount & Loan Type are required.';
    } else {
        // lookup user via account number
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE account_number=? LIMIT 1');
        $stmt->bind_param('s', $account);
        $stmt->execute();
        $stmt->bind_result($uid);
        if ($stmt->fetch()) {
            $stmt->close();

            // Insert loan (interest=0; last_interest_calc = CURDATE())
            $stmt2 = $mysqli->prepare('INSERT INTO loans (user_id, loan_type_id, amount, interest, status, last_interest_calc) VALUES (?,?,?,0,"pending",CURDATE())');
            $amt = (float)$amount;
            $stmt2->bind_param('iid', $uid, $type_id, $amt);
            if ($stmt2->execute()) {
                $success = 'Loan applied successfully! Status: Admin Approval Pending.';
                $applied_account_number = $account; // Store for popup
                $applied_amount_for_popup = $amount; // Store for popup
                // Clear form
                $account = $amount = '';
                $type_id = '';
            } else {
                $errors[] = 'Insert failed: ' . $stmt2->error;
            }
            $stmt2->close();
        } else {
            $errors[] = 'No user found with this account number.';
            $stmt->close();
        }
    }
}
?>
<div class="container w-full max-w-md bg-white p-8 rounded-xl shadow-lg fade-in">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Apply Loan for User</h1>
    <?php if ($errors): ?><div class="msg bg-red-100 border border-red-300 text-red-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center"><?php echo implode('<br>', array_map('h',$errors)); ?></div><?php endif; ?>
    <!-- Success message is now handled by the modal, so removed the PHP success div here -->
    
    <form method="post">
        <div class="mb-4">
            <label for="account_number" class="block font-medium mb-2 text-gray-700 text-base">Account Number</label>
            <input type="text" id="account_number" name="account_number" value="<?php echo h($account); ?>" maxlength="5" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-4">
            <label for="amount" class="block font-medium mb-2 text-gray-700 text-base">Amount</label>
            <input type="number" id="amount" step="0.01" name="amount" value="<?php echo h($amount); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <div class="mb-6">
            <label for="loan_type_id" class="block font-medium mb-2 text-gray-700 text-base">Loan Type</label>
            <select id="loan_type_id" name="loan_type_id" required
                    class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
                <option value="">-- Select --</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?php echo h($t['id']); ?>" <?php if ($t['id']==$type_id) echo 'selected'; ?>>
                        <?php echo h($t['loan_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex justify-between gap-4">
            <button type="submit"
                    class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer
                           transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                Apply Loan
            </button>
            <a href="dashboard.php"
               class="w-full p-3 text-center bg-gray-300 text-gray-800 border-none rounded-lg text-lg font-semibold no-underline cursor-pointer
                       transition-all duration-250 hover:bg-gray-400 hover:shadow-md hover:scale-105">
                Cancel
            </a>
        </div>
    </form>
</div>

<!-- Loan Application Success Modal Structure -->
<div id="loanApplySuccessModal" class="modal-overlay">
    <div class="modal-content">
        <div class="mb-6">
            <img src="https://placehold.co/100x100/4CAF50/ffffff?text=Success" alt="Success Icon" class="mx-auto mb-4 rounded-full">
            <h2 class="text-2xl font-bold text-gray-900 mt-4 mb-2">Loan Applied Successfully!</h2>
            <p class="text-gray-700 text-base">Your loan application is pending admin approval.</p>
        </div>
        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <p class="text-gray-800 font-semibold">Account Number: <span id="modalAccountNumber" class="text-blue-700"></span></p>
            <p class="text-gray-800 font-semibold">Amount: <span id="modalAmount" class="text-blue-700"></span></p>
        </div>
        <div class="flex flex-col space-y-3">
            <a href="dashboard.php" class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                Go to Dashboard
            </a>
            <button id="applyNewLoanBtn" class="w-full p-3 bg-indigo-500 text-white border-none rounded-lg text-lg font-semibold cursor-pointer transition-all duration-250 hover:bg-indigo-600 hover:shadow-md hover:scale-105">
                Apply New Loan
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('loanApplySuccessModal');
        const modalAccountNumber = document.getElementById('modalAccountNumber');
        const modalAmount = document.getElementById('modalAmount');
        const applyNewLoanBtn = document.getElementById('applyNewLoanBtn');

        // Form fields to clear
        const accountNumberInput = document.getElementById('account_number');
        const amountInput = document.getElementById('amount');
        const loanTypeSelect = document.getElementById('loan_type_id');

        // Check if a loan was successfully applied (PHP sets $success variable)
        <?php if ($success && empty($errors)): ?>
            // Populate modal content with PHP variables
            modalAccountNumber.textContent = '<?php echo h($applied_account_number); ?>';
            modalAmount.textContent = '₹' + parseFloat('<?php echo h($applied_amount_for_popup); ?>').toLocaleString('en-IN');
            modal.classList.add('show'); // Show the modal

            // Clear form fields after successful submission (if not already cleared by PHP)
            accountNumberInput.value = '';
            amountInput.value = '';
            loanTypeSelect.value = ''; // Reset select to default option
        <?php endif; ?>

        // Event listener for "Apply New Loan" button
        if (applyNewLoanBtn) {
            applyNewLoanBtn.addEventListener('click', function() {
                modal.classList.remove('show'); // Hide the modal
                // Form fields are already cleared by PHP/initial JS, but ensure focus for new entry
                accountNumberInput.focus();
            });
        }
    });
</script>

</body>
</html>
