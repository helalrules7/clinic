        /* Shared pagination scroll helper. Used by every paginated list so that
           changing a page scrolls the list's TOP just below the fixed header (so the
           first record is clearly visible) instead of jarringly jumping elsewhere. */
        window.scrollListToTop = function (target) {
            if (typeof target === 'string') target = document.getElementById(target) || document.querySelector(target);
            if (!target) return;
            // Measure any bar pinned to the very top of the viewport
            let headerH = 0;
            document.querySelectorAll('.top-bar, .notice-bar, .navbar.fixed-top').forEach(function (el) {
                const cs = getComputedStyle(el);
                if (cs.position === 'fixed' || cs.position === 'sticky') {
                    const r = el.getBoundingClientRect();
                    if (r.top <= 1) headerH = Math.max(headerH, r.bottom);
                }
            });
            const y = target.getBoundingClientRect().top + window.pageYOffset - headerH - 16;
            window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
        };

        /* Modal-open safety net.
           Bootstrap adds `body.modal-open { overflow: hidden }` while a
           modal is showing — which is correct. But if a modal is force-
           dismissed by a same-tab navigation (link click inside the
           modal, programmatic submit, history-back, etc.) the class can
           linger on the new page. The user then can't scroll the page
           at all — most visible at high page zoom where the lingering
           class is the only thing keeping content unreachable. Run as
           soon as DOM is interactive and again on full load. */
        (function () {
            function sweep() {
                var hasVisibleModal = !!document.querySelector(
                    '.modal.show, .modal.fade.show, .modal.in');
                if (!hasVisibleModal) {
                    if (document.body.classList.contains('modal-open')) {
                        document.body.classList.remove('modal-open');
                    }
                    if (document.body.style.overflow === 'hidden') {
                        document.body.style.overflow = '';
                    }
                    if (document.body.style.paddingRight) {
                        document.body.style.paddingRight = '';
                    }
                    document.querySelectorAll('.modal-backdrop').forEach(
                        function (b) { b.remove(); });
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', sweep);
            } else {
                sweep();
            }
            window.addEventListener('load', sweep);
            window.addEventListener('pageshow', sweep);
        })();

        // Global session check interceptor for fetch requests.
        //
        // Previous version fired a PRE-FLIGHT /api/auth/session-time request
        // BEFORE every /api/* call, doubling network traffic for every API
        // request on the page. On Cloudflare-fronted prod that caused
        // intermittent "Fetch API cannot load … due to access control checks"
        // errors in Safari — the doubled traffic occasionally tripped CF's
        // edge throttling, and the failed pre-flight bubbled up as a CORS
        // error. The post-response 401/403 check below catches real session
        // expiry just as reliably, so the pre-flight has been removed.
        (function() {
            const originalFetch = window.fetch;

            window.fetch = async function(...args) {
                const response = await originalFetch.apply(this, args);

                // Detect session expiry on the actual response.
                if (response.status === 401 || response.status === 403) {
                    try {
                        const clonedResponse = response.clone();
                        const data = await clonedResponse.json();

                        if (data.message && (data.message.includes('Unauthorized') || data.message.includes('expired') || data.message.includes('session'))) {
                            if (!window.location.pathname.includes('/login')) {
                                window.location.href = '/login?expired=1';
                            }
                            return Promise.reject(new Error('Session expired'));
                        }
                    } catch (e) {
                        // If not JSON or parse error, fall through and let the caller handle.
                        // (We deliberately do NOT redirect on bare 401/403 — many endpoints
                        // legitimately return those for non-session reasons, e.g. a
                        // permission-gated route or a 403 from CF for a transient block.)
                    }
                }

                return response;
            };
        })();

        // Theme toggle functionality
        const apply = mode => document.documentElement.classList.toggle('dark', mode === 'dark');
        
        // Function to update UI elements based on theme
        function updateThemeUI(theme) {
            // Update checkbox state
            const themeToggleInput = document.getElementById('themeToggleInput');
            if (themeToggleInput) {
                themeToggleInput.checked = theme === 'dark';
            }
            
            // Update logo
            const logo = document.getElementById('clinicLogo');
            if (logo) {
                logo.src = theme === 'dark' ? '/assets/images/Dark.png' : '/assets/images/Light.png';
            }
            
            // Favicon is now static (faicon.ico) - no need to update
        }

        // Exposed so the auto-schedule applier (theme-palette.js) can keep the
        // header toggle + logo in sync whenever it flips .dark on a timer.
        window.syncThemeUI = function () {
            updateThemeUI(document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        };
        
        // Function to save theme to database and localStorage
        async function saveThemeToDatabase(theme) {
            // Save to localStorage immediately (synchronous, no delay)
            localStorage.setItem('appTheme', theme);
            
            try {
                const response = await fetch('/api/doctor/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        theme: theme
                    })
                });
                
                if (!response.ok) {
                    // Silent error handling
                }
            } catch (error) {
                // Silent error handling
            }
        }
        
        // Function to load theme from database
        async function loadThemeFromDatabase() {
            try {
                const response = await fetch('/api/doctor/settings', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.settings && data.settings.theme) {
                        return data.settings.theme;
                    }
                }
            } catch (error) {
                // Silent error handling
            }
            return null;
        }
        
        async function onManualThemePick(nextTheme) {
            if (typeof window.disableThemeAutoSchedule === 'function') {
                await window.disableThemeAutoSchedule(true);
            } else {
                localStorage.setItem('appThemeAutoSchedule', '0');
            }
            apply(nextTheme);
            updateThemeUI(nextTheme);
            localStorage.setItem('appTheme', nextTheme);
            localStorage.setItem('theme', nextTheme);
            await saveThemeToDatabase(nextTheme);
        }

        // Initialize theme - Priority to localStorage, sync with database
        (async function() {
            // Auto dark/light schedule takes precedence: the pre-paint script
            // already set .dark from the clock, and theme-palette.js keeps it
            // current on a timer. Don't override it with a stale saved theme —
            // just mirror the current class onto the toggle + logo.
            const autoSchedule = localStorage.getItem('appThemeAutoSchedule') === '1';
            if (autoSchedule) {
                const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                updateThemeUI(current);
                document.documentElement.classList.add('theme-loaded');
                const themeToggleInputAuto = document.getElementById('themeToggleInput');
                if (themeToggleInputAuto) {
                    themeToggleInputAuto.addEventListener('change', async function () {
                        await onManualThemePick(this.checked ? 'dark' : 'light');
                    });
                }
                return;
            }

            // Read theme from localStorage first (fast, synchronous)
            let savedTheme = localStorage.getItem('appTheme') || localStorage.getItem('theme');
            
            // Validate theme value
            if (savedTheme !== 'light' && savedTheme !== 'dark') {
                savedTheme = null;
            }
            
            // Load from database if no valid theme in localStorage
            let dbTheme = null;
                if (!savedTheme) {
                dbTheme = await loadThemeFromDatabase();
                if (dbTheme === 'light' || dbTheme === 'dark') {
                    savedTheme = dbTheme;
                    // Save to localStorage for next time
                    localStorage.setItem('appTheme', savedTheme);
                    localStorage.setItem('theme', savedTheme);
                }
            } else {
                // We have a theme in localStorage, check database in background
                dbTheme = await loadThemeFromDatabase();
            }
            
            // If still no theme, default to 'dark'
            if (!savedTheme || (savedTheme !== 'light' && savedTheme !== 'dark')) {
                savedTheme = 'dark';
                localStorage.setItem('appTheme', savedTheme);
                localStorage.setItem('theme', savedTheme);
            }
            
            // If localStorage theme differs from database, update database to match localStorage
            if (savedTheme && dbTheme && dbTheme !== savedTheme) {
                // localStorage has priority - save it to database
                await saveThemeToDatabase(savedTheme);
            } else if (savedTheme && !dbTheme) {
                // No theme in database but we have one in localStorage - save it
                await saveThemeToDatabase(savedTheme);
            }
            
            // Apply theme (should already be applied by inline script, but ensure it's correct)
            apply(savedTheme);
            
            // Update UI elements
            updateThemeUI(savedTheme);
            
            // Mark theme as loaded to remove flash prevention
            document.documentElement.classList.add('theme-loaded');
            
            // Theme toggle checkbox change handler
            const themeToggleInput = document.getElementById('themeToggleInput');
            if (themeToggleInput) {
                themeToggleInput.addEventListener('change', async function () {
                    await onManualThemePick(this.checked ? 'dark' : 'light');
                });
            }
        })();
        
        // Save last viewed patient ID for Unified Clinical Dashboard
        (function() {
            function savePatientId(patientId) {
                if (patientId && patientId !== 'null' && patientId !== null) {
                    localStorage.setItem('lastViewedPatientId', patientId.toString());
                    // Reload dashboard if function exists
                    if (typeof loadUnifiedClinicalDashboard === 'function') {
                        loadUnifiedClinicalDashboard();
                    }
                }
            }
            
            // Check current page
            const currentPath = window.location.pathname;
            
            // Check if on patient page
            const patientMatch = currentPath.match(/\/doctor\/patients\/(\d+)/);
            if (patientMatch) {
                savePatientId(patientMatch[1]);
            }
            
            // Check if on appointment page and APPOINTMENT_CONFIG exists
            const appointmentMatch = currentPath.match(/\/doctor\/appointments\/(\d+)/);
            if (appointmentMatch) {
                // Wait for APPOINTMENT_CONFIG to be available
                if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                    savePatientId(window.APPOINTMENT_CONFIG.patientId);
                } else {
                    // Wait a bit for script to load
                    setTimeout(() => {
                        if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                            savePatientId(window.APPOINTMENT_CONFIG.patientId);
                        }
                    }, 500);
                }
            }
            
            // Listen for navigation events
            window.addEventListener('popstate', () => {
                const path = window.location.pathname;
                const patientMatch = path.match(/\/doctor\/patients\/(\d+)/);
                if (patientMatch) {
                    savePatientId(patientMatch[1]);
                }
                
                const appointmentMatch = path.match(/\/doctor\/appointments\/(\d+)/);
                if (appointmentMatch && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                    savePatientId(window.APPOINTMENT_CONFIG.patientId);
                }
            });
            
            // Also listen for APPOINTMENT_CONFIG changes (in case it loads after page load)
            let configCheckInterval = setInterval(() => {
                if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                    const appointmentMatch = currentPath.match(/\/doctor\/appointments\/(\d+)/);
                    if (appointmentMatch) {
                        savePatientId(window.APPOINTMENT_CONFIG.patientId);
                        clearInterval(configCheckInterval);
                    }
                }
            }, 100);
            
            // Clear interval after 5 seconds
            setTimeout(() => {
                clearInterval(configCheckInterval);
            }, 5000);
        })();
        
        // ---------------------------------------------------------------
        // Sidebar toggle — two behaviours depending on viewport:
        //   • ≥768px (desktop + tablet): flips body.sidebar-mini so the
        //     sidebar collapses to a 76px icon rail, and persists the
        //     choice across pages via localStorage (key: SIDEBAR_MODE_KEY).
        //   • <768px (phone): keeps the legacy off-canvas overlay
        //     (sidebar.show + overlay.show).
        // ---------------------------------------------------------------
        (function setupSidebarToggle() {
            const SIDEBAR_MODE_KEY = 'appSidebarMode'; // 'wide' | 'mini'
            const MOBILE_BP = 768;
            const TABLET_BP = 1366; // tablets (≤1366) default to mini rail
            /* Hard floor — below this effective width the wide sidebar
               crowds out the page (or the user has zoomed in so far that
               main content can't breathe). We force mini regardless of
               the saved preference; the preference re-applies as soon as
               they zoom out / resize wider. innerWidth is reported in
               CSS px, which shrinks proportionally with browser zoom-in,
               so this also covers the "zoom too high" case. */
            const FORCE_MINI_BP = 1100;

            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (!sidebarToggle || !sidebar || !overlay) return;

            function isMobile() { return window.innerWidth < MOBILE_BP; }
            function isCramped() { return window.innerWidth < FORCE_MINI_BP; }

            let _sidebarAnimTimer = null;
            function applyMode(mode) {
                const wantMini = (mode === 'mini');
                const isMini = document.body.classList.contains('sidebar-mini');
                if (wantMini === isMini) return; // no state change → no transition, skip
                // The collapse animates width (sidebar) + margin-left (main-content),
                // which reflows the page every frame AND forces every backdrop-filter
                // glass surface to re-blur its shifting backdrop — measured at ~1/3 of
                // frames dropping below 30fps on the glass-heavy dashboard. Drop the
                // blur for just the duration of the slide (imperceptible during a fast
                // transition, restored the instant it ends) → collapse stays at 60fps.
                document.body.classList.add('sidebar-animating');
                if (_sidebarAnimTimer) clearTimeout(_sidebarAnimTimer);
                _sidebarAnimTimer = setTimeout(() => {
                    document.body.classList.remove('sidebar-animating');
                    _sidebarAnimTimer = null;
                }, 280); // 0.2s transition + buffer
                if (wantMini) document.body.classList.add('sidebar-mini');
                else document.body.classList.remove('sidebar-mini');
                // Re-sync submenu open/closed state to match the new mode:
                // collapse all in mini (so the peek-overlay CSS stays off and
                // doesn't hide the page content); expand the submenu of the
                // active sub-page in expanded mode. Helper is exposed by the
                // submenu IIFE below.
                if (typeof window.__sidebarSyncSubmenu === 'function') {
                    window.__sidebarSyncSubmenu();
                }
            }

            function defaultMode() {
                // Smaller tablets / split-screen laptops benefit from a
                // mini rail by default. Wide desktops stay expanded.
                return window.innerWidth < TABLET_BP ? 'mini' : 'wide';
            }

            function readSaved() {
                try { return localStorage.getItem(SIDEBAR_MODE_KEY); }
                catch (e) { return null; }
            }

            // Effective mode: a cramped viewport ALWAYS forces mini, even
            // if the user previously chose wide on a bigger screen.
            function effectiveMode() {
                if (isCramped()) return 'mini';
                return readSaved() || defaultMode();
            }

            applyMode(effectiveMode());

            sidebarToggle.addEventListener('click', () => {
                if (isMobile()) {
                    // Phone: classic off-canvas overlay
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                    return;
                }
                // Desktop / tablet: toggle mini rail and persist the
                // user's intent. We still apply effectiveMode afterwards
                // so a "wide" choice on a cramped viewport is recorded
                // but does NOT visually override the safety floor.
                const wantMini = !document.body.classList.contains('sidebar-mini');
                try { localStorage.setItem(SIDEBAR_MODE_KEY, wantMini ? 'mini' : 'wide'); }
                catch (e) { /* ignore */ }
                applyMode(effectiveMode());
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            window.addEventListener('resize', () => {
                // Close any open mobile overlay once we're past the breakpoint
                if (window.innerWidth >= MOBILE_BP) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
                // Recompute every resize — this handles browser zoom
                // changes (which fire resize) and viewport rotations,
                // toggling the force-mini floor on/off automatically.
                applyMode(effectiveMode());
            });
        })();
        
        // Submenu toggle functionality
        (function() {
            const sidebar = document.getElementById('sidebar');
            const submenuToggles = document.querySelectorAll('.nav-link-toggle');

            // Mirror the "any submenu is expanded" state as classes on the
            // sidebar AND body elements. CSS uses these as non-:has()
            // fallbacks for sticky peek-expand on tablet, since Safari
            // `:has()` doesn't always recompute when classes are toggled
            // dynamically.
            function syncExpandedFlag() {
                const anyExpanded = !!document.querySelector('.sidebar .nav-item.has-submenu.expanded');
                if (sidebar) sidebar.classList.toggle('has-expanded-submenu', anyExpanded);
                document.body.classList.toggle('sidebar-has-expanded-submenu', anyExpanded);
            }

            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const navItem = this.closest('.nav-item.has-submenu');
                    if (navItem) {
                        navItem.classList.toggle('expanded');
                        syncExpandedFlag();
                    }
                });
            });

            // Keep submenu open/closed state in sync with the sidebar mode.
            // - In mini mode: ALL submenus are collapsed so the rail stays
            //   clean. (If a submenu has `.expanded` while body is mini, the
            //   peek-overlay CSS widens the sidebar visually while
            //   .main-content's margin stays at mini width → page content
            //   hides behind the sidebar. We only want that overlay when the
            //   user explicitly clicks a parent icon to peek-expand on
            //   demand, not unconditionally on page load.)
            // - In expanded mode: the submenu containing the active sub-page
            //   is auto-expanded so the user can see where they are.
            //
            // Exposed as window.__sidebarSyncSubmenu so applyMode() can
            // call it on every mode change (toggle, resize, etc.), not just
            // at first page load.
            function syncSubmenuForMode() {
                const isMini = document.body.classList.contains('sidebar-mini');
                if (isMini) {
                    document.querySelectorAll('.sidebar .nav-item.has-submenu.expanded').forEach(item => {
                        item.classList.remove('expanded');
                    });
                } else {
                    document.querySelectorAll('.nav-submenu-link.active').forEach(item => {
                        const navItem = item.closest('.nav-item.has-submenu');
                        if (navItem) navItem.classList.add('expanded');
                    });
                }
                syncExpandedFlag();
            }
            window.__sidebarSyncSubmenu = syncSubmenuForMode;
            syncSubmenuForMode();
        })();

        /* Mini-rail hover tooltip. Replaces the old hover peek-expand:
           hovering a collapsed icon now shows a small arrow tooltip with
           the page title and a one-line description, instead of widening
           the whole sidebar. Only active while body.sidebar-mini and the
           rail is NOT already expanded (toggle / Medical-Storage click). */
        (function setupMiniTip() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            // Keyed by the LAST path segment so it works for both the
            // /doctor/* and /secretary/* navs.
            const DESC = {
                dashboard: 'Overview & key stats',
                calendar:  'Appointments & bookings',
                patients:  'Patient records',
                board:     'Patients board',
                drugs:     'Drugs database',
                payments:  'Financial management',
                reports:   'Analytics & reports',
                alerts:    'Notifications & alerts',
                notes:     'Personal notes',
                settings:  'System settings',
                profile:   'Your account',
            };

            const tip = document.createElement('div');
            tip.className = 'nav-mini-tip';
            tip.innerHTML = '<b></b><span></span>';
            document.body.appendChild(tip);
            const tipTitle = tip.querySelector('b');
            const tipDesc  = tip.querySelector('span');

            function railIsCollapsed() {
                if (!document.body.classList.contains('sidebar-mini')) return false;
                // Expanded (sticky submenu) → labels are visible already.
                if (sidebar.classList.contains('has-expanded-submenu')) return false;
                if (document.body.classList.contains('sidebar-has-expanded-submenu')) return false;
                return true;
            }

            function showFor(link) {
                if (!railIsCollapsed()) return;
                let title = (link.textContent || '').replace(/\s+/g, ' ').trim();
                let desc = '';
                if (link.classList.contains('nav-link-toggle')) {
                    title = 'Medical Storage';
                    desc = 'Prescriptions, glasses & media';
                } else {
                    const seg = (link.getAttribute('href') || '')
                        .split('?')[0].replace(/\/+$/, '').split('/').pop();
                    desc = DESC[seg] || '';
                }
                if (!title) return;
                tipTitle.textContent = title;
                tipDesc.textContent = desc;
                tipDesc.style.display = desc ? '' : 'none';

                const r = link.getBoundingClientRect();
                tip.style.visibility = 'hidden';
                tip.classList.add('show');
                const isRtl =
                    (document.documentElement.getAttribute('dir') === 'rtl') ||
                    getComputedStyle(document.documentElement).direction === 'rtl';
                tip.classList.toggle('rtl', isRtl);
                const th = tip.offsetHeight;
                const tw = tip.offsetWidth;
                let top = r.top + r.height / 2 - th / 2;
                top = Math.max(8, Math.min(top, window.innerHeight - th - 8));
                tip.style.top = Math.round(top) + 'px';
                // LTR rail is on the left → tip to the icon's right.
                // RTL rail is on the right → tip to the icon's left.
                tip.style.left = isRtl
                    ? Math.round(r.left - tw - 12) + 'px'
                    : Math.round(r.right + 12) + 'px';
                tip.style.visibility = 'visible';
            }
            function hide() { tip.classList.remove('show'); }

            sidebar.querySelectorAll('.nav-link').forEach((link) => {
                link.addEventListener('mouseenter', () => showFor(link));
                link.addEventListener('mouseleave', hide);
                link.addEventListener('click', hide);
            });
            sidebar.addEventListener('scroll', hide, true);
            window.addEventListener('resize', hide);
        })();

        /* Avatar preview in the collapsed mini-rail. The in-sidebar
           .user-avatar-htooltip drops down 200px wide and gets sliced by
           the 76px rail's overflow. Here we show a body-level fixed
           flyout BESIDE the rail instead — same approach as setupMiniTip
           so the avatar behaves like every other mini-rail item. */
        (function setupAvatarMiniPreview() {
            const sidebar = document.getElementById('sidebar');
            const avatar = document.getElementById('sidebarUserAvatar');
            if (!sidebar || !avatar) return;

            const imgEl = avatar.querySelector('.user-avatar-preview-image')
                || avatar.querySelector('.user-avatar-img');
            const imgSrc = imgEl ? (imgEl.getAttribute('src') || '') : '';
            const name = (document.querySelector('.user-details h6')
                || {}).textContent || '';
            const role = (document.querySelector('.user-details small')
                || {}).textContent || '';

            const tip = document.createElement('div');
            tip.className = 'avatar-mini-tip';
            tip.innerHTML =
                (imgSrc
                    ? '<img alt="" src="' + encodeURI(imgSrc) + '">'
                    : '') +
                '<b></b><span></span>';
            document.body.appendChild(tip);
            tip.querySelector('b').textContent = name.trim();
            const roleEl = tip.querySelector('span');
            roleEl.textContent = role.trim();
            roleEl.style.display = role.trim() ? '' : 'none';

            function railIsCollapsed() {
                if (!document.body.classList.contains('sidebar-mini')) return false;
                if (sidebar.classList.contains('has-expanded-submenu')) return false;
                if (document.body.classList.contains('sidebar-has-expanded-submenu')) return false;
                return true;
            }

            function show() {
                if (!railIsCollapsed()) return;
                const r = avatar.getBoundingClientRect();
                tip.style.visibility = 'hidden';
                tip.classList.add('show');
                const isRtl =
                    (document.documentElement.getAttribute('dir') === 'rtl') ||
                    getComputedStyle(document.documentElement).direction === 'rtl';
                tip.classList.toggle('rtl', isRtl);
                const th = tip.offsetHeight;
                const tw = tip.offsetWidth;
                let top = r.top + r.height / 2 - th / 2;
                top = Math.max(8, Math.min(top, window.innerHeight - th - 8));
                tip.style.top = Math.round(top) + 'px';
                tip.style.left = isRtl
                    ? Math.round(r.left - tw - 12) + 'px'
                    : Math.round(r.right + 12) + 'px';
                tip.style.visibility = 'visible';
            }
            function hide() { tip.classList.remove('show'); }

            avatar.addEventListener('mouseenter', show);
            avatar.addEventListener('mouseleave', hide);
            avatar.addEventListener('click', hide);
            sidebar.addEventListener('scroll', hide, true);
            window.addEventListener('resize', hide);
        })();

        // Dock Stack Menu functionality (macOS-style genie effect)
        (function() {
            const medicalStorageDockItem = document.getElementById('medicalStorageDockItem');
            const medicalStorageStackMenu = document.getElementById('medicalStorageStackMenu');
            
            if (medicalStorageDockItem && medicalStorageStackMenu) {
                // Toggle stack menu on click
                medicalStorageDockItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.toggle('active');
                });
                
                // Close stack menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!medicalStorageDockItem.contains(e.target)) {
                        medicalStorageDockItem.classList.remove('active');
                    }
                });
                
                // Close stack menu when clicking on a stack item
                const stackItems = medicalStorageStackMenu.querySelectorAll('.dock-stack-item');
                stackItems.forEach(item => {
                    item.addEventListener('click', function() {
                        medicalStorageDockItem.classList.remove('active');
                    });
                });
                
                // Prevent closing when clicking inside the stack menu
                medicalStorageStackMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        })();
        
        // Top-bar scroll effect
        const topBar = document.querySelector('.top-bar');
        if (topBar) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 0) {
                    topBar.classList.add('scrolled');
                } else {
                    topBar.classList.remove('scrolled');
                }
            });
        }
        
        // Notice bar stays visible at all times (per request): the
        // Next-Appointment / weather / date-time controls must NOT disappear
        // on scroll. Ensure the hide classes are never applied.
        const noticeBar = document.querySelector('.notice-bar');
        if (noticeBar && topBar) {
            noticeBar.classList.remove('scrolled');
            topBar.classList.remove('notice-bar-hidden');
        }

        // Update date and time in notice bar
        function updateNoticeBarDateTime() {
            const dateTimeElement = document.getElementById('noticeBarDateTime');
            if (!dateTimeElement) return;

            const now = new Date();
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            const day = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            
            let hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const period = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            const timeString = `${hours}:${minutes}:${seconds}${period}`;
            
            dateTimeElement.textContent = `${day} ${date} ${month} ${timeString}`;
        }

        // Initialize and update date/time every second
        if (document.getElementById('noticeBarDateTime')) {
            updateNoticeBarDateTime();
            setInterval(updateNoticeBarDateTime, 1000);
        }

        // Weather functions for notice bar
        const noticeBarWeatherIconMap = {
            'clear': 'sun',
            'sunny': 'sun',
            'partly cloudy': 'partly-cloudy',
            'partly sunny': 'partly-cloudy',
            'mostly cloudy': 'cloud',
            'cloudy': 'cloud',
            'overcast': 'cloud',
            'fog': 'mist',
            'mist': 'mist',
            'haze': 'mist',
            'rain': 'rain',
            'light rain': 'rain',
            'heavy rain': 'rain',
            'showers': 'rain',
            'drizzle': 'rain',
            'thunderstorm': 'thunder',
            'thunder': 'thunder',
            'storm': 'thunder',
            'snow': 'snow',
            'light snow': 'snow',
            'heavy snow': 'snow',
            'sleet': 'snow',
            'windy': 'wind',
            'breezy': 'wind'
        };

        function isNoticeBarNightTime() {
            const hour = new Date().getHours();
            return hour < 6 || hour >= 19;
        }

        function getNoticeBarWeatherIconType(condition) {
            const lowerCondition = condition.toLowerCase();
            const isNight = isNoticeBarNightTime();

            for (const [key, value] of Object.entries(noticeBarWeatherIconMap)) {
                if (lowerCondition.includes(key)) {
                    if (isNight) {
                        if (value === 'sun') return 'moon';
                        if (value === 'partly-cloudy') return 'partly-cloudy-night';
                        if (value === 'rain') return 'rain-night';
                        if (value === 'snow') return 'snow-night';
                    }
                    return value;
                }
            }
            return isNight ? 'moon' : 'sun';
        }

        // Render weather icon HTML using SVG animations (same as dashboard)
        function renderNoticeBarWeatherIcon(iconType) {
            const icons = {
                'sun': `<svg class="weather-svg-icon icon-sunny" viewBox="0 0 220 220" style="width:16px;height:16px;">
                    <g class="sunny-short-ray">
                        <path fill="#EDC951" d="M111.961,65.447l-0.014-8.394c-0.003-1.617-1.318-2.927-2.935-2.925c-1.616,0.003-2.924,1.318-2.924,2.935l0.014,8.474C108.064,65.375,110.021,65.354,111.961,65.447z"/>
                        <path fill="#EDC951" d="M75.396,81.343c1.257-1.484,2.607-2.9,4.063-4.221l-5.938-5.918c-1.146-1.142-3-1.14-4.143,0.005c-1.142,1.146-1.139,3.001,0.008,4.142L75.396,81.343z"/>
                        <path fill="#EDC951" d="M163.276,112.648c0.388-0.001,0.756-0.078,1.094-0.213c1.074-0.437,1.83-1.492,1.83-2.721c-0.004-1.617-1.315-2.927-2.933-2.925l-8.478,0.015c0.164,1.96,0.186,3.917,0.091,5.856L163.276,112.648z"/>
                        <path fill="#EDC951" d="M143.207,80.158l5.918-5.937c1.144-1.146,1.14-3-0.005-4.142c-1.147-1.143-3.001-1.14-4.143,0.004l-5.992,6.013C140.471,77.353,141.884,78.704,143.207,80.158z"/>
                        <path fill="#EDC951" d="M56.353,108.382c-1.619,0.002-2.928,1.317-2.924,2.935c0.004,1.615,1.318,2.925,2.934,2.923l8.473-0.014c-0.16-1.963-0.182-3.917-0.088-5.858L56.353,108.382z"/>
                        <path fill="#EDC951" d="M144.234,139.686c-1.258,1.484-2.609,2.899-4.063,4.223l5.939,5.918c0.857,0.855,2.111,1.068,3.167,0.639c0.354-0.143,0.687-0.357,0.974-0.646c1.143-1.145,1.139-3-0.006-4.141L144.234,139.686z"/>
                        <path fill="#EDC951" d="M107.669,155.582l0.013,8.395c0.003,1.617,1.317,2.928,2.934,2.922c0.388,0,0.755-0.074,1.093-0.213c1.077-0.434,1.834-1.488,1.83-2.719l-0.014-8.475C111.564,155.654,109.608,155.676,107.669,155.582z"/>
                        <path fill="#EDC951" d="M76.421,140.871l-5.917,5.938c-1.142,1.144-1.141,2.999,0.006,4.142c0.857,0.855,2.112,1.068,3.17,0.641c0.354-0.144,0.687-0.361,0.972-0.646l5.991-6.012C79.159,143.676,77.743,142.326,76.421,140.871z"/>
                    </g>
                    <g class="sunny-long-ray">
                        <path fill="#EDC951" d="M138.495,51.723c0.936-2.209-0.096-4.761-2.307-5.697c-2.211-0.938-4.763,0.096-5.697,2.306l-7.959,18.792c-0.014,0.034-0.021,0.07-0.035,0.103c2.787,0.818,5.487,1.9,8.064,3.232L138.495,51.723z"/>
                        <path fill="#EDC951" d="M88.124,70.841c0.014,0.031,0.035,0.058,0.051,0.091c1.508-0.822,3.072-1.576,4.703-2.238c1.087-0.44,2.184-0.82,3.283-1.17l-7.639-18.862c-0.901-2.226-3.436-3.298-5.662-2.397c-2.223,0.901-3.299,3.435-2.395,5.66L88.124,70.841z"/>
                        <path fill="#EDC951" d="M47.633,89.838l18.79,7.959c0.033,0.012,0.07,0.021,0.104,0.032c0.818-2.786,1.901-5.485,3.234-8.061l-18.74-7.935c-2.209-0.937-4.761,0.098-5.696,2.308C44.388,86.354,45.423,88.904,47.633,89.838z"/>
                        <path fill="#EDC951" d="M149.397,88.874c0.821,1.508,1.576,3.074,2.236,4.705c0.439,1.088,0.821,2.183,1.171,3.284l18.862-7.638c2.226-0.902,3.299-3.437,2.398-5.661c-0.901-2.224-3.437-3.299-5.661-2.398l-18.916,7.66C149.458,88.837,149.43,88.859,149.397,88.874z"/>
                        <path fill="#EDC951" d="M81.135,169.308c-0.937,2.21,0.097,4.761,2.308,5.696c1.105,0.469,2.295,0.445,3.324,0.027c1.034-0.418,1.905-1.229,2.371-2.334l7.959-18.789c0.016-0.035,0.023-0.071,0.037-0.104c-2.787-0.818-5.488-1.901-8.065-3.233L81.135,169.308z"/>
                        <path fill="#EDC951" d="M131.503,150.19c-0.012-0.033-0.031-0.062-0.047-0.093c-1.508,0.822-3.074,1.574-4.704,2.238c-1.089,0.439-2.185,0.82-3.284,1.17l7.639,18.863c0.901,2.225,3.436,3.297,5.662,2.395c2.223-0.901,3.297-3.434,2.397-5.659L131.503,150.19z"/>
                        <path fill="#EDC951" d="M70.233,132.157c-0.824-1.51-1.578-3.074-2.238-4.707c-0.441-1.085-0.821-2.183-1.171-3.282l-18.862,7.641c-2.225,0.899-3.297,3.436-2.396,5.658c0.9,2.227,3.435,3.299,5.66,2.398l18.914-7.66C70.173,132.191,70.2,132.172,70.233,132.157z"/>
                        <path fill="#EDC951" d="M171.997,131.191l-18.791-7.959c-0.033-0.014-0.068-0.02-0.104-0.033c-0.818,2.786-1.9,5.484-3.234,8.062l18.739,7.936c1.104,0.467,2.295,0.443,3.327,0.025c1.029-0.417,1.902-1.228,2.371-2.334C175.24,134.678,174.207,132.127,171.997,131.191z"/>
                    </g>
                    <g class="sunny-body">
                        <path fill="#EDC951" d="M142.702,97.196c-7.357-18.162-28.043-26.923-46.205-19.568c-18.164,7.356-26.925,28.045-19.568,46.205c7.354,18.165,28.043,26.926,46.205,19.569C141.298,136.045,150.058,115.36,142.702,97.196z M117.348,84.979c-0.411,1.812-2.217,2.948-4.026,2.535c-4.427-1.007-8.997-0.636-13.221,1.075c-5.488,2.224-9.782,6.45-12.091,11.9c-2.308,5.452-2.356,11.475-0.134,16.964c0.697,1.721-0.134,3.684-1.857,4.381c-0.413,0.168-0.841,0.248-1.262,0.248c-1.33,0-2.588-0.795-3.117-2.104c-2.898-7.154-2.836-15.008,0.174-22.113c3.007-7.108,8.605-12.619,15.76-15.516c5.504-2.229,11.469-2.715,17.241-1.398C116.626,81.363,117.762,83.167,117.348,84.979z"/>
                    </g>
                </svg>`,
                'cloud': `<svg class="weather-svg-icon icon-cloudy" viewBox="0 0 220 220" style="width:16px;height:16px;">
                    <g class="small-cloud">
                        <path fill="#00A0B0" d="M69.054,67.463c-5.109-9.405-15.105-15.409-25.866-15.409c-14.947,0-27.066,10.456-29.036,24.651C6.634,78.396,1,85.121,1,93.143c0,9.293,7.561,16.854,16.853,16.854c3.911,0,7.547-1.27,10.472-3.617c4.715,3.022,9.6,4.497,14.864,4.497c4.978,0,8.361-0.792,12.25-2.944c3.312,1.927,7.053,2.944,10.932,2.944c12.016,0,21.792-9.776,21.792-21.792C88.162,77.976,79.807,68.789,69.054,67.463z"/>
                    </g>
                    <g class="cloud-offset">
                        <path fill="var(--weather-card-bg, #1a1d2e)" d="M113.903,179.264c-6.173,0-12.273-1.229-17.931-3.585c-6.062,2.515-12.218,3.585-19.999,3.585c-8.325,0-16.356-1.866-23.959-5.559c-5.329,2.711-11.262,4.119-17.492,4.119c-21.27,0-38.574-17.306-38.574-38.576c0-15.345,9.325-29.175,22.996-35.269c6.653-25.268,29.615-42.96,57.029-42.96c19.873,0,38.259,9.958,49.18,26.313c20.532,5.085,35.406,23.653,35.406,45.276C160.56,158.334,139.63,179.264,113.903,179.264z"/>
                    </g>
                    <g class="main-cloud">
                        <path fill="#00A0B0" d="M118.294,97.231c-8.359-15.388-24.715-25.212-42.32-25.212c-24.457,0-44.283,17.108-47.506,40.333c-12.301,2.767-21.52,13.771-21.52,26.896c0,15.205,12.369,27.576,27.574,27.576c6.396,0,12.348-2.078,17.133-5.917c7.713,4.944,15.705,7.356,24.318,7.356c8.145,0,13.68-1.295,20.043-4.816c5.418,3.152,11.541,4.816,17.887,4.816c19.662,0,35.656-15.996,35.656-35.656C149.56,114.432,135.888,99.401,118.294,97.231z"/>
                    </g>
                </svg>`,
                'rain': `<svg class="weather-svg-icon icon-rainy" viewBox="0 0 220 220" style="width:16px;height:16px;">
                    <g class="rain-drops">
                        <path fill="#00A0B0" d="M69.942,143.08c-0.852,6.32-11.666,18.842-11.666,27.824c0,6.443,5.225,11.664,11.666,11.664c6.443,0,11.666-5.221,11.666-11.664C81.608,161.521,70.696,149.551,69.942,143.08z"/>
                        <path fill="#00A0B0" d="M110.126,143.08c-0.854,6.32-11.666,18.842-11.666,27.824c0,6.443,5.223,11.664,11.666,11.664s11.666-5.221,11.666-11.664C121.792,161.521,110.878,149.551,110.126,143.08z"/>
                        <path fill="#00A0B0" d="M150.308,143.08c-0.854,6.32-11.664,18.842-11.664,27.824c0,6.443,5.223,11.664,11.664,11.664c6.445,0,11.666-5.221,11.666-11.664C161.974,161.521,151.062,149.551,150.308,143.08z"/>
                    </g>
                    <g class="cloud-offset">
                        <path fill="var(--weather-card-bg, #1a1d2e)" d="M144.901,144.943c-6.173,0-12.273-1.229-17.932-3.586c-6.06,2.516-12.216,3.586-19.998,3.586c-8.323,0-16.355-1.867-23.959-5.56c-5.329,2.71-11.261,4.118-17.492,4.118c-21.27,0-38.574-17.305-38.574-38.575c0-15.344,9.324-29.174,22.996-35.267c6.651-25.269,29.613-42.961,57.03-42.961c19.872,0,38.257,9.958,49.177,26.311c20.533,5.087,35.409,23.656,35.409,45.277C191.558,124.014,170.628,144.943,144.901,144.943z"/>
                    </g>
                    <g class="rain-cloud">
                        <path fill="#666" d="M150.288,62.909c-8.357-15.386-24.713-25.209-42.316-25.209c-24.459,0-44.285,17.107-47.506,40.334c-12.301,2.766-21.52,13.77-21.52,26.894c0,15.204,12.369,27.575,27.574,27.575c6.396,0,12.348-2.076,17.133-5.916c7.713,4.943,15.701,7.357,24.318,7.357c8.145,0,13.682-1.295,20.041-4.818c5.42,3.154,11.541,4.818,17.889,4.818c19.66,0,35.656-15.996,35.656-35.656C181.558,80.111,167.886,65.081,150.288,62.909z"/>
                    </g>
                </svg>`,
                'moon': `<svg class="weather-svg-icon icon-moon" viewBox="0 0 100 100" style="width:16px;height:16px;">
                    <defs>
                        <linearGradient id="moonGradNB" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#F5F3CE"/>
                            <stop offset="100%" style="stop-color:#E8E4B8"/>
                        </linearGradient>
                    </defs>
                    <g class="moon-stars">
                        <polygon fill="#F5F3CE" points="67,35 68.5,39.5 73,41 68.5,42.5 67,47 65.5,42.5 61,41 65.5,39.5" class="star star-1"/>
                        <polygon fill="#F5F3CE" points="86,42 87,45 90,46 87,47 86,50 85,47 82,46 85,45" class="star star-2"/>
                        <polygon fill="#F5F3CE" points="80,60 81,63 84,64 81,65 80,68 79,65 76,64 79,63" class="star star-3"/>
                    </g>
                    <g class="moon-body">
                        <path fill="url(#moonGradNB)" d="M35,15 C15,15 0,35 0,55 C0,75 15,95 35,95 C55,95 70,80 70,60 C55,70 35,65 25,50 C20,40 25,25 40,18 C38,16 36,15 35,15 Z"/>
                        <circle fill="#D4D0A0" cx="25" cy="45" r="5" opacity="0.4"/>
                        <circle fill="#D4D0A0" cx="35" cy="65" r="3" opacity="0.3"/>
                        <circle fill="#D4D0A0" cx="20" cy="60" r="2" opacity="0.25"/>
                    </g>
                </svg>`,
                'partly-cloudy': `<svg class="weather-svg-icon icon-partly-cloudy" viewBox="0 0 220 220" style="width:16px;height:16px;">
                    <g class="sunny-short-ray">
                        <path fill="#EDC951" d="M147.961,63.447l-0.014-8.394c-0.003-1.617-1.318-2.927-2.935-2.925c-1.616,0.003-2.924,1.318-2.924,2.935l0.014,8.474C144.064,63.375,146.021,63.354,147.961,63.447z"/>
                        <path fill="#EDC951" d="M111.396,79.343c1.257-1.484,2.607-2.9,4.063-4.221l-5.938-5.918c-1.146-1.142-3-1.14-4.143,0.005c-1.142,1.146-1.139,3.001,0.008,4.142L111.396,79.343z"/>
                        <path fill="#EDC951" d="M199.276,110.648c0.388-0.001,0.756-0.078,1.094-0.213c1.074-0.437,1.83-1.492,1.83-2.721c-0.004-1.617-1.315-2.927-2.933-2.925l-8.478,0.015c0.164,1.96,0.186,3.917,0.091,5.856L199.276,110.648z"/>
                        <path fill="#EDC951" d="M179.207,78.158l5.918-5.937c1.144-1.146,1.14-3-0.005-4.142c-1.147-1.143-3.001-1.14-4.143,0.004l-5.992,6.013C176.471,75.353,177.884,76.704,179.207,78.158z"/>
                    </g>
                    <g class="sunny-long-ray">
                        <path fill="#EDC951" d="M174.495,49.723c0.936-2.209-0.096-4.761-2.307-5.697c-2.211-0.938-4.763,0.096-5.697,2.306l-7.959,18.792c-0.014,0.034-0.021,0.07-0.035,0.103c2.787,0.818,5.487,1.9,8.064,3.232L174.495,49.723z"/>
                        <path fill="#EDC951" d="M124.124,68.841c0.014,0.031,0.035,0.058,0.051,0.091c1.508-0.822,3.072-1.576,4.703-2.238c1.087-0.44,2.184-0.82,3.283-1.17l-7.639-18.862c-0.901-2.226-3.436-3.298-5.662-2.397c-2.223,0.901-3.299,3.435-2.395,5.66L124.124,68.841z"/>
                        <path fill="#EDC951" d="M207.997,129.191l-18.791-7.959c-0.033-0.014-0.068-0.02-0.104-0.033c-0.818,2.786-1.9,5.484-3.234,8.062l18.739,7.936c1.104,0.467,2.295,0.443,3.327,0.025c1.029-0.417,1.902-1.228,2.371-2.334C211.24,132.678,210.207,130.127,207.997,129.191z"/>
                    </g>
                    <g class="sunny-body">
                        <path fill="#EDC951" d="M178.702,95.196c-7.357-18.162-28.043-26.923-46.205-19.568c-18.164,7.356-26.925,28.045-19.568,46.205c7.354,18.165,28.043,26.926,46.205,19.569C177.298,134.045,186.058,113.36,178.702,95.196z M153.348,82.979c-0.411,1.812-2.217,2.948-4.026,2.535c-4.427-1.007-8.997-0.636-13.221,1.075c-5.488,2.224-9.782,6.45-12.091,11.9c-2.308,5.452-2.356,11.475-0.134,16.964c0.697,1.721-0.134,3.684-1.857,4.381c-0.413,0.168-0.841,0.248-1.262,0.248c-1.33,0-2.588-0.795-3.117-2.104c-2.898-7.154-2.836-15.008,0.174-22.113c3.007-7.108,8.605-12.619,15.76-15.516c5.504-2.229,11.469-2.715,17.241-1.398C152.626,79.363,153.762,81.167,153.348,82.979z"/>
                    </g>
                    <g class="cloud-offset">
                        <path fill="var(--weather-card-bg, #1a1d2e)" d="M113.903,179.264c-6.173,0-12.273-1.229-17.931-3.585c-6.062,2.515-12.218,3.585-19.999,3.585c-8.325,0-16.356-1.866-23.959-5.559c-5.329,2.711-11.262,4.119-17.492,4.119c-21.27,0-38.574-17.306-38.574-38.576c0-15.345,9.325-29.175,22.996-35.269c6.653-25.268,29.615-42.96,57.029-42.96c19.873,0,38.259,9.958,49.18,26.313c20.532,5.085,35.406,23.653,35.406,45.276C160.56,158.334,139.63,179.264,113.903,179.264z"/>
                    </g>
                    <g class="main-cloud">
                        <path fill="#00A0B0" d="M118.294,97.231c-8.359-15.388-24.715-25.212-42.32-25.212c-24.457,0-44.283,17.108-47.506,40.333c-12.301,2.767-21.52,13.771-21.52,26.896c0,15.205,12.369,27.576,27.574,27.576c6.396,0,12.348-2.078,17.133-5.917c7.713,4.944,15.705,7.356,24.318,7.356c8.145,0,13.68-1.295,20.043-4.816c5.418,3.152,11.541,4.816,17.887,4.816c19.662,0,35.656-15.996,35.656-35.656C149.56,114.432,135.888,99.401,118.294,97.231z"/>
                    </g>
                </svg>`,
                'partly-cloudy-night': `<svg class="weather-svg-icon icon-partly-cloudy-night" viewBox="0 0 100 100" style="width:16px;height:16px;">
                    <defs>
                        <linearGradient id="moonGrad2NB" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#F5F3CE"/>
                            <stop offset="100%" style="stop-color:#E8E4B8"/>
                        </linearGradient>
                        <linearGradient id="cloudGradNightNB" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#00b4c6"/>
                            <stop offset="100%" style="stop-color:#00A0B0"/>
                        </linearGradient>
                        <linearGradient id="grayCloudGradNB" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#a0a0a0"/>
                            <stop offset="100%" style="stop-color:#808080"/>
                        </linearGradient>
                    </defs>
                    <g class="moon-stars">
                        <polygon fill="#F5F3CE" points="75,15 76.5,19.5 81,21 76.5,22.5 75,27 73.5,22.5 69,21 73.5,19.5" class="star star-1"/>
                        <polygon fill="#F5F3CE" points="90,25 91,28 94,29 91,30 90,33 89,30 86,29 89,28" class="star star-2"/>
                    </g>
                    <g class="moon-body">
                        <path fill="url(#moonGrad2NB)" d="M55,5 C40,5 28,18 28,33 C28,48 40,60 55,60 C60,60 65,58 68,55 C63,58 56,58 50,55 C40,50 35,38 40,28 C43,22 50,18 58,18 C64,18 70,22 72,28 C70,15 63,5 55,5 Z" transform="translate(5,-5)"/>
                    </g>
                    <g class="small-cloud-night">
                        <ellipse fill="url(#grayCloudGradNB)" cx="25" cy="55" rx="18" ry="12"/>
                        <ellipse fill="url(#grayCloudGradNB)" cx="40" cy="52" rx="15" ry="10"/>
                        <ellipse fill="url(#grayCloudGradNB)" cx="32" cy="48" rx="12" ry="8"/>
                    </g>
                    <g class="main-cloud-night">
                        <ellipse fill="url(#cloudGradNightNB)" cx="45" cy="72" rx="25" ry="16"/>
                        <ellipse fill="url(#cloudGradNightNB)" cx="65" cy="68" rx="20" ry="14"/>
                        <ellipse fill="url(#cloudGradNightNB)" cx="55" cy="62" rx="18" ry="12"/>
                        <ellipse fill="url(#cloudGradNightNB)" cx="75" cy="75" rx="15" ry="10"/>
                    </g>
                </svg>`,
                'rain-night': `<svg class="weather-svg-icon icon-rain-night" viewBox="0 0 100 100" style="width:16px;height:16px;">
                    <defs>
                        <linearGradient id="moonGrad3NB" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#F5F3CE"/>
                            <stop offset="100%" style="stop-color:#E8E4B8"/>
                        </linearGradient>
                        <linearGradient id="cloudGradRainNightNB" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#00b4c6"/>
                            <stop offset="100%" style="stop-color:#00A0B0"/>
                        </linearGradient>
                        <linearGradient id="grayCloudGrad2NB" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#888"/>
                            <stop offset="100%" style="stop-color:#666"/>
                        </linearGradient>
                    </defs>
                    <g class="moon-body">
                        <path fill="url(#moonGrad3NB)" d="M55,5 C40,5 28,18 28,33 C28,48 40,60 55,60 C60,60 65,58 68,55 C63,58 56,58 50,55 C40,50 35,38 40,28 C43,22 50,18 58,18 C64,18 70,22 72,28 C70,15 63,5 55,5 Z" transform="translate(5,-5)"/>
                    </g>
                    <g class="small-cloud-night">
                        <ellipse fill="url(#grayCloudGrad2NB)" cx="25" cy="45" rx="18" ry="12"/>
                        <ellipse fill="url(#grayCloudGrad2NB)" cx="40" cy="42" rx="15" ry="10"/>
                        <ellipse fill="url(#grayCloudGrad2NB)" cx="32" cy="38" rx="12" ry="8"/>
                    </g>
                    <g class="rain-drops-night">
                        <path fill="#00A0B0" d="M30,65 Q33,72 30,78 Q27,72 30,65 Z" class="drop drop-1"/>
                        <path fill="#00A0B0" d="M50,65 Q53,72 50,78 Q47,72 50,65 Z" class="drop drop-2"/>
                    </g>
                    <g class="main-cloud-night">
                        <ellipse fill="url(#cloudGradRainNightNB)" cx="45" cy="58" rx="25" ry="16"/>
                        <ellipse fill="url(#cloudGradRainNightNB)" cx="65" cy="54" rx="20" ry="14"/>
                        <ellipse fill="url(#cloudGradRainNightNB)" cx="55" cy="48" rx="18" ry="12"/>
                        <ellipse fill="url(#cloudGradRainNightNB)" cx="75" cy="60" rx="15" ry="10"/>
                    </g>
                </svg>`,
                'snow-night': `<svg class="weather-svg-icon icon-snow-night" viewBox="0 0 100 100" style="width:16px;height:16px;">
                    <defs>
                        <linearGradient id="moonGrad4NB" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#F5F3CE"/>
                            <stop offset="100%" style="stop-color:#E8E4B8"/>
                        </linearGradient>
                        <linearGradient id="cloudGradSnowNightNB" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#00b4c6"/>
                            <stop offset="100%" style="stop-color:#00A0B0"/>
                        </linearGradient>
                        <linearGradient id="grayCloudGrad3NB" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#ccc"/>
                            <stop offset="100%" style="stop-color:#aaa"/>
                        </linearGradient>
                    </defs>
                    <g class="moon-body">
                        <path fill="url(#moonGrad4NB)" d="M55,5 C40,5 28,18 28,33 C28,48 40,60 55,60 C60,60 65,58 68,55 C63,58 56,58 50,55 C40,50 35,38 40,28 C43,22 50,18 58,18 C64,18 70,22 72,28 C70,15 63,5 55,5 Z" transform="translate(5,-5)"/>
                    </g>
                    <g class="small-cloud-night">
                        <ellipse fill="url(#grayCloudGrad3NB)" cx="25" cy="45" rx="18" ry="12"/>
                        <ellipse fill="url(#grayCloudGrad3NB)" cx="40" cy="42" rx="15" ry="10"/>
                        <ellipse fill="url(#grayCloudGrad3NB)" cx="32" cy="38" rx="12" ry="8"/>
                    </g>
                    <g class="snowflakes-night">
                        <g class="snowflake snowflake-1" transform="translate(35, 70)">
                            <line x1="0" y1="-5" x2="0" y2="5" stroke="white" stroke-width="1"/>
                            <line x1="-5" y1="0" x2="5" y2="0" stroke="white" stroke-width="1"/>
                            <line x1="-3.5" y1="-3.5" x2="3.5" y2="3.5" stroke="white" stroke-width="1"/>
                            <line x1="-3.5" y1="3.5" x2="3.5" y2="-3.5" stroke="white" stroke-width="1"/>
                        </g>
                        <g class="snowflake snowflake-2" transform="translate(50, 75)">
                            <line x1="0" y1="-4" x2="0" y2="4" stroke="white" stroke-width="1"/>
                            <line x1="-4" y1="0" x2="4" y2="0" stroke="white" stroke-width="1"/>
                            <line x1="-2.8" y1="-2.8" x2="2.8" y2="2.8" stroke="white" stroke-width="1"/>
                            <line x1="-2.8" y1="2.8" x2="2.8" y2="-2.8" stroke="white" stroke-width="1"/>
                        </g>
                        <g class="snowflake snowflake-3" transform="translate(63, 68)">
                            <line x1="0" y1="-4" x2="0" y2="4" stroke="white" stroke-width="1"/>
                            <line x1="-4" y1="0" x2="4" y2="0" stroke="white" stroke-width="1"/>
                            <line x1="-2.8" y1="-2.8" x2="2.8" y2="2.8" stroke="white" stroke-width="1"/>
                            <line x1="-2.8" y1="2.8" x2="2.8" y2="-2.8" stroke="white" stroke-width="1"/>
                        </g>
                    </g>
                    <g class="main-cloud-night">
                        <ellipse fill="url(#cloudGradSnowNightNB)" cx="45" cy="58" rx="25" ry="16"/>
                        <ellipse fill="url(#cloudGradSnowNightNB)" cx="65" cy="54" rx="20" ry="14"/>
                        <ellipse fill="url(#cloudGradSnowNightNB)" cx="55" cy="48" rx="18" ry="12"/>
                        <ellipse fill="url(#cloudGradSnowNightNB)" cx="75" cy="60" rx="15" ry="10"/>
                    </g>
                </svg>`,
                'thunder': `<svg class="weather-svg-icon icon-rainy icon-thunder" viewBox="0 0 220 220" style="width:16px;height:16px;">
                    <g class="rain-drops">
                        <path fill="#00A0B0" d="M69.942,143.08c-0.852,6.32-11.666,18.842-11.666,27.824c0,6.443,5.225,11.664,11.666,11.664c6.443,0,11.666-5.221,11.666-11.664C81.608,161.521,70.696,149.551,69.942,143.08z"/>
                        <path fill="#00A0B0" d="M150.308,143.08c-0.854,6.32-11.664,18.842-11.664,27.824c0,6.443,5.223,11.664,11.664,11.664c6.445,0,11.666-5.221,11.666-11.664C161.974,161.521,151.062,149.551,150.308,143.08z"/>
                    </g>
                    <g class="lightning-bolt">
                        <path fill="#EDC951" d="M115,140l-8,25h12l-6,22l20-28h-14l10-19z"/>
                    </g>
                    <g class="cloud-offset">
                        <path fill="var(--weather-card-bg, #1a1d2e)" d="M144.901,144.943c-6.173,0-12.273-1.229-17.932-3.586c-6.06,2.516-12.216,3.586-19.998,3.586c-8.323,0-16.355-1.867-23.959-5.56c-5.329,2.71-11.261,4.118-17.492,4.118c-21.27,0-38.574-17.305-38.574-38.575c0-15.344,9.324-29.174,22.996-35.267c6.651-25.269,29.613-42.961,57.03-42.961c19.872,0,38.257,9.958,49.177,26.311c20.533,5.087,35.409,23.656,35.409,45.277C191.558,124.014,170.628,144.943,144.901,144.943z"/>
                    </g>
                    <g class="rain-cloud thunder-cloud">
                        <path fill="#555" d="M150.288,62.909c-8.357-15.386-24.713-25.209-42.316-25.209c-24.459,0-44.285,17.107-47.506,40.334c-12.301,2.766-21.52,13.77-21.52,26.894c0,15.204,12.369,27.575,27.574,27.575c6.396,0,12.348-2.076,17.133-5.916c7.713,4.943,15.701,7.357,24.318,7.357c8.145,0,13.682-1.295,20.041-4.818c5.42,3.154,11.541,4.818,17.889,4.818c19.66,0,35.656-15.996,35.656-35.656C181.558,80.111,167.886,65.081,150.288,62.909z"/>
                    </g>
                </svg>`,
                'snow': `<svg class="weather-svg-icon icon-snowy" viewBox="0 0 220 220" style="width:16px;height:16px;">
                    <g class="snowflakes">
                        <path fill="#CCC" d="M84.535,166.239l-5.663,1.73l-3.644-2.104c0.089-0.392,0.141-0.798,0.141-1.218c0-0.418-0.052-0.824-0.141-1.216l3.645-2.104l5.662,1.729c0.156,0.048,0.314,0.071,0.47,0.071c0.688,0,1.324-0.445,1.536-1.138c0.26-0.849-0.218-1.747-1.067-2.006l-2.795-0.854l1.482-0.856c0.769-0.443,1.032-1.426,0.588-2.194s-1.426-1.032-2.195-0.589l-1.483,0.856l0.658-2.848c0.2-0.865-0.339-1.728-1.204-1.928c-0.865-0.2-1.728,0.339-1.927,1.204l-1.333,5.769l-3.648,2.106c-0.595-0.553-1.309-0.979-2.104-1.224v-4.204l4.33-4.039c0.649-0.605,0.685-1.621,0.079-2.271c-0.605-0.648-1.622-0.685-2.271-0.078l-2.138,1.993v-1.712c0-0.888-0.72-1.607-1.606-1.607c-0.888,0-1.607,0.72-1.607,1.607v1.712l-2.138-1.993c-0.648-0.606-1.666-0.57-2.271,0.078c-0.605,0.649-0.57,1.665,0.079,2.271l4.33,4.039v4.204c-0.795,0.245-1.509,0.67-2.104,1.224l-3.649-2.106l-1.332-5.77c-0.2-0.864-1.062-1.403-1.927-1.203c-0.865,0.199-1.403,1.063-1.204,1.927l0.658,2.849l-1.483-0.856c-0.769-0.443-1.752-0.18-2.195,0.589c-0.444,0.768-0.18,1.751,0.588,2.194l1.483,0.856l-2.796,0.854c-0.849,0.26-1.326,1.158-1.067,2.006c0.212,0.693,0.848,1.139,1.537,1.139c0.155,0,0.313-0.023,0.47-0.071l5.662-1.729l3.645,2.104c-0.09,0.393-0.142,0.798-0.142,1.217s0.052,0.825,0.142,1.218l-3.646,2.104l-5.662-1.73c-0.848-0.259-1.747,0.218-2.006,1.067c-0.259,0.849,0.219,1.746,1.067,2.006l2.796,0.854l-1.483,0.856c-0.769,0.443-1.032,1.427-0.588,2.195c0.298,0.515,0.838,0.804,1.393,0.804c0.273,0,0.549-0.07,0.802-0.216l1.483-0.856l-0.658,2.849c-0.2,0.864,0.339,1.728,1.204,1.927c0.121,0.028,0.243,0.042,0.362,0.042c0.731,0,1.393-0.503,1.564-1.245l1.333-5.769l3.649-2.107c0.595,0.553,1.31,0.979,2.104,1.224v4.204l-4.329,4.039c-0.649,0.604-0.685,1.622-0.079,2.271c0.605,0.649,1.623,0.685,2.271,0.079l2.137-1.994v1.712c0,0.888,0.72,1.607,1.606,1.607c0.887,0,1.607-0.72,1.607-1.607v-1.712l2.138,1.994c0.31,0.289,0.703,0.432,1.095,0.432c0.431,0,0.859-0.171,1.176-0.511c0.605-0.648,0.57-1.666-0.079-2.271l-4.33-4.039v-4.204c0.795-0.245,1.509-0.671,2.104-1.224l3.649,2.107l1.333,5.769c0.171,0.743,0.833,1.245,1.564,1.245c0.12,0,0.241-0.014,0.362-0.042c0.865-0.199,1.404-1.063,1.205-1.927l-0.658-2.849l1.482,0.856c0.253,0.146,0.529,0.216,0.802,0.216c0.556,0,1.096-0.288,1.393-0.804c0.444-0.769,0.181-1.751-0.588-2.194l-1.483-0.857l2.796-0.854c0.849-0.259,1.327-1.157,1.067-2.006C86.281,166.457,85.382,165.979,84.535,166.239z M69.906,167.54c-1.594,0-2.892-1.297-2.892-2.893c0-1.594,1.297-2.892,2.892-2.892c1.595,0,2.893,1.298,2.893,2.892C72.798,166.243,71.501,167.54,69.906,167.54z"/>
                    </g>
                    <g class="cloud-offset">
                        <path fill="var(--weather-card-bg, #1a1d2e)" d="M144.979,144.945c-6.177,0-12.277-1.229-17.934-3.585c-6.06,2.515-12.216,3.585-19.997,3.585c-8.326,0-16.357-1.866-23.96-5.56c-5.329,2.71-11.261,4.118-17.491,4.118c-21.271,0-38.576-17.305-38.576-38.575c0-15.344,9.325-29.173,22.996-35.267c6.651-25.269,29.614-42.96,57.032-42.96c19.87,0,38.255,9.958,49.176,26.31c20.533,5.087,35.41,23.656,35.41,45.278C191.635,124.016,170.705,144.945,144.979,144.945z"/>
                    </g>
                    <g class="snow-cloud">
                        <path fill="#CCC" d="M149.365,62.911c-8.359-15.386-24.712-25.209-42.316-25.209c-24.461,0-44.287,17.107-47.508,40.333c-12.299,2.766-21.52,13.77-21.52,26.894c0,15.206,12.369,27.575,27.576,27.575c6.395,0,12.346-2.076,17.133-5.916c7.713,4.945,15.701,7.357,24.318,7.357c8.141,0,13.678-1.293,20.041-4.818c5.419,3.156,11.542,4.818,17.89,4.818c19.658,0,35.655-15.994,35.655-35.656C180.635,80.114,166.961,65.083,149.365,62.911z"/>
                    </g>
                </svg>`,
                'mist': `<svg class="weather-svg-icon icon-windy" viewBox="0 0 220 220" style="width:16px;height:16px;">
                    <g class="small-cloud">
                        <path fill="#00A0B0" d="M69.054,67.463c-5.109-9.405-15.105-15.409-25.866-15.409c-14.947,0-27.066,10.456-29.036,24.651C6.634,78.396,1,85.121,1,93.143c0,9.293,7.561,16.854,16.853,16.854c3.911,0,7.547-1.27,10.472-3.617c4.715,3.022,9.6,4.497,14.864,4.497c4.978,0,8.361-0.792,12.25-2.944c3.312,1.927,7.053,2.944,10.932,2.944c12.016,0,21.792-9.776,21.792-21.792C88.162,77.976,79.807,68.789,69.054,67.463z"/>
                    </g>
                    <g class="cloud-offset">
                        <path fill="var(--weather-card-bg, #1a1d2e)" d="M113.903,179.264c-6.173,0-12.273-1.229-17.931-3.585c-6.062,2.515-12.218,3.585-19.999,3.585c-8.325,0-16.356-1.866-23.959-5.559c-5.329,2.711-11.262,4.119-17.492,4.119c-21.27,0-38.574-17.306-38.574-38.576c0-15.345,9.325-29.175,22.996-35.269c6.653-25.268,29.615-42.96,57.029-42.96c19.873,0,38.259,9.958,49.18,26.313c20.532,5.085,35.406,23.653,35.406,45.276C160.56,158.334,139.63,179.264,113.903,179.264z"/>
                    </g>
                    <g class="main-cloud">
                        <path fill="#00A0B0" d="M118.294,97.231c-8.359-15.388-24.715-25.212-42.32-25.212c-24.457,0-44.283,17.108-47.506,40.333c-12.301,2.767-21.52,13.771-21.52,26.896c0,15.205,12.369,27.576,27.574,27.576c6.396,0,12.348-2.078,17.133-5.917c7.713,4.944,15.705,7.356,24.318,7.356c8.145,0,13.68-1.295,20.043-4.816c5.418,3.152,11.541,4.816,17.887,4.816c19.662,0,35.656-15.996,35.656-35.656C149.56,114.432,135.888,99.401,118.294,97.231z"/>
                    </g>
                    <g class="wind-string">
                        <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M85.263,105.176c3.002-1.646,6.403-2.549,9.903-2.549c11.375,0,20.633,9.256,20.633,20.633s-9.258,20.633-20.633,20.633H3.473"/>
                        <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M69.756,113.884c1.62-0.888,3.457-1.376,5.345-1.376c6.14,0,11.136,4.996,11.136,11.137c0,6.14-4.996,11.136-11.136,11.136H25.313"/>
                        <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M75.536,180.462c2.131,1.166,4.545,1.809,7.027,1.809c8.072,0,14.642-6.569,14.642-14.643s-6.569-14.643-14.642-14.643H18.043"/>
                    </g>
                </svg>`
            };
            return icons[iconType] || icons['sun'];
        }

        async function fetchNoticeBarWeatherData(latitude, longitude, saveToStorage = true) {
            try {
                const response = await fetch(`/api/weather?lat=${latitude}&lon=${longitude}`);
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || `Weather API error: ${response.status}`);
                }
                const data = await response.json();

                if (data.success && data.weather) {
                    updateNoticeBarWeather(data.weather);
                    
                    if (saveToStorage) {
                        const weatherData = {
                            data: data.weather,
                            latitude: latitude,
                            longitude: longitude,
                            timestamp: Date.now()
                        };
                        localStorage.setItem('noticebar_weather_data', JSON.stringify(weatherData));
                    }
                } else {
                    throw new Error(data.error || data.message || 'Failed to get weather data');
                }
            } catch (error) {
                const tempElement = document.getElementById('noticeBarWeatherTemp');
                if (tempElement) tempElement.textContent = '--°C';
            }
        }

        function loadNoticeBarWeatherFromStorage() {
            try {
                const stored = localStorage.getItem('noticebar_weather_data');
                if (!stored) return null;
                
                const weatherData = JSON.parse(stored);
                const now = Date.now();
                const age = now - weatherData.timestamp;
                const maxAge = 15 * 60 * 1000; // 15 minutes
                
                if (age < maxAge && weatherData.data) {
                    return weatherData;
                }
                
                localStorage.removeItem('noticebar_weather_data');
                return null;
            } catch (error) {
                localStorage.removeItem('noticebar_weather_data');
                return null;
            }
        }

        function updateNoticeBarWeather(weatherData) {
            const iconContainer = document.getElementById('noticeBarWeatherIcon');
            const tempElement = document.getElementById('noticeBarWeatherTemp');

            if (!iconContainer) return;

            const iconType = getNoticeBarWeatherIconType(weatherData.condition || 'clear');
            iconContainer.innerHTML = renderNoticeBarWeatherIcon(iconType);

            if (tempElement) {
                tempElement.textContent = `${Math.round(weatherData.temperature || 0)}°C`;
            }

            // Update warning icons based on pollen and dry eye risk
            updateWeatherWarnings(weatherData);
        }

        function updateWeatherWarnings(weatherData) {
            const weatherWarning = document.getElementById('noticeBarWeatherWarning');
            const pollenIcon = document.getElementById('warningPollenIcon');
            const dryEyeIcon = document.getElementById('warningDryEyeIcon');

            if (!weatherWarning) return;

            if (!weatherData) {
                weatherWarning.style.display = 'none';
                if (pollenIcon) pollenIcon.style.display = 'none';
                if (dryEyeIcon) dryEyeIcon.style.display = 'none';
                return;
            }

            // Calculate pollen index and dry eye risk
            let pollenIndex, dryEyeRisk;
            if (typeof calculatePollenIndex !== 'undefined' && typeof calculateDryEyeRisk !== 'undefined') {
                pollenIndex = calculatePollenIndex(weatherData);
                dryEyeRisk = calculateDryEyeRisk(weatherData);
            } else {
                pollenIndex = calculateWeatherModalPollenIndex(weatherData);
                dryEyeRisk = calculateWeatherModalDryEyeRisk(weatherData);
            }

            // Get level class - threshold is > 75 (high or very-high)
            const getLevelClass = (score) => {
                if (score <= 25) return 'low';
                if (score <= 50) return 'moderate';
                if (score <= 75) return 'high';
                return 'very-high';
            };

            const pollenLevel = getLevelClass(pollenIndex);
            const dryEyeLevel = getLevelClass(dryEyeRisk);

            const pollenIsHigh = pollenLevel === 'high' || pollenLevel === 'very-high';
            const dryEyeIsHigh = dryEyeLevel === 'high' || dryEyeLevel === 'very-high';

            // Show warning if at least one is high
            if (pollenIsHigh || dryEyeIsHigh) {
                weatherWarning.style.display = 'inline-flex';
                
                // Show icons based on which ones are high
                if (pollenIcon) {
                    pollenIcon.style.display = pollenIsHigh ? 'inline-block' : 'none';
                }
                if (dryEyeIcon) {
                    dryEyeIcon.style.display = dryEyeIsHigh ? 'inline-block' : 'none';
                }
            } else {
                weatherWarning.style.display = 'none';
                if (pollenIcon) pollenIcon.style.display = 'none';
                if (dryEyeIcon) dryEyeIcon.style.display = 'none';
            }
        }

        function initNoticeBarWeather(useCached = false) {
            const iconContainer = document.getElementById('noticeBarWeatherIcon');
            if (!iconContainer) return;

            const DEFAULT_LAT = 31.1117;
            const DEFAULT_LON = 30.9397;

            // Try to load from cache first
            if (useCached) {
                const cachedData = loadNoticeBarWeatherFromStorage();
                if (cachedData && cachedData.data) {
                    updateNoticeBarWeather(cachedData.data);
                }
            }

            // Use default coordinates - geolocation must only be requested in response to user gesture
            if (!useCached) {
                fetchNoticeBarWeatherData(DEFAULT_LAT, DEFAULT_LON);
            }
        }

        // Load next appointment for notice bar (including next day appointments)
        function loadNextAppointment() {
            const appointmentContainer = document.getElementById('noticeBarNextAppointment');
            if (!appointmentContainer) return;

            // Get upcoming appointments - fetch multiple for slider
            fetch('/api/upcoming-appointments?page=1&per_page=10')
                .then(response => response.json())
                .then(data => {
                    if (data.ok && data.data.items && data.data.items.length > 0) {
                        const appointments = data.data.items;
                        
                        // Format each appointment
                        const appointmentItems = appointments.map(appointment => {
                            const patientName = `${appointment.first_name || ''} ${appointment.last_name || ''}`.trim() || 'Unknown';
                            
                            // Format time (convert 24h to 12h format)
                            let timeStr = '';
                            if (appointment.start_time) {
                                const [hours, minutes] = appointment.start_time.split(':');
                                const hour24 = parseInt(hours);
                                const hour12 = hour24 === 0 ? 12 : (hour24 > 12 ? hour24 - 12 : hour24);
                                const period = hour24 >= 12 ? 'PM' : 'AM';
                                timeStr = `${hour12}:${minutes}${period}`;
                            }

                            // Format date - handle today, tomorrow, and future dates
                            let dateStr = '';
                            if (appointment.date) {
                                const appointmentDate = new Date(appointment.date + 'T00:00:00');
                                const today = new Date();
                                today.setHours(0, 0, 0, 0);
                                const tomorrow = new Date(today);
                                tomorrow.setDate(tomorrow.getDate() + 1);
                                
                                const appointmentDateOnly = new Date(appointmentDate);
                                appointmentDateOnly.setHours(0, 0, 0, 0);
                                
                                if (appointmentDateOnly.getTime() === today.getTime()) {
                                    dateStr = 'Today';
                                } else if (appointmentDateOnly.getTime() === tomorrow.getTime()) {
                                    dateStr = 'Tomorrow';
                                } else {
                                    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                    dateStr = `${days[appointmentDateOnly.getDay()]}, ${months[appointmentDateOnly.getMonth()]} ${appointmentDateOnly.getDate()}`;
                                }
                            }

                            return `<p><span class="appointment-patient-name">${escapeHtml(patientName)}</span> <span class="appointment-time">${dateStr} ${timeStr}</span></p>`;
                        });

                        // Store original count before duplication
                        const originalCount = appointmentItems.length;
                        
                        // If only one appointment, duplicate it for smooth loop
                        if (appointmentItems.length === 1) {
                            appointmentItems.push(appointmentItems[0]);
                        }

                        // Set innerHTML with all appointments
                        appointmentContainer.innerHTML = appointmentItems.join('');
                        
                        // Remove any existing data-items attribute first
                        appointmentContainer.removeAttribute('data-items');
                        // Re-enable animation (remove inline style that might have disabled it)
                        appointmentContainer.style.animation = '';
                        
                        // Set data attribute for dynamic animation
                        if (originalCount === 1) {
                            // No animation for single item (even if duplicated for display)
                            appointmentContainer.setAttribute('data-items', '1');
                        } else if (appointmentItems.length <= 4) {
                            // Use predefined animations for 2-4 items
                            appointmentContainer.setAttribute('data-items', appointmentItems.length.toString());
                        } else {
                            // Use dynamic animation for more than 4 items
                            const duration = appointmentItems.length * 3; // 3 seconds per item
                            createDynamicAppointmentAnimation(appointmentItems.length, duration);
                            appointmentContainer.setAttribute('data-items', appointmentItems.length.toString());
                        }
                    } else {
                        // No appointments - disable animation
                        appointmentContainer.innerHTML = '<p class="no-appointment">No upcoming appointments</p>';
                        // Remove data-items attribute to disable animation
                        appointmentContainer.removeAttribute('data-items');
                        // Remove animation style
                        appointmentContainer.style.animation = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading next appointment:', error);
                    const appointmentContainer = document.getElementById('noticeBarNextAppointment');
                    if (appointmentContainer) {
                        appointmentContainer.innerHTML = '<p class="no-appointment">Error loading appointments</p>';
                        // Remove data-items attribute to disable animation
                        appointmentContainer.removeAttribute('data-items');
                        // Remove animation style
                        appointmentContainer.style.animation = 'none';
                    }
                });
        }

        // Function to create dynamic animation for more than 4 items
        function createDynamicAppointmentAnimation(itemCount, duration) {
            const styleId = 'dynamic-appointment-animation';
            let styleElement = document.getElementById(styleId);
            
            if (!styleElement) {
                styleElement = document.createElement('style');
                styleElement.id = styleId;
                document.head.appendChild(styleElement);
            }
            
            // Calculate keyframe percentages
            const pausePercent = 100 / (itemCount * 2); // Pause time between transitions
            const transitionPercent = pausePercent * 0.3; // Transition time
            
            let keyframes = '@keyframes appointment-scroll-dynamic {\n';
            keyframes += '  0% { margin-top: 0; }\n';
            
            for (let i = 0; i < itemCount; i++) {
                const startPercent = i * pausePercent * 2;
                const endPercent = startPercent + pausePercent;
                const nextStartPercent = endPercent + transitionPercent;
                const nextEndPercent = nextStartPercent + pausePercent;
                
                keyframes += `  ${startPercent.toFixed(2)}% { margin-top: ${-i * 1.5}rem; }\n`;
                keyframes += `  ${endPercent.toFixed(2)}% { margin-top: ${-i * 1.5}rem; }\n`;
                
                if (i < itemCount - 1) {
                    keyframes += `  ${nextStartPercent.toFixed(2)}% { margin-top: ${-(i + 1) * 1.5}rem; }\n`;
                    keyframes += `  ${nextEndPercent.toFixed(2)}% { margin-top: ${-(i + 1) * 1.5}rem; }\n`;
                }
            }
            
            keyframes += `  100% { margin-top: 0; }\n`;
            keyframes += '}\n';
            
            keyframes += `.appointment-inner[data-items="${itemCount}"] {\n`;
            keyframes += `  animation: appointment-scroll-dynamic ${duration}s normal infinite running;\n`;
            keyframes += `  --animation-duration: ${duration}s;\n`;
            keyframes += '}\n';
            
            styleElement.textContent = keyframes;
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize weather - load from localStorage first, then try to update
        function initNoticeBarWeatherSimple() {
            const iconContainer = document.getElementById('noticeBarWeatherIcon');
            if (!iconContainer) return;

            // Always try to load from localStorage first
            const cachedData = loadNoticeBarWeatherFromStorage();
            if (cachedData && cachedData.data) {
                updateNoticeBarWeather(cachedData.data);
            } else {
                // Hide warnings if no data
                updateWeatherWarnings(null);
            }

            // Use default coordinates - geolocation must only be requested in response to user gesture
            // (browsers raise [Violation] otherwise). Use DEFAULT_LAT/LON from config.
            const DEFAULT_LAT = 31.1117;
            const DEFAULT_LON = 30.9397;
            fetchNoticeBarWeatherData(DEFAULT_LAT, DEFAULT_LON);
        }

        // Initialize weather — render from cache instantly (no network), then do the
        // real refresh only once the page is idle. `/api/weather` triggers a server-side
        // external call that can be slow on a cold cache; deferring it keeps it from
        // blocking the rest of the page's requests (notably the dashboard data loads).
        (function () {
            const cached = loadNoticeBarWeatherFromStorage();
            if (cached && cached.data) {
                updateNoticeBarWeather(cached.data);
            } else {
                updateWeatherWarnings(null);
            }
            const DEFAULT_LAT = 31.1117, DEFAULT_LON = 30.9397;
            const refresh = () => fetchNoticeBarWeatherData(DEFAULT_LAT, DEFAULT_LON);
            window.addEventListener('load', () => {
                if ('requestIdleCallback' in window) {
                    requestIdleCallback(refresh, { timeout: 5000 });
                } else {
                    setTimeout(refresh, 2500);
                }
            });
            // Refresh weather every 15 minutes
            setInterval(refresh, 15 * 60 * 1000);
        })();

        // Initialize next appointment on page load
        if (document.getElementById('noticeBarNextAppointment')) {
            loadNextAppointment();
            // Refresh every 5 minutes
            setInterval(loadNextAppointment, 5 * 60 * 1000);
        }

        // Listen for appointment added event to update carousel
        window.addEventListener('appointmentAdded', () => {
            if (document.getElementById('noticeBarNextAppointment')) {
                loadNextAppointment();
            }
        });

        // Listen for appointment deleted event to update carousel
        window.addEventListener('appointmentDeleted', () => {
            if (document.getElementById('noticeBarNextAppointment')) {
                loadNextAppointment();
            }
        });

        // Clock and Calendar Popover
        let clockCalendarPopover = null;
        const noticeBarDateTimeColumn = document.querySelector('.notice-bar-column-1');
        
        function createClockCalendarPopover() {
            // Remove existing popover if any
            if (clockCalendarPopover) {
                clockCalendarPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'clock-calendar-popover';
            popover.id = 'clockCalendarPopover';
            
            // Working-hours arc (clinic day = 14:00–23:00, i.e. 2 → 11 on the dial).
            // Angle for hour H on a 12h dial (12 at top): (H*30 - 90) deg.
            const workArcPath = (() => {
                const R = 122, cx = 160, cy = 160;
                const pt = (h) => {
                    const a = (h * 30 - 90) * Math.PI / 180;
                    return [cx + R * Math.cos(a), cy + R * Math.sin(a)];
                };
                const [sx, sy] = pt(2);   // 2 PM
                const [ex, ey] = pt(11);  // 11 PM
                // 2→11 clockwise spans 270° → large-arc-flag=1, sweep-flag=1
                return `M ${sx.toFixed(1)} ${sy.toFixed(1)} A ${R} ${R} 0 1 1 ${ex.toFixed(1)} ${ey.toFixed(1)}`;
            })();

            // Hour numbers (12 at top, clockwise) at radius 108
            const hourNumbers = Array.from({length: 12}, (_, i) => {
                const num = i === 0 ? 12 : i;
                const angle = (i * 30 - 90) * Math.PI / 180;
                const x = 160 + 108 * Math.cos(angle);
                const y = 160 + 108 * Math.sin(angle);
                return `<text class="cf-num" x="${x.toFixed(1)}" y="${y.toFixed(1)}" text-anchor="middle" dominant-baseline="central">${num}</text>`;
            }).join('');

            popover.innerHTML = `
                <button type="button" class="clock-popover-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                <div class="clock-calendar-popover-content">
                    <div class="clock-calendar-column clock-column">
                        <div class="clock">
                            <svg class="clock-face" viewBox="0 0 320 320" xmlns="http://www.w3.org/2000/svg">
                                <!-- White dial face -->
                                <circle class="cf-bg" cx="160" cy="160" r="152"/>
                                <circle class="cf-ring" cx="160" cy="160" r="150"/>
                                <!-- Hour markers -->
                                ${Array.from({length: 12}, (_, i) => {
                                    const angle = (i * 30 - 90) * Math.PI / 180;
                                    const x1 = 160 + 130 * Math.cos(angle);
                                    const y1 = 160 + 130 * Math.sin(angle);
                                    const x2 = 160 + 145 * Math.cos(angle);
                                    const y2 = 160 + 145 * Math.sin(angle);
                                    return `<line class="cf-tick-h" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"/>`;
                                }).join('')}
                                <!-- Minute markers -->
                                ${Array.from({length: 60}, (_, i) => {
                                    if (i % 5 !== 0) {
                                        const angle = (i * 6 - 90) * Math.PI / 180;
                                        const x1 = 160 + 135 * Math.cos(angle);
                                        const y1 = 160 + 135 * Math.sin(angle);
                                        const x2 = 160 + 142 * Math.cos(angle);
                                        const y2 = 160 + 142 * Math.sin(angle);
                                        return `<line class="cf-tick-m" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"/>`;
                                    }
                                    return '';
                                }).join('')}
                                <!-- Hour numbers -->
                                ${hourNumbers}
                                <!-- Working-hours arc (2 PM – 11 PM) -->
                                <path class="work-arc" d="${workArcPath}"/>
                                <!-- Center dot -->
                                <circle class="cf-center" cx="160" cy="160" r="8"/>
                            </svg>
                            <div class="hour hand" id="clockHour"></div>
                            <div class="minute hand" id="clockMinute"></div>
                            <div class="seconds hand" id="clockSeconds"></div>
                        </div>
                        <div class="clock-extras">
                            <div class="clock-digital">
                                <span class="clock-digital-time" id="clockDigitalTime">--:--:--</span>
                                <span class="clock-digital-date" id="clockDigitalDate"></span>
                            </div>
                            <div class="clock-stats" id="clockStats">
                                <div class="clock-stat"><span class="clock-stat-num" id="clockStatTotal">–</span><span class="clock-stat-label">Today</span></div>
                                <div class="clock-stat is-done"><span class="clock-stat-num" id="clockStatDone">–</span><span class="clock-stat-label">Done</span></div>
                                <div class="clock-stat is-left"><span class="clock-stat-num" id="clockStatLeft">–</span><span class="clock-stat-label">Left</span></div>
                            </div>
                            <div class="clock-next is-empty" id="clockNext">
                                <i class="bi bi-hourglass-split"></i>
                                <span class="clock-next-text">Loading…</span>
                            </div>
                        </div>
                    </div>
                    <div class="clock-calendar-column calendar-column">
                        <div class="calendar-container">
                            <header class="calendar-header">
                                <p class="calendar-current-date"></p>
                                <div class="calendar-navigation">
                                    <span id="calendar-prev" class="bi bi-chevron-left"></span>
                                    <span id="calendar-next" class="bi bi-chevron-right"></span>
                                </div>
                            </header>
                            <div class="calendar-body">
                                <ul class="calendar-weekdays">
                                    <li>Sun</li>
                                    <li>Mon</li>
                                    <li>Tue</li>
                                    <li>Wed</li>
                                    <li>Thu</li>
                                    <li>Fri</li>
                                    <li>Sat</li>
                                </ul>
                                <ul class="calendar-dates"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            clockCalendarPopover = popover;
            clockCalendarPopover._intervals = [];

            // Close button (esp. for mobile, where the popover is tall and the
            // backdrop is hard to tap).
            popover.querySelector('.clock-popover-close')?.addEventListener('click', closeClockCalendarPopover);

            // Initialize clock
            initAnalogClock();
            initDigitalClock();

            // Initialize calendar (renders the grid; appointment dots/counts fill in async)
            initCalendar();

            // Load appointment data: next-appt countdown + today's stats + calendar dots
            initClockData();

            // Position popover
            positionClockCalendarPopover();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'clock-calendar-popover-backdrop';
            backdrop.addEventListener('click', closeClockCalendarPopover);
            document.body.appendChild(backdrop);
        }

        function positionClockCalendarPopover() {
            if (!clockCalendarPopover) return;
            
            const noticeBar = document.querySelector('.notice-bar');
            if (!noticeBar) return;
            
            const rect = noticeBar.getBoundingClientRect();
            const popoverRect = clockCalendarPopover.getBoundingClientRect();
            
            // Position at top center of notice bar
            clockCalendarPopover.style.top = (rect.bottom + 10) + 'px';
            clockCalendarPopover.style.left = '50%';
            clockCalendarPopover.style.transform = 'translateX(-50%)';
        }

        function closeClockCalendarPopover() {
            // Resolve from the DOM too, so a stale variable can never wedge the toggle.
            const el = clockCalendarPopover || document.getElementById('clockCalendarPopover');
            if (el) {
                // Clear all timers started for this popover (clock, digital, countdown)
                if (Array.isArray(el._intervals)) {
                    el._intervals.forEach(id => clearInterval(id));
                }
                el.remove();
            }
            clockCalendarPopover = null;
            const backdrop = document.querySelector('.clock-calendar-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            const tip = document.getElementById('calDayTooltip');
            if (tip) tip.remove();
        }

        function initAnalogClock() {
            const hour = document.getElementById('clockHour');
            const minute = document.getElementById('clockMinute');
            const seconds = document.getElementById('clockSeconds');

            if (!hour || !minute || !seconds) return;

            function updateClock() {
                const date_now = new Date();
                const hr = date_now.getHours();
                const min = date_now.getMinutes();
                const sec = date_now.getSeconds();

                const calc_hr = (hr * 30) + (min / 2);
                const calc_min = (min * 6);
                const calc_sec = sec * 6;

                hour.style.transform = "rotate(" + calc_hr + "deg)";
                minute.style.transform = "rotate(" + calc_min + "deg)";
                seconds.style.transform = "rotate(" + calc_sec + "deg)";
            }

            updateClock();
            const clockInterval = setInterval(updateClock, 1000);
            if (clockCalendarPopover && clockCalendarPopover._intervals) {
                clockCalendarPopover._intervals.push(clockInterval);
            }
        }

        // Digital time + date readout under the analog clock
        function initDigitalClock() {
            const timeEl = document.getElementById('clockDigitalTime');
            const dateEl = document.getElementById('clockDigitalDate');
            if (!timeEl) return;

            const tick = () => {
                const now = new Date();
                let h = now.getHours();
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                timeEl.innerHTML = `${h}:${m}:${s}<span class="ampm">${ampm}</span>`;
                if (dateEl) {
                    dateEl.textContent = now.toLocaleDateString('en-US', {
                        weekday: 'long', month: 'long', day: 'numeric'
                    });
                }
            };
            tick();
            const id = setInterval(tick, 1000);
            if (clockCalendarPopover && clockCalendarPopover._intervals) {
                clockCalendarPopover._intervals.push(id);
            }
        }

        // Status → dot colour (matches the calendar page badge palette)
        const CLOCK_STATUS_COLORS = {
            Booked: '#3b82f6',
            CheckedIn: '#22c55e',
            InProgress: '#f59e0b',
            Completed: '#06b6d4',
            Rescheduled: '#a78bfa'
        };
        const CLOCK_STATUS_LABELS = {
            Booked: 'Booked',
            CheckedIn: 'Checked In',
            InProgress: 'In Progress',
            Completed: 'Completed',
            Rescheduled: 'Rescheduled'
        };

        function clockFmtTime(t) {
            if (!t) return '';
            const [hh, mm] = t.split(':');
            let h = parseInt(hh, 10);
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return `${h}:${mm} ${ampm}`;
        }

        // Rich, colour-organised tooltip for a calendar day's appointments
        function showCalDayTooltip(li, dateStr, appts) {
            if (!appts || !appts.length) return;
            let tip = document.getElementById('calDayTooltip');
            if (!tip) {
                tip = document.createElement('div');
                tip.id = 'calDayTooltip';
                tip.className = 'cal-day-tooltip';
                document.body.appendChild(tip);
            }
            const d = new Date(dateStr + 'T12:00:00');
            const head = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            const sorted = appts.slice().sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''));
            const MAX = 8;
            const rows = sorted.slice(0, MAX).map(a => {
                const c = CLOCK_STATUS_COLORS[a.status] || '#94a3b8';
                const label = CLOCK_STATUS_LABELS[a.status] || a.status || '';
                const name = a.patient_name || 'Patient';
                return `<div class="cal-tip-item" style="border-left-color:${c}">`
                    + `<span class="cal-tip-time">${clockFmtTime(a.start_time)}</span>`
                    + `<span class="cal-tip-name">${escapeHtml(name)}</span>`
                    + `<span class="cal-tip-status" style="color:${c}">${escapeHtml(label)}</span>`
                    + `</div>`;
            }).join('');
            const more = sorted.length > MAX ? `<div class="cal-tip-more">+${sorted.length - MAX} more</div>` : '';
            tip.innerHTML = `<div class="cal-tip-head"><span>${head}</span>`
                + `<span class="cal-tip-count">${appts.length} appt${appts.length > 1 ? 's' : ''}</span></div>`
                + `<div class="cal-tip-list">${rows}${more}</div>`;
            tip.style.display = 'block';

            // Position above the cell, flip below if there's no room
            const r = li.getBoundingClientRect();
            const tr = tip.getBoundingClientRect();
            let top = r.top - tr.height - 10;
            if (top < 8) top = r.bottom + 10;
            let left = r.left + r.width / 2 - tr.width / 2;
            left = Math.max(8, Math.min(left, window.innerWidth - tr.width - 8));
            tip.style.top = top + 'px';
            tip.style.left = left + 'px';
        }

        function hideCalDayTooltip() {
            const tip = document.getElementById('calDayTooltip');
            if (tip) tip.style.display = 'none';
        }

        // Fetch + cache a month's appointment data (organizer/month → dataByDate)
        function getClockMonthData(year, month1) {
            // month1 is 1-based
            if (!clockCalendarPopover) return Promise.resolve({});
            if (!clockCalendarPopover._monthCache) clockCalendarPopover._monthCache = {};
            const key = `${year}-${month1}`;
            if (clockCalendarPopover._monthCache[key]) {
                return Promise.resolve(clockCalendarPopover._monthCache[key]);
            }
            return fetch(`/api/organizer/month?year=${year}&month=${month1}`)
                .then(r => r.json())
                .then(res => {
                    const byDate = (res && res.ok && res.data && res.data.dataByDate) ? res.data.dataByDate : {};
                    clockCalendarPopover._monthCache[key] = byDate;
                    return byDate;
                })
                .catch(() => ({}));
        }

        function initCalendar() {
            let date = new Date();
            let year = date.getFullYear();
            let month = date.getMonth();

            const day = document.querySelector(".calendar-dates");
            const currdate = document.querySelector(".calendar-current-date");
            const prenexIcons = document.querySelectorAll(".calendar-navigation span");

            if (!day || !currdate) return;

            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            const pad = (n) => String(n).padStart(2, '0');

            // Build the dots + count markup for a given day's appointments
            const dayMarkup = (appts) => {
                const count = appts.length;
                if (!count) return '';
                const dots = appts.slice(0, 3).map(a => {
                    const c = CLOCK_STATUS_COLORS[a.status] || '#94a3b8';
                    return `<span class="cal-dot" style="background:${c}"></span>`;
                }).join('');
                return `<span class="cal-dots">${dots}</span><span class="cal-count">${count > 99 ? '99+' : count}</span>`;
            };

            const render = (byDate) => {
                let dayone = new Date(year, month, 1).getDay();
                let lastdate = new Date(year, month + 1, 0).getDate();
                let dayend = new Date(year, month, lastdate).getDay();
                let monthlastdate = new Date(year, month, 0).getDate();

                let lit = "";

                for (let i = dayone; i > 0; i--) {
                    lit += `<li class="inactive">${monthlastdate - i + 1}</li>`;
                }

                const realNow = new Date();
                for (let i = 1; i <= lastdate; i++) {
                    const isToday = i === realNow.getDate()
                        && month === realNow.getMonth()
                        && year === realNow.getFullYear();
                    const dateStr = `${year}-${pad(month + 1)}-${pad(i)}`;
                    const appts = (byDate[dateStr] && byDate[dateStr].appointments) ? byDate[dateStr].appointments : [];
                    const classes = ['cal-day'];
                    if (isToday) classes.push('active');
                    if (appts.length) classes.push('has-appts');
                    const title = appts.length ? `${appts.length} appointment${appts.length > 1 ? 's' : ''}` : 'No appointments';
                    lit += `<li class="${classes.join(' ')}" data-date="${dateStr}" title="${title}">`
                        + `${dayMarkup(appts)}<span class="cal-daynum">${i}</span></li>`;
                }

                for (let i = dayend; i < 6; i++) {
                    lit += `<li class="inactive">${i - dayend + 1}</li>`;
                }

                currdate.innerText = `${months[month]} ${year}`;
                day.innerHTML = lit;

                // Day click → open that day in the full calendar; hover → rich tooltip
                day.querySelectorAll('li.cal-day').forEach(li => {
                    const d = li.getAttribute('data-date');
                    const appts = (byDate[d] && byDate[d].appointments) ? byDate[d].appointments : [];
                    li.addEventListener('click', () => {
                        if (d) window.location.href = `/doctor/calendar?date=${d}`;
                    });
                    if (appts.length) {
                        li.addEventListener('mouseenter', () => showCalDayTooltip(li, d, appts));
                        li.addEventListener('mouseleave', hideCalDayTooltip);
                    }
                });
            };

            const loadMonth = () => {
                // Render immediately (no dots) so navigation feels instant, then fill in
                render({});
                getClockMonthData(year, month + 1).then(render);
            };

            loadMonth();

            prenexIcons.forEach(icon => {
                icon.addEventListener("click", () => {
                    month = icon.id === "calendar-prev" ? month - 1 : month + 1;

                    if (month < 0 || month > 11) {
                        date = new Date(year, month, new Date().getDate());
                        year = date.getFullYear();
                        month = date.getMonth();
                    } else {
                        date = new Date();
                    }

                    loadMonth();
                });
            });
        }

        // Clock appointment overlay: paint each of today's 15-min slots on the
        // working-hours arc, coloured by the appointment's state.
        //   green = Completed · red = Cancelled / NoShow (missed) / overdue (Due)
        //   blue  = currently in progress · everything else keeps the base arc colour.
        const CLOCK_APPT_COLORS = { done: '#22c55e', bad: '#ef4444', now: '#3b82f6' };
        function clockApptColor(a, nowMs, todayStr) {
            const st = a.status;
            if (st === 'Completed') return CLOCK_APPT_COLORS.done;                 // green
            if (st === 'Cancelled' || st === 'NoShow') return CLOCK_APPT_COLORS.bad; // red (cancelled / missed)
            if (a.start_time && (st === 'Booked' || st === 'CheckedIn' || st === 'InProgress')) {
                const start = new Date(`${todayStr}T${a.start_time}`).getTime();
                const end = start + 15 * 60000;
                if (st === 'InProgress') return CLOCK_APPT_COLORS.now;             // blue (in progress)
                if (nowMs >= start && nowMs < end) return CLOCK_APPT_COLORS.now;   // blue (currently)
                if (nowMs >= end) return CLOCK_APPT_COLORS.bad;                    // red (due / overdue)
            }
            return null; // future Booked / Rescheduled / Closed → base arc colour stays
        }
        function drawClockApptArcs(appts) {
            const svg = clockCalendarPopover && clockCalendarPopover.querySelector('.clock-face');
            if (!svg) return;
            svg.querySelectorAll('.clock-appt-arc').forEach(n => n.remove());
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
            const nowMs = now.getTime();
            const NS = 'http://www.w3.org/2000/svg';
            const cx = 160, cy = 160, R = 122; // same radius as the work-arc, so slots overlay it
            const pt = (h12) => {
                const ang = (h12 * 30 - 90) * Math.PI / 180; // 12 at top, clockwise; 1 min = 0.5°
                return [cx + R * Math.cos(ang), cy + R * Math.sin(ang)];
            };
            const center = svg.querySelector('.cf-center'); // keep our arcs under the centre dot
            (appts || []).forEach(a => {
                if (!a || !a.start_time) return;
                const color = clockApptColor(a, nowMs, todayStr);
                if (!color) return;
                const parts = String(a.start_time).split(':');
                const h = parseInt(parts[0], 10), m = parseInt(parts[1], 10) || 0;
                if (isNaN(h)) return;
                const startH = (h % 12) + (m / 60);
                const endH = startH + 0.25; // 15 minutes = a quarter of an hour-tick
                const [sx, sy] = pt(startH);
                const [ex, ey] = pt(endH);
                const path = document.createElementNS(NS, 'path');
                path.setAttribute('class', 'clock-appt-arc');
                path.setAttribute('d', `M ${sx.toFixed(1)} ${sy.toFixed(1)} A ${R} ${R} 0 0 1 ${ex.toFixed(1)} ${ey.toFixed(1)}`);
                path.setAttribute('stroke', color);
                if (center) svg.insertBefore(path, center); else svg.appendChild(path);
            });
        }

        // Today's stats (Total / Done / Left) + next-appointment live countdown
        function initClockData() {
            // --- Today's stats from the current month's data ---
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
            getClockMonthData(now.getFullYear(), now.getMonth() + 1).then(byDate => {
                const appts = (byDate[todayStr] && byDate[todayStr].appointments) ? byDate[todayStr].appointments : [];
                const total = appts.length;
                const done = appts.filter(a => a.status === 'Completed').length;
                const left = total - done;
                const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
                set('clockStatTotal', total);
                set('clockStatDone', done);
                set('clockStatLeft', left);
                drawClockApptArcs(appts); // paint today's appointment slots on the dial
            });

            // --- Next appointment countdown (live) ---
            const nextEl = document.getElementById('clockNext');
            if (!nextEl) return;

            const renderEmpty = (msg) => {
                nextEl.classList.add('is-empty');
                nextEl.style.cursor = '';
                nextEl.onclick = null;
                nextEl.innerHTML = `<i class="bi bi-calendar-check"></i><span class="clock-next-text">${msg}</span>`;
            };

            fetch('/api/upcoming-appointments?page=1&per_page=50')
                .then(r => r.json())
                .then(res => {
                    const items = (res && res.ok && res.data && res.data.items) ? res.data.items : [];
                    // first item whose full datetime is still in the future
                    let next = null;
                    for (const a of items) {
                        if (!a.date || !a.start_time) continue;
                        const dt = new Date(`${a.date}T${a.start_time}`);
                        if (dt.getTime() > Date.now()) { next = { dt, a }; break; }
                    }
                    if (!next) { renderEmpty('No upcoming appointments'); return; }

                    const name = `${next.a.first_name || ''} ${next.a.last_name || ''}`.trim() || 'Patient';
                    const apptId = next.a.id;

                    const fmtEta = (ms) => {
                        if (ms <= 0) return 'now';
                        const mins = Math.round(ms / 60000);
                        if (mins < 60) return `in ${mins} min`;
                        const hrs = Math.floor(mins / 60);
                        const rem = mins % 60;
                        if (hrs < 24) return rem ? `in ${hrs}h ${rem}m` : `in ${hrs}h`;
                        const days = Math.floor(hrs / 24);
                        return `in ${days} day${days > 1 ? 's' : ''}`;
                    };

                    const paint = () => {
                        const eta = fmtEta(next.dt.getTime() - Date.now());
                        nextEl.classList.remove('is-empty');
                        nextEl.innerHTML = `<i class="bi bi-hourglass-split"></i>`
                            + `<span class="clock-next-text">Next: <span class="clock-next-name">${escapeHtml(name)}</span> `
                            + `<span class="clock-next-eta">${eta}</span></span>`;
                    };
                    paint();
                    if (apptId) {
                        nextEl.style.cursor = 'pointer';
                        nextEl.onclick = () => { window.location.href = `/doctor/appointments/${apptId}`; };
                    }
                    const id = setInterval(paint, 1000);
                    if (clockCalendarPopover && clockCalendarPopover._intervals) {
                        clockCalendarPopover._intervals.push(id);
                    }
                })
                .catch(() => renderEmpty('Error loading appointments'));
        }

        // Open popover on click
        if (noticeBarDateTimeColumn) {
            noticeBarDateTimeColumn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Toggle based on what's actually in the DOM (not just the variable),
                // so the first click always opens even if the variable went stale.
                if (document.getElementById('clockCalendarPopover')) {
                    closeClockCalendarPopover();
                } else {
                    createClockCalendarPopover();
                }
            });
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && clockCalendarPopover) {
                closeClockCalendarPopover();
            }
            if (e.key === 'Escape' && weatherPopover) {
                closeWeatherPopover();
            }
            if (e.key === 'Escape' && appointmentsPopover) {
                closeAppointmentsPopover();
            }
        });

        // Weather Popover
        let weatherPopover = null;
        
        function createWeatherPopover() {
            if (weatherPopover) {
                weatherPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'weather-popover';
            popover.id = 'weatherPopover';
            
            // Get current weather data from localStorage
            const cachedData = loadNoticeBarWeatherFromStorage();
            const currentWeather = cachedData && cachedData.data ? cachedData.data : null;
            
            popover.innerHTML = `
                <div class="weather-popover-content">
                    <div class="weather-popover-body">
                        <div class="weather-card-inner weather-popover-clickable" title="View 5-day forecast">
                            <!-- Weather Section -->
                            <div class="weather-main wx-hero">
                                <div class="weather-icon-container" id="weatherPopoverIconContainer">
                                    <div class="weather-icon-loading">
                                        <div class="spinner-border spinner-border-sm text-light" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="weather-info">
                                    <div class="weather-temp" id="weatherPopoverTemp">--°C</div>
                                    <div class="weather-desc" id="weatherPopoverDesc">Loading...</div>
                                    <div class="weather-location" id="weatherPopoverLocation">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span>Detecting location...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Health Indices -->
                            <div class="health-indices">
                                <!-- Pollen Index -->
                                <div class="health-index pollen-index">
                                    <div class="index-icon">
                                        <i class="bi bi-flower1"></i>
                                    </div>
                                    <div class="index-info">
                                        <span class="index-label">Pollen Index</span>
                                        <div class="index-bar">
                                            <div class="index-fill" id="weatherPopoverPollenFill" style="width: 0%"></div>
                                        </div>
                                        <span class="index-value" id="weatherPopoverPollenValue">--</span>
                                    </div>
                                </div>

                                <!-- Dry Eye Risk -->
                                <div class="health-index dry-eye-index">
                                    <div class="index-icon">
                                        <i class="bi bi-eye"></i>
                                    </div>
                                    <div class="index-info">
                                        <span class="index-label">Dry Eye Risk</span>
                                        <div class="index-bar">
                                            <div class="index-fill" id="weatherPopoverDryEyeFill" style="width: 0%"></div>
                                        </div>
                                        <span class="index-value" id="weatherPopoverDryEyeValue">--</span>
                                    </div>
                                </div>
                            </div>

                            <!-- UV index meter -->
                            <div id="weatherPopoverUv" class="weather-popover-uv"></div>

                            <!-- Eye-care advisory -->
                            <div id="weatherPopoverAdvisory" class="weather-popover-advisory"></div>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(popover);
            weatherPopover = popover;
            
            // Position popover
            positionWeatherPopover();
            
            // The whole popover card opens the unified forecast window
            const popoverCard = popover.querySelector('.weather-card-inner');
            if (popoverCard) {
                popoverCard.style.cursor = 'pointer';
                popoverCard.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeWeatherPopover();
                    openWeatherForecastWindow();
                });
            }
            
            // Update current weather
            if (currentWeather) {
                updateWeatherPopoverCurrent(currentWeather);
            } else {
                // Fetch current weather
                const DEFAULT_LAT = 31.1117;
                const DEFAULT_LON = 30.9397;
                fetchNoticeBarWeatherData(DEFAULT_LAT, DEFAULT_LON, false).then(() => {
                    const updatedData = loadNoticeBarWeatherFromStorage();
                    if (updatedData && updatedData.data) {
                        updateWeatherPopoverCurrent(updatedData.data);
                    }
                });
            }
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'weather-popover-backdrop';
            backdrop.addEventListener('click', closeWeatherPopover);
            document.body.appendChild(backdrop);
        }

        function positionWeatherPopover() {
            if (!weatherPopover) return;
            
            // Position at center top of the viewport
            const viewportWidth = window.innerWidth;
            const popoverWidth = weatherPopover.offsetWidth || 500;
            const topOffset = 20; // 20px from top
            
            weatherPopover.style.top = topOffset + 'px';
            weatherPopover.style.left = '50%';
            weatherPopover.style.transform = 'translateX(-50%)';
            weatherPopover.style.right = 'auto';
            weatherPopover.style.bottom = 'auto';
        }

        function closeWeatherPopover() {
            if (weatherPopover) {
                weatherPopover.remove();
                weatherPopover = null;
            }
            const backdrop = document.querySelector('.weather-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function updateWeatherPopoverCurrent(weatherData) {
            const iconContainer = document.getElementById('weatherPopoverIconContainer');
            const tempElement = document.getElementById('weatherPopoverTemp');
            const descElement = document.getElementById('weatherPopoverDesc');
            const locationElement = document.getElementById('weatherPopoverLocation');
            const pollenValue = document.getElementById('weatherPopoverPollenValue');
            const pollenBar = document.getElementById('weatherPopoverPollenFill');
            const dryEyeValue = document.getElementById('weatherPopoverDryEyeValue');
            const dryEyeBar = document.getElementById('weatherPopoverDryEyeFill');

            if (!iconContainer) return;

            // Animated WeatherFx icon (falls back to the legacy icon if unavailable)
            if (window.WeatherFx) {
                iconContainer.innerHTML = WeatherFx.iconHTML(weatherData, 110);
            } else {
                let iconType, largeIcon;
                if (typeof getWeatherIconType !== 'undefined' && typeof renderWeatherIcon !== 'undefined') {
                    iconType = getWeatherIconType(weatherData.condition || 'clear');
                    largeIcon = renderWeatherIcon(iconType).replace('style="width:16px;height:16px;"', 'style="width:120px;height:120px;"');
                } else {
                    iconType = getNoticeBarWeatherIconType(weatherData.condition || 'clear');
                    largeIcon = renderNoticeBarWeatherIcon(iconType).replace('style="width:16px;height:16px;"', 'style="width:120px;height:120px;"');
                }
                iconContainer.innerHTML = largeIcon;
            }

            // Animated scene background behind the hero + eye-care advisory + UV meter
            if (window.WeatherFx) {
                const hero = document.querySelector('#weatherPopover .weather-main');
                if (hero) {
                    const old = hero.querySelector('.wx-scene');
                    if (old) old.remove();
                    hero.insertAdjacentHTML('afterbegin', WeatherFx.sceneHTML(weatherData));
                }
                const uvBox = document.getElementById('weatherPopoverUv');
                if (uvBox) uvBox.innerHTML = WeatherFx.uvMeterHTML(weatherData.uvIndex);
                const advBox = document.getElementById('weatherPopoverAdvisory');
                if (advBox) advBox.innerHTML = WeatherFx.advisoryHTML(weatherData);
            }

            if (tempElement) {
                tempElement.textContent = `${Math.round(weatherData.temperature || 0)}°C`;
            }

            if (descElement) {
                descElement.textContent = weatherData.condition || 'Clear';
            }

            if (locationElement) {
                locationElement.innerHTML = `<i class="bi bi-geo-alt"></i> ${weatherData.location || 'Unknown'}`;
            }

            // Calculate health indices - Use dashboard.js functions if available
            let pollenIndex, dryEyeRisk;
            if (typeof calculatePollenIndex !== 'undefined' && typeof calculateDryEyeRisk !== 'undefined') {
                pollenIndex = calculatePollenIndex(weatherData);
                dryEyeRisk = calculateDryEyeRisk(weatherData);
            } else {
                pollenIndex = calculateWeatherModalPollenIndex(weatherData);
                dryEyeRisk = calculateWeatherModalDryEyeRisk(weatherData);
            }

            if (pollenValue) {
                pollenValue.textContent = `${Math.round(pollenIndex)}%`;
            }
            if (pollenBar) {
                const levelClass = (typeof getLevelClass !== 'undefined') ? getLevelClass(pollenIndex) : getWeatherModalLevelClass(pollenIndex);
                pollenBar.style.width = `${pollenIndex}%`;
                // Remove all level classes first
                pollenBar.classList.remove('index-low', 'index-moderate', 'index-high', 'index-very-high');
                // Add the appropriate level class
                pollenBar.classList.add(`index-${levelClass}`);
            }

            if (dryEyeValue) {
                dryEyeValue.textContent = `${Math.round(dryEyeRisk)}%`;
            }
            if (dryEyeBar) {
                const levelClass = (typeof getLevelClass !== 'undefined') ? getLevelClass(dryEyeRisk) : getWeatherModalLevelClass(dryEyeRisk);
                dryEyeBar.style.width = `${dryEyeRisk}%`;
                // Remove all level classes first
                dryEyeBar.classList.remove('index-low', 'index-moderate', 'index-high', 'index-very-high');
                // Add the appropriate level class
                dryEyeBar.classList.add(`index-${levelClass}`);
            }

            // Update notice bar warnings
            updateWeatherWarnings(weatherData);
        }

        // Calculate pollen index - Same as dashboard.js
        function calculateWeatherModalPollenIndex(weatherData) {
            // Factors affecting pollen: temperature, humidity, wind, rain
            let pollenScore = 50; // Base score

            const temp = weatherData.temperature || 20;
            const humidity = weatherData.humidity || 50;
            const windSpeed = weatherData.windSpeed || 10;
            const isRaining = weatherData.condition?.toLowerCase().includes('rain');

            // Temperature factor (15-25°C is peak pollen)
            if (temp >= 15 && temp <= 25) {
                pollenScore += 20;
            } else if (temp > 25 && temp <= 30) {
                pollenScore += 10;
            } else if (temp < 10 || temp > 35) {
                pollenScore -= 20;
            }

            // Humidity factor (low humidity = more airborne pollen)
            if (humidity < 40) {
                pollenScore += 15;
            } else if (humidity > 70) {
                pollenScore -= 15;
            }

            // Wind factor (moderate wind spreads pollen)
            if (windSpeed >= 10 && windSpeed <= 25) {
                pollenScore += 15;
            } else if (windSpeed > 30) {
                pollenScore -= 10;
            }

            // Rain washes away pollen
            if (isRaining) {
                pollenScore -= 30;
            }

            return Math.max(0, Math.min(100, pollenScore));
        }

        // Calculate dry eye risk - Same as dashboard.js
        function calculateWeatherModalDryEyeRisk(weatherData) {
            let riskScore = 30; // Base score

            const temp = weatherData.temperature || 20;
            const humidity = weatherData.humidity || 50;
            const windSpeed = weatherData.windSpeed || 10;
            const uvIndex = weatherData.uvIndex || 5;

            // Low humidity increases dry eye risk significantly
            if (humidity < 30) {
                riskScore += 35;
            } else if (humidity < 45) {
                riskScore += 20;
            } else if (humidity > 60) {
                riskScore -= 15;
            }

            // High temperature with low humidity
            if (temp > 30 && humidity < 50) {
                riskScore += 15;
            }

            // Wind increases evaporation
            if (windSpeed > 20) {
                riskScore += 20;
            } else if (windSpeed > 10) {
                riskScore += 10;
            }

            // High UV exposure
            if (uvIndex > 7) {
                riskScore += 15;
            } else if (uvIndex > 5) {
                riskScore += 8;
            }

            return Math.max(0, Math.min(100, riskScore));
        }

        // Get level class - Same as dashboard.js
        function getWeatherModalLevelClass(score) {
            if (score <= 25) return 'low';
            if (score <= 50) return 'moderate';
            if (score <= 75) return 'high';
            return 'very-high';
        }

        // Weather Forecast Popover (4-Day Forecast)
        let noticeBarWeatherForecastPopover = null;

        function showNoticeBarWeatherForecastPopover() {
            // Close if already open
            if (noticeBarWeatherForecastPopover) {
                closeNoticeBarWeatherForecastPopover();
                return;
            }
            
            // Try to get coordinates from current weather data or use default
            const DEFAULT_LAT = 31.1117;
            const DEFAULT_LON = 30.9397;
            
            // Create popover
            const popover = document.createElement('div');
            popover.className = 'weather-forecast-popover';
            popover.id = 'noticeBarWeatherForecastPopover';
            popover.innerHTML = `
                <div class="weather-forecast-popover-content">
                    <div class="weather-forecast-popover-header">
                        <h5>4-Day Weather Forecast</h5>
                        <button class="weather-forecast-close" id="noticeBarWeatherForecastClose">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="weather-forecast-popover-body" id="noticeBarWeatherForecastBody">
                        <div class="weather-forecast-loading">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>Loading forecast...</span>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            noticeBarWeatherForecastPopover = popover;
            
            // Position at center top
            popover.style.top = '20px';
            popover.style.left = '50%';
            popover.style.transform = 'translateX(-50%)';
            
            // Close button handler
            document.getElementById('noticeBarWeatherForecastClose').addEventListener('click', closeNoticeBarWeatherForecastPopover);
            
            // Close on backdrop click
            popover.addEventListener('click', (e) => {
                if (e.target === popover) {
                    closeNoticeBarWeatherForecastPopover();
                }
            });
            
            // Close on ESC key
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    closeNoticeBarWeatherForecastPopover();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
            
            // Fetch forecast data
            fetchNoticeBarWeatherForecast(DEFAULT_LAT, DEFAULT_LON);
        }

        function closeNoticeBarWeatherForecastPopover() {
            if (noticeBarWeatherForecastPopover) {
                noticeBarWeatherForecastPopover.remove();
                noticeBarWeatherForecastPopover = null;
            }
        }

        async function fetchNoticeBarWeatherForecast(latitude, longitude) {
            try {
                const response = await fetch(`/api/weather-forecast?lat=${latitude}&lon=${longitude}`);
                if (!response.ok) {
                    throw new Error('Weather forecast API error');
                }
                const data = await response.json();
                
                if (data.success && data.forecast) {
                    renderNoticeBarWeatherForecast(data.forecast);
                } else {
                    throw new Error(data.error || 'Failed to get forecast data');
                }
            } catch (error) {
                const body = document.getElementById('noticeBarWeatherForecastBody');
                if (body) {
                    body.innerHTML = `
                        <div class="weather-forecast-error">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Unable to load forecast: ${error.message}</span>
                        </div>
                    `;
                }
            }
        }

        function renderNoticeBarWeatherForecast(forecast) {
            const body = document.getElementById('noticeBarWeatherForecastBody');
            if (!body) return;
            
            if (!forecast || forecast.length === 0) {
                body.innerHTML = `
                    <div class="weather-forecast-error">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>No forecast data available</span>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="weather-forecast-days">';
            
            forecast.forEach((day, index) => {
                const date = new Date(day.date);
                const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
                const dayNumber = date.getDate();
                const month = date.toLocaleDateString('en-US', { month: 'short' });
                
                // Use dashboard.js functions if available, otherwise use local functions
                let pollenIndex, dryEyeRisk, pollenLevel, dryEyeLevel;
                if (typeof calculatePollenIndex !== 'undefined' && typeof calculateDryEyeRisk !== 'undefined' && typeof getLevelClass !== 'undefined') {
                    pollenIndex = calculatePollenIndex(day);
                    dryEyeRisk = calculateDryEyeRisk(day);
                    pollenLevel = getLevelClass(pollenIndex);
                    dryEyeLevel = getLevelClass(dryEyeRisk);
                } else {
                    pollenIndex = calculateWeatherModalPollenIndex(day);
                    dryEyeRisk = calculateWeatherModalDryEyeRisk(day);
                    pollenLevel = getWeatherModalLevelClass(pollenIndex);
                    dryEyeLevel = getWeatherModalLevelClass(dryEyeRisk);
                }
                
                // Use dashboard.js renderWeatherIcon if available
                let weatherIcon;
                if (typeof getWeatherIconType !== 'undefined' && typeof renderWeatherIcon !== 'undefined') {
                    weatherIcon = renderWeatherIcon(getWeatherIconType(day.condition || 'clear'));
                } else {
                    const iconType = getNoticeBarWeatherIconType(day.condition || 'clear');
                    weatherIcon = renderNoticeBarWeatherIcon(iconType);
                }
                
                html += `
                    <div class="weather-forecast-day">
                        <div class="forecast-day-header">
                            <div class="forecast-day-name">${dayName}</div>
                            <div class="forecast-day-date">${dayNumber} ${month}</div>
                        </div>
                        <div class="forecast-day-weather">
                            <div class="forecast-day-icon">
                                ${weatherIcon}
                            </div>
                            <div class="forecast-day-temp">
                                <span class="forecast-temp-high">${Math.round(day.tempMax || day.temperature)}°</span>
                                <span class="forecast-temp-low">${Math.round(day.tempMin || day.temperature - 5)}°</span>
                            </div>
                            <div class="forecast-day-condition">${day.condition || 'Clear'}</div>
                        </div>
                        <div class="forecast-day-indices">
                            <div class="forecast-index-item">
                                <div class="forecast-index-label">
                                    <i class="bi bi-flower1"></i>
                                    <span>Pollen</span>
                                </div>
                                <div class="forecast-index-bar">
                                    <div class="forecast-index-fill index-${pollenLevel}" style="width: ${Math.max(2, pollenIndex)}%"></div>
                                </div>
                                <div class="forecast-index-value">${Math.round(pollenIndex)}%</div>
                            </div>
                            <div class="forecast-index-item">
                                <div class="forecast-index-label">
                                    <i class="bi bi-eye"></i>
                                    <span>Dry Eye</span>
                                </div>
                                <div class="forecast-index-bar">
                                    <div class="forecast-index-fill index-${dryEyeLevel}" style="width: ${Math.max(2, dryEyeRisk)}%"></div>
                                </div>
                                <div class="forecast-index-value">${Math.round(dryEyeRisk)}%</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            body.innerHTML = html;
        }

        // Add event listener for forecast button after popover is created
        function wfResolveIconType(condition, isNight) {
            const lc = (condition || '').toLowerCase();
            for (const [key, value] of Object.entries(noticeBarWeatherIconMap)) {
                if (lc.includes(key)) {
                    if (isNight) {
                        if (value === 'sun') return 'moon';
                        if (value === 'partly-cloudy') return 'partly-cloudy-night';
                        if (value === 'rain') return 'rain-night';
                        if (value === 'snow') return 'snow-night';
                    }
                    return value;
                }
            }
            return isNight ? 'moon' : 'sun';
        }
        function wfIcon(condition, isNight) {
            return renderNoticeBarWeatherIcon(wfResolveIconType(condition, isNight))
                .replace('style="width:16px;height:16px;"', '');
        }
        function wfFormatTime(iso) {
            if (!iso) return '--';
            const d = new Date(iso);
            if (isNaN(d)) return '--';
            return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }).toLowerCase();
        }
        function wfDayLength(sunrise, sunset) {
            if (!sunrise || !sunset) return '';
            const a = new Date(sunrise), b = new Date(sunset);
            if (isNaN(a) || isNaN(b)) return '';
            let mins = Math.max(0, Math.round((b - a) / 60000));
            const h = Math.floor(mins / 60); mins = mins % 60;
            return `${h} h ${mins} m`;
        }
        function wfCloseBtn() { return '<button class="wf-close" id="wfClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>'; }

        let weatherForecastWindow = null;
        function closeWeatherForecastWindow() {
            if (weatherForecastWindow) { weatherForecastWindow.remove(); weatherForecastWindow = null; }
        }
        function openWeatherForecastWindow() {
            if (weatherForecastWindow) { closeWeatherForecastWindow(); return; }
            let lat = 31.1117, lon = 30.9397;
            try {
                const s = JSON.parse(localStorage.getItem('dashboard_weather_data') || 'null');
                if (s && s.latitude && s.longitude) { lat = s.latitude; lon = s.longitude; }
            } catch (_) {}

            const overlay = document.createElement('div');
            overlay.className = 'weather-forecast-popover';
            overlay.id = 'weatherForecastWindow';
            overlay.innerHTML = `
                <div class="wf-window" id="weatherForecastBody">
                    ${wfCloseBtn()}
                    <div class="weather-forecast-loading">
                        <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>
                        <span>Loading forecast…</span>
                    </div>
                </div>`;
            document.body.appendChild(overlay);
            weatherForecastWindow = overlay;

            overlay.querySelector('#wfClose').addEventListener('click', closeWeatherForecastWindow);
            overlay.addEventListener('click', (e) => { if (e.target === overlay) closeWeatherForecastWindow(); });
            const esc = (e) => { if (e.key === 'Escape') { closeWeatherForecastWindow(); document.removeEventListener('keydown', esc); } };
            document.addEventListener('keydown', esc);

            wfFetchAndRender(lat, lon);
        }
        function wfBindClose() { const b = document.getElementById('wfClose'); if (b) b.addEventListener('click', closeWeatherForecastWindow); }
        async function wfFetchAndRender(lat, lon) {
            const body = document.getElementById('weatherForecastBody');
            try {
                const r = await fetch(`/api/weather-forecast?lat=${lat}&lon=${lon}`);
                const d = await r.json();
                if (!d.success || !d.forecast) throw new Error(d.error || 'Failed to load forecast');
                wfRenderWindow(d);
            } catch (e) {
                if (body) body.innerHTML = wfCloseBtn() + `<div class="weather-forecast-error"><i class="bi bi-exclamation-triangle-fill"></i><span>${e.message}</span></div>`;
                wfBindClose();
            }
        }
        function wfRenderWindow(data) {
            const body = document.getElementById('weatherForecastBody');
            if (!body) return;
            const cur = data.current || {}, forecast = data.forecast || [];
            const isNight = (cur.isDay !== undefined && cur.isDay !== null) ? (Number(cur.isDay) === 0) : isNoticeBarNightTime();
            const nowStr = new Date().toLocaleString('en-US', { weekday: 'long', hour: 'numeric', minute: '2-digit' });

            const fx = window.WeatherFx;

            let days = '';
            forecast.forEach((day, i) => {
                const d = new Date(day.date);
                const name = i === 0 ? 'Today' : d.toLocaleDateString('en-US', { weekday: 'short' });
                const dayIcon = fx ? fx.iconHTML({ condition: day.condition, isDay: 1 }, 36) : wfIcon(day.condition, false);
                days += `<div class="wf-day ${i === 0 ? 'is-today' : ''}"><div class="wf-day-name">${name}</div><div class="wf-day-icon">${dayIcon}</div><div class="wf-day-hi">${Math.round(day.tempMax)}°</div><div class="wf-day-lo">${Math.round(day.tempMin)}°</div></div>`;
            });
            const rain = (cur.precipitation !== null && cur.precipitation !== undefined)
                ? `<div class="wf-rain"><i class="bi bi-cloud-rain"></i> Rain: ${cur.precipitation}%</div>` : '';
            const sun = (cur.sunrise || cur.sunset)
                ? `<div class="wf-sun"><span class="wf-sun-item"><i class="bi bi-sunrise"></i> ${wfFormatTime(cur.sunrise)}</span><span class="wf-sun-mid">${wfDayLength(cur.sunrise, cur.sunset)}</span><span class="wf-sun-item">${wfFormatTime(cur.sunset)} <i class="bi bi-sunset"></i></span></div>` : '';

            const curIcon = fx ? fx.iconHTML(cur, 64) : wfIcon(cur.condition, isNight);
            const scene = fx ? fx.sceneHTML(cur) : '';
            const uv = (fx && cur.uvIndex != null) ? `<div class="wf-uv">${fx.uvMeterHTML(cur.uvIndex)}</div>` : '';
            const advisory = fx ? `<div class="wf-advisory">${fx.advisoryHTML(cur)}</div>` : '';

            body.innerHTML = wfCloseBtn() + `
                <div class="wf-current-top wx-hero">
                    ${scene}
                    <div class="wf-current-icon">${curIcon}</div>
                    <div class="wf-current-info">
                        <div class="wf-current-time">${nowStr}</div>
                        <div class="wf-current-main">${cur.condition || 'Clear'} ${Math.round(cur.temperature || 0)}°C</div>
                        <div class="wf-current-loc"><i class="bi bi-geo-alt-fill"></i> ${cur.location || 'Unknown'}</div>
                    </div>
                </div>
                ${sun}
                ${rain}
                <div class="wf-stats">
                    <span><span class="label">Humidity:</span> <span class="value">${cur.humidity != null ? cur.humidity + '%' : '--'}</span></span>
                    <span><span class="label">Wind:</span> <span class="value">${cur.windSpeed != null ? cur.windSpeed + ' km/h' : '--'}</span></span>
                </div>
                ${uv}
                ${advisory}
                <div class="wf-forecast">${days}</div>`;
            wfBindClose();
        }
        // Expose so the dashboard weather card (dashboard.js) can open the same window.
        window.openWeatherForecastWindow = openWeatherForecastWindow;

        function setupNoticeBarWeatherForecastButton() {
            const forecastBtn = document.getElementById('noticeBarWeatherForecastBtn');
            if (forecastBtn) {
                forecastBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openWeatherForecastWindow();
                });
            }
        }

        // Open weather popover on click
        const noticeBarWeatherColumn = document.querySelector('.notice-bar-column-weather');
        if (noticeBarWeatherColumn) {
            noticeBarWeatherColumn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (weatherPopover) {
                    closeWeatherPopover();
                } else {
                    createWeatherPopover();
                }
            });
        }

        // Upcoming Appointments Popover
        let appointmentsPopover = null;
        const noticeBarAppointmentColumn = document.querySelector('.notice-bar-column-3');
        
        function createAppointmentsPopover() {
            if (appointmentsPopover) {
                appointmentsPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'appointments-popover';
            popover.id = 'appointmentsPopover';
            
            popover.innerHTML = `
                <div class="appointments-popover-content">
                    <div class="appointments-popover-header">
                        <h5>Upcoming Appointments</h5>
                    </div>
                    <div class="appointments-popover-body" id="appointmentsPopoverBody">
                        <div class="appointments-loading">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>Loading appointments...</span>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            appointmentsPopover = popover;
            
            // Position popover
            positionAppointmentsPopover();
            
            // Load appointments
            loadAppointmentsPopover();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'appointments-popover-backdrop';
            backdrop.addEventListener('click', closeAppointmentsPopover);
            document.body.appendChild(backdrop);
        }

        function positionAppointmentsPopover() {
            if (!appointmentsPopover) return;
            
            const noticeBar = document.querySelector('.notice-bar');
            if (!noticeBar) return;
            
            const rect = noticeBar.getBoundingClientRect();
            
            // Position at top center of notice bar
            appointmentsPopover.style.top = (rect.bottom + 10) + 'px';
            appointmentsPopover.style.left = '50%';
            appointmentsPopover.style.transform = 'translateX(-50%)';
        }

        function closeAppointmentsPopover() {
            if (appointmentsPopover) {
                appointmentsPopover.remove();
                appointmentsPopover = null;
            }
            const backdrop = document.querySelector('.appointments-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function loadAppointmentsPopover() {
            const container = document.getElementById('appointmentsPopoverBody');
            if (!container) return;

            // Fetch all appointments (100 per page, maximum allowed)
            fetch('/api/upcoming-appointments?page=1&per_page=100')
                .then(response => response.json())
                .then(data => {
                    if (data.ok && data.data.items && data.data.items.length > 0) {
                        // If there are more pages, fetch them all
                        const totalPages = data.data.pagination?.total_pages || 1;
                        if (totalPages > 1) {
                            // Fetch all pages
                            const promises = [];
                            for (let page = 1; page <= totalPages; page++) {
                                promises.push(fetch(`/api/upcoming-appointments?page=${page}&per_page=100`).then(r => r.json()));
                            }
                            Promise.all(promises).then(results => {
                                const allAppointments = [];
                                results.forEach(result => {
                                    if (result.ok && result.data.items) {
                                        allAppointments.push(...result.data.items);
                                    }
                                });
                                renderAppointmentsPopover(allAppointments);
                            });
                        } else {
                            renderAppointmentsPopover(data.data.items);
                        }
                    } else {
                        container.innerHTML = `
                            <div class="appointments-empty">
                                <i class="bi bi-calendar-x"></i>
                                <span>No upcoming appointments</span>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    container.innerHTML = `
                        <div class="appointments-error">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Unable to load appointments: ${error.message}</span>
                        </div>
                    `;
                });
        }

        function renderAppointmentsPopover(appointments) {
            const container = document.getElementById('appointmentsPopoverBody');
            if (!container) return;

            const APPT_C = { Booked: '#3b82f6', CheckedIn: '#22c55e', InProgress: '#f59e0b', Completed: '#06b6d4', Rescheduled: '#a855f7' };
            const apptVIcon = (t) => ({ New: 'bi-stars', FollowUp: 'bi-arrow-repeat', Procedure: 'bi-clipboard2-pulse' }[t] || 'bi-calendar2-event');
            const apptUntil = (ds, st) => {
                if (!ds || !st) return '';
                const ms = new Date(`${ds}T${st}`).getTime() - Date.now();
                if (ms <= 0) return '';
                const mins = Math.round(ms / 60000);
                if (mins < 60) return `in ${mins} min`;
                const hrs = Math.floor(mins / 60), rem = mins % 60;
                if (hrs < 24) return rem ? `in ${hrs}h ${rem}m` : `in ${hrs}h`;
                return `in ${Math.floor(hrs / 24)}d`;
            };

            let html = '<div class="appointments-list">';

            appointments.forEach((appointment, idx) => {
                const patientName = `${appointment.first_name || ''} ${appointment.last_name || ''}`.trim() || 'Unknown';
                const sColor = APPT_C[appointment.status] || '#64748b';
                const initials = (((appointment.first_name || '').charAt(0) + (appointment.last_name || '').charAt(0)).toUpperCase()) || '?';
                const until = apptUntil(appointment.date, appointment.start_time);
                const isNext = idx === 0 && until !== '';
                const vIcon = apptVIcon(appointment.visit_type);

                // Format time (convert 24h to 12h format)
                let timeStr = '';
                if (appointment.start_time) {
                    const [hours, minutes] = appointment.start_time.split(':');
                    const hour24 = parseInt(hours);
                    const hour12 = hour24 === 0 ? 12 : (hour24 > 12 ? hour24 - 12 : hour24);
                    const period = hour24 >= 12 ? 'PM' : 'AM';
                    timeStr = `${hour12}:${minutes}${period}`;
                }

                // Format date
                let dateStr = '';
                if (appointment.date) {
                    const appointmentDate = new Date(appointment.date + 'T00:00:00');
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const tomorrow = new Date(today);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    
                    const appointmentDateOnly = new Date(appointmentDate);
                    appointmentDateOnly.setHours(0, 0, 0, 0);
                    
                    if (appointmentDateOnly.getTime() === today.getTime()) {
                        dateStr = 'Today';
                    } else if (appointmentDateOnly.getTime() === tomorrow.getTime()) {
                        dateStr = 'Tomorrow';
                    } else {
                        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        dateStr = `${days[appointmentDate.getDay()]} ${appointmentDate.getDate()} ${months[appointmentDate.getMonth()]}`;
                    }
                }

                // Format date for calendar navigation (YYYY-MM-DD)
                const calendarDate = appointment.date || '';
                
                html += `
                    <div class="appointment-item ${isNext ? 'is-next' : ''}" data-appointment-id="${appointment.id}" style="--appt-color:${sColor}">
                        <div class="appointment-item-avatar" style="background:${sColor}">${escapeHtml(initials)}</div>
                        <div class="appointment-item-content" onclick="window.location.href='/doctor/appointments/${appointment.id}'">
                            <div class="appointment-item-header">
                                <div class="appointment-item-patient">${escapeHtml(patientName)}${isNext ? '<span class="appt-pop-next"><i class="bi bi-stars"></i> Next up</span>' : ''}</div>
                                <div class="appointment-item-time">${timeStr}</div>
                            </div>
                            <div class="appointment-item-meta">
                                <span class="appointment-item-date"><i class="bi bi-calendar3"></i> ${dateStr}</span>
                                ${appointment.visit_type ? `<span class="appointment-item-type"><i class="bi ${vIcon}"></i> ${escapeHtml(appointment.visit_type)}</span>` : ''}
                                ${until ? `<span class="appt-pop-until">${until}</span>` : ''}
                            </div>
                        </div>
                        <button class="appointment-item-calendar-btn"
                                onclick="event.stopPropagation(); window.location.href='/doctor/calendar?date=${calendarDate}&appointment_id=${appointment.id}'"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                data-bs-title="View in Calendar">
                            <i class="bi bi-calendar3"></i>
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        // Open appointments popover on click
        if (noticeBarAppointmentColumn) {
            noticeBarAppointmentColumn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (appointmentsPopover) {
                    closeAppointmentsPopover();
                } else {
                    createAppointmentsPopover();
                }
            });
        }

        // ── Ophthalmology calculators & tools extracted to ophthalmology-tools.js ──
        //    (loaded only on Appointment + Patient-profile pages; gated by
        //     $__showEyeTools in layouts/main.php — keeps ~5.3k lines off other pages)
        
        // Scroll to Top Button
        const scrollToTopBtn = document.getElementById('scrollToTop');
        const mobileDock = document.getElementById('quickAccessDock');
        // FAB triplet: back-to-top → AI → dock. When back-to-top becomes
        // visible the AI widget lifts one slot (vertical on mobile, horizontal
        // on desktop) so the three FABs never overlap. The widget is built
        // LATE by ai-chat-widget.js (called from each page's inline init
        // script, AFTER main.js's DOMContentLoaded init has already run), so
        // caching the element here would always return null. Look it up per
        // scroll-tick instead — cheap inside the existing rAF.

        function updateMobileDockPosition() {
            if (!mobileDock || window.innerWidth > 768) return;

            if (scrollToTopBtn && scrollToTopBtn.classList.contains('show')) {
                // Back to top button is visible - move dock above it by 10px
                mobileDock.classList.add('dock-above-button');
            } else {
                // Back to top button is hidden - use its exact position
                mobileDock.classList.remove('dock-above-button');
            }
        }

        // Fill the back-to-top ring proportionally to how far the page is scrolled.
        const sttRingBar = scrollToTopBtn ? scrollToTopBtn.querySelector('.stt-ring-bar') : null;
        const STT_RING_C = 122.52; // 2π·19.5
        function updateScrollProgress() {
            if (!sttRingBar) return;
            const el = document.documentElement;
            const max = (el.scrollHeight - el.clientHeight) || 1;
            const p = Math.min(1, Math.max(0, window.pageYOffset / max));
            sttRingBar.style.strokeDashoffset = (STT_RING_C * (1 - p)).toFixed(2);
        }

        // All scroll-driven DOM work runs inside ONE requestAnimationFrame tick,
        // with every layout READ done before any WRITE. The previous code read
        // documentElement.scrollHeight synchronously on every scroll event (which
        // can fire many times per frame) right after mutating classes — forcing a
        // reflow each time. Batching to one rAF/frame and reading-before-writing
        // removes that forced reflow, which is what made roaya feel janky vs ortho
        // (ortho has no progress ring, so it never paid this cost).
        let _sttScrollScheduled = false;
        function onScrollTick() {
            if (_sttScrollScheduled) return;
            _sttScrollScheduled = true;
            requestAnimationFrame(() => {
                _sttScrollScheduled = false;
                // ---- READS (batched, before any write) ----
                const y = window.pageYOffset;
                const el = document.documentElement;
                const max = (el.scrollHeight - el.clientHeight) || 1;
                const ringP = Math.min(1, Math.max(0, y / max));
                const wantShow = y > 300;
                // ---- WRITES ----
                if (scrollToTopBtn) scrollToTopBtn.classList.toggle('show', wantShow);
                // `.dock-above-button` drives both the mobile vertical-column
                // shift (style.css:6892, inside @media max-width:768px) AND
                // the desktop horizontal-row shift (style.css:8597, on the
                // .quick-access-dock.minimized rule). Both are media-queried so
                // they can't leak across viewports — no need to gate the toggle.
                if (mobileDock) mobileDock.classList.toggle('dock-above-button', wantShow);
                // Per-tick lookup — see note above; widget DOM is built late.
                const aiFab = document.getElementById('aiChatWidget');
                if (aiFab) aiFab.classList.toggle('ai-above-backtotop', wantShow);
                if (sttRingBar) sttRingBar.style.strokeDashoffset = (STT_RING_C * (1 - ringP)).toFixed(2);
            });
        }
        
        // Load and apply personal preferences
        async function loadAndApplyPersonalPreferences() {
            try {
                const response = await fetch('/api/doctor/settings', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.settings) {
                        const settings = data.settings;
                        
                        // 1. Back to Top Display
                        if (scrollToTopBtn) {
                            const backToTopEnabled = settings.back_to_top_display !== false; // Default true
                            if (!backToTopEnabled) {
                                scrollToTopBtn.style.display = 'none';
                            } else {
                                scrollToTopBtn.style.display = '';
                                // Show/hide button, mobile-dock position and the
                                // scroll-progress ring are all handled together in
                                // one rAF-throttled tick (see onScrollTick above) to
                                // avoid per-scroll layout thrashing.
                                window.addEventListener('scroll', onScrollTick, { passive: true });
                                onScrollTick();
                                
                                // Scroll to top when button is clicked
                                scrollToTopBtn.addEventListener('click', () => {
                                    window.scrollTo({
                                        top: 0,
                                        behavior: 'smooth'
                                    });
                                });
                            }
                            // Initial position update
                            updateMobileDockPosition();
                        }
                        
                        // 2. Desktop Dock & Mobile Dock
                        const dock = document.getElementById('quickAccessDock');
                        if (dock) {
                            const dockEnabled = settings.desktop_dock_enabled !== false; // Default true
                            const mobileDockEnabled = settings.mobile_dock_enabled !== false; // Default true
                            
                            function updateDockVisibility() {
                                const isMobile = window.innerWidth <= 768;
                                
                                if (isMobile) {
                                    // Mobile: check mobile_dock_enabled
                                    // Control visibility using mobile-minimized class
                                    if (mobileDockEnabled !== false) {
                                        dock.style.display = '';
                                        // Ensure mobile-minimized class is present for mobile
                                        if (!dock.classList.contains('mobile-minimized') && !dock.classList.contains('mobile-expanded')) {
                                            dock.classList.add('mobile-minimized');
                                        }
                                        // Remove active state if present
                                        dock.classList.remove('active');
                                    } else {
                                        dock.style.display = 'none';
                                    }
                                } else {
                                    // Desktop: check desktop_dock_enabled
                                    dock.style.display = dockEnabled !== false ? '' : 'none';
                                    // Remove mobile classes on desktop
                                    dock.classList.remove('mobile-minimized', 'mobile-expanded', 'active');
                                }
                            }
                            
                            // Initial update
                            updateDockVisibility();
                            
                            // Listen for window resize to update dock visibility
                            let resizeTimeout;
                            window.addEventListener('resize', function() {
                                clearTimeout(resizeTimeout);
                                resizeTimeout = setTimeout(updateDockVisibility, 100);
                            });
                        }
                        
                        // 3. Sidebar Items
                        applySidebarItems(settings.sidebar_items_enabled);
                    }
                }
            } catch (error) {
                // Silent error handling
            }
        }
        
        // Apply sidebar items visibility
        function applySidebarItems(enabledItems) {
            if (!enabledItems) {
                // Default: all items enabled
                return;
            }
            
            // Parse if string
            if (typeof enabledItems === 'string') {
                enabledItems = JSON.parse(enabledItems);
            }
            
            // Define sidebar item mappings
            const sidebarMappings = {
                'dashboard': '/doctor/dashboard',
                'calendar': '/doctor/calendar',
                'patients': '/doctor/patients',
                'board': '/doctor/board',
                'drugs': '/doctor/drugs',
                'tags': '/doctor/instruction-templates',
                'payments': '/doctor/payments',
                'reports': '/doctor/reports',
                'media': '/doctor/media',
                'glasses': '/doctor/glasses',
                'medications': '/doctor/medications',
                'alerts': '/doctor/alerts',
                'notes': '/doctor/notes',
                'settings': '/doctor/settings',
                'profile': '/doctor/profile',
                'about': '/about',
                'logout': '/logout'
            };
            
            // Hide/show sidebar items
            Object.keys(sidebarMappings).forEach(key => {
                const url = sidebarMappings[key];
                const navItems = document.querySelectorAll(`.nav-menu .nav-item a[href="${url}"]`);
                navItems.forEach(item => {
                    const navItem = item.closest('.nav-item');
                    if (navItem) {
                        if (enabledItems.includes(key)) {
                            navItem.style.display = '';
                        } else {
                            navItem.style.display = 'none';
                        }
                    }
                });
            });
        }
        
        // Callback for settings page to apply preferences
        window.applyPersonalPreferencesCallback = function(preferences) {
            if (preferences.back_to_top_display !== undefined) {
                const scrollToTopBtn = document.getElementById('scrollToTop');
                if (scrollToTopBtn) {
                    scrollToTopBtn.style.display = preferences.back_to_top_display !== false ? '' : 'none';
                }
            }
            
            if (preferences.desktop_dock_enabled !== undefined) {
                const dock = document.getElementById('quickAccessDock');
                if (dock) {
                    dock.style.display = preferences.desktop_dock_enabled !== false ? '' : 'none';
                }
            }
            
            if (preferences.sidebar_items_enabled) {
                applySidebarItems(preferences.sidebar_items_enabled);
            }
        };
        
        // Load preferences on page load
        loadAndApplyPersonalPreferences();
        
        // Alert System - Real-time notification system like chat
        (function() {
            let alertCheckInterval = null;
            let isChecking = false;
            let shownAlertIds = new Set(); // Track shown alerts in current session
            const POLLING_INTERVAL = 5000; // Check every 5 seconds for more responsive alerts
            const MIN_CHECK_INTERVAL = 1000; // Minimum 1 second between checks
            
            // Create toast container immediately
            function createToastContainer() {
                const container = document.getElementById('toastContainer');
                if (container) return container;
                
                const newContainer = document.createElement('div');
                newContainer.id = 'toastContainer';
                newContainer.className = 'toast-container position-fixed bottom-0 start-50 translate-middle-x p-3';
                newContainer.style.zIndex = '99999';
                newContainer.style.pointerEvents = 'none';
                document.body.appendChild(newContainer);
                return newContainer;
            }
            
            // Initialize toast container on page load
            createToastContainer();
            
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Play notification sound
            function playNotificationSound() {
                try {
                    // Create a simple notification sound using Web Audio API
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Simple pleasant notification tone
                    oscillator.frequency.value = 800; // Hz
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                } catch (error) {
                    // Silent error handling
                }
            }
            
            function checkAlerts() {
                // Prevent multiple simultaneous checks
                if (isChecking) return;
                
                // Throttle checks - don't check too frequently
                const now = Date.now();
                if (now - (window.lastAlertCheckTime || 0) < MIN_CHECK_INTERVAL) {
                    return;
                }
                
                isChecking = true;
                window.lastAlertCheckTime = now;
                
                // Get current date and time for accurate checking
                const currentDate = new Date().toISOString().split('T')[0];
                const currentDateTime = new Date();
                const hours = String(currentDateTime.getHours()).padStart(2, '0');
                const minutes = String(currentDateTime.getMinutes()).padStart(2, '0');
                const seconds = String(currentDateTime.getSeconds()).padStart(2, '0');
                // Use current time without seconds for more lenient matching (allows alerts to show within the same minute)
                const currentTime = `${hours}:${minutes}:00`; // HH:mm:00 format - allows alerts to show throughout the minute
                
                fetch(`/api/alerts/active?date=${currentDate}&time=${currentTime}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    cache: 'no-cache',
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.alerts && data.alerts.length > 0) {
                        // Show only new alerts that haven't been shown yet
                        data.alerts.forEach(alert => {
                            // Create unique key for this alert instance
                            const alertKey = `${alert.id}_${alert.alert_date}_${alert.alert_time}`;
                            
                            // Check if this exact alert instance was already shown
                            if (!shownAlertIds.has(alertKey)) {
                                // Small delay to ensure DOM is ready
                                setTimeout(() => {
                                playNotificationSound(); // Play sound for new alert
                                showAlertToast(alert);
                                shownAlertIds.add(alertKey);
                                }, 100);
                            }
                        });
                    }
                })
                .catch(error => {
                    // Silent error handling
                })
                .finally(() => {
                    isChecking = false;
                });
            }
            
            function showAlertToast(alert) {
                if (!alert || !alert.id) return;
                
                const toastContainer = createToastContainer();
                const uniqueId = `${alert.id}_${alert.alert_date}_${alert.alert_time}_${Date.now()}`;
                const toastId = 'alert-toast-' + uniqueId;
                
                // Check if toast already exists (prevent duplicates)
                if (document.getElementById(toastId)) {
                    return;
                }
                
                // Check if there's already a toast for this alert (by checking all existing toasts)
                const existingToasts = toastContainer.querySelectorAll('.toast[data-alert-unique-id]');
                let alreadyShown = false;
                existingToasts.forEach(existingToast => {
                    const existingAlertId = existingToast.getAttribute('data-alert-id');
                    const existingDate = existingToast.getAttribute('data-alert-date');
                    const existingTime = existingToast.getAttribute('data-alert-time');
                    if (existingAlertId == alert.id && existingDate == alert.alert_date && existingTime == alert.alert_time) {
                        alreadyShown = true;
                    }
                });
                
                if (alreadyShown) {
                    return;
                }
                
                const patientName = alert.patient_first_name && alert.patient_last_name 
                    ? `${alert.patient_first_name} ${alert.patient_last_name}` 
                    : 'Patient';
                const patientLink = alert.patient_id ? `/doctor/patients/${alert.patient_id}` : '#';
                
                // Convert 24-hour time to 12-hour format for display
                let displayTime = '';
                if (alert.alert_time) {
                    const [hours, minutes] = alert.alert_time.split(':');
                    const hour24 = parseInt(hours);
                    const hour12 = hour24 === 0 ? 12 : (hour24 > 12 ? hour24 - 12 : hour24);
                    const period = hour24 >= 12 ? 'PM' : 'AM';
                    displayTime = `${hour12}:${minutes} ${period}`;
                }
                
                const toastHtml = `
                    <div id="${toastId}" class="toast alert-toast-glass align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-alert-id="${alert.id}" data-alert-date="${alert.alert_date}" data-alert-time="${alert.alert_time}" data-alert-unique-id="${uniqueId}" style="min-width: 550px; max-width: 700px; pointer-events: auto; z-index: 99999;">
                        <div class="d-flex align-items-center">
                            <div class="toast-body flex-grow-1">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem; margin-top: 2px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="alert-message-content">${alert.message}</div>
                                        ${alert.patient_id ? `<br><small><i class="bi bi-person me-1"></i>${escapeHtml(patientName)}</small>` : ''}
                                        ${alert.alert_date && displayTime ? `<br><small><i class="bi bi-clock me-1"></i>${escapeHtml(alert.alert_date)} ${escapeHtml(displayTime)}</small>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 me-2" style="flex-shrink: 0;">
                                ${alert.patient_id ? `
                                    <a href="${patientLink}" class="btn btn-sm btn-light alert-toast-btn view-patient-btn" data-alert-id="${alert.id}" data-toast-id="${toastId}" style="white-space: nowrap;">
                                        <i class="bi bi-person me-1"></i>View Patient
                                    </a>
                                ` : ''}
                                <button type="button" class="btn btn-sm alert-toast-btn snooze-btn" data-alert-id="${alert.id}" data-toast-id="${toastId}" style="white-space: nowrap;">
                                    <i class="bi bi-clock me-1"></i>Snooze
                                </button>
                                <button type="button" class="btn-close alert-toast-close-btn" data-bs-dismiss="toast" aria-label="Close" data-alert-id="${alert.id}" data-toast-id="${toastId}"></button>
                            </div>
                        </div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                
                if (toastElement) {
                    // Add event listeners for buttons
                    const viewPatientBtn = toastElement.querySelector('a[data-alert-id]');
                    const snoozeBtn = toastElement.querySelector('.snooze-btn');
                    const closeBtn = toastElement.querySelector('.btn-close');
                    
                    if (viewPatientBtn) {
                        viewPatientBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const alertId = parseInt(this.getAttribute('data-alert-id'));
                            const toastId = this.getAttribute('data-toast-id');
                            dismissAlert(alertId, toastId);
                            // Navigate to patient page after dismissing
                            setTimeout(() => {
                                window.location.href = patientLink;
                            }, 300);
                        });
                    }
                    
                    if (snoozeBtn) {
                        snoozeBtn.addEventListener('click', function() {
                            const alertId = parseInt(this.getAttribute('data-alert-id'));
                            const toastId = this.getAttribute('data-toast-id');
                            dismissAlert(alertId, toastId);
                        });
                    }
                    
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            const alertId = parseInt(this.getAttribute('data-alert-id'));
                            const toastId = this.getAttribute('data-toast-id');
                            dismissAlert(alertId, toastId);
                        });
                    }
                    
                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: false,
                        delay: 0
                    });
                    
                    // Add exit animation when toast is being hidden
                    toastElement.addEventListener('hide.bs.toast', function() {
                        if (!toastElement.classList.contains('hiding')) {
                            toastElement.classList.add('hiding');
                        }
                    });
                    
                    toast.show();
                    
                    toastElement.addEventListener('hidden.bs.toast', function() {
                        toastElement.remove();
                    });
                    
                    // Send push notification if enabled
                    if (window.sendPushNotification) {
                        window.sendPushNotification(alert).catch(error => {
                            // Silent error handling
                        });
                    }
                }
            }
            
            function dismissAlert(alertId, toastId) {
                if (!alertId || !toastId) return;
                
                fetch('/api/alerts/dismiss', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ alert_id: alertId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const toastElement = document.getElementById(toastId);
                        if (toastElement) {
                            // Add hiding class for smooth exit animation
                            toastElement.classList.add('hiding');
                            const toast = bootstrap.Toast.getInstance(toastElement);
                            if (toast) {
                                // Wait a bit for animation to start
                                setTimeout(() => {
                                    toast.hide();
                                }, 50);
                            } else {
                                // If no toast instance, remove after animation
                                setTimeout(() => {
                                    toastElement.remove();
                                }, 400);
                            }
                        }
                    }
                })
                .catch(error => {
                    // Silent error handling
                    // Still try to hide the toast
                    const toastElement = document.getElementById(toastId);
                    if (toastElement) {
                        // Add hiding class for smooth exit animation
                        toastElement.classList.add('hiding');
                        const toast = bootstrap.Toast.getInstance(toastElement);
                        if (toast) {
                            // Wait a bit for animation to start
                            setTimeout(() => {
                                toast.hide();
                            }, 50);
                        } else {
                            // If no toast instance, remove after animation
                            setTimeout(() => {
                                toastElement.remove();
                            }, 400);
                        }
                    }
                });
            }
            
            // Make dismissAlert available globally
            window.dismissAlert = dismissAlert;
            
            // Start polling system - like real-time chat
            function startAlertPolling() {
                // Clear any existing interval
                if (alertCheckInterval) {
                    clearInterval(alertCheckInterval);
                }
                
                // Check immediately
                checkAlerts();
                
                // Set up continuous polling (like chat system)
                alertCheckInterval = setInterval(() => {
                    checkAlerts();
                }, POLLING_INTERVAL);
            }
            
            // Initialize polling system
            function initAlertSystem() {
                // Start polling immediately
                startAlertPolling();
                
                // Also check when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(() => {
                            checkAlerts();
                        }, 500);
                    });
                } else {
                    // DOM already ready, check immediately
                    setTimeout(() => {
                        checkAlerts();
                    }, 500);
                }
                
                // Check when page becomes visible (user switches tabs back)
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        // Reset check time to allow immediate check
                        window.lastAlertCheckTime = 0;
                        setTimeout(() => {
                            checkAlerts();
                        }, 300);
                    }
                });
                
                // Check on window focus
                window.addEventListener('focus', function() {
                    window.lastAlertCheckTime = 0;
                    setTimeout(() => {
                        checkAlerts();
                    }, 300);
                });
                
                // Check when user interacts with page (like chat systems)
                ['click', 'keydown', 'mousemove'].forEach(eventType => {
                    document.addEventListener(eventType, function() {
                        // Reset check time occasionally to allow checks
                        if (Math.random() < 0.1) { // 10% chance
                            window.lastAlertCheckTime = 0;
                        }
                    }, { passive: true });
                });
                
                // Clean up on page unload
                window.addEventListener('beforeunload', function() {
                    if (alertCheckInterval) {
                        clearInterval(alertCheckInterval);
                    }
                });
            }
            
            // Start the alert system immediately
            initAlertSystem();
            
            // Make checkAlerts available globally for manual triggering
            window.checkAlerts = checkAlerts;
        })();
        
        // Push Notifications System
        (function() {
            let pushSubscription = null;
            let isPushEnabled = false;
            
            // Check if browser supports Push Notifications
            function isPushSupported() {
                return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
            }
            
            // Register Service Worker
            async function registerServiceWorker() {
                if (!isPushSupported()) {
                    return null;
                }
                
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    return registration;
                } catch (error) {
                    // Silent error handling
                    return null;
                }
            }
            
            // Request notification permission
            async function requestNotificationPermission() {
                if (!('Notification' in window)) {
                    return 'denied';
                }
                
                if (Notification.permission === 'granted') {
                    return 'granted';
                }
                
                const permission = await Notification.requestPermission();
                return permission;
            }
            
            // Subscribe to Push Notifications
            async function subscribeToPush(registration) {
                try {
                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(getVapidPublicKey())
                    });
                    
                    pushSubscription = subscription;
                    await savePushSubscription(subscription);
                    return subscription;
                } catch (error) {
                    // Silent error handling
                    return null;
                }
            }
            
            // Convert VAPID key from base64 URL to Uint8Array
            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');
                
                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }
            
            // Get VAPID public key
            // To generate VAPID keys, use one of these methods:
            // 1. PHP script: php generate_vapid_keys.php (in project root)
            // 2. Node.js: npx web-push generate-vapid-keys
            // 3. Online tool: https://tools.reactpwa.com/vapid
            // 4. Python: pip install py-vapid && python -c "from py_vapid import Vapid01; v=Vapid01(); v.generate_keys(); print(v.public_key)"
            function getVapidPublicKey() {
                // VAPID Public Key - Generated using: php generate_vapid_keys.php
                return 'BM81HP8k4re4ObeiBgk2BSdC3FDx5Ke8-XbtPF_RbsEF5M6SC0OyHcygclxzQbPeiY8re_q6Hco16kLvol-4ozg';
            }
            
            // Save push subscription to database (supports multiple browsers)
            async function savePushSubscription(subscription) {
                try {
                    // Get current subscriptions array
                    const settings = await loadPushSettings();
                    let subscriptionsArray = [];
                    
                    // If there are existing subscriptions, load them
                    if (settings.subscription) {
                        // Check if it's an array or single subscription (for backward compatibility)
                        if (Array.isArray(settings.subscription)) {
                            subscriptionsArray = settings.subscription;
                        } else {
                            // Convert single subscription to array for backward compatibility
                            subscriptionsArray = [settings.subscription];
                        }
                    }
                    
                    // Check if this subscription already exists (by endpoint)
                    const subscriptionEndpoint = subscription.endpoint;
                    const existingIndex = subscriptionsArray.findIndex(sub => {
                        const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                        return subEndpoint === subscriptionEndpoint;
                    });
                    
                    // Convert subscription to object if needed
                    const subscriptionObj = typeof subscription === 'string' ? JSON.parse(subscription) : subscription;
                    
                    if (existingIndex >= 0) {
                        // Update existing subscription
                        subscriptionsArray[existingIndex] = subscriptionObj;
                    } else {
                        // Add new subscription
                        subscriptionsArray.push(subscriptionObj);
                    }
                    
                    // Save updated subscriptions array
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            push_notifications_enabled: true,
                            push_subscription: subscriptionsArray
                        })
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            isPushEnabled = true;
                            pushSubscription = subscriptionObj;
                            return true;
                        }
                    }
                    return false;
                } catch (error) {
                    // Silent error handling
                    return false;
                }
            }
            
            // Get browser identifier (unique for each browser)
            function getBrowserIdentifier() {
                // Use user agent + origin as browser identifier
                return navigator.userAgent + '|' + window.location.origin;
            }
            
            // Load push notification settings (supports multiple browsers)
            async function loadPushSettings() {
                try {
                    const response = await fetch('/api/doctor/settings', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.settings) {
                            isPushEnabled = data.settings.push_notifications_enabled === true || 
                                          data.settings.push_notifications_enabled === '1' ||
                                          data.settings.push_notifications_enabled === 1;
                            
                            let subscriptionData = null;
                            if (data.settings.push_subscription) {
                                // Parse subscription data
                                subscriptionData = typeof data.settings.push_subscription === 'string' 
                                    ? JSON.parse(data.settings.push_subscription)
                                    : data.settings.push_subscription;
                                
                                // Handle backward compatibility: if it's a single subscription, convert to array
                                if (!Array.isArray(subscriptionData) && subscriptionData.endpoint) {
                                    subscriptionData = [subscriptionData];
                                }
                            }
                            
                            // Get browsers that declined push notifications
                            let declinedBrowsers = [];
                            if (data.settings.dont_ask_push_notifications_browsers) {
                                declinedBrowsers = typeof data.settings.dont_ask_push_notifications_browsers === 'string' 
                                    ? JSON.parse(data.settings.dont_ask_push_notifications_browsers)
                                    : data.settings.dont_ask_push_notifications_browsers;
                                
                                // Handle backward compatibility: if it's not an array, convert to array
                                if (!Array.isArray(declinedBrowsers)) {
                                    declinedBrowsers = [];
                                }
                            }
                            
                            // Get remind later timestamp
                            let remindLaterTimestamp = null;
                            if (data.settings.push_notification_remind_later) {
                                remindLaterTimestamp = parseInt(data.settings.push_notification_remind_later) || null;
                            }
                            
                            // Check if current browser is in declined list
                            const currentBrowserId = getBrowserIdentifier();
                            const isDeclined = declinedBrowsers.includes(currentBrowserId);
                            
                            // v12: weekly reminder cadence — the prompt re-surfaces at
                            // most once every 7 days (was 24h). The timestamp is recorded
                            // whenever the toast is shown, so closing it still snoozes a week.
                            const now = Date.now();
                            const oneWeekInMs = 7 * 24 * 60 * 60 * 1000; // 7 days
                            const shouldRemind = !remindLaterTimestamp || (now - remindLaterTimestamp) >= oneWeekInMs;
                            
                            return { 
                                enabled: isPushEnabled, 
                                subscription: subscriptionData, 
                                declinedBrowsers: declinedBrowsers, 
                                isDeclined: isDeclined,
                                remindLaterTimestamp: remindLaterTimestamp,
                                shouldRemind: shouldRemind
                            };
                        }
                    }
                } catch (error) {
                    // Silent error handling
                }
                return { enabled: false, subscription: null, declinedBrowsers: [], isDeclined: false, remindLaterTimestamp: null, shouldRemind: true };
            }
            
            // Compare two subscriptions to check if they're the same
            function compareSubscriptions(sub1, sub2) {
                if (!sub1 || !sub2) return false;
                
                // Compare endpoint (unique identifier for each browser/device)
                const endpoint1 = sub1.endpoint || '';
                const endpoint2 = sub2.endpoint || '';
                
                return endpoint1 === endpoint2;
            }
            
            // Get current browser subscription
            async function getCurrentBrowserSubscription(registration) {
                try {
                    const subscription = await registration.pushManager.getSubscription();
                    return subscription;
                } catch (error) {
                    // Silent error handling
                    return null;
                }
            }
            
            // Find subscription in array by endpoint
            function findSubscriptionByEndpoint(subscriptionsArray, endpoint) {
                if (!subscriptionsArray || !Array.isArray(subscriptionsArray)) {
                    return null;
                }
                
                for (let i = 0; i < subscriptionsArray.length; i++) {
                    const sub = subscriptionsArray[i];
                    const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                    if (subEndpoint === endpoint) {
                        return typeof sub === 'string' ? JSON.parse(sub) : sub;
                    }
                }
                
                return null;
            }
            
            // Check if current browser has valid subscription (supports multiple browsers)
            async function checkBrowserSubscription(registration) {
                try {
                    // Get current browser's subscription
                    const currentSubscription = await getCurrentBrowserSubscription(registration);
                    
                    if (!currentSubscription) {
                        // No subscription in this browser
                        return { isValid: false, needsSetup: true, reason: 'no_subscription' };
                    }
                    
                    // Load saved subscriptions from database
                    const settings = await loadPushSettings();
                    const savedSubscriptions = settings.subscription;
                    
                    // If push is enabled but no saved subscriptions, show toast
                    if (settings.enabled && (!savedSubscriptions || (Array.isArray(savedSubscriptions) && savedSubscriptions.length === 0))) {
                        return { isValid: false, needsSetup: true, reason: 'no_saved_subscriptions' };
                    }
                    
                    // If push is enabled, check if current subscription exists in saved subscriptions
                    if (settings.enabled && savedSubscriptions) {
                        const subscriptionsArray = Array.isArray(savedSubscriptions) ? savedSubscriptions : [savedSubscriptions];
                        const currentEndpoint = currentSubscription.endpoint;
                        const foundSubscription = findSubscriptionByEndpoint(subscriptionsArray, currentEndpoint);
                        
                        if (foundSubscription) {
                            // Current browser subscription found - all good
                            pushSubscription = currentSubscription;
                            return { isValid: true, needsSetup: false };
                        } else {
                            // Different browser/device - needs new subscription
                            return { isValid: false, needsSetup: true, reason: 'different_browser' };
                        }
                    }
                    
                    // If push is not enabled
                    if (!settings.enabled) {
                        return { isValid: false, needsSetup: true, reason: 'not_enabled' };
                    }
                    
                    return { isValid: true, needsSetup: false };
                } catch (error) {
                    // Silent error handling
                    return { isValid: false, needsSetup: true };
                }
            }
            
            // Save "Remind me later" setting (24 hours)
            async function saveRemindLater() {
                try {
                    const timestamp = Date.now(); // Current timestamp
                    
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            push_notification_remind_later: timestamp
                        })
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        return data.success;
                    }
                    return false;
                } catch (error) {
                    // Silent error handling
                    return false;
                }
            }
            
            // Save "Don't ask for this browser" setting
            async function saveDontAskForThisBrowser() {
                try {
                    // Get current browser identifier
                    const currentBrowserId = getBrowserIdentifier();
                    
                    // Load current settings
                    const settings = await loadPushSettings();
                    let declinedBrowsers = settings.declinedBrowsers || [];
                    
                    // Add current browser to declined list if not already there
                    if (!declinedBrowsers.includes(currentBrowserId)) {
                        declinedBrowsers.push(currentBrowserId);
                    }
                    
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            dont_ask_push_notifications_browsers: declinedBrowsers
                        })
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        return data.success;
                    }
                    return false;
                } catch (error) {
                    // Silent error handling
                    return false;
                }
            }
            
            // Show toast to enable push notifications
            async function showPushNotificationToast() {
                // Check if current browser has declined push notifications
                const settings = await loadPushSettings();
                if (settings.isDeclined) {
                    return; // Don't show toast if this browser declined
                }

                // v12: already subscribed → never remind
                if (settings.enabled) {
                    return;
                }

                // v12: weekly cadence — skip if shown within the last 7 days
                if (!settings.shouldRemind) {
                    return; // Don't show toast if remind later is still active
                }

                // Create separate toast container in the center of screen for push notifications
                let pushToastContainer = document.getElementById('pushToastContainer');
                if (!pushToastContainer) {
                    pushToastContainer = document.createElement('div');
                    pushToastContainer.id = 'pushToastContainer';
                    pushToastContainer.className = 'toast-container push-toast-container';
                    document.body.appendChild(pushToastContainer);
                }
                const toastId = 'push-notification-toast';
                
                // Check if toast already exists
                if (document.getElementById(toastId)) {
                    return;
                }
                
                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center text-white push-notification-toast border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                        <div class="toast-header push-toast-header">
                            <div class="d-flex align-items-center flex-grow-1">
                                <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
                                <strong class="me-auto">Enable Push Notifications</strong>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <div>Get notified with your alerts, notes, appointments and more even when the browser is closed<br>You can disable this in your browser settings.</div>
                        </div>
                        <div class="toast-footer push-toast-footer">
                            <div class="d-flex align-items-center gap-2 w-100 flex-wrap">
                                <button type="button" class="btn btn-sm btn-light" id="enablePushBtn">
                                    <i class="bi bi-check-circle me-1"></i>Enable
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="remindMeLaterBtn" style="font-size: 0.75rem; white-space: nowrap;">
                                    <i class="bi bi-clock me-1"></i>Remind me later
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="dontAskForThisBrowserBtn" style="font-size: 0.75rem; white-space: nowrap;">
                                    <i class="bi bi-x-circle me-1"></i>Don't ask for this browser
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                pushToastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                
                if (toastElement) {
                    const enableBtn = toastElement.querySelector('#enablePushBtn');
                    const closeBtn = toastElement.querySelector('.btn-close');
                    const remindMeLaterBtn = toastElement.querySelector('#remindMeLaterBtn');
                    const dontAskForThisBrowserBtn = toastElement.querySelector('#dontAskForThisBrowserBtn');
                    
                    if (enableBtn) {
                        enableBtn.addEventListener('click', async function() {
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enabling...';
                            
                            const permission = await requestNotificationPermission();
                            
                            if (permission === 'granted') {
                                const registration = await navigator.serviceWorker.ready;
                                const subscription = await subscribeToPush(registration);
                                
                                if (subscription) {
                                    // Add hiding class for exit animation
                                    toastElement.classList.add('hiding');
                                    const toast = bootstrap.Toast.getInstance(toastElement);
                                    if (toast) {
                                        // Wait for animation to complete before hiding
                                        setTimeout(() => {
                                            toast.hide();
                                        }, 300);
                                    }
                                    showToast('success', 'Push Notifications Enabled', 'You will now receive notifications even when the browser is closed.');
                                } else {
                                    this.disabled = false;
                                    this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Enable';
                                    showToast('error', 'Error', 'Failed to enable push notifications. Please try again.');
                                }
                            } else {
                                this.disabled = false;
                                this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Enable';
                                showToast('error', 'Permission Denied', 'Please allow notifications in your browser settings.');
                            }
                        });
                    }
                    
                    if (remindMeLaterBtn) {
                        remindMeLaterBtn.addEventListener('click', async function() {
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                            
                            const saved = await saveRemindLater();
                            
                            if (saved) {
                                // Add hiding class for exit animation
                                toastElement.classList.add('hiding');
                                const toast = bootstrap.Toast.getInstance(toastElement);
                                if (toast) {
                                    // Wait for animation to complete before hiding
                                    setTimeout(() => {
                                        toast.hide();
                                    }, 300);
                                }
                            } else {
                                this.disabled = false;
                                this.innerHTML = '<i class="bi bi-clock me-1"></i>Remind me later';
                                showToast('error', 'Error', 'Failed to save preference. Please try again.');
                            }
                        });
                    }
                    
                    if (dontAskForThisBrowserBtn) {
                        dontAskForThisBrowserBtn.addEventListener('click', async function() {
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                            
                            const saved = await saveDontAskForThisBrowser();
                            
                            if (saved) {
                                // Add hiding class for exit animation
                                toastElement.classList.add('hiding');
                                const toast = bootstrap.Toast.getInstance(toastElement);
                                if (toast) {
                                    // Wait for animation to complete before hiding
                                    setTimeout(() => {
                                        toast.hide();
                                    }, 300);
                                }
                            } else {
                                this.disabled = false;
                                this.innerHTML = '<i class="bi bi-x-circle me-1"></i>Don\'t ask for this browser';
                                showToast('error', 'Error', 'Failed to save preference. Please try again.');
                            }
                        });
                    }
                    
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            // Add hiding class for exit animation
                            toastElement.classList.add('hiding');
                            const toast = bootstrap.Toast.getInstance(toastElement);
                            if (toast) {
                                // Wait for animation to complete before hiding
                                setTimeout(() => {
                                    toast.hide();
                                }, 300);
                            }
                        });
                    }
                    
                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: false,
                        delay: 0
                    });
                    toast.show();

                    // v12: record that the prompt was shown → the weekly cadence holds
                    // even if the user just closes it (next prompt is +7 days out).
                    saveRemindLater();

                    // Add exit animation when toast is being hidden
                    toastElement.addEventListener('hide.bs.toast', function() {
                        if (!toastElement.classList.contains('hiding')) {
                            toastElement.classList.add('hiding');
                        }
                    });
                    
                    toastElement.addEventListener('hidden.bs.toast', function() {
                        toastElement.remove();
                    });
                }
            }
            
            // Send push notification using Service Worker
            // NOTE: This shows a local notification. For true push notifications from server,
            // you need to implement server-side push sending with VAPID keys
            async function sendPushNotification(alert) {
                if (!isPushEnabled) {
                    return false;
                }
                
                try {
                    // Get service worker registration
                    const registration = await navigator.serviceWorker.ready;
                    
                    // Prepare notification data
                    const patientName = alert.patient_first_name && alert.patient_last_name 
                        ? `${alert.patient_first_name} ${alert.patient_last_name}` 
                        : '';
                    const notificationTitle = 'New Alert';
                    let notificationBody = alert.message || 'You have a new alert';
                    if (patientName) {
                        notificationBody += ` - ${patientName}`;
                    }
                    
                    const notificationData = {
                        title: notificationTitle,
                        body: notificationBody,
                        icon: '/assets/images/Light.png',
                        badge: '/assets/images/Light.png',
                        tag: `alert-${alert.id}-${alert.alert_date}-${alert.alert_time}`,
                        requireInteraction: false,
                        data: {
                            alert_id: alert.id,
                            patient_id: alert.patient_id,
                            url: alert.patient_id ? `/doctor/patients/${alert.patient_id}` : '/doctor/alerts'
                        },
                        actions: alert.patient_id ? [
                            {
                                action: 'view',
                                title: 'View Patient'
                            },
                            {
                                action: 'dismiss',
                                title: 'Dismiss'
                            }
                        ] : [
                            {
                                action: 'view',
                                title: 'View Alerts'
                            }
                        ]
                    };
                    
                    // Show notification using Service Worker
                    // This works even when the page is in background or closed
                    await registration.showNotification(notificationTitle, notificationData);
                    
                    return true;
                } catch (error) {
                    // Silent error handling
                    return false;
                }
            }
            
            // Initialize push notifications system
            async function initPushNotifications() {
                if (!isPushSupported()) {
                    return;
                }
                
                // Check if current browser has declined push notifications
                const settings = await loadPushSettings();
                if (settings.isDeclined) {
                    return; // Don't show toast if this browser declined
                }

                // v12: if the user is already subscribed to notifications, never remind.
                if (settings.enabled) {
                    return;
                }

                // v12: weekly cadence — skip if shown within the last 7 days
                if (!settings.shouldRemind) {
                    return; // Don't show toast if remind later is still active
                }

                // Register service worker
                const registration = await registerServiceWorker();
                if (!registration) {
                    return;
                }
                
                // Check current browser's subscription
                const subscriptionCheck = await checkBrowserSubscription(registration);
                
                if (subscriptionCheck.needsSetup) {
                    // New browser or subscription mismatch - show toast to enable
                    // Wait a bit before showing toast to avoid overwhelming user
                    setTimeout(() => {
                        showPushNotificationToast();
                    }, 3000);
                } else if (subscriptionCheck.isValid) {
                    // Subscription is valid - pushSubscription is already set in checkBrowserSubscription
                    isPushEnabled = true;
                } else {
                    // Push not enabled - show toast
                    setTimeout(() => {
                        showPushNotificationToast();
                    }, 3000);
                }
            }
            
            // Initialize on page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPushNotifications);
            } else {
                initPushNotifications();
            }
            
            // Make sendPushNotification available globally
            window.sendPushNotification = sendPushNotification;
        })();
        
        // Quick Access Dock - Show only on desktop and Minimize functionality
        (function() {
            const dock = document.getElementById('quickAccessDock');
            const minimizeBtn = document.getElementById('dockMinimizeBtn');
            const autohideBtn = document.getElementById('dockAutohideBtn');
            const autohideIcon = document.getElementById('dockAutohideIcon');
            const autohideTooltip = document.getElementById('dockAutohideTooltip');
            
            function isMobile() {
                return window.innerWidth <= 768;
            }
            
            function initMobileDock() {
                if (!dock) return;
                
                // On mobile, dock is minimized by default
                if (isMobile()) {
                    dock.classList.add('mobile-minimized');
                    dock.classList.remove('mobile-expanded', 'autohide', 'active', 'scrolled');
                    // Hide auto-hide button on mobile
                    if (autohideBtn) {
                        autohideBtn.style.display = 'none';
                    }
                    
                    // Add click handler to dock container when minimized
                    // (C-Shape Radial Menu Toggle).
                    //
                    // We used to clone the container "to remove existing
                    // listeners", but that detaches every handler attached
                    // earlier in this DOMContentLoaded (medicalStorage stack
                    // toggle, etc.) AND makes the `minimizeBtn` ref captured
                    // above point at the old detached node — clicks on the
                    // visible minimize button did nothing. Use a one-shot
                    // dataset flag instead so we only attach once and keep
                    // every other listener intact.
                    const dockContainer = dock.querySelector('.dock-container');
                    if (dockContainer && !dockContainer.dataset.mobileInit) {
                        dockContainer.dataset.mobileInit = '1';
                        dockContainer.addEventListener('click', function(e) {
                            // Don't trigger if clicking on a dock item (radial menu item)
                            if (e.target.closest('.dock-item')) {
                                return;
                            }
                            // Don't trigger when clicking the minimize button
                            // itself — its own handler below collapses the
                            // menu; we don't want this toggle to re-open it.
                            if (e.target.closest('.dock-minimize-btn')) {
                                return;
                            }
                            // Don't trigger when tapping the chat button — it
                            // opens the chat panel via its own handler; the
                            // radial menu must stay closed.
                            if (e.target.closest('.dock-chat-btn')) {
                                return;
                            }
                            dock.classList.toggle('active');
                            updateMobileDockScrollState();
                        });
                    }
                    
                    // Close dock when clicking on a dock item (radial menu item)
                    const dockItems = dock.querySelectorAll('.dock-item');
                    dockItems.forEach(item => {
                        item.addEventListener('click', function() {
                            setTimeout(() => {
                                dock.classList.remove('active');
                            }, 300);
                        });
                    });
                    
                    // Mobile dock launcher / minimize button.
                    if (minimizeBtn) {
                        minimizeBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            // In mobile-minimized state THIS button is the
                            // launcher, so tapping it must OPEN the dock; when
                            // expanded it collapses. It previously only ever
                            // removed 'active', so tapping the minimized
                            // launcher did nothing → the dock could never be
                            // opened on mobile. Toggle fixes both directions.
                            dock.classList.toggle('active');
                            updateMobileDockScrollState();
                        });
                    }
                    
                    // Close radial menu when clicking outside
                    document.addEventListener('click', function(e) {
                        if (dock.classList.contains('active') && 
                            !dock.contains(e.target) && 
                            !e.target.closest('.quick-access-dock')) {
                            dock.classList.remove('active');
                        }
                    });
                    
                    // Handle scroll to show/hide additional item (Notes)
                    // Show them only when backToTop button is visible
                    function updateMobileDockScrollState() {
                        if (!dock.classList.contains('mobile-minimized') || !dock.classList.contains('active')) {
                            return;
                        }
                        
                        const scrollToTopBtn = document.getElementById('scrollToTop');
                        // Show additional items when backToTop button is visible
                        if (scrollToTopBtn && scrollToTopBtn.classList.contains('show')) {
                            dock.classList.add('backtotop-visible');
                        } else {
                            dock.classList.remove('backtotop-visible');
                        }
                    }
                    
                    // Update on scroll
                    let scrollTimeout;
                    window.addEventListener('scroll', function() {
                        clearTimeout(scrollTimeout);
                        scrollTimeout = setTimeout(updateMobileDockScrollState, 50);
                    }, { passive: true });
                    
                    // Also update when backToTop button visibility changes
                    const scrollToTopBtn = document.getElementById('scrollToTop');
                    if (scrollToTopBtn) {
                        // Use MutationObserver to watch for class changes on scrollToTop button
                        const observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                    updateMobileDockScrollState();
                                }
                            });
                        });
                        
                        observer.observe(scrollToTopBtn, {
                            attributes: true,
                            attributeFilter: ['class']
                        });
                    }
                    
                    // Initial check
                    updateMobileDockScrollState();
                } else {
                    // Desktop: remove mobile classes
                    dock.classList.remove('mobile-minimized', 'mobile-expanded', 'active', 'scrolled');
                }
            }
            
            function updateDockVisibility() {
                if (!dock) return;

                // Handle mobile dock (<= 768px)
                if (isMobile()) {
                    // Remove desktop minimized class if exists
                    dock.classList.remove('minimized', 'autohide');
                    // Hide auto-hide button on mobile
                    if (autohideBtn) {
                        autohideBtn.style.display = 'none';
                    }
                    initMobileDock();
                    /* Reveal the dock now that the mobile-minimized class
                       (applied by initMobileDock) is on. The HTML rendered
                       with inline visibility:hidden so the first paint
                       doesn't show a full-size dock that JS then shrinks. */
                    dock.style.visibility = '';
                    return;
                }

                // Desktop: show dock and remove mobile classes (>= 769px)
                if (window.innerWidth >= 769) {
                    dock.style.display = 'block';
                    dock.classList.remove('mobile-minimized', 'mobile-expanded');
                    // Show auto-hide button on desktop (only if not minimized)
                    if (autohideBtn) {
                        if (!dock.classList.contains('minimized')) {
                            autohideBtn.style.display = 'flex';
                        } else {
                            autohideBtn.style.display = 'none';
                        }
                    }
                    // Load desktop dock state (reads localStorage for
                    // 'dock_minimized' / 'dock_autohide' before revealing).
                    loadDockState();
                    dock.style.visibility = '';
                } else {
                    dock.style.display = 'none';
                    /* Even when hidden, drop the inline visibility:hidden
                       so a later resize back to mobile/desktop reveals
                       the dock without us having to touch this again. */
                    dock.style.visibility = '';
                }
            }
            
            // Load dock minimized state from localStorage
            function loadDockState() {
                if (!dock) return;
                
                try {
                    // Load minimized state from localStorage
                    const savedMinimized = localStorage.getItem('dock_minimized');
                    const isMinimized = savedMinimized === 'true' || savedMinimized === '1';
                    
                    if (isMinimized) {
                        dock.classList.add('minimized');
                    } else {
                        dock.classList.remove('minimized');
                    }
                    
                    // Load auto-hide state from localStorage (only if dock is not minimized)
                    if (!isMinimized) {
                        const savedAutohide = localStorage.getItem('dock_autohide');
                        const isAutohide = savedAutohide === 'true' || savedAutohide === '1';
                        
                        if (isAutohide) {
                            dock.classList.add('autohide');
                            if (autohideBtn) {
                                autohideBtn.classList.add('active');
                                autohideBtn.style.display = 'flex';
                            }
                        } else {
                            dock.classList.remove('autohide');
                            if (autohideBtn) {
                                autohideBtn.classList.remove('active');
                                autohideBtn.style.display = 'flex';
                            }
                        }
                    } else {
                        // Dock is minimized, remove autohide
                        dock.classList.remove('autohide');
                        if (autohideBtn) {
                            autohideBtn.classList.remove('active');
                            autohideBtn.style.display = 'none';
                        }
                    }
                    
                    // Update button titles after loading state
                    updateMinimizeButtonTitle();
                    updateAutohideButtonState();
                } catch (error) {
                    // Silent error handling
                    // Default: not minimized, not auto-hide on error
                    dock.classList.remove('minimized', 'autohide');
                    if (autohideBtn) autohideBtn.classList.remove('active');
                    updateMinimizeButtonTitle();
                    updateAutohideButtonState();
                }
            }
            
            // Save dock minimized state to localStorage and database
            function saveDockState(isMinimized) {
                try {
                    // Save to localStorage first (immediate, no delay)
                    localStorage.setItem('dock_minimized', isMinimized ? 'true' : 'false');
                    
                    // Save to database asynchronously (in background, doesn't block UI)
                    saveDockStateToDatabase(isMinimized).catch(error => {
                        // Silent error handling - localStorage is already saved
                    });
                } catch (error) {
                    // Silent error handling
                    console.error('Error saving dock state to localStorage:', error);
                }
            }
            
            // Save dock minimized state to database (async, non-blocking)
            async function saveDockStateToDatabase(isMinimized) {
                try {
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            dock_minimized: isMinimized ? '1' : '0'
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to save dock state');
                    }
                    
                    const data = await response.json();
                    if (!data.success) {
                        throw new Error('Failed to save dock state');
                    }
                } catch (error) {
                    // Silent error handling - localStorage is already saved
                }
            }
            
            // Save dock auto-hide state to localStorage and database
            function saveAutohideState(isAutohide) {
                try {
                    // Save to localStorage first (immediate, no delay)
                    localStorage.setItem('dock_autohide', isAutohide ? 'true' : 'false');
                    
                    // Save to database asynchronously (in background, doesn't block UI)
                    saveAutohideStateToDatabase(isAutohide).catch(error => {
                        // Silent error handling - localStorage is already saved
                    });
                } catch (error) {
                    // Silent error handling
                    console.error('Error saving dock autohide state to localStorage:', error);
                }
            }
            
            // Save dock auto-hide state to database (async, non-blocking)
            async function saveAutohideStateToDatabase(isAutohide) {
                try {
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            dock_autohide: isAutohide ? '1' : '0'
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to save auto-hide state');
                    }
                    
                    const data = await response.json();
                    if (!data.success) {
                        throw new Error('Failed to save auto-hide state');
                    }
                } catch (error) {
                    // Silent error handling - localStorage is already saved
                }
            }
            
            // Update auto-hide button state and icon
            function updateAutohideButtonState() {
                if (!autohideBtn || !dock) return;
                const isAutohide = dock.classList.contains('autohide');
                
                if (isAutohide) {
                    autohideBtn.classList.add('active');
                    if (autohideIcon) {
                        autohideIcon.className = 'bi bi-eye';
                    }
                    autohideBtn.setAttribute('title', 'Disable Auto Hide');
                    if (autohideTooltip) {
                        autohideTooltip.textContent = 'Disable Auto Hide';
                    }
                } else {
                    autohideBtn.classList.remove('active');
                    if (autohideIcon) {
                        autohideIcon.className = 'bi bi-eye-slash';
                    }
                    autohideBtn.setAttribute('title', 'Auto Hide Dock');
                    if (autohideTooltip) {
                        autohideTooltip.textContent = 'Auto Hide Dock';
                    }
                }
            }
            
            // Toggle dock auto-hide state
            function toggleDockAutohide() {
                if (!dock) return;
                
                // Only allow autohide when dock is not minimized
                if (dock.classList.contains('minimized')) {
                    return;
                }
                
                const isAutohide = dock.classList.contains('autohide');
                if (isAutohide) {
                    dock.classList.remove('autohide');
                    saveAutohideState(false);
                } else {
                    dock.classList.add('autohide');
                    saveAutohideState(true);
                }
                
                // Update button state
                updateAutohideButtonState();
            }
            
            // Update minimize button title based on state
            function updateMinimizeButtonTitle() {
                if (!minimizeBtn || !dock) return;
                const isMinimized = dock.classList.contains('minimized');
                minimizeBtn.setAttribute('title', isMinimized ? 'Maximize Dock' : 'Minimize Dock');
                const htooltip = document.getElementById('dockMinimizeTooltip');
                if (htooltip) {
                    htooltip.textContent = isMinimized ? 'Maximize Dock' : 'Minimize Dock';
                }
            }
            
            // Toggle dock minimized state
            function toggleDockMinimize() {
                if (!dock) return;
                
                const isMinimized = dock.classList.contains('minimized');
                if (isMinimized) {
                    dock.classList.remove('minimized');
                    saveDockState(false);
                    // Show autohide button when dock is expanded (if not mobile)
                    if (!isMobile() && autohideBtn) {
                        autohideBtn.style.display = 'flex';
                    }
                } else {
                    dock.classList.add('minimized');
                    saveDockState(true);
                    
                    // If autohide is enabled, disable it and hide the button when minimizing
                    if (dock.classList.contains('autohide')) {
                        dock.classList.remove('autohide');
                        saveAutohideState(false);
                        updateAutohideButtonState();
                    }
                    
                    // Hide autohide button when dock is minimized
                    if (autohideBtn) {
                        autohideBtn.style.display = 'none';
                    }
                }
                
                // Update button title
                updateMinimizeButtonTitle();
            }
            
            // Initialize auto-hide button
            if (autohideBtn) {
                autohideBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Only work on desktop
                    if (!isMobile()) {
                        toggleDockAutohide();
                    }
                });
            }
            
            // Initialize minimize button
            if (minimizeBtn) {
                minimizeBtn.addEventListener('click', function(e) {
                    // On mobile, handle differently
                    if (isMobile()) {
                        e.preventDefault();
                        e.stopPropagation();
                        dock.classList.remove('mobile-expanded');
                        dock.classList.add('mobile-minimized');
                    } else {
                        toggleDockMinimize();
                    }
                });
            }
            
            // Load state on page load - wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    if (!isMobile()) {
                        loadDockState();
                        // Show auto-hide button on desktop
                        if (autohideBtn) {
                            autohideBtn.style.display = 'flex';
                        }
                    } else {
                        // Hide auto-hide button on mobile
                        if (autohideBtn) {
                            autohideBtn.style.display = 'none';
                        }
                    }
                    updateDockVisibility();
                });
            } else {
                // DOM is already ready
                if (!isMobile()) {
                    loadDockState();
                    // Show auto-hide button on desktop
                    if (autohideBtn) {
                        autohideBtn.style.display = 'flex';
                    }
                } else {
                    // Hide auto-hide button on mobile
                    if (autohideBtn) {
                        autohideBtn.style.display = 'none';
                    }
                }
                updateDockVisibility();
            }
            
            // Update visibility on resize
            window.addEventListener('resize', function() {
                if (isMobile()) {
                    // Hide auto-hide button on mobile
                    if (autohideBtn) {
                        autohideBtn.style.display = 'none';
                    }
                } else {
                    // Show auto-hide button on desktop
                    if (autohideBtn) {
                        autohideBtn.style.display = 'flex';
                    }
                }
                updateDockVisibility();
            });
        })();
        
        // Make modals draggable globally
    function initializeDraggableModals() {
    /* Drag/center/animation unified in layouts/modal-kit.js. No-op. */
    return;
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            // Skip alertModal - it has its own draggable implementation
            if (modal.id === 'alertModal') {
                return;
            }
                const modalDialog = modal.querySelector('.modal-dialog');
                if (!modalDialog) return;
                
                let isDragging = false;
                let currentX;
                let currentY;
                let initialX;
                let initialY;
                let xOffset = 0;
                let yOffset = 0;
                
                // Make modal header the drag handle
                const modalHeader = modal.querySelector('.modal-header');
                if (!modalHeader) return;
                
                modalHeader.style.cursor = 'move';
                
                modalHeader.addEventListener('mousedown', dragStart);
                
                function dragStart(e) {
                    // Don't drag if clicking on buttons or inputs
                    if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                        return;
                    }
                    
                    // Only start dragging if clicking on header (not on title text)
                    if (e.target === modalHeader || (modalHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
                        // Get current transform values
                        const transform = modalDialog.style.transform;
                        if (transform) {
                            const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                            if (match) {
                                xOffset = parseFloat(match[1]) || 0;
                                yOffset = parseFloat(match[2]) || 0;
                            }
                        }
                        
                        initialX = e.clientX - xOffset;
                        initialY = e.clientY - yOffset;
                        
                        // Store initial mouse position to detect if it's a drag or click
                        const startX = e.clientX;
                        const startY = e.clientY;
                        
                        // Set a flag to track if mouse moved
                        let hasMoved = false;
                        
                        function checkMove(moveEvent) {
                            const deltaX = Math.abs(moveEvent.clientX - startX);
                            const deltaY = Math.abs(moveEvent.clientY - startY);
                            if (deltaX > 5 || deltaY > 5) {
                                hasMoved = true;
                                isDragging = true;
                                modalDialog.style.transition = 'none';
                                moveEvent.preventDefault();
                                moveEvent.stopPropagation();
                            }
                        }
                        
                        function handleMove(moveEvent) {
                            if (hasMoved) {
                                drag(moveEvent);
                            } else {
                                checkMove(moveEvent);
                            }
                        }
                        
                        function handleEnd(endEvent) {
                            if (!hasMoved) {
                                // It was just a click, allow normal behavior
                                document.removeEventListener('mousemove', handleMove);
                                document.removeEventListener('mouseup', handleEnd);
                                return;
                            }
                            dragEnd(endEvent);
                            document.removeEventListener('mousemove', handleMove);
                            document.removeEventListener('mouseup', handleEnd);
                        }
                        
                        document.addEventListener('mousemove', handleMove);
                        document.addEventListener('mouseup', handleEnd);
                    }
                }
                
                function drag(e) {
                    if (isDragging) {
                        e.preventDefault();
                        e.stopPropagation(); // Prevent modal from closing
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;
                        
                        xOffset = currentX;
                        yOffset = currentY;
                        
                        setTranslate(currentX, currentY, modalDialog);
                    }
                }
                
                function dragEnd(e) {
                    initialX = currentX;
                    initialY = currentY;
                    isDragging = false;
                    modalDialog.style.transition = '';
                }
                
                function setTranslate(xPos, yPos, el) {
                    // Get viewport dimensions
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;
                    
                    // Get modal dimensions
                    const modalRect = el.getBoundingClientRect();
                    const modalWidth = modalRect.width;
                    const modalHeight = modalRect.height;
                    
                    // Get the original position (center of viewport)
                    const originalLeft = (viewportWidth - modalWidth) / 2;
                    const originalTop = 50; // Keep at least 50px from top
                    
                    // Calculate boundaries relative to original position
                    // Allow movement within viewport bounds
                    const minX = -(originalLeft - 20); // Allow 20px from left edge
                    const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
                    const minY = -(originalTop - 20); // Allow 20px from top
                    const maxY = viewportHeight - modalHeight - originalTop - 20; // Allow 20px from bottom
                    
                    // Constrain movement
                    const constrainedX = Math.max(minX, Math.min(maxX, xPos));
                    const constrainedY = Math.max(minY, Math.min(maxY, yPos));
                    
                    el.style.transform = `translate(${constrainedX}px, ${constrainedY}px)`;
                }
                
                // Reset position when modal is hidden
                modal.addEventListener('hidden.bs.modal', function() {
                    xOffset = 0;
                    yOffset = 0;
                    modalDialog.style.transform = '';
                });
            });
        }
        
        // Initialize draggable modals when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDraggableModals);
        } else {
            initializeDraggableModals();
        }
        
        // Notifications System
        (function() {
            const notificationsToggle = document.getElementById('notificationsToggle');
            const notificationsPanel = document.getElementById('notificationsPanel');
            const notificationsOverlay = document.getElementById('notificationsOverlay');
            const notificationsBody = document.getElementById('notificationsBody');
            const notificationsBadge = document.getElementById('notificationsBadge');
            const closeNotificationsBtn = document.getElementById('closeNotificationsBtn');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const clearAllBtn = document.getElementById('clearAllBtn');
            const viewAllNotificationsBtn = document.getElementById('viewAllNotificationsBtn');
            
            let notificationsPollingInterval = null;
            let isNotificationsOpen = false;
            
            // Calculate panel height dynamically
            function calculatePanelHeight() {
                if (!notificationsBody) return;
                
                const panelHeader = document.querySelector('.notifications-panel-header');
                const headerHeight = panelHeader ? panelHeader.offsetHeight : 80;
                const buttonHeight = 60; // Height for "View All" button
                const padding = 32; // Top and bottom padding
                const availableHeight = window.innerHeight - headerHeight - buttonHeight - padding;
                
                // Set max-height for notifications panel body
                notificationsBody.style.maxHeight = Math.max(300, availableHeight) + 'px';
            }
            
            // Toggle notifications panel
            function toggleNotifications() {
                // v11 notification-center removes #notificationsPanel from the DOM;
                // bail out so classList writes never throw on a detached node.
                if (!notificationsPanel || !notificationsOverlay) return;
                isNotificationsOpen = !isNotificationsOpen;
                if (isNotificationsOpen) {
                    notificationsPanel.classList.add('show');
                    notificationsOverlay.classList.add('show');
                    // Calculate and set panel height dynamically
                    setTimeout(() => {
                        calculatePanelHeight();
                    }, 100);
                    loadNotifications();
                    startNotificationsPolling();
                } else {
                    notificationsPanel.classList.remove('show');
                    notificationsOverlay.classList.remove('show');
                    stopNotificationsPolling();
                }
            }
            
            // Close notifications
            function closeNotifications() {
                isNotificationsOpen = false;
                notificationsPanel.classList.remove('show');
                notificationsOverlay.classList.remove('show');
                stopNotificationsPolling();
            }
            
            // Recalculate on window resize
            window.addEventListener('resize', function() {
                if (isNotificationsOpen) {
                    calculatePanelHeight();
                }
            });
            
            // Event listeners
            if (notificationsToggle) {
                notificationsToggle.addEventListener('click', toggleNotifications);
            }
            
            if (closeNotificationsBtn) {
                closeNotificationsBtn.addEventListener('click', closeNotifications);
            }
            
            if (notificationsOverlay) {
                notificationsOverlay.addEventListener('click', function(e) {
                    // Only close if clicking directly on overlay, not on panel content
                    if (e.target === notificationsOverlay) {
                        closeNotifications();
                    }
                });
            }
            
            // Load notifications
            async function loadNotifications() {
                try {
                    const response = await fetch('/api/notifications?limit=50', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        // Silent error handling
                        return;
                    }
                    
                    if (data.success) {
                        const previousUnreadCount = parseInt(notificationsBadge?.textContent || '0');
                        const currentUnreadCount = data.unread_count || 0;
                        
                        // Play notification sound if new unread notifications
                        if (currentUnreadCount > previousUnreadCount) {
                            playNotificationSound();
                        }
                        
                        renderNotifications(data.notifications);
                        updateBadge(data.unread_count);
                    }
                } catch (error) {
                    // Silent error handling
                }
            }
            
            // Update unread count badge
            async function updateUnreadCount() {
                try {
                    const response = await fetch('/api/notifications/unread-count', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        // Silent error handling
                        return;
                    }
                    
                    if (data.success) {
                        updateBadge(data.unread_count);
                    }
                } catch (error) {
                    // Silent error handling
                }
            }
            
            // Update badge
            function updateBadge(count) {
                const previousCount = parseInt(notificationsBadge?.textContent || '0');
                
                if (notificationsBadge) {
                    if (count > 0) {
                        notificationsBadge.textContent = count > 99 ? '99+' : count;
                        notificationsBadge.style.display = 'flex';
                        
                        // Play notification sound if new unread notifications
                        if (count > previousCount) {
                            playNotificationSound();
                        }
                    } else {
                        notificationsBadge.style.display = 'none';
                    }
                }
            }
            
            // Play notification sound
            function playNotificationSound() {
                try {
                    // Create a simple notification sound using Web Audio API
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Simple pleasant notification tone
                    oscillator.frequency.value = 800; // Hz
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                } catch (error) {
                    // Silent error handling
                }
            }
            
            // Render notifications - calculate view height and show limited notifications
            function renderNotifications(notifications) {
                if (!notificationsBody) return;
                
                if (!notifications || notifications.length === 0) {
                    notificationsBody.innerHTML = `
                        <div class="notifications-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>No notifications</p>
                        </div>
                        <button class="view-all-notifications-btn" onclick="showAllNotifications()">
                            <i class="bi bi-list-ul me-2"></i>View All Notifications
                        </button>
                    `;
                    return;
                }
                
                // Sort by date (newest first)
                const sortedNotifications = [...notifications].sort((a, b) => {
                    return new Date(b.created_at) - new Date(a.created_at);
                });
                
                // Calculate available height dynamically
                const panelHeader = document.querySelector('.notifications-panel-header');
                const headerHeight = panelHeader ? panelHeader.offsetHeight : 80;
                const buttonHeight = 60; // Height for "View All" button
                const padding = 32; // Top and bottom padding
                const availableHeight = window.innerHeight - headerHeight - buttonHeight - padding;
                
                // Set max-height for notifications panel body if not already set
                if (notificationsBody && !notificationsBody.style.maxHeight) {
                    notificationsBody.style.maxHeight = Math.max(300, availableHeight) + 'px';
                }
                
                // Estimate notification height (average ~80px per notification)
                const notificationHeight = 80;
                const maxVisible = Math.max(3, Math.floor(availableHeight / notificationHeight));
                
                // Get visible notifications
                const visibleNotifications = sortedNotifications.slice(0, maxVisible);
                const hasMore = sortedNotifications.length > maxVisible;
                
                let html = '';
                
                // Render visible notifications
                visibleNotifications.forEach(notif => {
                    html += renderNotificationItem(notif);
                });
                
                // Always add "View All" button at the bottom
                html += `<button class="view-all-notifications-btn" onclick="showAllNotifications()">
                    <i class="bi bi-list-ul me-2"></i>View All Notifications${hasMore ? ` (${sortedNotifications.length - maxVisible} more)` : ''}
                </button>`;
                
                notificationsBody.innerHTML = html;
                
                // macOS-style animations are handled via CSS nth-child selectors
                // No need for JavaScript animation delays
                
                // Add event listeners to close buttons (now delete)
                notificationsBody.querySelectorAll('.notification-item-close').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        const notificationId = this.getAttribute('data-notification-id');
                        if (notificationId) {
                            deleteNotification(notificationId);
                        } else {
                            // Silent error handling
                        }
                    });
                });
                
                // Add hover listeners for notification items
                notificationsBody.querySelectorAll('.notification-item').forEach(item => {
                    // Add click listener for appointment-related notifications
                    const appointmentId = item.getAttribute('data-appointment-id');
                    if (appointmentId) {
                        item.addEventListener('click', function(e) {
                            // Don't trigger if clicking on buttons
                            if (e.target.closest('.notification-item-close') || 
                                e.target.closest('.notification-item-patient')) {
                                return;
                            }
                            
                            // Navigate to appointment page
                            window.location.href = `/doctor/appointments/${appointmentId}`;
                        });
                    }
                    
                    // Add swipe gesture for mobile
                    let touchStartX = 0;
                    let touchStartY = 0;
                    let touchEndX = 0;
                    let touchEndY = 0;
                    
                    item.addEventListener('touchstart', function(e) {
                        touchStartX = e.changedTouches[0].screenX;
                        touchStartY = e.changedTouches[0].screenY;
                    }, { passive: true });
                    
                    item.addEventListener('touchend', function(e) {
                        touchEndX = e.changedTouches[0].screenX;
                        touchEndY = e.changedTouches[0].screenY;
                        handleSwipe(item);
                    }, { passive: true });
                    
                    function handleSwipe(element) {
                        const deltaX = touchEndX - touchStartX;
                        const deltaY = touchEndY - touchStartY;
                        
                        // Check if it's a horizontal swipe (more horizontal than vertical)
                        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                            // Swipe right to delete (left to right swipe)
                            if (deltaX > 50) {
                                const notificationId = element.getAttribute('data-notification-id');
                                if (notificationId) {
                                    deleteNotification(notificationId);
                                }
                            }
                        }
                    }
                });
                
                // Add click listener for patient names
                notificationsBody.querySelectorAll('.notification-item-patient[data-patient-id]').forEach(patientElement => {
                    patientElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const patientId = this.getAttribute('data-patient-id');
                        if (patientId) {
                            window.location.href = `/doctor/patients/${patientId}`;
                        }
                    });
                });
            }
            
            // Get notification icon based on type
            function getNotificationIcon(type) {
                const icons = {
                    'appointment': '📅',
                    'alert': '🔔',
                    'system': '⚙️',
                    'default': '📬'
                };
                // Check title for login/logout
                if (type === 'system') {
                    // Will be determined by title in renderNotificationItem
                    return '⚙️';
                }
                return icons[type] || icons['default'];
            }
            
            // Get icon from notification title/type
            function getIconFromNotification(notif) {
                const title = (notif.title || '').toLowerCase();
                if (title.includes('login')) return '🔓';
                if (title.includes('logout')) return '🔒';
                if (notif.type === 'appointment') return '📅';
                if (notif.type === 'alert') return '🔔';
                if (notif.type === 'system') return '⚙️';
                return '📬';
            }
            
            // Shorten date and time in message
            function shortenDateTime(message) {
                if (!message) return message;
                
                // Replace "on YYYY-MM-DD at HH:MM:SS" with "on YYYY-MM-DD HH:MM"
                message = message.replace(/on (\d{4}-\d{2}-\d{2}) at (\d{2}):(\d{2}):\d{2}/g, 'on $1 $2:$3');
                
                // Replace "on YYYY-MM-DD HH:MM" with shorter format "on MM/DD HH:MM"
                message = message.replace(/on (\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/g, function(match, year, month, day, hour, minute) {
                    return `on ${month}/${day} ${hour}:${minute}`;
                });
                
                return message;
            }
            
            // Render single notification item
            function renderNotificationItem(notif) {
                const timeAgo = getTimeAgo(notif.created_at);
                const icon = getIconFromNotification(notif);
                const patientInfo = notif.patient_first_name && notif.patient_last_name 
                    ? `<div class="notification-item-patient" ${notif.patient_id ? `data-patient-id="${notif.patient_id}" style="cursor: pointer;"` : ''}>
                        <i class="bi bi-person me-1"></i>${escapeHtml(notif.patient_first_name + ' ' + notif.patient_last_name)}
                    </div>`
                    : '';
                
                // Check if notification is related to an appointment
                const isAppointmentRelated = notif.related_type === 'appointment' && notif.related_id;
                const appointmentId = isAppointmentRelated ? notif.related_id : null;
                
                // Shorten message if it contains long date/time
                let message = escapeHtml(notif.message);
                message = shortenDateTime(message);
                
                return `
                    <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" 
                         data-notification-id="${notif.id}"
                         ${isAppointmentRelated ? `data-appointment-id="${appointmentId}" style="cursor: pointer;"` : ''}
                         data-related-type="${notif.related_type || ''}"
                         data-related-id="${notif.related_id || ''}"
                         data-patient-id="${notif.patient_id || ''}">
                        <button class="notification-item-close" data-notification-id="${notif.id}" title="Delete notification">
                            <i class="bi bi-x"></i>
                        </button>
                        <div class="notification-icon">${icon}</div>
                        <div class="notification-body">
                            <div class="notification-item-header">
                                <h6 class="notification-item-title">${escapeHtml(notif.title)}</h6>
                                <span class="notification-item-time">${timeAgo}</span>
                            </div>
                            <p class="notification-item-message">${message}</p>
                            ${patientInfo}
                        </div>
                    </div>
                `;
            }
            
            // Delete notification
            async function deleteNotification(notificationId) {
                if (!notificationId) {
                    // Silent error handling
                    return;
                }
                
                try {
                    const response = await fetch(`/api/notifications/${notificationId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        // Silent error handling
                        return;
                    }
                    
                    if (data.success) {
                        // Remove notification from DOM with animation
                        const notificationItem = notificationsBody.querySelector(`[data-notification-id="${notificationId}"]`);
                        if (notificationItem) {
                            // Apply macOS-style delete animation
                            notificationItem.classList.add('notification-out');
                            setTimeout(() => {
                                notificationItem.remove();

                                // Check if this was the last notification (excluding the view-all button)
                                const remainingNotifications = notificationsBody.querySelectorAll('.notification-item');
                                if (remainingNotifications.length === 0) {
                                    // Auto-close the notification panel when last notification is deleted
                                    setTimeout(() => {
                                        closeNotifications();
                                    }, 200); // Small delay for visual feedback
                                } else {
                                    // Recalculate panel height after deletion
                                    calculatePanelHeight();
                                    // Reload notifications to ensure sync with database
                                    setTimeout(() => {
                                        loadNotifications();
                                    }, 100);
                                }
                            }, 300); // Wait for macOS animation to complete
                        } else {
                            // If item not found in DOM, reload to sync
                            loadNotifications();
                        }
                        updateUnreadCount();
                    } else {
                        // Reload notifications to restore state
                        loadNotifications();
                    }
                } catch (error) {
                    // Silent error handling
                    // Reload notifications to restore state on error
                    loadNotifications();
                }
            }
            
            // Mark as read and hide
            async function markAsReadAndHide(notificationId) {
                try {
                    const response = await fetch(`/api/notifications/${notificationId}/read`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove notification from DOM
                        const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                        if (notifElement) {
                            notifElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            notifElement.style.opacity = '0';
                            notifElement.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                notifElement.remove();
                                // Recalculate panel height after deletion
                                calculatePanelHeight();
                                // Reload if empty
                                if (notificationsBody.children.length === 0) {
                                    loadNotifications();
                                }
                            }, 300);
                        }
                        updateUnreadCount();
                    }
                } catch (error) {
                    // Silent error handling
                }
            }
            
            // Mark all as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async function() {
                    try {
                        const response = await fetch('/api/notifications/read-all', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        const data = await response.json();
                        
                        if (data.success) {
                            loadNotifications();
                            updateUnreadCount();
                        }
                    } catch (error) {
                        // Silent error handling
                    }
                });
            }
            
            // Clear all
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', async function() {
                    try {
                        // Get all notification items
                        const notificationItems = notificationsBody.querySelectorAll('.notification-item');
                        
                        if (notificationItems.length === 0) {
                            return;
                        }
                        
                        // First, delete from server (same way as deleteNotification)
                        const response = await fetch('/api/notifications/clear-all', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const text = await response.text();
                        let data;
                        
                        try {
                            data = JSON.parse(text);
                        } catch (parseError) {
                            // Silent error handling
                            return;
                        }
                        
                        if (data.success) {
                            // Apply macOS-style delete animation to all notifications
                            notificationItems.forEach((item, index) => {
                                setTimeout(() => {
                                    item.classList.add('notification-out');
                                }, index * 40); // Stagger animations
                            });

                            // Wait for animations to complete, then close the panel
                            setTimeout(() => {
                                // Remove all items from DOM
                                notificationItems.forEach(item => {
                                    item.remove();
                                });

                                // Update unread count
                                updateUnreadCount();

                                // Auto-close notification panel after clearing all
                                closeNotifications();
                            }, (notificationItems.length * 40) + 300); // Wait for all animations (300ms for macOS animation duration)
                        } else {
                            // Reload to restore state
                            loadNotifications();
                        }
                    } catch (error) {
                        // Silent error handling
                        // Reload to restore state on error
                        loadNotifications();
                    }
                });
            }
            
            
            // Show all notifications modal
            window.showAllNotifications = async function() {
                try {
                    const response = await fetch('/api/notifications?limit=1000', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        // Silent error handling
                        return;
                    }
                    
                    if (data.success && data.notifications) {
                        showAllNotificationsModal(data.notifications);
                    }
                } catch (error) {
                    // Silent error handling
                }
            };
            
            // Show all notifications for specific patient
            window.showAllNotificationsForPatient = async function(patientId) {
                try {
                    const response = await fetch('/api/notifications?limit=1000', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        // Silent error handling
                        return;
                    }
                    
                    if (data.success && data.notifications) {
                        const patientNotifs = data.notifications.filter(n => n.patient_id == patientId);
                        const patientName = patientNotifs.length > 0 && patientNotifs[0].patient_first_name 
                            ? `${patientNotifs[0].patient_first_name} ${patientNotifs[0].patient_last_name || ''}`.trim()
                            : 'Patient';
                        showAllNotificationsModal(patientNotifs, `Notifications for ${patientName}`);
                    }
                } catch (error) {
                    // Silent error handling
                }
            };
            
            // Show all notifications modal
            function showAllNotificationsModal(notifications, title = 'All Notifications') {
                // Close notifications panel immediately
                closeNotifications();
                
                let modalHtml = `
                    <div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsModalLabel" aria-hidden="true" style="z-index: 10000001;">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(40px) saturate(180%); border: 0.5px solid rgba(255, 255, 255, 0.15);">
                                <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                    <h5 class="modal-title" id="allNotificationsModalLabel" style="color: white;">
                                        <i class="bi bi-bell me-2"></i>${escapeHtml(title)}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="allNotificationsModalBody" style="max-height: 70vh; overflow-y: auto;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span style="color: rgba(255, 255, 255, 0.8);">Total: ${notifications.length} notification${notifications.length !== 1 ? 's' : ''}</span>
                                        <button class="btn btn-sm btn-primary" onclick="markAllNotificationsAsRead()">
                                            <i class="bi bi-check-all me-1"></i>Mark All Read
                                        </button>
                                    </div>
                                    <div id="allNotificationsList" style="display: flex; flex-direction: column; gap: 12px;">
                `;
                
                if (notifications.length === 0) {
                    modalHtml += `
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size: 3rem; opacity: 0.3; color: rgba(255, 255, 255, 0.5);"></i>
                            <p class="mt-3" style="color: rgba(255, 255, 255, 0.7);">No notifications</p>
                        </div>
                    `;
                } else {
                    notifications.forEach(notif => {
                        modalHtml += renderNotificationItem(notif);
                    });
                }
                
                modalHtml += `
                                    </div>
                                </div>
                                <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('allNotificationsModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal backdrop with higher z-index
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.style.zIndex = '10000000';
                document.body.appendChild(backdrop);
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Initialize modal
                const modalElement = document.getElementById('allNotificationsModal');
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true
                });
                modal.show();
                
                // Add event listeners to close buttons
                document.querySelectorAll('#allNotificationsModal .notification-item-close').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const notificationId = this.getAttribute('data-notification-id');
                        markAsReadAndHide(notificationId);
                    });
                });
                
                // Clean up on hide
                modalElement.addEventListener('hidden.bs.modal', function() {
                    this.remove();
                    if (backdrop && backdrop.parentNode) {
                        backdrop.parentNode.removeChild(backdrop);
                    }
                });
            }
            
            // Mark all notifications as read (from modal)
            window.markAllNotificationsAsRead = async function() {
                try {
                    const response = await fetch('/api/notifications/read-all', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        // Silent error handling
                        return;
                    }
                    
                    if (data.success) {
                        // Reload notifications in modal
                        if (window.showAllNotifications) {
                            window.showAllNotifications();
                        }
                        // Reload main panel
                        loadNotifications();
                        updateUnreadCount();
                    }
                } catch (error) {
                    // Silent error handling
                }
            };
            
            // Get time ago
            function getTimeAgo(dateString) {
                const now = new Date();
                const date = new Date(dateString);
                const diff = Math.floor((now - date) / 1000);
                
                if (diff < 60) return 'Just now';
                if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
                if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
                return date.toLocaleDateString();
            }
            
            // Escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Start polling
            function startNotificationsPolling() {
                if (notificationsPollingInterval) return;
                
                notificationsPollingInterval = setInterval(() => {
                    if (isNotificationsOpen) {
                        loadNotifications();
                    } else {
                        updateUnreadCount();
                    }
                }, 10000); // Poll every 10 seconds
            }
            
            // Stop polling
            function stopNotificationsPolling() {
                if (notificationsPollingInterval) {
                    clearInterval(notificationsPollingInterval);
                    notificationsPollingInterval = null;
                }
            }
            
            // Initialize: Load unread count on page load
            updateUnreadCount();
            
            // Start polling for unread count (when panel is closed)
            setInterval(() => {
                if (!isNotificationsOpen) {
                    updateUnreadCount();
                }
            }, 30000); // Check every 30 seconds when panel is closed
        })();
        
        
        // Global Search Functionality
        (function() {
            const searchInput = document.getElementById('globalSearchInput');
            const searchContainer = document.getElementById('globalSearchContainer');
            const searchResults = document.getElementById('globalSearchResults');
            const searchClear = document.getElementById('globalSearchClear');
            const searchToggle = document.getElementById('globalSearchToggle');
            const searchBackdrop = document.getElementById('globalSearchBackdrop');
            
            if (!searchInput || !searchContainer || !searchResults) return;
            
            let searchTimeout;
            let currentResults = [];
            let selectedIndex = -1;
            
            // Store original parent for restoration
            let originalParent = null;
            let isExpanding = false; // Flag to prevent immediate collapse when expanding
            let blurTimeout = null;
            
            function getSearchAnchorTop() {
                const noticeBar = document.querySelector('.notice-bar');
                const topBar = document.querySelector('.top-bar');
                let bottom = 0;

                if (noticeBar) {
                    const rect = noticeBar.getBoundingClientRect();
                    const style = window.getComputedStyle(noticeBar);
                    const visible = rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden';
                    if (visible) {
                        bottom = Math.max(bottom, rect.bottom);
                    }
                }

                if (topBar) {
                    bottom = Math.max(bottom, topBar.getBoundingClientRect().bottom);
                }

                return Math.max(0, Math.round(bottom));
            }

            function applySearchAnchor() {
                searchContainer.style.setProperty('--global-search-anchor-top', getSearchAnchorTop() + 'px');
            }

            function onSearchOverlayLayout() {
                if (searchContainer.classList.contains('expanded')) {
                    applySearchAnchor();
                }
            }

            window.addEventListener('resize', onSearchOverlayLayout);
            window.addEventListener('scroll', onSearchOverlayLayout, { passive: true });

            // Expand search on click/focus — unified mobile + desktop command palette
            function expandSearch(e) {
                if (e) {
                    e.stopPropagation();
                    e.preventDefault();
                }

                if (searchContainer.classList.contains('expanded')) {
                    return;
                }

                isExpanding = true;
                applySearchAnchor();

                if (searchContainer.parentElement !== document.body) {
                    originalParent = searchContainer.parentElement;
                    document.body.appendChild(searchContainer);
                }

                searchContainer.classList.remove('collapsing', 'show');

                if (searchBackdrop) {
                    searchBackdrop.style.display = 'block';
                    searchBackdrop.style.visibility = 'visible';
                    searchBackdrop.style.zIndex = '999998';
                }

                searchContainer.offsetHeight;

                searchContainer.classList.add('expanded');
                document.body.style.overflow = 'hidden';
                document.body.classList.add('global-search-open');

                if (blurTimeout) {
                    clearTimeout(blurTimeout);
                    blurTimeout = null;
                }

                setTimeout(() => {
                    searchInput.focus();
                    if (searchInput.value.trim().length >= 2 && currentResults.length > 0) {
                        searchResults.classList.add('show');
                    }
                    setTimeout(() => {
                        isExpanding = false;
                    }, 300);
                }, 100);
            }
            
            // Collapse search - 3D perspective close animation
            function collapseSearch() {
                hideResults();
                searchInput.blur();

                if (!searchContainer.classList.contains('expanded')) {
                    searchContainer.classList.remove('show');
                    document.body.classList.remove('global-search-open');
                    return;
                }

                // Force reflow to ensure CSS transition starts from expanded state
                searchContainer.offsetHeight;
                
                // Add collapsing class for 3D perspective close animation
                // Keep expanded class during transition so backdrop stays visible
                searchContainer.classList.add('collapsing');

                // Wait for CSS transition to complete (0.5s)
                setTimeout(() => {
                    // Remove expanded and collapsing classes
                    searchContainer.classList.remove('expanded', 'show', 'collapsing');

                    document.body.style.overflow = '';
                    document.body.classList.remove('global-search-open');
                    
                    // Remove backdrop completely to prevent interference with other elements
                    if (searchBackdrop) {
                        searchBackdrop.style.display = 'none';
                        searchBackdrop.style.opacity = '0';
                        searchBackdrop.style.pointerEvents = 'none';
                        searchBackdrop.style.zIndex = '-1';
                        searchBackdrop.style.visibility = 'hidden';
                    }

                    // Move container back to original parent if it was moved
                    if (originalParent && searchContainer.parentElement === document.body) {
                        // Use requestAnimationFrame for smooth DOM manipulation
                        requestAnimationFrame(() => {
                            originalParent.appendChild(searchContainer);
                            originalParent = null;

                            // Ensure container is visible in original position
                            requestAnimationFrame(() => {
                                searchContainer.style.opacity = '';
                                searchContainer.style.pointerEvents = '';
                            });
                        });
                    }
                    // Clear any pending blur timeout
                    if (blurTimeout) {
                        clearTimeout(blurTimeout);
                        blurTimeout = null;
                    }
                }, 520); // Match CSS transition duration (0.5s + buffer)
            }
            
            // Handle search input blur
            function handleSearchBlur() {
                // Don't collapse if we're expanding or if user is clicking inside
                if (isExpanding) {
                    return;
                }
                
                // Delay collapse to allow click events to process first
                blurTimeout = setTimeout(() => {
                    // Double check that we're not expanding
                    if (!isExpanding && searchContainer.classList.contains('expanded')) {
                        collapseSearch();
                    }
                }, 200);
            }
            
            // Mobile toggle
            if (searchToggle) {
                searchToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (searchContainer.classList.contains('expanded')) {
                        collapseSearch();
                    } else {
                        expandSearch();
                    }
                });
            }
            
            // Expand on input click/focus
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!searchContainer.classList.contains('expanded')) {
                    expandSearch(e);
                }
            });
            
            searchInput.addEventListener('focus', function(e) {
                e.stopPropagation();
                if (!searchContainer.classList.contains('expanded')) {
                    expandSearch(e);
                }
                if (this.value.trim().length >= 2 && currentResults.length > 0) {
                    searchResults.classList.add('show');
                }
            });
            
            // Handle blur on expanded search input
            searchInput.addEventListener('blur', function(e) {
                // Only handle blur if search is expanded
                if (searchContainer.classList.contains('expanded')) {
                    handleSearchBlur();
                }
            });
            
            // Prevent top search input from closing when clicked
            searchInput.addEventListener('mousedown', function(e) {
                if (!searchContainer.classList.contains('expanded')) {
                    e.stopPropagation();
                }
            });
            
            // Backdrop click to close
            if (searchBackdrop) {
                searchBackdrop.addEventListener('click', function(e) {
                    e.stopPropagation();
                    collapseSearch();
                });
            }
            
            // Prevent collapse if clicking inside the search wrapper
            searchContainer.addEventListener('click', function(e) {
                if (e.target.closest('.global-search-input-wrapper')) {
                    if (blurTimeout) {
                        clearTimeout(blurTimeout);
                        blurTimeout = null;
                    }
                    e.stopPropagation();
                }
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                // Don't close if clicking on the search input or if expanding
                if (e.target === searchInput || searchInput.contains(e.target) || isExpanding) {
                    return;
                }
                
                // Close if clicking outside the expanded search
                if (searchContainer.classList.contains('expanded') && !searchContainer.contains(e.target)) {
                    collapseSearch();
                }
            });

            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchContainer.classList.contains('expanded')) {
                    collapseSearch();
                }
            });
            
            // Clear button
            if (searchClear) {
                searchClear.addEventListener('click', function(e) {
                    e.stopPropagation();
                    searchInput.value = '';
                    searchInput.focus();
                    hideResults();
                    searchClear.classList.add('d-none');
                });
            }
            
            // Check if query is a date (DD-MM-YYYY, DD/MM/YYYY, YYYY-MM-DD, YYYY/MM/DD, etc.)
            function isDateQuery(query) {
                if (!query) {
                    return false;
                }
                
                const trimmed = (window.DigitNormalizer && window.DigitNormalizer.normalizeSearchQuery)
                    ? window.DigitNormalizer.normalizeSearchQuery(query)
                    : query.trim();
                
                // Must have at least 6 characters (e.g., "17/12/25")
                if (trimmed.length < 6) {
                    return false;
                }
                
                // Check for common date patterns
                // Pattern 1: DD-MM-YYYY or DD/MM/YYYY (1-2 digits, separator, 1-2 digits, separator, 2-4 digits)
                // Examples: "17/12/2025", "17-12-2025", "7/1/25", "7-1-2025"
                const pattern1 = /^\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}$/;
                // Pattern 2: YYYY-MM-DD or YYYY/MM/DD (2-4 digits, separator, 1-2 digits, separator, 1-2 digits)
                // Examples: "2025-12-17", "2025/12/17", "25-1-7"
                const pattern2 = /^\d{2,4}[-\/]\d{1,2}[-\/]\d{1,2}$/;
                
                return pattern1.test(trimmed) || pattern2.test(trimmed);
            }
            
            // Search input handler
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                if (searchClear) {
                    searchClear.classList.toggle('d-none', query.length === 0);
                }
                
                // For date queries, allow search with minimum 6 characters (e.g., "17/12/25")
                // For regular queries, require minimum 2 characters
                const isDate = isDateQuery(query);
                const minLength = isDate ? 6 : 2;
                
                if (query.length < minLength) {
                    hideResults();
                    return;
                }
                
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            });
            
            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    collapseSearch();
                    return;
                }
                
                if (!searchResults.classList.contains('show') || currentResults.length === 0) {
                    if (e.key === 'Enter' && this.value.trim().length >= 2) {
                        performSearch(this.value.trim());
                    }
                    return;
                }
                
                const items = searchResults.querySelectorAll('.global-search-result-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                    updateSelection(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateSelection(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedIndex >= 0 && items[selectedIndex]) {
                        items[selectedIndex].click();
                    }
                }
            });
            
            // Click outside to close (for mobile)
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!searchContainer.contains(e.target) && !searchToggle?.contains(e.target)) {
                        searchContainer.classList.remove('show');
                        hideResults();
                    }
                }
            });
            
            function updateSelection(items) {
                items.forEach((item, index) => {
                    item.classList.toggle('active', index === selectedIndex);
                    item.classList.toggle('selected', index === selectedIndex);
                });
            }
            
            function performSearch(query) {
                // Ensure search is expanded before showing results
                if (!searchContainer.classList.contains('expanded') && window.innerWidth > 768) {
                    expandSearch();
                }
                
                // Check if query is a date - if so, search only appointments
                const trimmedQuery = (window.DigitNormalizer && window.DigitNormalizer.normalizeSearchQuery)
                    ? window.DigitNormalizer.normalizeSearchQuery(query)
                    : query.trim();
                let searchUrl;
                const isDate = isDateQuery(trimmedQuery);
                
                if (isDate) {
                    // Search only appointments by date
                    searchUrl = `/api/appointments/search?q=${encodeURIComponent(trimmedQuery)}&limit=10`;
                } else {
                    // Normal comprehensive search
                    searchUrl = `/api/search/comprehensive?q=${encodeURIComponent(trimmedQuery)}&limit=10`;
                }
                
                fetch(searchUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Search failed');
                    }
                    return response.json();
                })
                .then(data => {
                    // Handle appointment search response format
                    if (data.ok && data.data && Array.isArray(data.data)) {
                        // Convert appointment data to results format
                        currentResults = data.data.map(apt => {
                            // Format date from YYYY-MM-DD to DD-MM-YYYY
                            let dateStr = '';
                            if (apt.date) {
                                try {
                                    const dateParts = apt.date.split('-');
                                    if (dateParts.length === 3) {
                                        const day = dateParts[2].padStart(2, '0');
                                        const month = dateParts[1].padStart(2, '0');
                                        const year = dateParts[0];
                                        dateStr = `${day}-${month}-${year}`;
                                    } else {
                                        dateStr = apt.date;
                                    }
                                } catch (e) {
                                    dateStr = apt.date;
                                }
                            }
                            const timeStr = apt.start_time ? apt.start_time.substring(0, 5) : '';
                            const patientName = (apt.patient_name || '').trim();
                            
                            return {
                                id: apt.id,
                                title: `Appointment #${apt.id}${patientName ? ' - ' + patientName : ''}`,
                                subtitle: `${dateStr}${timeStr ? ' at ' + timeStr : ''}${apt.status ? ' - ' + apt.status : ''}`,
                                type: 'appointment',
                                icon: 'bi-calendar3',
                                url: `/doctor/appointments/${apt.id}`
                            };
                        });
                    } else {
                        // Normal comprehensive search results
                    currentResults = data.results || [];
                    }
                    displayResults(currentResults, query);
                })
                .catch(error => {
                    // Show error message
                    searchResults.innerHTML = `
                        <div class="global-search-results-empty">
                            <div class="global-search-empty-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="global-search-empty-text">
                                <div class="global-search-empty-title">Search error</div>
                                <div class="global-search-empty-subtitle">Please try again</div>
                            </div>
                        </div>
                    `;
                    searchResults.classList.add('show');
                });
            }
            
            function displayResults(results, query) {
                // Ensure container is visible
                if (!searchContainer.classList.contains('expanded') && window.innerWidth > 768) {
                    expandSearch();
                }
                
                if (results.length === 0) {
                    searchResults.innerHTML = `
                        <div class="global-search-results-empty">
                            <div class="global-search-empty-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <div class="global-search-empty-text">
                                <div class="global-search-empty-title">No results found</div>
                                <div class="global-search-empty-subtitle">No results found for "${escapeHtml(query)}"</div>
                            </div>
                        </div>
                    `;
                    searchResults.classList.add('show');
                    return;
                }
                
                const html = results.map((result, index) => {
                    return `
                        <div class="global-search-result-item" data-url="${escapeHtml(result.url)}" data-index="${index}">
                            <div class="global-search-result-icon">
                                <i class="bi ${escapeHtml(result.icon)}"></i>
                            </div>
                            <div class="global-search-result-content">
                                <div class="global-search-result-title">${escapeHtml(result.title)}</div>
                                <div class="global-search-result-subtitle">${escapeHtml(result.subtitle || '')}</div>
                            </div>
                            <span class="global-search-result-type">${escapeHtml(result.type)}</span>
                        </div>
                    `;
                }).join('');
                
                searchResults.innerHTML = html;
                searchResults.classList.add('show');
                selectedIndex = -1;
                
                // Force reflow to ensure visibility
                searchResults.offsetHeight;
                
                // Add click handlers
                searchResults.querySelectorAll('.global-search-result-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        const url = this.dataset.url;
                        if (url) {
                            // Close search and cleanup backdrop before navigation
                            collapseSearch();
                            
                            // Immediately hide and disable backdrop
                            if (searchBackdrop) {
                                searchBackdrop.style.display = 'none';
                                searchBackdrop.style.opacity = '0';
                                searchBackdrop.style.pointerEvents = 'none';
                                searchBackdrop.style.zIndex = '-1';
                                searchBackdrop.style.visibility = 'hidden';
                            }
                            
                            // Remove any remaining backdrop elements (only modal-backdrop, not global-search-backdrop as it's part of container)
                            const modalBackdrops = document.querySelectorAll('.modal-backdrop');
                            modalBackdrops.forEach(function(backdrop) {
                                backdrop.remove();
                            });
                            
                            // Restore body overflow
                            document.body.style.overflow = '';
                            
                            // Small delay to ensure cleanup before navigation
                            setTimeout(function() {
                                window.location.href = url;
                            }, 50);
                        }
                    });
                });
            }
            
            function hideResults() {
                searchResults.classList.remove('show');
                selectedIndex = -1;
            }
            
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        })();