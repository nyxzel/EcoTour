<?php
/*
 * destinations.php — EcoTour+ Destinations
 * Fully DB-connected from eco_sites table.
 * Search is in the navbar. No emojis — SVG icons throughout.
 */

session_start();
require_once 'database.php';

$is_logged_in = !empty($_SESSION['user_id']);
$current_page = 'destinations';
$show_search  = true;   // tells navbar.php to render the search bar

// ── Pull all active sites from DB ────────────────────────────────────────────
$sites_stmt = $pdo->query("
    SELECT
        es.site_id,
        es.name,
        es.type,
        es.location,
        es.description,
        es.difficulty,
        es.max_visitors,
        es.entrance_fee,
        es.status,
        es.image_path,
        /* booked slots today (pending + confirmed) */
        COALESCE((
            SELECT SUM(b.total_members)
            FROM   bookings b
            JOIN   schedules s ON s.schedule_id = b.schedule_id
            WHERE  b.site_id = es.site_id
              AND  b.status  IN ('pending','confirmed')
              AND  s.available_date = CURDATE()
        ), 0) AS booked_today,
        /* next available date */
        (
            SELECT MIN(s2.available_date)
            FROM   schedules s2
            WHERE  s2.site_id = es.site_id
              AND  s2.available_date >= CURDATE()
        ) AS next_date
    FROM eco_sites es
    WHERE es.status = 'active'
    ORDER BY es.name ASC
");
$sites = $sites_stmt->fetchAll();

// ── Build unique type list for filter tabs ────────────────────────────────────
$types_raw = array_unique(array_column($sites, 'type'));
sort($types_raw);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations — EcoTour+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/destinations.css">
    <link rel="stylesheet" href="/css/booking.css">
</head>

<body>

    <?php
    $current_page = 'destinations';
    $show_search = true;

    include 'navbar.php';
    ?>

    <!-- ══ PAGE HERO ══ -->
    <section class="page-hero">

        <!-- Decorative rings -->
        <div class="hero-ring hero-ring-1"></div>
        <div class="hero-ring hero-ring-2"></div>
        <div class="hero-ring hero-ring-3"></div>

        <div class="page-hero-inner">

            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="ecotour.php">Home</a>
                <span class="breadcrumb-sep">›</span>
                <span>Destinations</span>
            </nav>

            <p class="page-eyebrow">Davao del Norte, Philippines</p>

            <h1 class="page-hero-title">
                Explore <em>Nature's</em><br>
                Hidden Sanctuaries
            </h1>

            <p class="page-hero-desc">
                <?= count($sites) ?> curated eco-destinations — from mist-draped peaks and thundering waterfalls
                to coastal mangroves and ancient wildlife sanctuaries.
                Book responsibly, tread lightly.
            </p>

            <div class="hero-chips">
                <span class="hero-chip">
                    <strong><?= count($sites) ?></strong> Destinations
                </span>

                <span class="hero-chip">
                    <strong>100%</strong> Capacity-Managed
                </span>

                <span class="hero-chip">
                    Sustainable Eco-Tourism
                </span>

                <span class="hero-chip">
                    Davao del Norte, PH
                </span>
            </div>

        </div>
    </section>

    <!-- ══ FILTERS BAR ══ -->
    <div class="filters-bar" id="filtersBar">
        <div class="filters-inner">
            <div class="filter-tabs" id="filterTabs" role="tablist">
                <button class="filter-tab active" data-filter="all" role="tab" aria-selected="true">
                    All <span class="tab-count"><?= count($sites) ?></span>
                </button>
                <?php
                // Count per type
                $type_counts = [];
                foreach ($sites as $s) {
                    $t = $s['type'];
                    $type_counts[$t] = ($type_counts[$t] ?? 0) + 1;
                }
                foreach ($type_counts as $type => $count):
                    $slug = strtolower(preg_replace('/[^a-z0-9]/i', '-', $type));
                ?>
                    <button class="filter-tab" data-filter="<?= htmlspecialchars($slug) ?>" role="tab" aria-selected="false">
                        <?= htmlspecialchars($type) ?> <span class="tab-count"><?= $count ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="filters-right">
                <div class="sort-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 6h18M7 12h10M11 18h2" />
                    </svg>
                    <select class="sort-select" id="sortSelect" aria-label="Sort destinations">
                        <option value="default">Default</option>
                        <option value="name">Name A–Z</option>
                        <option value="fee-low">Fee: Low–High</option>
                        <option value="fee-high">Fee: High–Low</option>
                        <option value="capacity">Most Available</option>
                    </select>
                </div>
                <span class="results-count"><strong id="visibleCount"><?= count($sites) ?></strong> results</span>
            </div>
        </div>
    </div>

    <!-- ══ DESTINATIONS GRID ══ -->
    <section class="destinations-section">
        <div class="destinations-inner">
            <div class="destinations-grid" id="destGrid">

                <?php foreach ($sites as $s):
                    $cap_pct   = $s['max_visitors'] > 0 ? round(($s['booked_today'] / $s['max_visitors']) * 100) : 0;
                    $cap_cls   = $cap_pct >= 80 ? 'cap-high' : ($cap_pct >= 50 ? 'cap-mid' : 'cap-low');
                    $type_slug = strtolower(preg_replace('/[^a-z0-9]/i', '-', $s['type']));
                    $fee_str   = $s['entrance_fee'] == 0 ? 'Free' : '₱' . number_format($s['entrance_fee'], 0);
                    $is_free   = $s['entrance_fee'] == 0;

                    // Difficulty badge class
                    $diff_cls = match (strtolower($s['difficulty'] ?? '')) {
                        'easy'        => 'diff-easy',
                        'moderate'    => 'diff-moderate',
                        'challenging' => 'diff-hard',
                        default       => 'diff-easy',
                    };

                    // Book button — redirect to auth if not logged in
                    $book_attr = "data-site-id=\"{$s['site_id']}\" data-site-name=\"" . htmlspecialchars($s['name'], ENT_QUOTES) . "\" data-site-type=\"" . htmlspecialchars($s['type'], ENT_QUOTES) . "\" data-fee=\"{$s['entrance_fee']}\" data-max=\"{$s['max_visitors']}\"";

                    // Image or gradient fallback
                    $has_image  = !empty($s['image_path']);
                ?>
                    <div class="dest-card"
                        data-cat="<?= htmlspecialchars($type_slug) ?>"
                        data-name="<?= htmlspecialchars($s['name']) ?>"
                        data-fee="<?= (int)$s['entrance_fee'] ?>">

                        <!-- Card image -->
                        <div class="dest-card-img">
                            <?php if ($has_image): ?>
                                <img src="<?= htmlspecialchars($s['image_path']) ?>" alt="<?= htmlspecialchars($s['name']) ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div class="dest-img-fallback <?= htmlspecialchars($type_slug) ?>" style="display:none;">
                                    <?php echo get_type_icon($s['type']); ?>
                                </div>
                            <?php else: ?>
                                <div class="dest-img-fallback <?= htmlspecialchars($type_slug) ?>">
                                    <?php echo get_type_icon($s['type']); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Badges over image -->
                            <div class="dest-img-badges">
                                <span class="dest-type-tag"><?= htmlspecialchars($s['type']) ?></span>
                                <span class="dest-diff-tag <?= $diff_cls ?>"><?= htmlspecialchars($s['difficulty'] ?? 'Easy') ?></span>
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="dest-card-body">
                            <div class="dest-location">
                                <svg viewBox="0 0 24 24">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?= htmlspecialchars($s['location']) ?>
                            </div>
                            <h3 class="dest-name"><?= htmlspecialchars($s['name']) ?></h3>
                            <p class="dest-desc"><?= htmlspecialchars($s['description']) ?></p>

                            <div class="dest-meta-row">
                                <span class="dest-meta-item">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                                    </svg>
                                    <?= number_format($s['max_visitors']) ?> max/day
                                </span>
                                <?php if ($s['next_date']): ?>
                                    <span class="dest-meta-item">
                                        <svg viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        <?= date('M j', strtotime($s['next_date'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Capacity bar -->
                            <div class="dest-cap">
                                <div class="dest-cap-header">
                                    <span>Today's Capacity</span>
                                    <span><?= (int)$s['booked_today'] ?> / <?= number_format($s['max_visitors']) ?></span>
                                </div>
                                <div class="dest-cap-track">
                                    <div class="dest-cap-fill <?= $cap_cls ?>" style="width:0" data-width="<?= $cap_pct ?>%"></div>
                                </div>
                            </div>

                            <div class="dest-card-footer">
                                <div class="dest-fee">
                                    <span class="dest-fee-label">Entry Fee</span>
                                    <span class="dest-fee-amount <?= $is_free ? 'is-free' : '' ?>"><?= $fee_str ?></span>
                                </div>
                                <button class="btn-book-dest" <?= $book_attr ?>
                                    onclick="handleBookClick(this)">
                                    Book Visit
                                    <svg viewBox="0 0 24 24">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div><!-- /grid -->

            <div class="no-results" id="noResults" hidden>
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                    <path d="M8 11h6M11 8v6" />
                </svg>
                <h3>No destinations found</h3>
                <p>Try a different search term or category filter.</p>
                <button onclick="clearFilters()" class="btn-clear-filter">Clear Filters</button>
            </div>
        </div>
    </section>

    <!-- ══ CTA BANNER ══ -->
    <div class="cta-strip">
        <div class="cta-strip-inner">
            <div class="cta-strip-text">
                <h2>Every Booking Protects These Lands</h2>
                <p>EcoTour+ enforces visitor limits, funds site maintenance, and partners with local communities — keeping these destinations pristine for generations.</p>
            </div>
            <div class="cta-strip-actions">
                <?php if (!$is_logged_in): ?>
                    <a href="auth.php?tab=signup" class="btn-cta-primary">
                        Create Free Account
                        <svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="#destGrid" class="btn-cta-primary">
                        Browse Destinations
                        <svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php endif; ?>
                <a href="about.php" class="btn-cta-ghost">Learn About SDG 15</a>
            </div>
        </div>
    </div>

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

    <!-- ══ BOOKING MODAL ══ -->
    <?php include 'booking-modal.php'; ?>

    <script>
        // ── Filter + sort logic ──────────────────────────────────────────────────────
        const filterTabs = document.querySelectorAll('.filter-tab');
        const allCards = Array.from(document.querySelectorAll('.dest-card'));
        const grid = document.getElementById('destGrid');
        const noResults = document.getElementById('noResults');
        const sortSel = document.getElementById('sortSelect');
        const visCount = document.getElementById('visibleCount');
        const searchInput = document.getElementById('navSearchInput');

        let activeFilter = 'all';
        let activeSearch = '';

        function applyFilters() {
            const sort = sortSel.value;
            let cards = [...allCards];

            // Sort
            if (sort === 'name') cards.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
            else if (sort === 'fee-low') cards.sort((a, b) => +a.dataset.fee - +b.dataset.fee);
            else if (sort === 'fee-high') cards.sort((a, b) => +b.dataset.fee - +a.dataset.fee);
            else if (sort === 'capacity') {
                cards.sort((a, b) => {
                    const aw = parseFloat(a.querySelector('.dest-cap-fill')?.dataset.width || '100');
                    const bw = parseFloat(b.querySelector('.dest-cap-fill')?.dataset.width || '100');
                    return aw - bw;
                });
            } else {
                cards.sort((a, b) => allCards.indexOf(a) - allCards.indexOf(b));
            }

            cards.forEach(c => grid.appendChild(c));

            let shown = 0;
            cards.forEach(card => {
                const catOk = activeFilter === 'all' || card.dataset.cat === activeFilter;
                const nameOk = card.dataset.name.toLowerCase().includes(activeSearch.toLowerCase());
                const visible = catOk && nameOk;
                card.style.display = visible ? '' : 'none';
                if (visible) shown++;
            });

            visCount.textContent = shown;
            noResults.hidden = shown > 0;
        }

        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                filterTabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                tab.classList.add('active');
                tab.setAttribute('aria-selected', 'true');
                activeFilter = tab.dataset.filter;
                applyFilters();
            });
        });

        sortSel.addEventListener('change', applyFilters);

        if (searchInput) {
            searchInput.addEventListener('input', e => {
                activeSearch = e.target.value;
                applyFilters();
            });
            // ⌘K / Ctrl+K focus shortcut
            document.addEventListener('keydown', e => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        }

        function clearFilters() {
            activeFilter = 'all';
            activeSearch = '';
            if (searchInput) searchInput.value = '';
            filterTabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            filterTabs[0].classList.add('active');
            filterTabs[0].setAttribute('aria-selected', 'true');
            sortSel.value = 'default';
            applyFilters();
        }

        // ── Capacity bars animation ──────────────────────────────────────────────────
        window.addEventListener('load', () => {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const bar = entry.target;
                        bar.style.width = bar.dataset.width;
                        observer.unobserve(bar);
                    }
                });
            }, {
                threshold: .1
            });

            document.querySelectorAll('.dest-cap-fill').forEach(bar => observer.observe(bar));
        });

        // ── Book button click ────────────────────────────────────────────────────────
        function handleBookClick(btn) {
            const loggedIn = <?= $is_logged_in ? 'true' : 'false' ?>;
            if (!loggedIn) {
                window.location.href = 'auth.php';
                return;
            }
            const name = btn.dataset.siteName;
            const type = btn.dataset.siteType;
            const fee = parseFloat(btn.dataset.fee);
            const siteId = parseInt(btn.dataset.siteId);
            const maxSlot = parseInt(btn.dataset.max);
            openBooking(name, type, fee, siteId, maxSlot);
        }
    </script>

