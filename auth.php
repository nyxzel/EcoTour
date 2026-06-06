<?php
/*
 * auth.php — EcoTour+ Login & Registration
 * Handles both GET (render page) and POST (process login/register).
 */

session_start();
require_once 'database.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: destinations.php');
    exit;
}

$error_login  = '';
$error_signup = '';
$active_tab   = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = trim($_POST['action'] ?? '');

    if ($action === 'login') {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $error_login = 'Please enter your email and password.';
            $active_tab  = 'login';
        } else {
            $stmt = $pdo->prepare("
                SELECT u.user_id, u.name, u.email, u.password, r.role_name
                FROM   users u
                JOIN   roles r ON r.role_id = u.role_id
                WHERE  u.email = :email
                LIMIT  1
            ");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role']      = $user['role_name'];

                $log = $pdo->prepare("INSERT INTO activity_log (user_id, action, time_stamp) VALUES (:uid, :action, NOW())");
                $log->execute([':uid' => $user['user_id'], ':action' => 'User logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')]);

                $role = strtolower($user['role_name']);
                if ($role === 'admin') header('Location: admin-dashboard.php');
                elseif ($role === 'site manager') header('Location: manager-dashboard.php');
                else header('Location: destinations.php');
                exit;
            } else {
                $error_login = 'Incorrect email or password. Please try again.';
                $active_tab  = 'login';
            }
        }
    } elseif ($action === 'register') {
        $active_tab = 'signup';
        $name       = trim($_POST['name']     ?? '');
        $email      = trim($_POST['email']    ?? '');
        $password   = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            $error_signup = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_signup = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error_signup = 'Password must be at least 8 characters.';
        } else {
            $check = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
            $check->execute([':email' => $email]);

            if ($check->fetch()) {
                $error_signup = 'An account with this email already exists.';
            } else {
                $role_stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = 'tourist' LIMIT 1");
                $role_stmt->execute();
                $role = $role_stmt->fetch();

                if (!$role) {
                    $error_signup = 'System configuration error. Please contact support.';
                } else {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $insert = $pdo->prepare("INSERT INTO users (name, email, password, role_id, created_at) VALUES (:name, :email, :password, :role_id, NOW())");
                    $insert->execute([':name' => $name, ':email' => $email, ':password' => $hashed, ':role_id' => $role['role_id']]);

                    $new_id = (int) $pdo->lastInsertId();
                    session_regenerate_id(true);
                    $_SESSION['user_id']    = $new_id;
                    $_SESSION['user_name']  = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['role']       = 'tourist';

                    $log = $pdo->prepare("INSERT INTO activity_log (user_id, action, time_stamp) VALUES (:uid, 'New tourist account registered', NOW())");
                    $log->execute([':uid' => $new_id]);

                    header('Location: destinations.php');
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTour+ | Login & Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <!-- Shared navbar -->
    <link rel="stylesheet" href="/css/navbar.css">
    <!-- Auth page styles -->
    <link rel="stylesheet" href="/css/auth.css">
</head>

<body>

    <!-- ═══════════════════════ NAVBAR ═══════════════════════ -->
    <header class="navbar" id="navbar">
        <a href="ecotour.php" class="nav-brand">
            <div class="nav-logo">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C8 2 4 5.5 4 10c0 3.5 2.5 7 8 10 5.5-3 8-6.5 8-10 0-4.5-4-8-8-8zm0 2.5c1.2 1.8 2 4 2 5.5 0 2-1 3.5-2 4.5-1-1-2-2.5-2-4.5 0-1.5.8-3.7 2-5.5z" />
                </svg>
            </div>
            <span class="nav-brand-name">EcoTour+</span>
        </a>

        <div class="nav-center">
            <ul class="nav-links">
                <li><a href="ecotour.php">Home</a></li>
                <li><a href="destinations.php">Destinations</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
        </div>

        <div class="nav-actions">
            <a href="auth.php" class="btn-login active-nav">Log In</a>
            <a href="auth.php?tab=signup" class="btn-signup">Sign Up</a>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </header>

    <!-- Mobile Nav -->
    <div class="mobile-nav" id="mobileNav">
        <a href="ecotour.php">Home</a>
        <a href="destinations.php">Destinations</a>
        <a href="about.php">About</a>
        <div style="display:flex;gap:.75rem;padding-top:.5rem;">
            <a href="auth.php" class="btn-login" style="flex:1;text-align:center;border-radius:8px;padding:.75rem;border:1px solid rgba(255,255,255,.2);">Log In</a>
            <a href="auth.php?tab=signup" class="btn-signup" style="flex:1;text-align:center;border-radius:8px;padding:.75rem;">Sign Up</a>
        </div>
    </div>

    <!-- ═══════════════════════ AUTH BACKGROUND ═══════════════════════ -->
    <div class="auth-background"></div>

    <!-- ═══════════════════════ MAIN ═══════════════════════ -->
    <main class="auth-container">

        <!-- LEFT PANEL -->
        <section class="auth-left">
            <div class="auth-left-content">
                <span class="mini-label">Sustainable Tourism • Davao del Norte</span>
                <h1>Explore Nature<br>Responsibly</h1>
                <p>Reserve hiking trails, parks, and natural landmarks while supporting sustainable tourism and environmental protection.</p>
                <div class="auth-features">
                    <div class="feature-item">
                        <div class="feature-icon">🌿</div>
                        <div>
                            <h4>Smart Visitor Limits</h4>
                            <span>Real-time capacity management protects every site</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📋</div>
                        <div>
                            <h4>Instant Booking</h4>
                            <span>Solo or group — reserve your slot in minutes</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🌍</div>
                        <div>
                            <h4>SDG 15 Aligned</h4>
                            <span>Every booking contributes to land conservation</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- RIGHT PANEL -->
        <section class="auth-right">
            <div class="auth-card">

                <!-- TAB TOGGLE -->
                <div class="toggle-wrap">
                    <button class="toggle-btn <?= $active_tab === 'login'  ? 'active' : '' ?>" id="loginBtn">Log In</button>
                    <button class="toggle-btn <?= $active_tab === 'signup' ? 'active' : '' ?>" id="signupBtn">Sign Up</button>
                </div>

                <!-- LOGIN FORM -->
                <form class="form-section <?= $active_tab === 'login' ? 'active-form' : '' ?>"
                    id="loginForm" method="POST" action="auth.php" novalidate>
                    <input type="hidden" name="action" value="login">

                    <div class="form-header">
                        <h2>Welcome Back</h2>
                        <p>Log in to continue your eco-tourism journey.</p>
                    </div>

                    <?php if ($error_login): ?>
                        <div class="form-error">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4m0 4h.01" />
                            </svg>
                            <?= htmlspecialchars($error_login) ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter your email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="submit-btn">Log In</button>

                    <p class="manager-note">
                        Site managers and admins are automatically redirected<br>to their dashboards after login.
                    </p>
                </form>

                <!-- SIGNUP FORM -->
                <form class="form-section <?= $active_tab === 'signup' ? 'active-form' : '' ?>"
                    id="signupForm" method="POST" action="auth.php" novalidate>
                    <input type="hidden" name="action" value="register">

                    <div class="form-header">
                        <h2>Create Account</h2>
                        <p>Register as a tourist to start booking eco destinations.</p>
                    </div>

                    <?php if ($error_signup): ?>
                        <div class="form-error">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4m0 4h.01" />
                            </svg>
                            <?= htmlspecialchars($error_signup) ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Enter your full name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter your email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="input-group">
                        <label>Password <span style="font-size:.75rem;color:#aaa;font-weight:400;">(min. 8 characters)</span></label>
                        <input type="password" name="password" placeholder="Create a password" required>
                    </div>

                    <button type="submit" class="submit-btn">Create Tourist Account</button>
                </form>

            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer>

        <div class="footer-top">

            <div class="footer-brand">

                <a href="#" class="nav-brand">

                    <div class="nav-logo">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2L3 20h18L12 2zm0 4.5l4.5 10h-2.8l-1.7-4-1.7 4H7.5L12 6.5z" />
                        </svg>
                    </div>

                    <span class="nav-brand-name">
                        EcoTour+
                    </span>

                </a>

                <p>
                    Sustainable eco-tourism booking and land management platform for Davao del Norte.
                </p>

            </div>

            <div class="footer-col">

                <h5>Navigation</h5>

                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#destinations">Destinations</a></li>
                    <li><a href="#about">About</a></li>
                </ul>

            </div>

            <div class="footer-col">

                <h5>System</h5>

                <ul>
                    <li><a href="#">Log In</a></li>
                    <li><a href="#">Sign Up</a></li>
                    <li><a href="#">Bookings</a></li>
                </ul>

            </div>

            <div class="footer-col">

                <h5>Advocacy</h5>

                <ul>
                    <li><a href="#">SDG 15</a></li>
                    <li><a href="#">Sustainability</a></li>
                    <li><a href="#">Eco Tourism</a></li>
                </ul>

            </div>

        </div>

        <div class="footer-bottom">

            <p>
                © 2026 EcoTour+. All Rights Reserved.
            </p>

            <p>
                Davao del Norte Eco-Tourism Booking System
            </p>

        </div>

    </footer>

    <!-- ═══════════════════════ AUTH CSS OVERRIDE ═══════════════════════ -->
    <style>
        /*
         * Override auth.css to remove its old white navbar styles
         * so the shared navbar.css takes full control.
         * Add this block inside auth.css or keep it here as a patch.
         */

        /* Ensure the auth page body still gets the right top offset */
        body {
            padding-top: 68px;
        }

        /* Make sure .navbar on auth page stays colorful even when not scrolled */
        .navbar {
            background: linear-gradient(100deg, #1a3a24 0%, #2d5a3d 100%);
        }
    </style>

    <!-- ═══════════════════════ JS ═══════════════════════ -->
    <script>
        const loginBtn = document.getElementById('loginBtn');
        const signupBtn = document.getElementById('signupBtn');
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');

        loginBtn.addEventListener('click', () => {
            loginBtn.classList.add('active');
            signupBtn.classList.remove('active');
            loginForm.classList.add('active-form');
            signupForm.classList.remove('active-form');
        });

        signupBtn.addEventListener('click', () => {
            signupBtn.classList.add('active');
            loginBtn.classList.remove('active');
            signupForm.classList.add('active-form');
            loginForm.classList.remove('active-form');
        });

        // Navbar scroll state
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        });

        // Hamburger
        document.getElementById('hamburger').addEventListener('click', () => {
            document.getElementById('mobileNav').classList.toggle('open');
        });

        // Open signup tab if URL has ?tab=signup
        if (new URLSearchParams(window.location.search).get('tab') === 'signup') {
            signupBtn.click();
        }
    </script>

</body>

</html>