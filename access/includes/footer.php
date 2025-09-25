<?php
/**
 * Common Footer
 * 
 * This file contains the common footer elements for all dashboard pages
 */
?>
<!-- Mobile Navigation -->
<div class="mobile-nav">
    <a href="dashboard.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="patient-registration.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'patient-registration.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-plus"></i>
        <span>Patients</span>
    </a>
    <a href="visits.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'visits.php' ? 'active' : ''; ?>">
        <i class="fas fa-clipboard-list"></i>
        <span>Visits</span>
    </a>
    <a href="claims.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'claims.php' ? 'active' : ''; ?>">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>Claims</span>
    </a>
    <a href="/smartclaimsCL/api/logout.php" class="mobile-nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>

<script>
    // User dropdown menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userMenuButton && userDropdown) {
            // Toggle dropdown when clicking the user menu button
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        }
    });
</script>