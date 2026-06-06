<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoTour+ | Davao del Norte Eco-Tourism Booking</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>

    <!-- ═══════════════════════ NAVBAR ═══════════════════════ -->
    <?php
    $current_page = 'home';
    $show_search = false;

    include 'navbar.php';
    ?>

    <!-- Mobile Nav -->
    <div class="mobile-nav" id="mobileNav">
        <a href="ecotour.php">Home</a>
        <a href="destinations.php">Destinations</a>
        <a href="about.php">About</a>
        <a href="#how">How It Works</a>
        <a href="#sdg">Sustainability</a>
        <div style="display:flex;gap:.75rem;padding-top:.5rem;">
            <a href="auth.php" class="btn-login" style="flex:1;text-align:center;border-radius:8px;padding:.75rem;border:1px solid rgba(255,255,255,.2);">Log In</a>
            <a href="auth.php?tab=signup" class="btn-signup" style="flex:1;text-align:center;border-radius:8px;padding:.75rem;">Sign Up</a>
        </div>
    </div>

    <!-- ═══════════════════════ HERO ═══════════════════════ -->
    <<section class="page-hero">

        <!-- Decorative rings -->
        <div class="hero-ring hero-ring-1"></div>
        <div class="hero-ring hero-ring-2"></div>
        <div class="hero-ring hero-ring-3"></div>

        <div class="page-hero-inner">

            <p class="page-eyebrow">
                Sustainable Tourism • Davao del Norte
            </p>

            <h1 class="page-hero-title">
                Explore Nature <em>Responsibly</em>
            </h1>

            <p class="page-hero-desc">
                Book hiking trails, parks, waterfalls, and eco-destinations across Davao del Norte while helping preserve ecosystems through sustainable tourism management.
            </p>

            <div class="hero-chips">
                <div class="hero-chip">
                    <strong>20+</strong> Destinations
                </div>

                <div class="hero-chip">
                    <strong>100%</strong> Managed Capacity
                </div>

                <div class="hero-chip">
                    <strong>SDG 15</strong> Sustainable Tourism
                </div>
            </div>

            <div class="hero-cta" style="margin-top:2rem;">
                <a href="auth.php" class="btn-primary">
                    Start Exploring
                </a>

                <a href="about.php" class="btn-ghost">
                    Learn More
                </a>
            </div>

        </div>

        </section>

        <!-- ═══════════════════════ STATS ═══════════════════════ -->
        <div class="stats-bar">
            <div class="stats-inner">
                <div class="stat-item">
                    <div class="stat-number">20+</div>
                    <div class="stat-label">Eco Destinations</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Managed Capacity</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">SDG 15</div>
                    <div class="stat-label">Life on Land</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Booking Access</div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════ ABOUT ═══════════════════════ -->
        <section class="intro-section" id="about">
            <div class="section-wrap">
                <div class="intro-grid">
                    <div class="intro-visual">
                        <div class="intro-card-main">
                            <div class="intro-card-main-inner">
                                <h4>Protected Nature Experiences</h4>
                                <p>EcoTour+ helps regulate eco-tourism while protecting natural ecosystems and promoting responsible travel.</p>
                            </div>
                        </div>
                        <div class="intro-badge">
                            <strong>15</strong>
                            SDG Goal
                        </div>
                    </div>
                    <div>
                        <span class="section-label">About The System</span>
                        <h2 class="section-title">Sustainable Booking for Nature Destinations</h2>
                        <p class="section-desc">
                            EcoTour+ is an integrated eco-tourism booking and land management system focused on hiking trails,
                            parks, and natural landmarks in Davao del Norte.
                        </p>
                        <div class="intro-features">
                            <div class="intro-feature">
                                <div class="intro-feature-icon">🌿</div>
                                <div class="intro-feature-text">
                                    <h4>Environmental Protection</h4>
                                    <p>Visitor limits help reduce overcrowding and environmental damage.</p>
                                </div>
                            </div>
                            <div class="intro-feature">
                                <div class="intro-feature-icon">🥾</div>
                                <div class="intro-feature-text">
                                    <h4>Easy Trail Reservations</h4>
                                    <p>Tourists can reserve hiking schedules and monitor destination availability.</p>
                                </div>
                            </div>
                            <div class="intro-feature">
                                <div class="intro-feature-icon">📍</div>
                                <div class="intro-feature-text">
                                    <h4>Coordinator Management</h4>
                                    <p>Assigned site coordinators regulate visitor counts and monitor environmental status.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ DESTINATIONS ═══════════════════════ -->
        <section class="destinations-section" id="destinations">
            <div class="section-wrap">
                <div class="destinations-header">
                    <div>
                        <span class="section-label">Featured Destinations</span>
                        <h2 class="section-title">Discover Davao del Norte</h2>
                    </div>
                    <a href="destinations.php" class="btn-ghost" style="color:#2d4a34;">See All Destinations</a>
                </div>

                <div class="destinations-grid">

                    <!-- ───────────────────────────────────────────────────────
                     CARD 1 — Mt. Candalaga Trail
                     Replace the <div class="dest-img"> gradient with:
                       <img src="/images/mt-candalaga.jpg" alt="Mt. Candalaga Trail"
                            style="width:100%;height:100%;object-fit:cover;">
                     once you have the photo.
                ─────────────────────────────────────────────────────────── -->
                    <div class="destination-card featured">
                        <div class="dest-img dest-img--candalaga">
                            <!-- 📸 SWAP: replace div content with your <img> tag -->
                            <div class="dest-img-placeholder">
                                <svg viewBox="0 0 64 64" fill="none" stroke="rgba(255,255,255,.5)" stroke-width="2">
                                    <path d="M4 56L24 20l10 16 6-8 20 28H4z" />
                                </svg>
                                <span>Mt. Candalaga</span>
                            </div>
                        </div>
                        <div class="dest-badge">Popular</div>
                        <div class="dest-diff diff-moderate">Moderate</div>
                        <div class="dest-body">
                            <div class="dest-type">Hiking Trail</div>
                            <div class="dest-name">Mt. Candalaga Trail</div>
                            <p>Scenic mountain hiking destination with controlled visitor access and eco-monitoring.</p>
                            <div class="dest-meta">
                                <span>📍 Davao del Norte</span>
                                <span>🥾 4–5 Hours</span>
                            </div>
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill" style="width:70%;"></div>
                                </div>
                                <span class="cap-label">70% Capacity</span>
                            </div>
                        </div>
                    </div>

                    <!-- ───────────────────────────────────────────────────────
                     CARD 2 — New Corella Eco Park
                     Replace with: <img src="/images/new-corella-eco-park.jpg" ...>
                ─────────────────────────────────────────────────────────── -->
                    <div class="destination-card">
                        <div class="dest-img dest-img--corella">
                            <div class="dest-img-placeholder">
                                <svg viewBox="0 0 64 64" fill="none" stroke="rgba(255,255,255,.5)" stroke-width="2">
                                    <circle cx="32" cy="32" r="22" />
                                    <path d="M18 44c3-8 8-14 14-14s11 6 14 14" />
                                    <path d="M32 30V18" />
                                </svg>
                                <span>New Corella Eco Park</span>
                            </div>
                        </div>
                        <div class="dest-badge">Nature Park</div>
                        <div class="dest-diff diff-easy">Easy</div>
                        <div class="dest-body">
                            <div class="dest-type">Eco Park</div>
                            <div class="dest-name">New Corella Eco Park</div>
                            <p>Relaxing eco-tourism destination ideal for families and nature walks.</p>
                            <div class="dest-meta">
                                <span>🌳 Public Park</span>
                            </div>
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill" style="width:45%;"></div>
                                </div>
                                <span class="cap-label">45% Capacity</span>
                            </div>
                        </div>
                    </div>

                    <!-- ───────────────────────────────────────────────────────
                     CARD 3 — Talaingod Highlands
                     Replace with: <img src="/images/talaingod-highlands.jpg" ...>
                ─────────────────────────────────────────────────────────── -->
                    <div class="destination-card">
                        <div class="dest-img dest-img--talaingod">
                            <div class="dest-img-placeholder">
                                <svg viewBox="0 0 64 64" fill="none" stroke="rgba(255,255,255,.5)" stroke-width="2">
                                    <path d="M4 56L20 24l8 12 8-16 24 36H4z" />
                                </svg>
                                <span>Talaingod Highlands</span>
                            </div>
                        </div>
                        <div class="dest-badge">Protected</div>
                        <div class="dest-diff diff-hard">Hard</div>
                        <div class="dest-body">
                            <div class="dest-type">Natural Landmark</div>
                            <div class="dest-name">Talaingod Highlands</div>
                            <p>A highland eco-destination promoting biodiversity and responsible tourism.</p>
                            <div class="dest-meta">
                                <span>⛰️ Protected Area</span>
                            </div>
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill cap-fill--high" style="width:82%;"></div>
                                </div>
                                <span class="cap-label">Near Limit</span>
                            </div>
                        </div>
                    </div>

                </div><!-- /destinations-grid -->
            </div>
        </section>

        <!-- ═══════════════════════ HOW IT WORKS ═══════════════════════ -->
        <section class="how-section" id="how">
            <div class="section-wrap">
                <span class="section-label">Booking Process</span>
                <h2 class="section-title">How EcoTour+ Works</h2>
                <div class="how-grid">
                    <div class="how-step">
                        <div class="how-icon">1</div>
                        <div class="how-number">STEP 01</div>
                        <div class="how-title">Create Account</div>
                        <div class="how-desc">Users must register or log in before booking destinations.</div>
                    </div>
                    <div class="how-step">
                        <div class="how-icon">2</div>
                        <div class="how-number">STEP 02</div>
                        <div class="how-title">Choose Destination</div>
                        <div class="how-desc">Browse hiking trails, parks, and landmarks in Davao del Norte.</div>
                    </div>
                    <div class="how-step">
                        <div class="how-icon">3</div>
                        <div class="how-number">STEP 03</div>
                        <div class="how-title">Reserve Schedule</div>
                        <div class="how-desc">Book based on environmental limits and visitor availability.</div>
                    </div>
                    <div class="how-step">
                        <div class="how-icon">4</div>
                        <div class="how-number">STEP 04</div>
                        <div class="how-title">Protected Experience</div>
                        <div class="how-desc">Site coordinators monitor capacity and maintain sustainability.</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ SDG ═══════════════════════ -->
        <section class="sdg-section" id="sdg">
            <div class="section-wrap">
                <div class="sdg-inner">
                    <div>
                        <h2 class="sdg-title">Supporting Sustainable Development Goal 15</h2>
                        <p class="sdg-desc">
                            EcoTour+ promotes sustainable tourism practices by helping regulate visitor access,
                            reducing environmental damage, and protecting terrestrial ecosystems.
                        </p>
                        <div class="sdg-goals">
                            <div class="sdg-goal">
                                <div class="sdg-goal-icon">🌱</div>
                                <div class="sdg-goal-text">
                                    <h4>Protect Natural Ecosystems</h4>
                                    <p>Reduce overcrowding and maintain ecological balance.</p>
                                </div>
                            </div>
                            <div class="sdg-goal">
                                <div class="sdg-goal-icon">📊</div>
                                <div class="sdg-goal-text">
                                    <h4>Environmental Monitoring</h4>
                                    <p>Coordinators can update environmental conditions in real time.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sdg-visual">
                        <div class="sdg-ring">
                            <div class="sdg-ring-center">
                                <div class="sdg-ring-num">15</div>
                                <div class="sdg-ring-label">Life on Land</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ CTA ═══════════════════════ -->
        <section class="cta-section">
            <div class="cta-inner">
                <h2 class="cta-title">Start Your Eco Adventure</h2>
                <p class="cta-desc">
                    Explore Davao del Norte responsibly through a smarter and more sustainable booking experience.
                </p>
                <div class="cta-buttons">
                    <a href="auth.php?tab=signup" class="btn-cta-p">Create Account</a>
                    <a href="destinations.php" class="btn-cta-s">Browse Destinations</a>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ FOOTER ═══════════════════════ -->
        <footer>
            <div class="footer-top">
                <div class="footer-brand">
                    <a href="ecotour.php" class="nav-brand" style="text-decoration:none;">
                        <div class="nav-logo">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2C8 2 4 5.5 4 10c0 3.5 2.5 7 8 10 5.5-3 8-6.5 8-10 0-4.5-4-8-8-8zm0 2.5c1.2 1.8 2 4 2 5.5 0 2-1 3.5-2 4.5-1-1-2-2.5-2-4.5 0-1.5.8-3.7 2-5.5z" />
                            </svg>
                        </div>
                        <span class="nav-brand-name">EcoTour+</span>
                    </a>
                    <p>Sustainable eco-tourism booking and land management platform for Davao del Norte.</p>
                </div>
                <div class="footer-col">
                    <h5>Navigation</h5>
                    <ul>
                        <li><a href="ecotour.php">Home</a></li>
                        <li><a href="destinations.php">Destinations</a></li>
                        <li><a href="about.php">About</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>System</h5>
                    <ul>
                        <li><a href="auth.php">Log In</a></li>
                        <li><a href="auth.php?tab=signup">Sign Up</a></li>
                        <li><a href="destinations.php">Bookings</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Advocacy</h5>
                    <ul>
                        <li><a href="#sdg">SDG 15</a></li>
                        <li><a href="#about">Sustainability</a></li>
                        <li><a href="destinations.php">Eco Tourism</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 EcoTour+. All Rights Reserved.</p>
                <p>Davao del Norte Eco-Tourism Booking System</p>
            </div>
        </footer>

        <!-- ═══════════════════════ EXTRA STYLES FOR IMAGE CARDS ═══════════════════════ -->
        <style>
            /* Image placeholder areas — replace .dest-img-placeholder with a real <img> when ready */
            .dest-img {
                position: relative;
                height: 200px;
                overflow: hidden;
            }

            /* Gradient fallbacks per destination — swap for real images when you have them */
            .dest-img--candalaga {
                background: linear-gradient(155deg, #1e3a28 0%, #3a7a50 50%, #c9863a 100%);
            }

            .dest-img--corella {
                background: linear-gradient(155deg, #2a4a2e 0%, #6aaa6f 55%, #e07b39 100%);
            }

            .dest-img--talaingod {
                background: linear-gradient(155deg, #1a2e1e 0%, #4a7a58 50%, #f2a65a 100%);
            }

            .dest-img-placeholder {
                position: absolute;
                inset: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: .5rem;
            }

            .dest-img-placeholder svg {
                width: 52px;
                height: 52px;
                opacity: .7;
            }

            .dest-img-placeholder span {
                font-size: .75rem;
                font-weight: 600;
                color: rgba(255, 255, 255, .6);
                letter-spacing: .05em;
                text-transform: uppercase;
            }

            /* When you add a real <img> inside .dest-img, the placeholder auto-hides */
            .dest-img img {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .dest-img img+.dest-img-placeholder {
                display: none;
            }

            .cap-fill--high {
                background: linear-gradient(90deg, #e07b39, #d94f4f) !important;
            }
        </style>

        <!-- ═══════════════════════ JS ═══════════════════════ -->
        <script>
            const navbar = document.getElementById('navbar');
            window.addEventListener('scroll', () => {
                navbar.classList.toggle('scrolled', window.scrollY > 40);
            });

            document.getElementById('hamburger').addEventListener('click', () => {
                document.getElementById('mobileNav').classList.toggle('open');
            });
        </script>

</body>

</html>