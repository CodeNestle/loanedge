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

require_user_login();
$uid = user_id();

// Get active loan types list
$types = [];
$res = $mysqli->query('SELECT id, loan_name, interest_rate, duration_months FROM loan_types WHERE is_active=1 ORDER BY loan_name');
while ($row = $res->fetch_assoc()) { $types[] = $row; }

$amount = '';
$type_id = '';
$errors = [];
$success = '';
$applied_loan_name = ''; // To store the name of the applied loan for the popup
$applied_amount = '';    // To store the amount for the popup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_id = (int)($_POST['loan_type_id'] ?? 0);
    $amount = trim($_POST['amount'] ?? '');

    if ($type_id <= 0) { $errors[] = 'Select a loan type.'; }
    if ($amount === '' || !is_numeric($amount) || $amount <= 0) { $errors[] = 'Enter valid amount.'; }

    if (empty($errors)) {
        $stmt = $mysqli->prepare('INSERT INTO loans (user_id, loan_type_id, amount) VALUES (?,?,?)');
        $amt = (float)$amount;
        $stmt->bind_param('iid', $uid, $type_id, $amt);
        if ($stmt->execute()) {
            $success = 'Loan applied! Admin approval pending.';
            $applied_amount = $amount; // Store for popup
            // Find the loan name for the popup
            foreach ($types as $t) {
                if ($t['id'] == $type_id) {
                    $applied_loan_name = $t['loan_name'];
                    break;
                }
            }
            $amount = ''; // Clear form fields after successful application
            $type_id = '';
        } else {
            $errors[] = 'DB error: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<div class="container w-full max-w-md bg-white p-8 rounded-xl shadow-lg fade-in">
    <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Apply Loan</h1>
    <?php if ($errors): ?>
        <div class="msg bg-red-100 border border-red-300 text-red-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
            <?php echo implode('<br>', array_map('h',$errors)); ?>
        </div>
    <?php endif; ?>
    <?php if ($success && empty($errors)): // Only show this message if there are no errors and success is set ?>
        <div class="msg bg-green-100 border border-green-300 text-green-800 p-3 rounded-lg mb-4 text-sm font-semibold text-center hidden" id="initial-success-message">
            <?php echo h($success); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-4">
            <label for="loan_type_id" class="block font-medium mb-2 text-gray-700 text-base">Loan Type</label>
            <select id="loan_type_id" name="loan_type_id" required
                    class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
                <option value="">-- Select --</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?php echo h($t['id']); ?>" <?php if ($t['id']==$type_id) echo 'selected'; ?>>
                        <?php echo h($t['loan_name'] . ' (' . $t['interest_rate'] . '% / ' . $t['duration_months'] . 'm)'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-6">
            <label for="amount" class="block font-medium mb-2 text-gray-700 text-base">Amount</label>
            <input type="number" id="amount" step="0.01" name="amount" value="<?php echo h($amount); ?>" required
                   class="w-full p-3 border border-gray-300 rounded-lg text-base
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
        </div>
        <button type="submit"
                class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer
                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
            Apply
        </button>
    </form>
    <div class="nav mt-6 text-center space-x-4">
        <a href="dashboard.php"
           class="inline-block px-5 py-2 text-base no-underline text-blue-600 border border-blue-600 rounded-lg
                   transition-all duration-200 hover:bg-blue-600 hover:text-white">
            Back to Dashboard
        </a>
        <a href="logout.php"
           class="inline-block px-5 py-2 text-base no-underline text-red-600 border border-red-600 rounded-lg
                   transition-all duration-200 hover:bg-red-600 hover:text-white">
            Logout
        </a>
    </div>
</div>

<!-- Loan Success Modal Structure -->
<div id="loanSuccessModal" class="modal-overlay">
    <div class="modal-content">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mt-4 mb-2">Loan Applied Successfully!</h2>
            <p class="text-gray-700 text-base">Please wait for admin approval.</p>
        </div>
        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <p class="text-gray-800 font-semibold">Loan Details:</p>
            <p class="text-gray-600">Type: <span class="font-medium text-blue-700" id="modalLoanName"></span></p>
            <p class="text-gray-600">Amount: <span class="font-medium text-blue-700" id="modalLoanAmount"></span></p>
        </div>
        <div class="flex flex-col space-y-3">
            <a href="dashboard.php" class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                Back to Dashboard
            </a>
            <button id="applyNewLoanBtn" class="w-full p-3 bg-indigo-500 text-white border-none rounded-lg text-lg font-semibold cursor-pointer transition-all duration-250 hover:bg-indigo-600 hover:shadow-md hover:scale-105">
                Apply New Loan
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('loanSuccessModal');
        const modalLoanName = document.getElementById('modalLoanName');
        const modalLoanAmount = document.getElementById('modalLoanAmount');
        const applyNewLoanBtn = document.getElementById('applyNewLoanBtn');

        // Check if a loan was successfully applied (PHP sets $success variable)
        <?php if ($success && empty($errors)): ?>
            // Populate modal content with PHP variables
            modalLoanName.textContent = '<?php echo h($applied_loan_name); ?>';
            modalLoanAmount.textContent = '₹' + parseFloat('<?php echo h($applied_amount); ?>').toLocaleString('en-IN');
            modal.classList.add('show'); // Show the modal

            // Hide the initial success message if it was present
            const initialSuccessMessage = document.getElementById('initial-success-message');
            if (initialSuccessMessage) {
                initialSuccessMessage.classList.add('hidden');
            }
        <?php endif; ?>

        // Event listener for "Apply New Loan" button
        if (applyNewLoanBtn) {
            applyNewLoanBtn.addEventListener('click', function() {
                modal.classList.remove('show'); // Hide the modal
                // Optionally, reset the form if needed, or just let the page reload for a fresh start
                // window.location.reload(); // Uncomment if you want to force a full page reload
            });
        }
    });
</script>

</body>
</html>
