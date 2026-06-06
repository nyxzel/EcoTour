<?php
/*
 * booking-modal.php
 * Include before </body> in destinations.php.
 * Reads the logged-in user's name/email from $_SESSION to pre-fill solo form.
 * Sends bookings to booking.php via fetch().
 */

// Session must already be started by the parent page (destinations.php)
$sess_name  = htmlspecialchars($_SESSION['user_name']  ?? '');
$sess_email = htmlspecialchars($_SESSION['user_email'] ?? '');
$logged_in  = !empty($_SESSION['user_id']);
?>

<!-- ════════════════════════════════════════════════════
     BOOKING MODAL OVERLAY
     Open with: openBooking(name, type, fee, siteId, maxSlots)
════════════════════════════════════════════════════ -->
<div class="booking-overlay" id="bookingOverlay">
    <div class="booking-modal" id="bookingModal" role="dialog" aria-modal="true" aria-labelledby="bkSiteName">

        <!-- HEADER -->
        <div class="bk-header">
            <div class="bk-header-left">
                <p class="bk-site-tag" id="bkSiteType">Eco-Tourism Site</p>
                <h2 class="bk-site-name" id="bkSiteName">Site Name</h2>
            </div>
            <button class="bk-close" id="bkClose" aria-label="Close">
                <svg viewBox="0 0 24 24">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- STEP INDICATOR -->
        <div class="bk-steps">
            <div class="bk-step active" id="step-ind-1">
                <div class="bk-step-num">1</div>
                <span class="bk-step-label">Booking Type</span>
            </div>
            <div class="bk-step" id="step-ind-2">
                <div class="bk-step-num">2</div>
                <span class="bk-step-label">Details</span>
            </div>
            <div class="bk-step" id="step-ind-3">
                <div class="bk-step-num">3</div>
                <span class="bk-step-label">Review</span>
            </div>
            <div class="bk-step" id="step-ind-4">
                <div class="bk-step-num">4</div>
                <span class="bk-step-label">Confirmed</span>
            </div>
        </div>

        <!-- BODY -->
        <div class="bk-body">

            <!-- ══ PANEL 1 — TYPE ══ -->
            <div class="bk-panel active" id="panel1">
                <p class="bk-section-title">How will you be visiting?</p>
                <div class="bk-type-grid">
                    <button class="bk-type-card" id="typeSolo" onclick="selectType('solo')">
                        <div class="bk-type-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M6 20v-2a6 6 0 0112 0v2" />
                            </svg>
                        </div>
                        <h3>Solo Visit</h3>
                        <p>Just you — one slot, one destination, your own pace.</p>
                        <div class="bk-type-check"><svg viewBox="0 0 24 24">
                                <path d="M20 6 9 17l-5-5" />
                            </svg></div>
                    </button>
                    <button class="bk-type-card" id="typeGroup" onclick="selectType('group')">
                        <div class="bk-type-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="9" cy="8" r="3" />
                                <circle cx="16" cy="8" r="3" />
                                <path d="M3 20v-1a6 6 0 0112 0v1M13 20v-1a6 6 0 016-6h.5" />
                            </svg>
                        </div>
                        <h3>Group Visit</h3>
                        <p>With friends, family, or an organized team. Add all members.</p>
                        <div class="bk-type-check"><svg viewBox="0 0 24 24">
                                <path d="M20 6 9 17l-5-5" />
                            </svg></div>
                    </button>
                </div>

                <!-- Site strip -->
                <div class="bk-site-strip">
                    <div class="bk-site-strip-img" id="bkStripImg">
                        <svg viewBox="0 0 64 64">
                            <path d="M32 8C18 8 8 18 8 32s10 24 24 24 24-10 24-24S46 8 32 8z" />
                        </svg>
                    </div>
                    <div class="bk-site-strip-info">
                        <strong id="bkStripName">—</strong>
                        <span id="bkStripSlots">—</span>
                    </div>
                    <div class="bk-site-strip-fee">
                        <span class="fee-per">Entry fee / person</span>
                        <span class="fee-amt" id="bkStripFee">—</span>
                    </div>
                </div>
            </div>

            <!-- ══ PANEL 2 — DETAILS ══ -->
            <div class="bk-panel" id="panel2">

                <p class="bk-section-title">Visit Details</p>
                <div class="bk-form-row">
                    <div class="bk-field">
                        <label for="bkDate">Visit Date <span class="req">*</span></label>
                        <input type="date" id="bkDate" name="visit_date" />
                        <span class="bk-field-hint">Bookings open from tomorrow onwards</span>
                    </div>
                    <div class="bk-field">
                        <label for="bkSchedule">Time Slot <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select id="bkSchedule" name="schedule_id">
                                <option value="">-- Select time slot --</option>
                                <option value="1">Morning (7:00 AM – 12:00 PM)</option>
                                <option value="2">Afternoon (1:00 PM – 5:00 PM)</option>
                                <option value="3">Full Day (7:00 AM – 5:00 PM)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bk-form-row single" style="margin-bottom:1.6rem;">
                    <div class="bk-field">
                        <label for="bkNotes">Special Notes / Requirements</label>
                        <textarea id="bkNotes" name="special_notes" placeholder="Accessibility needs, equipment rental, dietary requirements, etc."></textarea>
                    </div>
                </div>

                <!-- SOLO section -->
                <div id="soloSection">
                    <p class="bk-section-title">Your Details</p>
                    <div class="bk-form-row">
                        <div class="bk-field">
                            <label>Full Name</label>
                            <input type="text" id="soloName" value="<?= $sess_name ?>" readonly
                                style="background:#f5f5f5;color:#888;" placeholder="<?= $logged_in ? '' : 'Log in to auto-fill' ?>" />
                        </div>
                        <div class="bk-field">
                            <label>Email Address</label>
                            <input type="email" id="soloEmail" value="<?= $sess_email ?>" readonly
                                style="background:#f5f5f5;color:#888;" placeholder="<?= $logged_in ? '' : 'Log in to auto-fill' ?>" />
                        </div>
                    </div>
                </div>

                <!-- GROUP section -->
                <div id="groupSection" style="display:none;">

                    <div class="bk-group-name-wrap">
                        <p class="bk-section-title">Group / Team Info</p>
                        <div class="bk-form-row single">
                            <div class="bk-field">
                                <label for="bkTeamName">Team / Group Name <span class="req">*</span></label>
                                <input type="text" id="bkTeamName" name="team_name"
                                    placeholder="e.g. Dela Cruz Family, UP Mountaineers Batch 25" />
                                <span class="bk-field-hint">This name appears on your booking reference.</span>
                            </div>
                        </div>
                    </div>

                    <div class="bk-members-wrap">
                        <div class="bk-members-header">
                            <p class="bk-section-title" style="margin-bottom:0;">
                                Group Members
                                <span class="member-count-pill" id="memberCountPill">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                    </svg>
                                    <span id="memberCountNum">0</span> added
                                </span>
                            </p>
                            <button class="btn-add-member" type="button" onclick="addMember()">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                Add Member
                            </button>
                        </div>

                        <div class="bk-self-include">
                            <input type="checkbox" id="includeLeader" checked onchange="updateMemberCount()" />
                            <span>Include <strong>yourself (<?= $sess_name ?: 'you' ?>)</strong> as group leader in the headcount</span>
                        </div>

                        <div class="bk-member-list" id="memberList"></div>
                    </div>

                </div>
            </div><!-- /panel2 -->

            <!-- ══ PANEL 3 — REVIEW ══ -->
            <div class="bk-panel" id="panel3">
                <p class="bk-section-title">Review Your Booking</p>

                <div class="bk-review-card">
                    <div class="bk-review-card-header">
                        <h4>Booking Details</h4>
                        <button class="bk-review-edit" type="button" onclick="goToStep(2)">
                            <svg viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            Edit
                        </button>
                    </div>
                    <div class="bk-review-rows">
                        <div class="bk-review-row"><span class="rv-label">Destination</span><span class="rv-val" id="rvSite">—</span></div>
                        <div class="bk-review-row"><span class="rv-label">Booking Type</span><span class="rv-val" id="rvType">—</span></div>
                        <div class="bk-review-row"><span class="rv-label">Visit Date</span><span class="rv-val" id="rvDate">—</span></div>
                        <div class="bk-review-row"><span class="rv-label">Time Slot</span><span class="rv-val" id="rvSlot">—</span></div>
                        <div class="bk-review-row" id="rvTeamRow" style="display:none;"><span class="rv-label">Team Name</span><span class="rv-val" id="rvTeam">—</span></div>
                        <div class="bk-review-row"><span class="rv-label">Total Visitors</span><span class="rv-val" id="rvTotal">—</span></div>
                    </div>
                </div>

                <div class="bk-review-card" id="rvMembersCard" style="display:none;">
                    <div class="bk-review-card-header">
                        <h4>Group Members</h4>
                        <button class="bk-review-edit" type="button" onclick="goToStep(2)">
                            <svg viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            Edit
                        </button>
                    </div>
                    <div class="bk-members-review" id="rvMembersList"></div>
                </div>

                <div class="bk-fee-summary">
                    <div class="bk-fee-row"><span class="fee-label">Entry fee / person</span><span class="fee-val" id="feePer">—</span></div>
                    <div class="bk-fee-row"><span class="fee-label">Total visitors</span><span class="fee-val" id="feeCount">—</span></div>
                    <div class="bk-fee-row total"><span class="fee-label">Estimated Total</span><span class="fee-val" id="feeTotal">—</span></div>
                </div>

                <div class="bk-eco-note">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    <p>By confirming, you agree to follow environmental site guidelines, respect visitor limits, and leave no trace. Your booking funds site conservation.</p>
                </div>
            </div>

            <!-- ══ PANEL 4 — SUCCESS ══ -->
            <div class="bk-panel" id="panel4">
                <div class="bk-success">
                    <div class="bk-success-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                            <path d="M22 4 12 14.01l-3-3" />
                        </svg>
                    </div>
                    <h2>Booking Submitted!</h2>
                    <p>Your visit is pending site manager approval. Check your booking history for updates.</p>
                    <div class="bk-ref" id="bkRefCode">ECO-——————</div>
                    <div class="bk-success-details">
                        <div class="bk-success-detail"><span class="sd-label">Destination</span><span class="sd-val" id="sdSite">—</span></div>
                        <div class="bk-success-detail"><span class="sd-label">Visit Date</span><span class="sd-val" id="sdDate">—</span></div>
                        <div class="bk-success-detail"><span class="sd-label">Visitors</span><span class="sd-val" id="sdTotal">—</span></div>
                    </div>
                    <p>Save your reference code. The site manager will confirm or contact you shortly.</p>
                </div>
            </div>

        </div><!-- /bk-body -->

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

    </div>
