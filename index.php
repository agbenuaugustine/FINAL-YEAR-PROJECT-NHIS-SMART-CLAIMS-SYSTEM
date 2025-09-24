<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Claims - NHIS Claims Administration System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body, html {
            font-family: 'Poppins', sans-serif;
            height: 100%;
            margin: 0;
            overflow-x: hidden;
        }
        
        .app-container {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(248,250,252,0.8));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        /* Animated Background */
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
            background-color: #f8fafc;
        }
        
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.5;
            animation: float 15s infinite ease-in-out;
        }
        
        .bg-shape-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .bg-shape-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            bottom: -150px;
            right: -50px;
            animation-delay: -5s;
        }
        
        .bg-shape-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #0d9488, #0891b2);
            top: 40%;
            left: 30%;
            animation-delay: -10s;
        }
        
        .bg-shape-4 {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            top: 20%;
            right: 10%;
            animation-delay: -7s;
        }
        
        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            33% {
                transform: translate(30px, 30px) rotate(5deg) scale(1.05);
            }
            66% {
                transform: translate(-20px, 20px) rotate(-3deg) scale(0.95);
            }
            100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
        }
        
        /* Color transition for shapes */
        .color-transition {
            animation: colorChange 15s infinite alternate;
        }
        
        @keyframes colorChange {
            0% {
                background: linear-gradient(135deg, #4f46e5, #7c3aed);
            }
            25% {
                background: linear-gradient(135deg, #0ea5e9, #0284c7);
            }
            50% {
                background: linear-gradient(135deg, #0d9488, #0891b2);
            }
            75% {
                background: linear-gradient(135deg, #8b5cf6, #6366f1);
            }
            100% {
                background: linear-gradient(135deg, #4f46e5, #7c3aed);
            }
        }
        
        /* Decorative shapes in foreground */
        .shape-bottom {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40vh;
            z-index: -1;
            overflow: hidden;
        }
        
        .shape-1 {
            position: absolute;
            bottom: -100px;
            left: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            opacity: 0.7;
            animation: pulse 8s infinite alternate;
        }
        
        .shape-2 {
            position: absolute;
            bottom: -70px;
            right: -50px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            opacity: 0.8;
            animation: pulse 12s infinite alternate-reverse;
        }
        
        .shape-3 {
            position: absolute;
            bottom: 50px;
            left: 30%;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d9488, #0891b2);
            opacity: 0.6;
            animation: pulse 10s infinite alternate;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 0.6;
            }
        }
        
        /* Wave animation */
        .waves {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 15vh;
            min-height: 100px;
            max-height: 150px;
        }
        
        .parallax > use {
            animation: move-forever 25s cubic-bezier(.55,.5,.45,.5) infinite;
        }
        
        .parallax > use:nth-child(1) {
            animation-delay: -2s;
            animation-duration: 7s;
        }
        
        .parallax > use:nth-child(2) {
            animation-delay: -3s;
            animation-duration: 10s;
        }
        
        .parallax > use:nth-child(3) {
            animation-delay: -4s;
            animation-duration: 13s;
        }
        
        .parallax > use:nth-child(4) {
            animation-delay: -5s;
            animation-duration: 20s;
        }
        
        @keyframes move-forever {
            0% {
                transform: translate3d(-90px, 0, 0);
            }
            100% {
                transform: translate3d(85px, 0, 0);
            }
        }
        
        /* Login card styles */
        .login-card {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #0f2b5b, #1e88e5);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            transform: rotate(10deg);
            box-shadow: 0 8px 16px rgba(30, 136, 229, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0f2b5b, #1e88e5);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 136, 229, 0.4);
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 0.25rem;
            transition: all 0.3s;
            background-color: #f8fafc;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #1e88e5;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.2);
            background-color: white;
        }
        
        .input-icon-container {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        
        .input-with-icon {
            padding-left: 2.5rem;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            margin-top: 2rem;
            border-radius: 12px;
            overflow: hidden;
            background-color: #f1f5f9;
            padding: 0.25rem;
        }
        
        .tab {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #64748b;
            border-radius: 10px;
        }
        
        .tab.active {
            background-color: white;
            color: #0f2b5b;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .tab-content {
            display: none;
            padding: 1.5rem 0;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* How it works section */
        .step {
            display: flex;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }
        
        .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0f2b5b, #1e88e5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        /* Contact form */
        .contact-form .form-input {
            margin-bottom: 1rem;
        }
        
        /* Mobile app style footer */
        .mobile-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
            display: flex;
            justify-content: space-around;
            padding: 0.75rem 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            z-index: 20;
        }
        
        .footer-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .footer-item i {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        
        .footer-item.active {
            color: #1e88e5;
        }
        
        /* Responsive adjustments */
        @media (min-width: 768px) {
            .login-card {
                margin-top: 2rem;
            }
            
            .mobile-footer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-background">
        <div class="bg-shape bg-shape-1 color-transition"></div>
        <div class="bg-shape bg-shape-2 color-transition"></div>
        <div class="bg-shape bg-shape-3 color-transition"></div>
        <div class="bg-shape bg-shape-4 color-transition"></div>
        
        <!-- SVG Wave Shapes -->
        <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto" style="position: absolute; bottom: 0; width: 100%; height: 15vh; z-index: -1;">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
            </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(30, 136, 229, 0.1)" />
                <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(30, 136, 229, 0.2)" />
                <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(30, 136, 229, 0.3)" />
                <use xlink:href="#gentle-wave" x="48" y="7" fill="rgba(30, 136, 229, 0.1)" />
            </g>
        </svg>
    </div>
    
    <div class="app-container flex flex-col items-center justify-center p-4 pt-10 pb-20">
        <!-- Decorative shapes -->
        <div class="shape-bottom">
            <div class="shape-1"></div>
            <div class="shape-2"></div>
            <div class="shape-3"></div>
        </div>
        
        <!-- Login Card -->
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-file-medical"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mt-4">Smart Claims</h1>
                <p class="text-gray-500">NHIS Claims Administration System</p>
            </div>
            
            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" data-tab="login">Login</div>
                <div class="tab" data-tab="how-it-works">How it Works</div>
                <div class="tab" data-tab="contact">Contact Us</div>
            </div>
            
            <!-- Login Tab Content -->
            <div class="tab-content active" id="login-content">
                <div id="login-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong class="font-bold mr-1">Error:</strong>
                    <span id="error-message">Invalid credentials</span>
                </div>
                <form id="loginForm" action="/api/login" method="POST" class="mt-4">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="input-icon-container">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" class="form-input input-with-icon" required>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="input-icon-container">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input input-with-icon" required>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="w-full btn-primary py-3 flex items-center justify-center">
                        <span>Sign In</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600">Are you a healthcare provider? <a href="/smartclaimsCL/register" class="text-blue-600 hover:underline">Register here</a></p>
                    </div>
                </form>
            </div>
            
            <!-- How it Works Tab Content -->
            <div class="tab-content" id="how-it-works-content">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">How Smart Claims Works</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <h4 class="font-medium text-gray-800">Patient Registration</h4>
                        <p class="text-sm text-gray-600">Register patients with their NHIS details and personal information.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <h4 class="font-medium text-gray-800">Service Requisition</h4>
                        <p class="text-sm text-gray-600">Record all services provided to patients with auto-generated tariffs.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <h4 class="font-medium text-gray-800">Diagnosis & Medication</h4>
                        <p class="text-sm text-gray-600">Link diagnoses to prescriptions using standard ICD-10 codes.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <div>
                        <h4 class="font-medium text-gray-800">Claims Processing</h4>
                        <p class="text-sm text-gray-600">Generate NHIS-compliant claim forms automatically and track status.</p>
                    </div>
                </div>
                
                <button class="w-full btn-primary py-2 mt-4 back-to-login">
                    Back to Login
                </button>
            </div>
            
            <!-- Contact Us Tab Content -->
            <div class="tab-content" id="contact-content">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Us</h3>
                
                <form class="contact-form">
                    <div class="input-icon-container">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" placeholder="Your Name" class="form-input input-with-icon">
                    </div>
                    
                    <div class="input-icon-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" placeholder="Email Address" class="form-input input-with-icon">
                    </div>
                    
                    <div class="input-icon-container">
                        <i class="fas fa-comment input-icon"></i>
                        <textarea placeholder="Your Message" class="form-input input-with-icon" rows="3"></textarea>
                    </div>
                    
                    <button type="button" class="w-full btn-primary py-2 mt-2">
                        Send Message
                    </button>
                </form>
                
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600 mb-2">Or reach us directly at:</p>
                    <div class="flex justify-center space-x-4">
                        <a href="#" class="text-blue-600 hover:text-blue-800">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                        <a href="#" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-phone text-xl"></i>
                        </a>
                        <a href="#" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-envelope text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <button class="w-full btn-primary py-2 mt-4 back-to-login">
                    Back to Login
                </button>
            </div>
            
            <div class="mt-6 text-center text-xs text-gray-500">
                <p>© 2024 Smart Claims. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <!-- Mobile App Style Footer -->
    <div class="mobile-footer">
        <div class="footer-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </div>
        <div class="footer-item">
            <i class="fas fa-search"></i>
            <span>Search</span>
        </div>
        <div class="footer-item">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
        </div>
        <div class="footer-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </div>
    </div>

    <script>
        // Function to get URL parameters
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }
        
        // Check for error parameters in URL
        document.addEventListener('DOMContentLoaded', function() {
            const errorParam = getUrlParameter('error');
            const errorDiv = document.getElementById('login-error');
            const errorMessage = document.getElementById('error-message');
            
            if (errorParam) {
                let message = '';
                switch(errorParam) {
                    case 'account_deactivated':
                        message = 'Your account has been deactivated. Please contact an administrator.';
                        break;
                    case 'session_expired':
                        message = 'Your session has expired. Please log in again.';
                        break;
                    case 'csrf_error':
                        message = 'Security token mismatch. Please try again.';
                        break;
                    default:
                        message = 'An error occurred. Please try again.';
                }
                
                errorMessage.textContent = message;
                errorDiv.classList.remove('hidden');
                
                // Remove the error parameter from URL to prevent showing the error again on refresh
                const url = new URL(window.location);
                url.searchParams.delete('error');
                window.history.replaceState({}, '', url);
            }
        });
        
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show the corresponding tab content
                const tabName = this.getAttribute('data-tab');
                document.getElementById(tabName + '-content').classList.add('active');
            });
        });
        
        // Back to login buttons
        document.querySelectorAll('.back-to-login').forEach(button => {
            button.addEventListener('click', function() {
                // Activate login tab
                document.querySelectorAll('.tab').forEach(t => {
                    t.classList.remove('active');
                });
                document.querySelector('[data-tab="login"]').classList.add('active');
                
                // Show login content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById('login-content').classList.add('active');
            });
        });
        
        // Login form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('login-error');
            const errorMessage = document.getElementById('error-message');
            
            // Hide any previous error messages
            errorDiv.classList.add('hidden');
            
            // Create request data
            const data = {
                username: username,
                password: password
            };
            
            // Send login request to API
            fetch('/smartclaimsCL/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                // Always try to parse the JSON response, even for error status codes
                return response.text().then(text => {
                    try {
                        const result = JSON.parse(text);
                        // Add the HTTP status to the result object for reference
                        result.httpStatus = response.status;
                        return result;
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response Text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(result => {
                if (result.status === 'success') {
                    // Store user info for role-based redirect
                    const userRole = result.user.role;
                    const userHospitalId = result.user.hospital_id;
                    
                    // Role-based redirect
                    let redirectUrl = '/smartclaimsCL/api/access/dashboard.php';
                    
                    switch(userRole) {
                        case 'superadmin':
                            redirectUrl = '/smartclaimsCL/api/access/dashboard.php?section=admin';
                            break;
                        case 'hospital_admin':
                            redirectUrl = '/smartclaimsCL/api/access/dashboard.php?section=hospital';
                            break;
                        case 'doctor':
                        case 'nurse':
                            redirectUrl = '/smartclaimsCL/api/access/dashboard.php?section=clinical';
                            break;
                        case 'lab_technician':
                            redirectUrl = '/smartclaimsCL/api/access/lab-dashboard.php';
                            break;
                        case 'pharmacist':
                            redirectUrl = '/smartclaimsCL/api/access/pharmacy-dashboard.php';
                            break;
                        case 'records_officer':
                            redirectUrl = '/smartclaimsCL/api/access/records-dashboard.php';
                            break;
                        case 'finance_officer':
                            redirectUrl = '/smartclaimsCL/api/access/finance-dashboard.php';
                            break;
                        case 'claims_officer':
                            redirectUrl = '/smartclaimsCL/api/access/claims-dashboard.php';
                            break;
                        default:
                            redirectUrl = '/smartclaimsCL/api/access/dashboard.php';
                    }
                    
                    // Show success message briefly before redirect
                    errorDiv.classList.add('hidden');
                    
                    // Add success styling temporarily
                    errorDiv.style.backgroundColor = '#d4edda';
                    errorDiv.style.borderColor = '#c3e6cb';
                    errorDiv.style.color = '#155724';
                    errorMessage.textContent = 'Login successful! Redirecting...';
                    errorDiv.classList.remove('hidden');
                    
                    // Redirect after brief delay
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1500);
                } else {
                    // Show error message from the server
                    errorMessage.textContent = result.message || 'Login failed';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>