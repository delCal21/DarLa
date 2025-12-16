<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';
require_once 'activity_logger.php';

$successMessage = '';
$errorMessage = '';

// Check if admin table exists, if not create it
try {
    $pdo->query("SELECT 1 FROM admins LIMIT 1");
} catch (PDOException $e) {
    // Table doesn't exist, create it
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(150) DEFAULT NULL,
                email VARCHAR(150) DEFAULT NULL,
                last_login TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username)
            )
        ");
        
        // Insert default admin if table was just created
        $defaultPassword = password_hash('DARLU2025', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT IGNORE INTO admins (username, password_hash, full_name, email) 
            VALUES ('DarLa', '$defaultPassword', 'DARLa Administrator', 'admin@darla.gov.ph')
        ");
    } catch (PDOException $e2) {
        $errorMessage = 'Database setup error. Please run create_admin_table.sql manually.';
    }
}

// Get current admin info
$currentUsername = $_SESSION['admin_username'] ?? 'DarLa';
$admin = null;

try {
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username');
    $stmt->execute([':username' => $currentUsername]);
    $admin = $stmt->fetch();
} catch (PDOException $e) {
    // If admin table doesn't exist or query fails, use session data
    $admin = [
        'username' => $currentUsername,
        'full_name' => 'DARLa Administrator',
        'email' => 'admin@darla.gov.ph'
    ];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'New password and confirmation do not match.';
        } elseif (strlen($newPassword) < 6) {
            $errorMessage = 'New password must be at least 6 characters long.';
        } else {
            // Verify current password
            if ($admin && isset($admin['password_hash'])) {
                if (!password_verify($currentPassword, $admin['password_hash'])) {
                    $errorMessage = 'Current password is incorrect.';
                } else {
                    // Update password
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    try {
                        $stmt = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE username = :username');
                    $stmt->execute([
                        ':hash' => $newHash,
                        ':username' => $currentUsername
                    ]);
                    
                    // Log activity
                    logActivity('password_change', "Password changed for user: {$currentUsername}");
                    
                    $successMessage = 'Password changed successfully.';
                        // Refresh admin data
                        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username');
                        $stmt->execute([':username' => $currentUsername]);
                        $admin = $stmt->fetch();
                    } catch (PDOException $e) {
                        $errorMessage = 'Error updating password: ' . htmlspecialchars($e->getMessage());
                    }
                }
            } else {
                // Fallback: check against hardcoded password
                $validPassword = 'DARLU2025';
                if ($currentPassword !== $validPassword) {
                    $errorMessage = 'Current password is incorrect.';
                } else {
                    // If admin table doesn't exist, we can't update password
                    $errorMessage = 'Admin table not found. Please run create_admin_table.sql first.';
                }
            }
        }
    } elseif ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($fullName)) {
            $errorMessage = 'Full name is required.';
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email address.';
        } else {
            try {
                if ($admin && isset($admin['id'])) {
                    $stmt = $pdo->prepare('UPDATE admins SET full_name = :full_name, email = :email WHERE id = :id');
                    $stmt->execute([
                        ':full_name' => $fullName,
                        ':email' => $email ?: null,
                        ':id' => $admin['id']
                    ]);
                    
                    // Log activity
                    logActivity('profile_update', "Profile updated for user: {$currentUsername}");
                    
                    $successMessage = 'Profile updated successfully.';
                    // Refresh admin data
                    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username');
                    $stmt->execute([':username' => $currentUsername]);
                    $admin = $stmt->fetch();
                } else {
                    // If admin table doesn't exist, update session
                    $_SESSION['admin_full_name'] = $fullName;
                    $_SESSION['admin_email'] = $email;
                    $successMessage = 'Profile information saved (session only).';
                }
            } catch (PDOException $e) {
                $errorMessage = 'Error updating profile: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// Get updated admin info
if (!$admin || !isset($admin['id'])) {
    $admin = [
        'username' => $currentUsername,
        'full_name' => $_SESSION['admin_full_name'] ?? 'DARLa Administrator',
        'email' => $_SESSION['admin_email'] ?? 'admin@darla.gov.ph',
        'last_login' => null
    ];
}

require_once 'header.php';
?>

<!-- Breadcrumb -->
<ol class="breadcrumb mb-3">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Admin Profile</li>
</ol>

<!-- Professional Header Card -->
<div class="card border-0 shadow-sm mb-3 professional-header-card compact-header">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="directory-avatar me-2">
                        <div class="avatar-circle compact-avatar">
                            <i class="fas fa-user-cog"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Admin Profile</h1>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-info-circle me-1"></i>
                            Manage your administrator account settings
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <div><?= htmlspecialchars($errorMessage) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($successMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <div><?= htmlspecialchars($successMessage) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-3">
    <!-- Profile Information -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2 text-primary"></i>
                    Profile Information
                </h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            value="<?= htmlspecialchars($admin['username'] ?? $currentUsername) ?>" 
                            disabled
                        >
                        <small class="text-muted">Username cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="full_name" 
                            name="full_name" 
                            value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" 
                            required
                            placeholder="Enter your full name"
                        >
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($admin['email'] ?? '') ?>" 
                            placeholder="Enter your email address"
                        >
                    </div>
                    
                    <?php if (isset($admin['last_login']) && $admin['last_login']): ?>
                        <div class="mb-3">
                            <label class="form-label">Last Login</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                value="<?= htmlspecialchars($admin['last_login']) ?>" 
                                disabled
                            >
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lock me-2 text-primary"></i>
                    Change Password
                </h5>
            </div>
            <div class="card-body">
                <form method="post" id="changePasswordForm">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="current_password" 
                                name="current_password" 
                                required
                                autocomplete="current-password"
                                placeholder="Enter current password"
                            >
                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                <i class="fas fa-eye" id="currentPasswordIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="new_password" 
                                name="new_password" 
                                required
                                autocomplete="new-password"
                                placeholder="Enter new password (min. 6 characters)"
                                minlength="6"
                            >
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <i class="fas fa-eye" id="newPasswordIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Password must be at least 6 characters long</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required
                                autocomplete="new-password"
                                placeholder="Confirm new password"
                                minlength="6"
                            >
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Account Statistics -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-bar me-2 text-primary"></i>
            Account Statistics
        </h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-item-compact">
                    <?php
                    try {
                        $stmt = $pdo->query('SELECT COUNT(*) as total FROM employees');
                        $totalEmployees = $stmt->fetch()['total'];
                    } catch (PDOException $e) {
                        $totalEmployees = 0;
                    }
                    ?>
                    <div class="stat-number-compact"><?= number_format($totalEmployees) ?></div>
                    <div class="stat-label-compact">Total Employees</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item-compact">
                    <?php
                    try {
                        $stmt = $pdo->query('SELECT COUNT(*) as total FROM appointments');
                        $totalAppointments = $stmt->fetch()['total'];
                    } catch (PDOException $e) {
                        $totalAppointments = 0;
                    }
                    ?>
                    <div class="stat-number-compact"><?= number_format($totalAppointments) ?></div>
                    <div class="stat-label-compact">Appointments</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item-compact">
                    <?php
                    $backupDir = __DIR__ . '/backups';
                    $backupCount = is_dir($backupDir) ? count(glob($backupDir . '/backup_*.sql')) : 0;
                    ?>
                    <div class="stat-number-compact"><?= number_format($backupCount) ?></div>
                    <div class="stat-label-compact">Backups</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password visibility toggles
function setupPasswordToggle(buttonId, inputId, iconId) {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (button && input && icon) {
        button.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
}

// Setup all password toggles
setupPasswordToggle('toggleCurrentPassword', 'current_password', 'currentPasswordIcon');
setupPasswordToggle('toggleNewPassword', 'new_password', 'newPasswordIcon');
setupPasswordToggle('toggleConfirmPassword', 'confirm_password', 'confirmPasswordIcon');

// Password confirmation validation
document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirmation do not match!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
    
    return confirm('Are you sure you want to change your password?');
});
</script>

<?php
require_once 'footer.php';
?>
