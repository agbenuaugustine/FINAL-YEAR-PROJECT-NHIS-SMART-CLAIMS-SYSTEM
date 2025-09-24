<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Claims NHIS</title>
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
        
        /* Registration card styles */
        .register-card {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem auto;
            position: relative;
            z-index: 10;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-logo {
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
        
        /* Form grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Message animation */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message-animation {
            animation: fadeInDown 0.5s ease-out forwards;
        }
        
        /* Validation styles */
        .form-input.invalid {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
        }
        
        .form-input.valid {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
        }
        
        .error-text {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .auto-generated {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }
        
        .character-count {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .character-count.over-limit {
            color: #dc2626;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-background">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
        <div class="bg-shape bg-shape-4"></div>
    </div>
    
    <div class="app-container flex flex-col items-center justify-center p-4">
        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">
                    <i class="fas fa-hospital-user"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mt-4">Hospital Registration</h1>
                <p class="text-gray-600 mt-2">Register your hospital for Smart Claims NHIS system</p>
            </div>
            
            <!-- Message container - completely hidden by default -->
            <div id="messageContainer" class="hidden">
                <!-- Success Message -->
                <div id="successMessage" class="mb-6 rounded-lg bg-green-100 p-4 text-sm text-green-800 shadow-md border border-green-200 flex items-center hidden">
                    <div class="mr-3 flex-shrink-0">
                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="font-medium">Success!</span> Registration successful! Your account is pending approval by an administrator.
                    </div>
                </div>
                
                <!-- Error Message -->
                <div id="errorMessage" class="mb-6 rounded-lg bg-red-100 p-4 text-sm text-red-800 shadow-md border border-red-200 flex items-center hidden">
                    <div class="mr-3 flex-shrink-0">
                        <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="font-medium">Error!</span> <span id="errorText"></span>
                    </div>
                </div>
            </div>
            
            <form id="registrationForm">
                <!-- Hospital Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Hospital Information</h3>
                    
                    <div class="form-grid">
                        <div class="mb-4">
                            <label for="hospitalName" class="block text-sm font-medium text-gray-700 mb-1">Hospital Name</label>
                            <div class="input-icon-container">
                                <i class="fas fa-hospital input-icon"></i>
                                <input type="text" id="hospitalName" name="hospitalName" class="form-input input-with-icon" placeholder="Enter hospital name" required>
                                <div class="error-text" id="hospitalNameError"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="hospitalCode" class="block text-sm font-medium text-gray-700 mb-1">Hospital Code <span class="text-xs text-green-600">(Auto-generated)</span></label>
                            <div class="input-icon-container">
                                <i class="fas fa-barcode input-icon"></i>
                                <input type="text" id="hospitalCode" name="hospitalCode" class="form-input input-with-icon auto-generated" placeholder="Will be auto-generated" readonly>
                                <div class="error-text" id="hospitalCodeError"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="hospitalId" class="block text-sm font-medium text-gray-700 mb-1">Hospital ID <span class="text-xs text-green-600">(Auto-generated)</span></label>
                        <div class="input-icon-container">
                            <i class="fas fa-id-card input-icon"></i>
                            <input type="text" id="hospitalId" name="hospitalId" class="form-input input-with-icon auto-generated" placeholder="Will be auto-generated" readonly>
                            <div class="error-text" id="hospitalIdError"></div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="mb-4">
                            <label for="hospitalType" class="block text-sm font-medium text-gray-700 mb-1">Hospital Type</label>
                            <select id="hospitalType" name="hospitalType" class="form-input" required>
                                <option value="">Select hospital type</option>
                                <option value="Government">Government</option>
                                <option value="Private">Private</option>
                                <option value="Mission">Mission</option>
                                <option value="Quasi-Government">Quasi-Government</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="hospitalCategory" class="block text-sm font-medium text-gray-700 mb-1">Hospital Category</label>
                            <select id="hospitalCategory" name="hospitalCategory" class="form-input" required>
                                <option value="">Select hospital category</option>
                                <option value="Teaching Hospital">Teaching Hospital</option>
                                <option value="Regional Hospital">Regional Hospital</option>
                                <option value="District Hospital">District Hospital</option>
                                <option value="Polyclinic">Polyclinic</option>
                                <option value="Health Centre">Health Centre</option>
                                <option value="CHPS">CHPS</option>
                                <option value="Clinic">Clinic</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="nhiaAccreditation" class="block text-sm font-medium text-gray-700 mb-1">NHIA Accreditation Number <span class="text-xs text-gray-500">(Numbers only)</span></label>
                        <div class="input-icon-container">
                            <i class="fas fa-certificate input-icon"></i>
                            <input type="text" id="nhiaAccreditation" name="nhiaAccreditation" class="form-input input-with-icon" placeholder="Enter NHIA accreditation number (numbers only)" pattern="[0-9]*" inputmode="numeric">
                            <div class="error-text" id="nhiaAccreditationError"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Location Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Location Information</h3>
                    
                    <div class="form-grid">
                        <div class="mb-4">
                            <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                            <select id="region" name="region" class="form-input" required>
                                <option value="">Select region</option>
                                <option value="Greater Accra">Greater Accra</option>
                                <option value="Ashanti">Ashanti</option>
                                <option value="Western">Western</option>
                                <option value="Central">Central</option>
                                <option value="Northern">Northern</option>
                                <option value="Eastern">Eastern</option>
                                <option value="Volta">Volta</option>
                                <option value="Upper East">Upper East</option>
                                <option value="Upper West">Upper West</option>
                                <option value="Brong Ahafo">Brong Ahafo</option>
                                <option value="Western North">Western North</option>
                                <option value="Ahafo">Ahafo</option>
                                <option value="Bono East">Bono East</option>
                                <option value="Oti">Oti</option>
                                <option value="North East">North East</option>
                                <option value="Savannah">Savannah</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="district" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                            <div class="input-icon-container">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <input type="text" id="district" name="district" class="form-input input-with-icon" placeholder="Enter district" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="mb-4">
                            <label for="townCity" class="block text-sm font-medium text-gray-700 mb-1">Town/City</label>
                            <div class="input-icon-container">
                                <i class="fas fa-city input-icon"></i>
                                <input type="text" id="townCity" name="townCity" class="form-input input-with-icon" placeholder="Enter town/city" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="postalAddress" class="block text-sm font-medium text-gray-700 mb-1">Postal Address</label>
                            <div class="input-icon-container">
                                <i class="fas fa-mail-bulk input-icon"></i>
                                <input type="text" id="postalAddress" name="postalAddress" class="form-input input-with-icon" placeholder="Enter postal address" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Primary Contact Information</h3>
                    
                    <div class="mb-4">
                        <label for="contactPerson" class="block text-sm font-medium text-gray-700 mb-1">Contact Person Name</label>
                        <div class="input-icon-container">
                            <i class="fas fa-user-tie input-icon"></i>
                            <input type="text" id="contactPerson" name="contactPerson" class="form-input input-with-icon" placeholder="Enter contact person name" required>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="mb-4">
                            <label for="contactEmail" class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                            <div class="input-icon-container">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" id="contactEmail" name="contactEmail" class="form-input input-with-icon" placeholder="Enter contact email" required>
                                <div class="error-text" id="contactEmailError"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="contactPhone" class="block text-sm font-medium text-gray-700 mb-1">Contact Phone <span class="text-xs text-gray-500">(Numbers only)</span></label>
                            <div class="input-icon-container">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="tel" id="contactPhone" name="contactPhone" class="form-input input-with-icon" placeholder="Enter contact phone (numbers only)" pattern="[0-9]*" inputmode="numeric" required>
                                <div class="error-text" id="contactPhoneError"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Account Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Hospital Administrator Account</h3>
                    
                    <div class="form-grid">
                        <div class="mb-4">
                            <label for="adminUsername" class="block text-sm font-medium text-gray-700 mb-1">Admin Username</label>
                            <div class="input-icon-container">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="adminUsername" name="adminUsername" class="form-input input-with-icon" placeholder="Choose admin username" required>
                                <div class="error-text" id="adminUsernameError"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="adminPassword" class="block text-sm font-medium text-gray-700 mb-1">Admin Password</label>
                            <div class="input-icon-container">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="adminPassword" name="adminPassword" class="form-input input-with-icon" placeholder="Create admin password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="adminFullName" class="block text-sm font-medium text-gray-700 mb-1">Admin Full Name</label>
                        <div class="input-icon-container">
                            <i class="fas fa-user-cog input-icon"></i>
                            <input type="text" id="adminFullName" name="adminFullName" class="form-input input-with-icon" placeholder="Enter admin full name" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" required>
                        <span class="ml-2 text-sm text-gray-700">I agree to the <a href="#" class="text-blue-600 hover:underline">Terms and Conditions</a> and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a></span>
                    </label>
                </div>
                
                <div class="flex justify-between items-center">
                    <button type="submit" class="btn-primary w-full">Register</button>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">Already have an account? <a href="index.php" class="text-blue-600 hover:underline">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registrationForm = document.getElementById('registrationForm');
            const messageContainer = document.getElementById('messageContainer');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            
            // Auto-generate hospital code when hospital name changes
            const hospitalNameInput = document.getElementById('hospitalName');
            const hospitalCodeInput = document.getElementById('hospitalCode');
            const hospitalIdInput = document.getElementById('hospitalId');
            const hospitalIdCount = document.getElementById('hospitalIdCount');
            const nhiaAccreditationInput = document.getElementById('nhiaAccreditation');
            const contactPhoneInput = document.getElementById('contactPhone');
            
            // Generate hospital code and ID based on hospital name
            hospitalNameInput.addEventListener('input', function() {
                const name = this.value.trim();
                if (name) {
                    // Create code from first letters of words + random number
                    const words = name.split(' ');
                    const initials = words.map(word => word.charAt(0).toUpperCase()).join('');
                    const randomNum = Math.floor(Math.random() * 9000) + 1000; // 4-digit random number
                    const code = initials.substring(0, 4) + randomNum;
                    hospitalCodeInput.value = code;
                    
                    // Generate Hospital ID (HSP + timestamp + random)
                    const timestamp = Date.now().toString().slice(-6); // Last 6 digits of timestamp
                    const randomId = Math.floor(Math.random() * 900) + 100; // 3-digit random number
                    const hospitalId = 'HSP' + timestamp + randomId;
                    hospitalIdInput.value = hospitalId;
                } else {
                    hospitalCodeInput.value = '';
                    hospitalIdInput.value = '';
                }
            });
            
            // Hospital ID is now auto-generated, no need for character count
            
            // Restrict NHIA accreditation to numbers only
            nhiaAccreditationInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                validateField(this, 'nhiaAccreditationError', 'NHIA accreditation number must contain only numbers');
            });
            
            // Restrict contact phone to numbers only
            contactPhoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                validateField(this, 'contactPhoneError', 'Phone number must contain only numbers');
            });
            
            // Validation functions
            function validateField(field, errorElementId, errorMessage) {
                const errorElement = document.getElementById(errorElementId);
                const value = field.value.trim();
                
                // Clear previous validation state
                field.classList.remove('invalid', 'valid');
                errorElement.style.display = 'none';
                
                // Check if field is required and empty
                if (field.hasAttribute('required') && !value) {
                    field.classList.add('invalid');
                    errorElement.textContent = `${field.previousElementSibling.textContent.replace(/\s*\(.*?\)/g, '')} is required`;
                    errorElement.style.display = 'block';
                    return false;
                }
                
                // Specific field validations
                if (field.type === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        field.classList.add('invalid');
                        errorElement.textContent = 'Please enter a valid email address';
                        errorElement.style.display = 'block';
                        return false;
                    }
                }
                
                // Hospital ID is auto-generated, no validation needed
                
                if (field.id === 'contactPhone' && value) {
                    if (value.length < 10) {
                        field.classList.add('invalid');
                        errorElement.textContent = 'Phone number must be at least 10 digits';
                        errorElement.style.display = 'block';
                        return false;
                    }
                }
                
                if (field.id === 'nhiaAccreditation' && value) {
                    if (value.length < 6) {
                        field.classList.add('invalid');
                        errorElement.textContent = 'NHIA accreditation number must be at least 6 digits';
                        errorElement.style.display = 'block';
                        return false;
                    }
                }
                
                if (field.id === 'adminUsername' && value) {
                    if (value.length < 4) {
                        field.classList.add('invalid');
                        errorElement.textContent = 'Username must be at least 4 characters';
                        errorElement.style.display = 'block';
                        return false;
                    }
                    const usernameRegex = /^[a-zA-Z0-9_]+$/;
                    if (!usernameRegex.test(value)) {
                        field.classList.add('invalid');
                        errorElement.textContent = 'Username can only contain letters, numbers, and underscores';
                        errorElement.style.display = 'block';
                        return false;
                    }
                }
                
                // If we get here, field is valid
                if (value) {
                    field.classList.add('valid');
                }
                return true;
            }
            
            // Add validation to all form inputs
            const formInputs = registrationForm.querySelectorAll('input, select');
            formInputs.forEach(input => {
                if (input.type !== 'checkbox') {
                    input.addEventListener('blur', function() {
                        const errorElementId = this.id + 'Error';
                        if (document.getElementById(errorElementId)) {
                            validateField(this, errorElementId);
                        }
                    });
                }
            });
            
            // Function to show a message and scroll to it
            function showMessage(isSuccess, message = '') {
                // Hide both messages first
                successMessage.classList.add('hidden');
                errorMessage.classList.add('hidden');
                
                // Show the message container
                messageContainer.classList.remove('hidden');
                
                if (isSuccess) {
                    // Show success message
                    successMessage.classList.remove('hidden');
                    successMessage.classList.add('message-animation');
                } else {
                    // Show error message with the provided text
                    errorText.textContent = message;
                    errorMessage.classList.remove('hidden');
                    errorMessage.classList.add('message-animation');
                }
                
                // Scroll to the message container with a slight delay to ensure DOM updates
                setTimeout(() => {
                    messageContainer.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }
            
            registrationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Hide any existing messages
                messageContainer.classList.add('hidden');
                successMessage.classList.add('hidden');
                errorMessage.classList.add('hidden');
                
                // Validate all fields before submission
                let isFormValid = true;
                const requiredFields = [
                    { id: 'hospitalName', errorId: 'hospitalNameError' },
                    { id: 'hospitalType', errorId: null },
                    { id: 'hospitalCategory', errorId: null },
                    { id: 'region', errorId: null },
                    { id: 'district', errorId: null },
                    { id: 'townCity', errorId: null },
                    { id: 'postalAddress', errorId: null },
                    { id: 'contactPerson', errorId: null },
                    { id: 'contactEmail', errorId: 'contactEmailError' },
                    { id: 'contactPhone', errorId: 'contactPhoneError' },
                    { id: 'adminUsername', errorId: 'adminUsernameError' },
                    { id: 'adminPassword', errorId: null },
                    { id: 'adminFullName', errorId: null }
                ];
                
                // Validate each required field
                requiredFields.forEach(field => {
                    const element = document.getElementById(field.id);
                    if (field.errorId) {
                        if (!validateField(element, field.errorId)) {
                            isFormValid = false;
                        }
                    } else {
                        // Basic required field check for fields without specific error elements
                        if (!element.value.trim()) {
                            element.classList.add('invalid');
                            isFormValid = false;
                        } else {
                            element.classList.remove('invalid');
                            element.classList.add('valid');
                        }
                    }
                });
                
                // Check if NHIA accreditation has value and validate
                const nhiaAccreditation = document.getElementById('nhiaAccreditation');
                if (nhiaAccreditation.value.trim()) {
                    if (!validateField(nhiaAccreditation, 'nhiaAccreditationError')) {
                        isFormValid = false;
                    }
                }
                
                // Check terms and conditions
                const termsCheckbox = registrationForm.querySelector('input[type="checkbox"]');
                if (!termsCheckbox.checked) {
                    showMessage(false, 'Please accept the Terms and Conditions to continue.');
                    return;
                }
                
                if (!isFormValid) {
                    showMessage(false, 'Please fix the validation errors before submitting.');
                    return;
                }
                
                // Get form values
                const hospitalName = document.getElementById('hospitalName').value;
                const hospitalCode = document.getElementById('hospitalCode').value;
                const hospitalId = document.getElementById('hospitalId').value;
                const hospitalType = document.getElementById('hospitalType').value;
                const hospitalCategory = document.getElementById('hospitalCategory').value;
                const nhiaAccreditationValue = document.getElementById('nhiaAccreditation').value;
                const region = document.getElementById('region').value;
                const district = document.getElementById('district').value;
                const townCity = document.getElementById('townCity').value;
                const postalAddress = document.getElementById('postalAddress').value;
                const contactPerson = document.getElementById('contactPerson').value;
                const contactEmail = document.getElementById('contactEmail').value;
                const contactPhone = document.getElementById('contactPhone').value;
                const adminUsername = document.getElementById('adminUsername').value;
                const adminPassword = document.getElementById('adminPassword').value;
                const adminFullName = document.getElementById('adminFullName').value;
                
                // Create request data for hospital registration
                const data = {
                    hospital_name: hospitalName,
                    hospital_code: hospitalCode,
                    hospital_id: hospitalId,
                    hospital_type: hospitalType,
                    hospital_category: hospitalCategory,
                    nhia_accreditation_number: nhiaAccreditationValue,
                    region: region,
                    district: district,
                    town_city: townCity,
                    postal_address: postalAddress,
                    primary_contact_person: contactPerson,
                    primary_contact_email: contactEmail,
                    primary_contact_phone: contactPhone,
                    admin_username: adminUsername,
                    admin_password: adminPassword,
                    admin_full_name: adminFullName
                };
                
                // Show loading state
                const submitBtn = registrationForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
                
                // Send registration request to hospital registration API
                fetch('api/hospital-register.php', {
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
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    
                    if (result.status === 'success') {
                        // Show success message and scroll to it
                        showMessage(true);
                        // Reset form
                        registrationForm.reset();
                    } else {
                        // Show error message and scroll to it
                        showMessage(false, result.message || 'Registration failed. Try again later or reach support');
                    }
                })
                .catch(error => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    
                    console.error('Error:', error);
                    showMessage(false, 'An error occurred. Please try again.');
                });
            });
        });
    </script>
</body>
</html>