</body>

</html>

<?php
// ── Helper: SVG icon per site type ──────────────────────────────────────────
function get_type_icon(string $type): string
{
    $t = strtolower($type);
    if (str_contains($t, 'urban') || str_contains($t, 'park'))   return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V12m0 0C9 12 6 9.5 6 6.5S9 2 12 2s6 2.5 6 4.5S15 12 12 12zM6 22h12"/></svg>';
    if (str_contains($t, 'falls') || str_contains($t, 'river'))  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v10M8 6s1 2 4 2 4-2 4-2M5 22c0-4 3-7 7-8s7-4 7-8"/></svg>';
    if (str_contains($t, 'wetland') || str_contains($t, 'mangrove')) return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22v-8M8 14s1-2 4-2 4 2 4 2M6 10s2-4 6-4 6 4 6 4"/></svg>';
    if (str_contains($t, 'marine') || str_contains($t, 'clam'))  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-5 8-12a8 8 0 00-16 0c0 7 8 12 8 12z"/><path d="M12 10a2 2 0 100 4 2 2 0 000-4z"/></svg>';
    if (str_contains($t, 'wildlife') || str_contains($t, 'bat')) return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12c0-1 1-2 3-2h2c0-3 2-5 5-5s5 2 5 5h2c2 0 3 1 3 2v2c0 1-1 2-2 2h-1c0 1-1 2-2 2h-1a3 3 0 01-6 0H9c-1 0-2-1-2-2H6c-1 0-2-1-2-2v-2z"/></svg>';
    if (str_contains($t, 'cave'))  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20 L6 10 Q9 4 12 4 Q15 4 18 10 L21 20 Z"/><path d="M9 20 L10 14 Q11 12 12 12 Q13 12 14 14 L15 20"/></svg>';
    // default: mountain
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20l7-12 4 6 3-4 4 10H3z"/></svg>';
}
?>