</div>

<!-- ════════════════════════════════════════
     INLINE JAVASCRIPT
════════════════════════════════════════ -->
<script>
    (function() {

        // ── State object ─────────────────────────────────────────────────────────────
        const BK = {
            step: 1,
            type: null,
            siteName: '',
            siteType: '',
            siteFee: 0,
            siteId: 0,
            maxSlots: 0,
            teamName: '',
            date: '',
            scheduleId: 0,
            slotLabel: '',
            members: [],
            includeLeader: true,
            reference: ''
        };
        let _memberId = 0;
        const loggedIn = <?= $logged_in ? 'true' : 'false' ?>;

        // ── Open ──────────────────────────────────────────────────────────────────────
        window.openBooking = function(name, type, fee, siteId, maxSlots) {
            if (!loggedIn) {
                window.location.href = 'auth.php';
                return;
            }
            Object.assign(BK, {
                step: 1,
                type: null,
                siteName: name,
                siteType: type,
                siteFee: fee,
                siteId: siteId,
                maxSlots: maxSlots,
                teamName: '',
                date: '',
                scheduleId: 0,
                slotLabel: '',
                members: [],
                includeLeader: true,
                reference: ''
            });
            _memberId = 0;

            document.getElementById('bkSiteName').textContent = name;
            document.getElementById('bkSiteType').textContent = type;
            document.getElementById('bkStripName').textContent = name;
            document.getElementById('bkStripSlots').textContent = maxSlots + ' max slots / day';
            const feeEl = document.getElementById('bkStripFee');
            feeEl.textContent = fee > 0 ? '₱' + fee : 'Free';
            feeEl.className = 'fee-amt' + (fee === 0 ? ' free' : '');
            document.getElementById('bkStripImg').className = 'bk-site-strip-img ' + type;

            // Min date = tomorrow
            const t = new Date();
            t.setDate(t.getDate() + 1);
            document.getElementById('bkDate').min = t.toISOString().split('T')[0];
            document.getElementById('bkDate').value = '';
            document.getElementById('bkSchedule').value = '';
            document.getElementById('bkNotes').value = '';
            document.getElementById('bkTeamName').value = '';
            document.getElementById('memberList').innerHTML = '';
            document.getElementById('includeLeader').checked = true;
            updateMemberCount();
            resetCards();
            _goToStep(1);

            document.getElementById('bookingOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        // ── Close ─────────────────────────────────────────────────────────────────────
        window.closeBooking = function() {
            document.getElementById('bookingOverlay').classList.remove('active');
            document.body.style.overflow = '';
        };
        document.getElementById('bookingOverlay').addEventListener('click', e => {
            if (e.target === document.getElementById('bookingOverlay')) closeBooking();
        });
        document.getElementById('bkClose').addEventListener('click', closeBooking);

        // ── Steps ─────────────────────────────────────────────────────────────────────
        function _goToStep(n) {
            BK.step = n;
            document.querySelectorAll('.bk-panel').forEach((p, i) => p.classList.toggle('active', i + 1 === n));
            document.querySelectorAll('.bk-step').forEach((s, i) => {
                s.classList.remove('active', 'done');
                if (i + 1 === n) s.classList.add('active');
                if (i + 1 < n) s.classList.add('done');
                const num = s.querySelector('.bk-step-num');
                num.innerHTML = (i + 1 < n) ?
                    '<svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:white;fill:none;stroke-width:3"><path d="M20 6 9 17l-5-5"/></svg>' :
                    String(i + 1);
            });
            const btnBack = document.getElementById('btnBack');
            const btnNext = document.getElementById('btnNext');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnDone = document.getElementById('btnDone');
            btnBack.style.display = (n > 1 && n < 4) ? '' : 'none';
            btnNext.style.display = n < 3 ? '' : 'none';
            btnSubmit.style.display = n === 3 ? '' : 'none';
            btnDone.style.display = n === 4 ? '' : 'none';
            btnNext.disabled = (n === 1 && !BK.type);
            document.getElementById('bookingModal').scrollTop = 0;

            // Toggle solo/group panels on step 2
            if (n === 2) {
                document.getElementById('soloSection').style.display = BK.type === 'solo' ? '' : 'none';
                document.getElementById('groupSection').style.display = BK.type === 'group' ? '' : 'none';
            }
        }
        // Expose globally for Edit buttons in review panel
        window.goToStep = _goToStep;

        window.bkNext = function() {
            if (BK.step === 1) {
                if (BK.type) _goToStep(2);
            } else if (BK.step === 2) {
                if (validateStep2()) {
                    buildReview();
                    _goToStep(3);
                }
            }
        };
        window.bkBack = function() {
            if (BK.step > 1 && BK.step < 4) _goToStep(BK.step - 1);
        };

        // ── Type select ───────────────────────────────────────────────────────────────
        window.selectType = function(type) {
            BK.type = type;
            document.getElementById('typeSolo').classList.toggle('selected', type === 'solo');
            document.getElementById('typeGroup').classList.toggle('selected', type === 'group');
            document.getElementById('btnNext').disabled = false;
        };

        function resetCards() {
            document.getElementById('typeSolo').classList.remove('selected');
            document.getElementById('typeGroup').classList.remove('selected');
            BK.type = null;
            document.getElementById('btnNext').disabled = true;
        }

        // ── Members ───────────────────────────────────────────────────────────────────
        window.addMember = function() {
            const id = ++_memberId;
            const row = document.createElement('div');
            row.className = 'bk-member-row';
            row.id = 'member-' + id;
            row.innerHTML =
                '<input type="text"   class="mem-name" placeholder="Full name" />' +
                '<input type="number" class="mem-age"  placeholder="Age" min="1" max="120" />' +
                '<div class="minor-check">' +
                '<input type="checkbox" class="mem-minor" id="minor-' + id + '" onchange="updateMemberCount()" />' +
                '<label for="minor-' + id + '">Minor</label>' +
                '</div>' +
                '<button type="button" class="btn-remove-member" onclick="removeMember(' + id + ')" aria-label="Remove">' +
                '<svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>' +
                '</button>';
            document.getElementById('memberList').appendChild(row);
            updateMemberCount();
            row.querySelector('.mem-name').focus();
        };
        window.removeMember = function(id) {
            const el = document.getElementById('member-' + id);
            if (!el) return;
            el.style.opacity = '0';
            el.style.transform = 'translateY(-4px)';
            el.style.transition = 'all 0.2s ease';
            setTimeout(() => {
                el.remove();
                updateMemberCount();
            }, 200);
        };
        window.updateMemberCount = function() {
            const count = document.querySelectorAll('#memberList .bk-member-row').length;
            const el = document.getElementById('memberCountNum');
            if (el) el.textContent = count;
        };

        // ── Validate step 2 ───────────────────────────────────────────────────────────
        function validateStep2() {
            let ok = true;
            const dateEl = document.getElementById('bkDate');
            const slotEl = document.getElementById('bkSchedule');

            if (!dateEl.value) {
                dateEl.classList.add('invalid');
                ok = false;
            } else {
                dateEl.classList.remove('invalid');
                BK.date = dateEl.value;
            }

            if (!slotEl.value) {
                slotEl.classList.add('invalid');
                ok = false;
            } else {
                slotEl.classList.remove('invalid');
                BK.scheduleId = parseInt(slotEl.value);
                BK.slotLabel = slotEl.options[slotEl.selectedIndex].text;
            }

            if (BK.type === 'group') {
                const teamEl = document.getElementById('bkTeamName');
                if (!teamEl.value.trim()) {
                    teamEl.classList.add('invalid');
                    ok = false;
                } else {
                    teamEl.classList.remove('invalid');
                    BK.teamName = teamEl.value.trim();
                }

                BK.members = [];
                document.querySelectorAll('#memberList .bk-member-row').forEach(row => {
                    const name = row.querySelector('.mem-name').value.trim();
                    const age = parseInt(row.querySelector('.mem-age').value || 0);
                    const minor = row.querySelector('.mem-minor').checked;
                    if (!name) {
                        row.querySelector('.mem-name').classList.add('invalid');
                        ok = false;
                    } else {
                        row.querySelector('.mem-name').classList.remove('invalid');
                        if (name) BK.members.push({
                            name,
                            age,
                            is_minor: minor
                        });
                    }
                });
                BK.includeLeader = document.getElementById('includeLeader').checked;

                if (BK.members.length === 0 && !BK.includeLeader) {
                    alert('Your group must have at least 1 visitor. Check "Include yourself" or add a member.');
                    ok = false;
                }
            }
            return ok;
        }

        // ── Build review ──────────────────────────────────────────────────────────────
        function buildReview() {
            const total = BK.type === 'solo' ? 1 : BK.members.length + (BK.includeLeader ? 1 : 0);
            document.getElementById('rvSite').textContent = BK.siteName;
            document.getElementById('rvType').textContent = BK.type === 'solo' ? 'Solo Visit' : 'Group Visit';
            document.getElementById('rvDate').textContent = fmtDate(BK.date);
            document.getElementById('rvSlot').textContent = BK.slotLabel;
            document.getElementById('rvTotal').textContent = total + ' visitor' + (total > 1 ? 's' : '');

            const teamRow = document.getElementById('rvTeamRow');
            if (BK.type === 'group') {
                teamRow.style.display = '';
                document.getElementById('rvTeam').textContent = BK.teamName;
            } else {
                teamRow.style.display = 'none';
            }

            const memCard = document.getElementById('rvMembersCard');
            const memList = document.getElementById('rvMembersList');
            if (BK.type === 'group') {
                memCard.style.display = '';
                memList.innerHTML = '';
                if (BK.includeLeader) {
                    memList.insertAdjacentHTML('beforeend',
                        '<div class="bk-member-review-row">' +
                        '<div class="bk-member-avatar leader">L</div>' +
                        '<div class="bk-member-review-info"><strong><?= $sess_name ?: 'You (Group Leader)' ?></strong>' +
                        '<span>Registered user · Leader</span></div></div>');
                }
                BK.members.forEach(m => {
                    const ini = m.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                    memList.insertAdjacentHTML('beforeend',
                        '<div class="bk-member-review-row">' +
                        '<div class="bk-member-avatar">' + ini + '</div>' +
                        '<div class="bk-member-review-info"><strong>' + esc(m.name) + '</strong>' +
                        '<span>' + (m.age ? 'Age ' + m.age : 'Age not specified') + (m.is_minor ? ' · Minor' : '') + '</span></div></div>');
                });
            } else {
                memCard.style.display = 'none';
            }

            document.getElementById('feePer').textContent = BK.siteFee > 0 ? '₱' + BK.siteFee : 'Free';
            document.getElementById('feeCount').textContent = total;
            document.getElementById('feeTotal').textContent = BK.siteFee > 0 ? '₱' + (BK.siteFee * total).toLocaleString() : 'Free';
        }

        // ── Submit ────────────────────────────────────────────────────────────────────
        window.bkSubmit = function() {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:white;fill:none;stroke-width:2.5;animation:_spin .8s linear infinite"><path d="M21 12a9 9 0 11-18 0"/></svg> Processing…';

            const total = BK.type === 'solo' ? 1 : BK.members.length + (BK.includeLeader ? 1 : 0);
            const fd = new FormData();
            fd.append('booking_type', BK.type);
            fd.append('site_id', BK.siteId);
            fd.append('schedule_id', BK.scheduleId);
            fd.append('team_name', BK.teamName);
            fd.append('visit_date', BK.date);
            fd.append('include_leader', BK.includeLeader ? '1' : '0');
            fd.append('special_notes', document.getElementById('bkNotes').value.trim());
            fd.append('members', JSON.stringify(BK.members));

            fetch('booking.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => {
                    if (!r.ok && r.status === 401) {
                        window.location.href = 'auth.php';
                        throw new Error('unauthenticated');
                    }
                    return r.json();
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = 'Confirm Booking <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:white;fill:none;stroke-width:2.5"><path d="M22 4 12 14.01l-3-3"/></svg>';
                    if (data.success) {
                        document.getElementById('bkRefCode').textContent = data.reference;
                        document.getElementById('sdSite').textContent = BK.siteName;
                        document.getElementById('sdDate').textContent = fmtDate(BK.date);
                        document.getElementById('sdTotal').textContent = total + ' visitor' + (total > 1 ? 's' : '');
                        _goToStep(4);
                    } else {
                        const msg = data.errors ? data.errors.join('\n') : (data.message || 'Booking failed.');
                        alert('⚠ ' + msg);
                    }
                })
                .catch(err => {
                    if (err.message !== 'unauthenticated') {
                        btn.disabled = false;
                        btn.innerHTML = 'Confirm Booking';
                        alert('Network error. Please check your connection and try again.');
                    }
                });
        };

        // ── Helpers ───────────────────────────────────────────────────────────────────
        function fmtDate(str) {
            if (!str) return '—';
            return new Date(str + 'T00:00:00').toLocaleDateString('en-PH', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function esc(s) {
            return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Spinner CSS
        const ss = document.createElement('style');
        ss.textContent = '@keyframes _spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(ss);

    })(); // end IIFE
</script>