<?php
/*
 * navbar.php — EcoTour+ Shared Navigation
 * Include at the top of every public-facing page (after session_start).
 * Requires: $is_logged_in (bool), $user_name (string), $user_role (string)
 * Optional: $current_page — set to 'home','destinations','about' to highlight active link
 * Optional: $show_search — set to true on destinations page to show search in navbar
 */

$_nav_role     = strtolower($_SESSION['role'] ?? '');
$_is_logged_in = !empty($_SESSION['user_id']);
$_user_name    = htmlspecialchars($_SESSION['user_name'] ?? '');
$_current_page = $current_page ?? '';
$_show_search  = $show_search  ?? false;
?>
<header class="navbar" id="navbar">
    <div class="nav-inner">

        <!-- BRAND -->
        <a href="ecotour.php" class="nav-brand">
            <div class="nav-logo-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 2C8 2 4 5.5 4 10c0 3.5 2.5 7 8 10 5.5-3 8-6.5 8-10 0-4.5-4-8-8-8z" fill="currentColor" opacity=".9" />
                    <path d="M12 4.5c1.2 1.8 2 4 2 5.5 0 2-1 3.5-2 4.5-1-1-2-2.5-2-4.5 0-1.5.8-3.7 2-5.5z" fill="white" opacity=".6" />
                </svg>
            </div>
            <span>EcoTour<span class="brand-plus">+</span></span>
        </a>

        <!-- CENTER NAV -->
        <nav class="nav-links" role="navigation">
            <a href="ecotour.php" class="nav-link <?= $_current_page === 'home'         ? 'active' : '' ?>">Home</a>
            <a href="destinations.php" class="nav-link <?= $_current_page === 'destinations'  ? 'active' : '' ?>">Destinations</a>
            <a href="about.php" class="nav-link <?= $_current_page === 'about'         ? 'active' : '' ?>">About</a>
        </nav>

        <!-- SEARCH IN NAVBAR (destinations page) -->
        <?php if ($_show_search): ?>
            <div class="nav-search-wrap">
                <svg class="nav-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" id="navSearchInput" class="nav-search-input" placeholder="Search destinations…" autocomplete="off">
                <kbd class="nav-search-kbd">⌘K</kbd>
            </div>
        <?php endif; ?>

        <!-- RIGHT ACTIONS -->
        <div class="nav-actions">
            <?php if ($_is_logged_in): ?>
                <?php
                $dashboard_link = 'tourist-dashboard.php';
                if ($_nav_role === 'admin')        $dashboard_link = 'admin-dashboard.php';
                if ($_nav_role === 'site_manager') $dashboard_link = 'manager-dashboard.php';
                ?>
                <a href="<?= $dashboard_link ?>" class="nav-user-pill">
                    <span class="nav-user-avatar"><?= mb_strtoupper(mb_substr($_user_name, 0, 1)) ?></span>
                    <span class="nav-user-name"><?= explode(' ', $_user_name)[0] ?></span>
                </a>
                <a href="logout.php" class="nav-btn nav-btn-ghost">Log Out</a>
            <?php else: ?>
                <a href="auth.php" class="nav-btn nav-btn-ghost">Log In</a>
                <a href="auth.php?tab=signup" class="nav-btn nav-btn-solid">Sign Up</a>
            <?php endif; ?>
        </div>

        <!-- HAMBURGER (mobile) -->
        <button class="nav-hamburger" id="navHamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

    </div><!-- /nav-inner -->

    <!-- MOBILE DRAWER -->
    <div class="nav-drawer" id="navDrawer">
        <a href="ecotour.php" class="drawer-link <?= $_current_page === 'home'         ? 'active' : '' ?>">Home</a>
        <a href="destinations.php" class="drawer-link <?= $_current_page === 'destinations'  ? 'active' : '' ?>">Destinations</a>
        <a href="about.php" class="drawer-link <?= $_current_page === 'about'         ? 'active' : '' ?>">About</a>
        <div class="drawer-divider"></div>
        <?php if ($_is_logged_in): ?>
            <a href="tourist-dashboard.php" class="drawer-link">My Dashboard</a>
            <a href="logout.php" class="drawer-link drawer-logout">Log Out</a>
        <?php else: ?>
            <a href="auth.php" class="drawer-link">Log In</a>
            <a href="auth.php?tab=signup" class="drawer-link drawer-signup">Sign Up</a>
        <?php endif; ?>
    </div>
</header>

<script>
    (function() {
        const ham = document.getElementById('navHamburger');
        const drawer = document.getElementById('navDrawer');
        if (ham && drawer) {
            ham.addEventListener('click', function() {
                const open = drawer.classList.toggle('open');
                ham.setAttribute('aria-expanded', open);
                ham.classList.toggle('open', open);
            });
        }
        // Scroll shrink
        const nav = document.getElementById('navbar');
        if (nav) {
            window.addEventListener('scroll', function() {
                nav.classList.toggle('scrolled', window.scrollY > 30);
            }, {
                passive: true
            });
        }
    })();
</script>