<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
// PHPMailer includes. Note: The path is relative to the register.php file.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once(__DIR__ . '/../PHPMailer/src/Exception.php');
require_once(__DIR__ . '/../PHPMailer/src/PHPMailer.php');
require_once(__DIR__ . '/../PHPMailer/src/SMTP.php');

$name = $email = $password = $phone = '';
$errors = [];
$success = '';
$new_account_no = '';

// Function to send an email using PHPMailer
function send_email($to, $subject, $body) {
    global $errors; // Use the global errors array to push errors

    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                      // Disable verbose debug output
        $mail->isSMTP();                                         // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                // Enable SMTP authentication
        $mail->Username   = 'codenestle13@gmail.com';            // SMTP username (REPLACE THIS)
        $mail->Password   = 'udyd gnlg oqdc tgok';               // SMTP password (REPLACE THIS)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable implicit TLS encryption
        $mail->Port       = 465;                                 // TCP port to connect to

        //Recipients
        $mail->setFrom('codenestle13@gmail.com', 'LoanEdge Admin'); // REPLACE with your email
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Plain text version for non-HTML mail clients

        $mail->send();
        return true;
    } catch (Exception $e) {
        $errors[] = "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') { $errors[] = 'Name required.'; }
    if ($email === '') { $errors[] = 'Email required.'; }
    
    // Server-side email format validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address format (e.g., user@example.com).';
    }

    // Strong password check: Minimum 8 characters, at least one uppercase, one lowercase, one number, one special character
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }

    // Phone number validation: Must not exceed 10 digits
    if (!empty($phone) && strlen($phone) > 10) {
        $errors[] = 'Phone number cannot exceed 10 digits.';
    }

    // Duplicate email check
    if (empty($errors)) { // Check errors again after password and phone validation
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already exists.';
        }
        $stmt->close();
    }

    // Generate unique 5-digit account number
    if (empty($errors)) { // Check errors again before generating account number
        $new_account_no = generate_account_number($mysqli);
        if ($new_account_no === null) {
            $errors[] = 'Unable to generate account number. Try again.';
        }
    }

    if (empty($errors)) { // Final check before database insertion
        $stmt = $mysqli->prepare('INSERT INTO users (name,email,account_number,password,phone) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssss', $name, $email, $new_account_no, $password, $phone);
        if ($stmt->execute()) {
            // Send a confirmation email to the new user
            $email_subject = 'Welcome to LoanEdge!';
            $email_body = "
                <html>
                <body style='font-family: Inter, sans-serif; line-height: 1.6; background-color: #f3f4f6; padding: 20px; text-align: center;'>
                    <div style='max-width: 600px; margin: auto; padding: 20px; border-radius: 10px; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);'>
                        <h2 style='color: #1f2937; font-size: 24px; font-weight: bold;'>Registration Successful!</h2>
                        <hr style='border: 0; height: 1px; background-color: #e5e7eb; margin: 20px 0;'>
                        <p style='color: #4b5563;'>Hello " . h($name) . ",</p>
                        <p style='color: #4b5563;'>Thank you for registering with LoanEdge. Your account has been successfully created.</p>
                        <p style='color: #4b5563;'>Your unique account number is: <strong style='color: #2563eb;'>" . h($new_account_no) . "</strong></p>
                        <p style='color: #4b5563;'>Please use this account number along with your password to log in.</p>
                        <br>
                        <p style='color: #4b5563;'>Best regards,<br>
                        <strong>The LoanEdge Team</strong></p>
                    </div>
                </body>
                </html>
            ";
            
            // Call the send_email function. The errors array will be populated if it fails.
            send_email($email, $email_subject, $email_body);

            $success = 'Registration successful!'; 
            // Clear form
            $name = $email = $password = $phone = '';
        } else {
            $errors[] = 'DB insert failed: ' . $stmt->error;
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
    <title>LoanEdge - A digital solution of loan management</title> <!-- Updated Title -->
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom styles for animations or specific overrides if needed */
        body {
            font-family: 'Inter', sans-serif;
            /* Background Image Styles */
            background-image: none;
            background-size: auto;
            background-position: initial;
            background-attachment: initial;
            background-repeat: initial;
        }
        /* Simple fade-in animation for the container */
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toast Notification Styles */
        #toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 0.75rem; /* Equivalent to Tailwind 'space-y-3' */
            max-width: 20rem; /* max-w-xs */
        }
        .toast {
            padding: 0.75rem 1rem; /* p-3 px-4 */
            border-radius: 0.5rem; /* rounded-lg */
            font-size: 0.875rem; /* text-sm */
            font-weight: 600; /* font-semibold */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* shadow-md */
            opacity: 0;
            transform: translateY(-10px);
            animation: toastIn 0.3s ease-out forwards; /* Only animate in, removal handled by JS */
        }
        .toast.success {
            background-color: #d1fae5; /* bg-green-100 */
            border: 1px solid #34d399; /* border-green-300 */
            color: #065f46; /* text-green-800 */
        }
        .toast.error {
            background-color: #fee2e2; /* bg-red-100 */
            border: 1px solid #ef4444; /* border-red-300 */
            color: #991b1b; /* text-red-800 */
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* No toastOut animation here, handled by JS for removal */

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

<!-- Full-Width Header Section (Logo Left, Buttons Right) -->
<header class="w-full bg-gray-900 text-white py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <!-- Logo -->
        <img src="https://codenestle.neocities.org/LDlogo.png" alt="LoanEdge Logo" 
             class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-full shadow-lg border-2 border-blue-100">
        
        <!-- Hamburger Menu Icon (visible on small screens) -->
        <button id="hamburger-button" class="sm:hidden text-white focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Buttons Group (hidden on small screens, flex on medium and larger) -->
        <div id="nav-buttons" class="hidden sm:flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
            <a href="register.php"
               class="inline-block px-4 py-2 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105 w-full sm:w-auto">
                Register
            </a>
            <a href="login.php"
               class="inline-block px-4 py-2 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg
                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105 w-full sm:w-auto">
                Login
            </a>
            <a href="../admin/login.php"
               class="inline-block px-4 py-2 bg-indigo-600 text-white text-base font-semibold no-underline rounded-lg
                       transition-all duration-250 hover:bg-indigo-700 hover:shadow-md hover:scale-105 w-full sm:w-auto">
                Admin Login
            </a>
        </div>
    </div>
    
    <!-- Mobile Menu Container (hidden by default, shown when hamburger is clicked) -->
    <div id="mobile-menu-container" class="mobile-menu sm:hidden mt-4">
        <div class="flex flex-col space-y-2">
            <a href="register.php"
               class="block px-4 py-2 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg text-center
                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                Register
            </a>
            <a href="login.php"
               class="block px-4 py-2 bg-blue-600 text-white text-base font-semibold no-underline rounded-lg text-center
                       transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                Login
            </a>
            <a href="../admin/login.php"
               class="block px-4 py-2 bg-indigo-600 text-white text-base font-semibold no-underline rounded-lg text-center
                       transition-all duration-250 hover:bg-indigo-700 hover:shadow-md hover:scale-105">
                Admin Login
            </a>
        </div>
    </div>
</header>

<!-- Updated body background with a gradient for a professional banking look -->
<body class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-gray-800 flex justify-center items-center min-h-screen p-8">

    <div class="container max-w-md w-full bg-white p-8 rounded-xl shadow-lg fade-in">
        <h1 class="text-center text-3xl font-bold text-gray-900 mb-6">Register (Customer)</h1>
        
        <!-- Removed PHP error/success divs here, JavaScript toasts will handle messages -->

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
                <label for="password" class="block font-medium mb-2 text-gray-700 text-base">Password</label>
                <input type="password" id="password" name="password" value="<?php echo h($password); ?>" required
                       class="w-full p-3 border border-gray-300 rounded-lg text-base
                              focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
            </div>
            <div class="mb-6">
                <label for="phone" class="block font-medium mb-2 text-gray-700 text-base">Phone</label>
                <input type="text" id="phone" name="phone" maxlength="10" value="<?php echo h($phone); ?>"
                       class="w-full p-3 border border-gray-300 rounded-lg text-base
                              focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-200">
            </div>
            <button type="submit"
                    class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer
                           transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                Register
            </button>
        </form>
        <div class="nav mt-6 text-center">
            <a href="login.php"
               class="inline-block mx-2 my-1 px-4 py-2 text-sm no-underline text-blue-600 border border-blue-600 rounded-lg
                       transition-all duration-200 hover:bg-blue-600 hover:text-white">
                Already registered? Login
            </a>
            <a href="index.php"
               class="inline-block mx-2 my-1 px-4 py-2 text-sm no-underline text-blue-600 border border-blue-600 rounded-lg
                       transition-all duration-200 hover:bg-blue-600 hover:text-white">
                Home
            </a>
        </div>
    </div>

    <!-- Registration Success Modal Structure -->
    <div id="registrationSuccessModal" class="modal-overlay">
        <div class="modal-content">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mt-4 mb-2">Registration Successful!</h2>
                <p class="text-gray-700 text-base">Your account has been created. Please log in to continue.</p>
                <p class="text-gray-800 font-semibold mt-4">Your Account Number: <span id="modalAccountNumber" class="text-blue-700"></span></p>
                <p class="text-gray-600 text-sm mt-2">Redirecting to login page in <span id="countdown">5</span> seconds...</p>
            </div>
            <div class="flex flex-col space-y-3">
                <button id="goToLoginBtn" class="w-full p-3 bg-blue-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer transition-all duration-250 hover:bg-blue-700 hover:shadow-md hover:scale-105">
                    Go to Login Now
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastContainer = document.getElementById('toast-container');
            const modal = document.getElementById('registrationSuccessModal');
            const modalAccountNumber = document.getElementById('modalAccountNumber');
            const goToLoginBtn = document.getElementById('goToLoginBtn');
            const countdownSpan = document.getElementById('countdown');
            const emailInput = document.getElementById('email');
            let countdownInterval;

            function showToast(message, type = 'info', duration = 3000) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastContainer.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, duration);
            }

            emailInput.addEventListener('blur', function() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/; 

                if (email !== '' && !emailRegex.test(email)) {
                    showToast('Please enter a valid email address format (e.g., user@example.com).', 'error');
                }
            });

            <?php if ($errors): ?>
                <?php foreach ($errors as $error): ?>
                    showToast('<?php echo h($error); ?>', 'error');
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($success && empty($errors)): ?>
                modalAccountNumber.textContent = '<?php echo h($new_account_no); ?>';
                modal.classList.add('show');

                let seconds = 5;
                countdownSpan.textContent = seconds;
                countdownInterval = setInterval(() => {
                    seconds--;
                    countdownSpan.textContent = seconds;
                    if (seconds <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = 'login.php';
                    }
                }, 1000);

                if (goToLoginBtn) {
                    goToLoginBtn.addEventListener('click', function() {
                        clearInterval(countdownInterval);
                        window.location.href = 'login.php';
                    });
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>
