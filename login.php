<?php
// ...existing code...

require_once 'auth.php';

// If already logged in, go straight to main page
if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $authenticated = false;
        
        // Try database authentication first
        try {
            require_once 'db.php';
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Update last login
                try {
                    $stmt = $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id = :id');
                    $stmt->execute([':id' => $admin['id']]);
                } catch (PDOException $e) {
                    // Ignore update error
                }
                $authenticated = true;
            }
        } catch (PDOException $e) {
            // Admin table doesn't exist or query failed, use fallback
        }
        
   ////////////// THIS IS THE ADMIN ACCOUNT PLS AVOID TO DELETE THIS PART OF CODE.....DEL<3/////////
        if (!$authenticated) {
          $validUsername = 'DARLU';
          $validPassword = 'DARLU2026';
  ////////////////////////////////////////////////////////////////////////////////////////////////

            if ($username === $validUsername && $password === $validPassword) {
                $authenticated = true;
            }
        }
        
        if ($authenticated) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            
            // Log login activity
            require_once 'db.php';
            require_once 'activity_logger.php';
            logActivity('login', "User '{$username}' logged in successfully");
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - DARLU HRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/employee.css">
    <link rel="icon" type="image/png" sizes="50x50" href="/DarLa/BG-DAR.png">
    <link rel="shortcut icon" href="/DarLa/BG-DAR.png" type="image/x-icon">
    <meta name="theme-color" content="#0b6b3d">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>


<body class="login-page">
<div class="login-card-wrapper">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden" style="background-color: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px);">
        <div class="card-body p-0">
            <div class="p-4 p-sm-4 p-md-4">
                <div class="text-center mb-3">
                    <img src="BG-DAR.png"
                         alt="DAR La Union Logo"
                         class="img-fluid mb-2 login-logo">
                    <div class="small text-muted fw-semibold text-uppercase tracking-wide">
                        Department of Agrarian Reform<br>La Union HRIS
                    </div>
                </div>
           
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3 text-center"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off" class="login-form">
                    <div class="mb-2">
                        <label for="username" class="form-label small mb-1">Username</label>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            id="username"
                            name="username"
                            placeholder="Enter admin username"
                            required
                            autofocus
                        >
                    </div>
                    <div class="mb-2">
                        <label for="password" class="form-label small mb-1">Password</label>
                        <div class="position-relative">
                            <input
                                type="password"
                                class="form-control form-control-sm"
                                id="password"
                                name="password"
                                placeholder="Enter password"
                                required
                                style="padding-right: 40px;"
                            >
                            <button
                                type="button"
                                class="position-absolute"
                                id="togglePassword"
                                style="
                                    right: 12px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    border: none;
                                    background: transparent;
                                    padding: 4px 8px;
                                    cursor: pointer;
                                    z-index: 10;
                                    color: #6c757d;
                                    font-size: 16px;
                                    line-height: 1;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                "
                                onmouseover="this.style.color='#212529'"
                                onmouseout="this.style.color='#6c757d'"
                            >
                                <i class="fas fa-eye" id="eyeIcon" style="display: inline-block;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check small">
                            <input class="form-check-input" type="checkbox" value="" id="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                      
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm py-2">
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-footer text-center small text-muted bg-white border-0 py-2">
            DARLU HRIS @2026
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
#togglePassword {
    right: 12px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    border: none !important;
    background: transparent !important;
    padding: 4px 8px !important;
    cursor: pointer !important;
    z-index: 1000 !important;
    color: #6c757d !important;
    font-size: 16px !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    opacity: 1 !important;
    visibility: visible !important;
}

#togglePassword:hover {
    color: #212529 !important;
}

#togglePassword i {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}
</style>
<script>
// Toggle password visibility with eye icon
(function () {
    var toggleBtn = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eyeIcon');
    
    if (!toggleBtn || !passwordInput || !eyeIcon) {
        console.error('Password toggle elements not found');
        return;
    }
    
    // Ensure icon is visible on load
    eyeIcon.style.display = 'inline-block';
    eyeIcon.style.visibility = 'visible';
    eyeIcon.style.opacity = '1';
    
    toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });
})();
</script>
</body>
</html>