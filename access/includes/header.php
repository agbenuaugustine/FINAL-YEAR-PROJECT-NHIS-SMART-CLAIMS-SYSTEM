<?php
/**
 * Common Header
 * 
 * This file contains the common header elements for all dashboard pages
 */

// Make sure user data is available
if (!isset($user)) {
    $user = $_SESSION['user'] ?? [];
}
?>
<!-- Header -->
<header class="app-header">
    <h1 class="app-title">
        <div class="app-logo">
            <i class="fas fa-file-medical"></i>
        </div>
        Smart Claims
    </h1>
    
    <div class="user-menu relative">
        <div class="user-button cursor-pointer" id="userMenuButton">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <span class="hidden md:inline"><?php echo htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'User'); ?></span>
            <i class="fas fa-chevron-down ml-2 text-xs"></i>
        </div>
        <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden">
            <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                <i class="fas fa-user-circle mr-2"></i> Profile
            </a>
            <a href="settings.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                <i class="fas fa-cog mr-2"></i> Settings
            </a>
            <div class="border-t border-gray-200 my-1"></div>
            <a href="/smartclaimsCL/api/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>
</header>

<!-- Navigation -->
<nav class="app-nav">
    <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="patient-registration.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'patient-registration.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-plus"></i>
        <span>Patients</span>
    </a>
    <a href="visits.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'visits.php' ? 'active' : ''; ?>">
        <i class="fas fa-clipboard-list"></i>
        <span>Visits</span>
    </a>
    <a href="vital-signs.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'vital-signs.php' ? 'active' : ''; ?>">
        <i class="fas fa-heartbeat"></i>
        <span>Vitals</span>
    </a>
    <a href="diagnosis.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'diagnosis.php' ? 'active' : ''; ?>">
        <i class="fas fa-stethoscope"></i>
        <span>Diagnosis</span>
    </a>
    <a href="claims.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'claims.php' ? 'active' : ''; ?>">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>Claims</span>
    </a>
    <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
    </a>
</nav>