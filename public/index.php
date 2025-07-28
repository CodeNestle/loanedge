<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LoanEdge - A digital solution of loan management</title>
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="icon" href="https://codenestle.neocities.org/LDlogo.png">
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
    /* Custom style for mobile menu transition */
    .mobile-menu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .mobile-menu.open {
        max-height: 500px; /* Adjust as needed to fit content */
        transition: max-height 0.5s ease-in;
    }
</style>
</head>
<!-- The body is set to flex column to ensure the footer sticks to the bottom -->
<body class="bg-gradient-to-br from-blue-600 via-red-800 to-violet-900 text-gray-100 flex flex-col min-h-screen">

<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
?>

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

<!-- Main Content Container (below the full-width header) -->
<div class="container text-center bg-white bg-opacity-90 p-8 sm:p-8 rounded-xl shadow-lg max-w-md w-full fade-in mx-auto my-8">
    <!-- Main Title and Welcome Message -->
    <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4">LoanEdge</h1>
    <!-- Changed welcome message to English -->
    <p class="text-base sm:text-lg text-gray-700 mb-8">Welcome! Register or log in here to apply for a loan.</p>
</div>

<!-- Loan Products Section -->
<section class="w-full flex-grow py-5 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-center text-3xl sm:text-4xl font-bold text-white mb-10 fade-in">Our Loan Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Personal Loan Card - Now clickable -->
            <a href="login.php" class="block">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 fade-in h-full">
                    <img src="https://placehold.co/200x120/ADD8E6/000000?text=Personal+Loan" alt="Personal Loan" class="mx-auto mb-4 rounded-lg object-cover w-full h-32">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Personal Loan</h3>
                    <p class="text-gray-700 text-sm sm:text-base">Flexible loans for your personal needs, from emergencies to dream vacations. Quick approval process.</p>
                </div>
            </a>

            <!-- Home Loan Card - Now clickable -->
            <a href="login.php" class="block">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 fade-in h-full">
                    <img src="https://placehold.co/200x120/90EE90/000000?text=Home+Loan" alt="Home Loan" class="mx-auto mb-4 rounded-lg object-cover w-full h-32">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Home Loan</h3>
                    <p class="text-gray-700 text-sm sm:text-base">Realize your dream of owning a home with our low-interest home loans. Long repayment periods.</p>
                </div>
            </a>

            <!-- Car Loan Card - Now clickable -->
            <a href="login.php" class="block">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 fade-in h-full">
                    <img src="https://placehold.co/200x120/FFD700/000000?text=Car+Loan" alt="Car Loan" class="mx-auto mb-4 rounded-lg object-cover w-full h-32">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Car Loan</h3>
                    <p class="text-gray-700 text-sm sm:text-base">Drive your dream car today! Competitive interest rates and easy EMI options.</p>
                </div>
            </a>

            <!-- Education Loan Card - Now clickable -->
            <a href="login.php" class="block">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 fade-in h-full">
                    <img src="https://placehold.co/200x120/87CEEB/000000?text=Education+Loan" alt="Education Loan" class="mx-auto mb-4 rounded-lg object-cover w-full h-32">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Education Loan</h3>
                    <p class="text-gray-700 text-sm sm:text-base">Invest in your future with our education loans. Support for higher studies in India and abroad.</p>
                </div>
            </a>

            <!-- Business Loan Card - Now clickable -->
            <a href="login.php" class="block">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 fade-in h-full">
                    <img src="https://placehold.co/200x120/FFB6C1/000000?text=Business+Loan" alt="Business Loan" class="mx-auto mb-4 rounded-lg object-cover w-full h-32">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Business Loan</h3>
                    <p class="text-gray-700 text-sm sm:text-base">Fuel your business growth with our tailored business loans. Flexible repayment terms.</p>
                </div>
            </a>

            <!-- Gold Loan Card - Now clickable -->
            <a href="login.php" class="block">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 fade-in h-full">
                    <img src="https://placehold.co/200x120/FFD700/000000?text=Gold+Loan" alt="Gold Loan" class="mx-auto mb-4 rounded-lg object-cover w-full h-32">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Gold Loan</h3>
                    <p class="text-gray-700 text-sm sm:text-base">Get quick funds against your gold ornaments. Instant processing and minimal documentation.</p>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer class="w-full bg-gray-900 text-white py-6 mt-auto">
    <div class="max-w-6xl mx-auto text-center text-sm px-4">
        <p>&copy; <?php echo date('Y'); ?> LoanEdge. All rights reserved.</p>
        <p class="mt-2">Contact us: info@loanedge.com | +91 12345 67890</p>
        <div class="flex justify-center space-x-4 mt-4">
            <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Privacy Policy</a>
            <span class="text-gray-600">|</span>
            <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Terms of Service</a>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerBtn = document.getElementById('hamburger-button');
        const navButtons = document.getElementById('nav-buttons');
        const mobileMenuContainer = document.getElementById('mobile-menu-container');

        hamburgerBtn.addEventListener('click', function() {
            // Toggle the visibility of the main buttons group on small screens
            // navButtons.classList.toggle('hidden'); // This is for the desktop view, not needed for mobile toggle
            
            // Toggle the mobile menu container's open class for height transition
            mobileMenuContainer.classList.toggle('open');
            
            // Toggle the 'hidden' class for actual display
            if (mobileMenuContainer.classList.contains('open')) {
                mobileMenuContainer.classList.remove('hidden');
            } else {
                // Use a timeout to allow transition to complete before hiding
                setTimeout(() => {
                    mobileMenuContainer.classList.add('hidden');
                }, 500); // Match this with your transition duration
            }
        });

        // Close mobile menu if screen resizes to desktop view
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 640) { // Tailwind's 'sm' breakpoint
                mobileMenuContainer.classList.remove('open', 'hidden');
            }
        });
    });
</script>

</body>
</html>
