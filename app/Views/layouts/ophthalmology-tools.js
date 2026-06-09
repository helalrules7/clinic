/* ============================================================================
 * Ophthalmology calculators & tools — EXTRACTED from main.js on 2026-06-09.
 *
 * Loaded ONLY on the Appointment + Patient-profile detail pages (gated by
 * $__showEyeTools in layouts/main.php), so this ~5.3k-line block no longer
 * ships/parses/executes on the dashboard, board, lists, settings, calendar, etc.
 *
 * Contents: the "Ophthalmology Tools" mobile dropdown wiring, all notice-bar
 * tool-menu bindings, and every calculator popover — IOL, Pediatric IOL,
 * Target IOP, Refraction consistency, Visual-acuity progress, OSDI,
 * Pachymetry-adjusted IOP, Diabetic-retinopathy risk, Macular-thickness trend,
 * Cataract readiness / post-op outcome, Corneal astigmatism, IOP-trend analyzer.
 *
 * Scope: top-level (identical to where it sat in main.js). Reads page globals
 * window.APPOINTMENT_CONFIG / PATIENT_CONFIG / currentPatientId at CLICK time and
 * calls absolute /api/* URLs (no main.js internals). Exposes
 * window.selectIOPPatientFromAutocomplete + window.clearIOPPatientSelection
 * (used by inline onclick in the IOP autocomplete template).
 *
 * Must load AFTER main.js and AFTER <?= $content ?> (the notice-bar buttons +
 * patientIOPTrendBtn must already be in the DOM). Keep STANDALONE — no new
 * cross-deps on main.js. Mirror any change to ortho.
 * ========================================================================== */

        // Unified Ophthalmology Tools Dropdown (Mobile/Tablet)
        (function() {
            const ophthalmologyToolsBtn = document.getElementById('noticeBarOphthalmologyToolsBtn');
            const ophthalmologyToolsDropdown = document.getElementById('ophthalmologyToolsDropdown');
            
            if (ophthalmologyToolsBtn && ophthalmologyToolsDropdown) {
                // Toggle dropdown on button click
                ophthalmologyToolsBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    ophthalmologyToolsDropdown.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!ophthalmologyToolsDropdown.contains(e.target) && 
                        !ophthalmologyToolsBtn.contains(e.target)) {
                        ophthalmologyToolsDropdown.classList.remove('show');
                    }
                });
                
                // Close dropdown when clicking on backdrop
                ophthalmologyToolsDropdown.addEventListener('click', (e) => {
                    if (e.target === ophthalmologyToolsDropdown) {
                        ophthalmologyToolsDropdown.classList.remove('show');
                    }
                });
                
                // Close dropdown on ESC key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && ophthalmologyToolsDropdown.classList.contains('show')) {
                        ophthalmologyToolsDropdown.classList.remove('show');
                    }
                });
            }
        })();
        
        // Core Calculators - Simple hover menu
        document.getElementById('calculatorsDropdownIOL')?.addEventListener('click', (e) => {
            e.preventDefault();
            createIOLCalculatorPopover();
        });
        
        // Mobile Core Calculators
        document.getElementById('mobileCalculatorsDropdownIOL')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createIOLCalculatorPopover();
        });

        document.getElementById('calculatorsDropdownPediatric')?.addEventListener('click', (e) => {
            e.preventDefault();
            createPediatricIOLPopover();
        });
        
        document.getElementById('mobileCalculatorsDropdownPediatric')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createPediatricIOLPopover();
        });

        document.getElementById('calculatorsDropdownAstigmatism')?.addEventListener('click', (e) => {
            e.preventDefault();
            createCornealAstigmatismPopover();
        });
        
        document.getElementById('mobileCalculatorsDropdownAstigmatism')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createCornealAstigmatismPopover();
        });

        // Glaucoma Tools - Simple hover menu
        document.getElementById('glaucomaDropdownIOP')?.addEventListener('click', (e) => {
            e.preventDefault();
            createIOPTrendAnalyzerPopover();
        });
        
        document.getElementById('mobileGlaucomaDropdownIOP')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createIOPTrendAnalyzerPopover();
        });

        document.getElementById('glaucomaDropdownTargetIOP')?.addEventListener('click', (e) => {
            e.preventDefault();
            createTargetIOPPopover();
        });
        
        document.getElementById('mobileGlaucomaDropdownTargetIOP')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createTargetIOPPopover();
        });

        // Vision Tools - Simple hover menu
        document.getElementById('visionDropdownRefraction')?.addEventListener('click', (e) => {
            e.preventDefault();
            createRefractionConsistencyPopover();
        });
        
        document.getElementById('mobileVisionDropdownRefraction')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createRefractionConsistencyPopover();
        });

        document.getElementById('visionDropdownVA')?.addEventListener('click', (e) => {
            e.preventDefault();
            // Get patientId and appointmentId from window if available
            const patientId = window.APPOINTMENT_CONFIG?.patientId || window.currentPatientId || null;
            const appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            createVisualAcuityProgressPopover(patientId, appointmentId);
        });
        
        document.getElementById('mobileVisionDropdownVA')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            const patientId = window.APPOINTMENT_CONFIG?.patientId || window.currentPatientId || null;
            const appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            createVisualAcuityProgressPopover(patientId, appointmentId);
        });

        // Cornea Tools - Simple hover menu
        document.getElementById('corneaDropdownOSDI')?.addEventListener('click', (e) => {
            e.preventDefault();
            const patientId = window.APPOINTMENT_CONFIG?.patientId || null;
            const appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            createOSDIPopover(patientId, appointmentId);
        });
        
        document.getElementById('mobileCorneaDropdownOSDI')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            const patientId = window.APPOINTMENT_CONFIG?.patientId || null;
            const appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            createOSDIPopover(patientId, appointmentId);
        });

        document.getElementById('corneaDropdownPachymetry')?.addEventListener('click', (e) => {
            e.preventDefault();
            createPachymetryAdjustedIOPPopover();
        });
        
        document.getElementById('mobileCorneaDropdownPachymetry')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createPachymetryAdjustedIOPPopover();
        });

        // Retina Tools - Simple hover menu
        document.getElementById('retinaDropdownDiabetic')?.addEventListener('click', (e) => {
            e.preventDefault();
            createDiabeticRetinopathyRiskPopover();
        });
        
        document.getElementById('mobileRetinaDropdownDiabetic')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            createDiabeticRetinopathyRiskPopover();
        });

        document.getElementById('retinaDropdownMacular')?.addEventListener('click', (e) => {
            e.preventDefault();
            // Try to get patientId from APPOINTMENT_CONFIG or PATIENT_CONFIG
            let patientId = window.APPOINTMENT_CONFIG?.patientId || window.PATIENT_CONFIG?.patientId || null;
            let appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            // Fallback: try to get from URL if in patient page
            if (!patientId) {
                const pathMatch = window.location.pathname.match(/\/doctor\/patients\/(\d+)/);
                if (pathMatch && pathMatch[1]) {
                    patientId = parseInt(pathMatch[1]);
                }
            }
            createMacularThicknessTrendPopover(patientId, appointmentId);
        });
        
        document.getElementById('mobileRetinaDropdownMacular')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            let patientId = window.APPOINTMENT_CONFIG?.patientId || window.PATIENT_CONFIG?.patientId || null;
            let appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            if (!patientId) {
                const pathMatch = window.location.pathname.match(/\/doctor\/patients\/(\d+)/);
                if (pathMatch && pathMatch[1]) {
                    patientId = parseInt(pathMatch[1]);
                }
            }
            createMacularThicknessTrendPopover(patientId, appointmentId);
        });

        // Cataract Tools - Simple hover menu
        document.getElementById('cataractDropdownReadiness')?.addEventListener('click', (e) => {
            e.preventDefault();
            // Try to get patientId from APPOINTMENT_CONFIG or PATIENT_CONFIG
            let patientId = window.APPOINTMENT_CONFIG?.patientId || window.PATIENT_CONFIG?.patientId || null;
            let appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            // Fallback: try to get from URL if in patient page
            if (!patientId) {
                const pathMatch = window.location.pathname.match(/\/doctor\/patients\/(\d+)/);
                if (pathMatch && pathMatch[1]) {
                    patientId = parseInt(pathMatch[1]);
                }
            }
            createCataractSurgeryReadinessPopover(patientId, appointmentId);
        });
        
        document.getElementById('mobileCataractDropdownReadiness')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            let patientId = window.APPOINTMENT_CONFIG?.patientId || window.PATIENT_CONFIG?.patientId || null;
            let appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            if (!patientId) {
                const pathMatch = window.location.pathname.match(/\/doctor\/patients\/(\d+)/);
                if (pathMatch && pathMatch[1]) {
                    patientId = parseInt(pathMatch[1]);
                }
            }
            createCataractSurgeryReadinessPopover(patientId, appointmentId);
        });

        document.getElementById('cataractDropdownOutcome')?.addEventListener('click', (e) => {
            e.preventDefault();
            // Try to get patientId from APPOINTMENT_CONFIG or PATIENT_CONFIG
            let patientId = window.APPOINTMENT_CONFIG?.patientId || window.PATIENT_CONFIG?.patientId || null;
            let appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            // Fallback: try to get from URL if in patient page
            if (!patientId) {
                const pathMatch = window.location.pathname.match(/\/doctor\/patients\/(\d+)/);
                if (pathMatch && pathMatch[1]) {
                    patientId = parseInt(pathMatch[1]);
                }
            }
            createPostOperativeOutcomePopover(patientId, appointmentId);
        });
        
        document.getElementById('mobileCataractDropdownOutcome')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('ophthalmologyToolsDropdown')?.classList.remove('show');
            let patientId = window.APPOINTMENT_CONFIG?.patientId || window.PATIENT_CONFIG?.patientId || null;
            let appointmentId = window.APPOINTMENT_CONFIG?.appointmentId || null;
            if (!patientId) {
                const pathMatch = window.location.pathname.match(/\/doctor\/patients\/(\d+)/);
                if (pathMatch && pathMatch[1]) {
                    patientId = parseInt(pathMatch[1]);
                }
            }
            createPostOperativeOutcomePopover(patientId, appointmentId);
        });

        // IOL Calculator Popover
        let iolCalculatorPopover = null;

        function createIOLCalculatorPopover() {
            // Remove existing popover if any
            if (iolCalculatorPopover) {
                iolCalculatorPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'iol-calculator-popover';
            popover.id = 'iolCalculatorPopover';
            
            popover.innerHTML = `
                <div class="iol-calculator-popover-content">
                    <div class="iol-calculator-popover-header">
                        <h5>IOL Power Calculator</h5>
                        <button type="button" class="iol-calculator-close-btn" id="iolCalculatorCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="iol-calculator-popover-body">
                        <form id="iolCalculatorForm" class="iol-calculator-form">
                            <div class="iol-form-grid">
                                <div class="iol-form-group">
                                    <label for="iolAxialLength">Axial Length (mm) *</label>
                                    <input type="number" id="iolAxialLength" name="axial_length" step="0.01" min="15" max="35" required>
                                    <small class="form-text">Range: 15.0 - 35.0 mm</small>
                                </div>
                                <div class="iol-form-group">
                                    <label for="iolK1">K1 (diopters) *</label>
                                    <input type="number" id="iolK1" name="k1" step="0.01" min="35" max="50" required>
                                    <small class="form-text">Range: 35.0 - 50.0 D</small>
                                </div>
                                <div class="iol-form-group">
                                    <label for="iolK2">K2 (diopters) *</label>
                                    <input type="number" id="iolK2" name="k2" step="0.01" min="35" max="50" required>
                                    <small class="form-text">Range: 35.0 - 50.0 D</small>
                                </div>
                                <div class="iol-form-group">
                                    <label for="iolAConstant">A-Constant *</label>
                                    <input type="number" id="iolAConstant" name="a_constant" step="0.1" min="110" max="130" value="118.5" required>
                                    <small class="form-text">Range: 110.0 - 130.0</small>
                                </div>
                                <div class="iol-form-group">
                                    <label for="iolTargetRefraction">Target Refraction (D)</label>
                                    <input type="number" id="iolTargetRefraction" name="target_refraction" step="0.01" min="-5" max="5" value="0">
                                    <small class="form-text">Range: -5.0 to +5.0 D (optional)</small>
                                </div>
                                <div class="iol-form-group">
                                    <label for="iolACD">Anterior Chamber Depth (mm)</label>
                                    <input type="number" id="iolACD" name="acd" step="0.01" min="2" max="5">
                                    <small class="form-text">Range: 2.0 - 5.0 mm (optional, recommended for Hoffer Q & Holladay 1)</small>
                                </div>
                            </div>
                            <div class="iol-form-actions">
                                <button type="submit" class="btn btn-primary" id="iolCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate
                                </button>
                                <button type="button" class="btn btn-secondary" id="iolResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="iolCalculatorResults" class="iol-calculator-results" style="display: none;">
                            <div id="iolWarningBadge" class="iol-warning-badge"></div>
                            <div id="iolSuggestedPower" class="iol-suggested-power"></div>
                            <div class="iol-comparison-table-wrapper">
                                <table class="iol-comparison-table">
                                    <thead>
                                        <tr>
                                            <th>Formula</th>
                                            <th>IOL Power (D)</th>
                                            <th>Expected Refraction (D)</th>
                                            <th>Warnings</th>
                                        </tr>
                                    </thead>
                                    <tbody id="iolComparisonTableBody">
                                        <!-- Results will be inserted here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            iolCalculatorPopover = popover;
            
            // Position popover
            positionIOLCalculatorPopover();
            
            // Initialize event listeners
            initIOLCalculatorEvents();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'iol-calculator-popover-backdrop';
            backdrop.addEventListener('click', closeIOLCalculatorPopover);
            document.body.appendChild(backdrop);
        }

        function positionIOLCalculatorPopover() {
            if (!iolCalculatorPopover) return;
            
            const iolBtn = document.getElementById('noticeBarIOLBtn');
            if (!iolBtn) return;
            
            const btnRect = iolBtn.getBoundingClientRect();
            const popoverRect = iolCalculatorPopover.getBoundingClientRect();
            
            // Position below button, centered horizontally
            iolCalculatorPopover.style.top = (btnRect.bottom + 10) + 'px';
            iolCalculatorPopover.style.left = '50%';
            iolCalculatorPopover.style.transform = 'translateX(-50%)';
        }

        function closeIOLCalculatorPopover() {
            if (iolCalculatorPopover) {
                iolCalculatorPopover.remove();
                iolCalculatorPopover = null;
            }
            const backdrop = document.querySelector('.iol-calculator-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initIOLCalculatorEvents() {
            // Close button
            const closeBtn = document.getElementById('iolCalculatorCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeIOLCalculatorPopover);
            }

            // Form submission
            const form = document.getElementById('iolCalculatorForm');
            if (form) {
                form.addEventListener('submit', handleIOLCalculation);
            }

            // Reset button
            const resetBtn = document.getElementById('iolResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    form.reset();
                    document.getElementById('iolCalculatorResults').style.display = 'none';
                });
            }

            // Close on escape key
            const escapeHandlerIOL = (e) => {
                if (e.key === 'Escape' && iolCalculatorPopover) {
                    closeIOLCalculatorPopover();
                    document.removeEventListener('keydown', escapeHandlerIOL);
                }
            };
            document.addEventListener('keydown', escapeHandlerIOL);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (iolCalculatorPopover) {
                    positionIOLCalculatorPopover();
                }
            });
        }

        function handleIOLCalculation(e) {
            e.preventDefault();
            
            const form = document.getElementById('iolCalculatorForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate inputs
            if (!validateIOLInputs(data)) {
                return;
            }

            // Show loading state
            const calculateBtn = document.getElementById('iolCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            // Submit via AJAX
            fetch('/api/iol/calculate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderIOLResults(result);
                } else {
                    alert('Calculation failed: ' + (result.error || 'Unknown error'));
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                alert('Error: ' + error.message);
            });
        }

        function validateIOLInputs(data) {
            const axialLength = parseFloat(data.axial_length);
            const k1 = parseFloat(data.k1);
            const k2 = parseFloat(data.k2);
            const aConstant = parseFloat(data.a_constant);

            if (isNaN(axialLength) || axialLength < 15 || axialLength > 35) {
                alert('Axial Length must be between 15.0 and 35.0 mm');
                return false;
            }

            if (isNaN(k1) || k1 < 35 || k1 > 50) {
                alert('K1 must be between 35.0 and 50.0 diopters');
                return false;
            }

            if (isNaN(k2) || k2 < 35 || k2 > 50) {
                alert('K2 must be between 35.0 and 50.0 diopters');
                return false;
            }

            if (isNaN(aConstant) || aConstant < 110 || aConstant > 130) {
                alert('A-Constant must be between 110.0 and 130.0');
                return false;
            }

            if (data.target_refraction && (isNaN(parseFloat(data.target_refraction)) || parseFloat(data.target_refraction) < -5 || parseFloat(data.target_refraction) > 5)) {
                alert('Target Refraction must be between -5.0 and +5.0 diopters');
                return false;
            }

            if (data.acd && (isNaN(parseFloat(data.acd)) || parseFloat(data.acd) < 2 || parseFloat(data.acd) > 5)) {
                alert('ACD must be between 2.0 and 5.0 mm');
                return false;
            }

            return true;
        }

        function renderIOLResults(result) {
            const resultsContainer = document.getElementById('iolCalculatorResults');
            const tableBody = document.getElementById('iolComparisonTableBody');
            const warningBadge = document.getElementById('iolWarningBadge');
            const suggestedPower = document.getElementById('iolSuggestedPower');

            // Show results container
            resultsContainer.style.display = 'block';

            // Render warning badge
            const alWarning = result.al_warning || 'normal';
            let badgeClass = 'iol-warning-normal';
            let badgeIcon = 'bi-check-circle';
            
            if (alWarning === 'short') {
                badgeClass = 'iol-warning-short';
                badgeIcon = 'bi-exclamation-triangle';
            } else if (alWarning === 'long') {
                badgeClass = 'iol-warning-long';
                badgeIcon = 'bi-exclamation-triangle';
            }

            warningBadge.className = `iol-warning-badge ${badgeClass}`;
            warningBadge.innerHTML = `
                <i class="bi ${badgeIcon}"></i>
                <span>${result.al_warning_message || 'Normal Eye'}</span>
            `;

            // Render suggested power
            if (result.suggested_power !== null) {
                suggestedPower.innerHTML = `
                    <div class="iol-suggested-power-label">Suggested IOL Power</div>
                    <div class="iol-suggested-power-value">${result.suggested_power.toFixed(1)} D</div>
                `;
            }

            // Render comparison table
            tableBody.innerHTML = '';
            
            const formulas = [
                { key: 'srkt', name: 'SRK/T' },
                { key: 'hoffer_q', name: 'Hoffer Q' },
                { key: 'holladay_1', name: 'Holladay 1' }
            ];

            formulas.forEach(formula => {
                const formulaResult = result.results[formula.key];
                const row = document.createElement('tr');
                
                let powerCell = '<td>-</td>';
                let refractionCell = '<td>-</td>';
                let warningsCell = '<td>-</td>';

                if (formulaResult && formulaResult.power !== null) {
                    powerCell = `<td><strong>${formulaResult.power.toFixed(2)} D</strong></td>`;
                    refractionCell = `<td>${formulaResult.expected_refraction !== null ? formulaResult.expected_refraction.toFixed(2) + ' D' : '-'}</td>`;
                    
                    if (formulaResult.warnings && formulaResult.warnings.length > 0) {
                        warningsCell = `<td><small>${formulaResult.warnings.join('<br>')}</small></td>`;
                    } else {
                        warningsCell = '<td>-</td>';
                    }
                }

                row.innerHTML = `
                    <td><strong>${formula.name}</strong></td>
                    ${powerCell}
                    ${refractionCell}
                    ${warningsCell}
                `;
                
                tableBody.appendChild(row);
            });

            // Scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Pediatric IOL Undercorrection Popover
        let pediatricIOLPopover = null;

        function createPediatricIOLPopover() {
            // Remove existing popover if any
            if (pediatricIOLPopover) {
                pediatricIOLPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'pediatric-iol-popover';
            popover.id = 'pediatricIOLPopover';
            
            popover.innerHTML = `
                <div class="pediatric-iol-popover-content">
                    <div class="pediatric-iol-popover-header">
                        <h5>Pediatric IOL Undercorrection Calculator</h5>
                        <button type="button" class="pediatric-iol-close-btn" id="pediatricIOLCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="pediatric-iol-popover-body">
                        <form id="pediatricIOLForm" class="pediatric-iol-form">
                            <div class="pediatric-form-group">
                                <label for="pediatricAgeValue">Age *</label>
                                <div class="pediatric-age-input-group">
                                    <input type="number" id="pediatricAgeValue" name="age_value" step="0.1" min="0" max="216" required>
                                    <select id="pediatricAgeUnit" name="age_unit" required>
                                        <option value="months">Months</option>
                                        <option value="years" selected>Years</option>
                                    </select>
                                </div>
                                <small class="form-text">Range: 0-18 years (0-216 months)</small>
                            </div>
                            <div class="pediatric-form-group">
                                <label for="pediatricIOLPower">Calculated IOL Power (D) *</label>
                                <input type="number" id="pediatricIOLPower" name="calculated_iol_power" step="0.01" min="0" max="40" required>
                                <small class="form-text">IOL power from standard calculator (Range: 0-40 D)</small>
                            </div>
                            <div class="pediatric-form-actions">
                                <button type="button" class="btn btn-primary" id="pediatricIOLCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate
                                </button>
                                <button type="button" class="btn btn-secondary" id="pediatricIOLResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="pediatricIOLResults" class="pediatric-iol-results" style="display: none;">
                            <div class="pediatric-results-card">
                                <div class="pediatric-result-item">
                                    <label>Age Group</label>
                                    <span id="pediatricAgeGroup">-</span>
                                </div>
                                <div class="pediatric-result-item">
                                    <label>Age</label>
                                    <span id="pediatricAgeDisplay">-</span>
                                </div>
                                <div class="pediatric-result-item">
                                    <label>Original IOL Power</label>
                                    <span id="pediatricOriginalPower">-</span>
                                </div>
                                <div class="pediatric-result-item">
                                    <label>Undercorrection</label>
                                    <span id="pediatricUndercorrection">-</span>
                                </div>
                                <div class="pediatric-result-item highlight">
                                    <label>Recommended IOL Power</label>
                                    <span id="pediatricRecommendedPower">-</span>
                                </div>
                            </div>
                            <div class="pediatric-clinical-note">
                                <h6>Clinical Note</h6>
                                <p id="pediatricClinicalNote">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            pediatricIOLPopover = popover;
            
            // Position popover
            positionPediatricIOLPopover();
            
            // Initialize event listeners
            initPediatricIOLEvents();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'pediatric-iol-popover-backdrop';
            backdrop.addEventListener('click', closePediatricIOLPopover);
            document.body.appendChild(backdrop);
        }

        function positionPediatricIOLPopover() {
            if (!pediatricIOLPopover) return;
            
            const iolBtn = document.getElementById('noticeBarIOLBtn');
            if (!iolBtn) {
                pediatricIOLPopover.style.top = '50%';
                pediatricIOLPopover.style.left = '50%';
                pediatricIOLPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            
            const btnRect = iolBtn.getBoundingClientRect();
            pediatricIOLPopover.style.top = (btnRect.bottom + 10) + 'px';
            pediatricIOLPopover.style.left = '50%';
            pediatricIOLPopover.style.transform = 'translateX(-50%)';
        }

        function closePediatricIOLPopover() {
            if (pediatricIOLPopover) {
                pediatricIOLPopover.remove();
                pediatricIOLPopover = null;
            }
            const backdrop = document.querySelector('.pediatric-iol-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initPediatricIOLEvents() {
            // Close button
            const closeBtn = document.getElementById('pediatricIOLCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', closePediatricIOLPopover);
            }

            // Calculate button
            const calculateBtn = document.getElementById('pediatricIOLCalculateBtn');
            if (calculateBtn) {
                calculateBtn.addEventListener('click', handlePediatricIOLCalculation);
            }

            // Reset button
            const resetBtn = document.getElementById('pediatricIOLResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    document.getElementById('pediatricIOLResults').style.display = 'none';
                    document.getElementById('pediatricIOLForm').reset();
                });
            }

            // Close on escape key
            const escapeHandlerPediatric = (e) => {
                if (e.key === 'Escape' && pediatricIOLPopover) {
                    closePediatricIOLPopover();
                    document.removeEventListener('keydown', escapeHandlerPediatric);
                }
            };
            document.addEventListener('keydown', escapeHandlerPediatric);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (pediatricIOLPopover) {
                    positionPediatricIOLPopover();
                }
            });
        }

        function handlePediatricIOLCalculation() {
            const form = document.getElementById('pediatricIOLForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate inputs
            if (!data.age_value || !data.age_unit || !data.calculated_iol_power) {
                alert('Please fill in all required fields');
                return;
            }

            // Show loading state
            const calculateBtn = document.getElementById('pediatricIOLCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            // Submit via AJAX
            // Ensure numeric values are properly formatted
            const params = new URLSearchParams();
            params.append('age_value', data.age_value);
            params.append('age_unit', data.age_unit);
            params.append('calculated_iol_power', data.calculated_iol_power);
            
            fetch('/api/pediatric-iol/calculate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            })
            .then(response => response.json())
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderPediatricIOLResults(result);
                } else {
                    alert('Calculation failed: ' + (result.error || result.errors?.join(', ') || 'Unknown error'));
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                alert('Error: ' + error.message);
            });
        }

        function renderPediatricIOLResults(result) {
            const resultsContainer = document.getElementById('pediatricIOLResults');
            if (!resultsContainer) return;

            // Show results
            resultsContainer.style.display = 'block';

            // Update result fields
            document.getElementById('pediatricAgeGroup').textContent = result.age_group || '-';
            document.getElementById('pediatricAgeDisplay').textContent = `${result.age_years} years (${result.age_months} months)`;
            document.getElementById('pediatricOriginalPower').textContent = `${result.calculated_iol_power} D`;
            document.getElementById('pediatricUndercorrection').textContent = `${result.undercorrection_percentage}%`;
            document.getElementById('pediatricRecommendedPower').textContent = `${result.rounded_iol_power} D`;
            document.getElementById('pediatricClinicalNote').textContent = result.clinical_note || '-';

            // Scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Target IOP Calculator Popover
        let targetIOPPopover = null;

        function createTargetIOPPopover() {
            // Remove existing popover if any
            if (targetIOPPopover) {
                targetIOPPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'target-iop-popover';
            popover.id = 'targetIOPPopover';
            
            popover.innerHTML = `
                <div class="target-iop-popover-content">
                    <div class="target-iop-popover-header">
                        <h5>Target IOP Calculator</h5>
                        <button type="button" class="target-iop-close-btn" id="targetIOPCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="target-iop-popover-body">
                        <form id="targetIOPForm" class="target-iop-form">
                            <div class="target-iop-form-group">
                                <label for="targetIOPBaseline">Baseline IOP (mmHg) *</label>
                                <input type="number" id="targetIOPBaseline" name="baseline_iop" step="0.1" min="5" max="60" required>
                                <small class="form-text">Range: 5-60 mmHg</small>
                            </div>
                            <div class="target-iop-form-group">
                                <label for="targetIOPStage">Glaucoma Stage *</label>
                                <select id="targetIOPStage" name="glaucoma_stage" required>
                                    <option value="">Select stage...</option>
                                    <option value="Early">Early</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                                <small class="form-text">Select the glaucoma stage</small>
                            </div>
                            <div class="target-iop-form-actions">
                                <button type="button" class="btn btn-primary" id="targetIOPCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate
                                </button>
                                <button type="button" class="btn btn-secondary" id="targetIOPResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="targetIOPResults" class="target-iop-results" style="display: none;">
                            <div class="target-iop-results-card">
                                <div class="target-iop-result-item">
                                    <label>Baseline IOP</label>
                                    <span id="targetIOPBaselineDisplay">-</span>
                                </div>
                                <div class="target-iop-result-item">
                                    <label>Glaucoma Stage</label>
                                    <span id="targetIOPStageDisplay">-</span>
                                </div>
                                <div class="target-iop-result-item">
                                    <label>Reduction Percentage</label>
                                    <span id="targetIOPReduction">-</span>
                                </div>
                                <div class="target-iop-result-item">
                                    <label>Applied Rule</label>
                                    <span id="targetIOPAppliedRule">-</span>
                                </div>
                                <div class="target-iop-result-item highlight">
                                    <label>Target IOP</label>
                                    <span id="targetIOPTarget">-</span>
                                </div>
                            </div>
                            <div class="target-iop-clinical-note">
                                <h6>Clinical Note</h6>
                                <p id="targetIOPClinicalNote">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            targetIOPPopover = popover;
            
            // Position popover
            positionTargetIOPPopover();
            
            // Initialize event listeners
            initTargetIOPEvents();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'target-iop-popover-backdrop';
            backdrop.addEventListener('click', closeTargetIOPPopover);
            document.body.appendChild(backdrop);
        }

        function positionTargetIOPPopover() {
            if (!targetIOPPopover) return;
            
            const glaucomaBtn = document.getElementById('noticeBarGlaucomaBtn');
            if (!glaucomaBtn) {
                targetIOPPopover.style.top = '50%';
                targetIOPPopover.style.left = '50%';
                targetIOPPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            
            const btnRect = glaucomaBtn.getBoundingClientRect();
            targetIOPPopover.style.top = (btnRect.bottom + 10) + 'px';
            targetIOPPopover.style.left = '50%';
            targetIOPPopover.style.transform = 'translateX(-50%)';
        }

        function closeTargetIOPPopover() {
            if (targetIOPPopover) {
                targetIOPPopover.remove();
                targetIOPPopover = null;
            }
            const backdrop = document.querySelector('.target-iop-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initTargetIOPEvents() {
            // Close button
            const closeBtn = document.getElementById('targetIOPCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeTargetIOPPopover);
            }

            // Calculate button
            const calculateBtn = document.getElementById('targetIOPCalculateBtn');
            if (calculateBtn) {
                calculateBtn.addEventListener('click', handleTargetIOPCalculation);
            }

            // Reset button
            const resetBtn = document.getElementById('targetIOPResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    document.getElementById('targetIOPResults').style.display = 'none';
                    document.getElementById('targetIOPForm').reset();
                });
            }

            // Close on escape key
            const escapeHandlerTargetIOP = (e) => {
                if (e.key === 'Escape' && targetIOPPopover) {
                    closeTargetIOPPopover();
                    document.removeEventListener('keydown', escapeHandlerTargetIOP);
                }
            };
            document.addEventListener('keydown', escapeHandlerTargetIOP);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (targetIOPPopover) {
                    positionTargetIOPPopover();
                }
            });
        }

        function handleTargetIOPCalculation() {
            const form = document.getElementById('targetIOPForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate inputs
            if (!data.baseline_iop || !data.glaucoma_stage) {
                alert('Please fill in all required fields');
                return;
            }

            // Show loading state
            const calculateBtn = document.getElementById('targetIOPCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            // Submit via AJAX
            const params = new URLSearchParams();
            params.append('baseline_iop', data.baseline_iop);
            params.append('glaucoma_stage', data.glaucoma_stage);
            
            fetch('/api/target-iop/calculate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            })
            .then(response => response.json())
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderTargetIOPResults(result);
                } else {
                    alert('Calculation failed: ' + (result.error || result.errors?.join(', ') || 'Unknown error'));
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                alert('Error: ' + error.message);
            });
        }

        function renderTargetIOPResults(result) {
            const resultsContainer = document.getElementById('targetIOPResults');
            if (!resultsContainer) return;

            // Show results
            resultsContainer.style.display = 'block';

            // Update result fields
            document.getElementById('targetIOPBaselineDisplay').textContent = `${result.baseline_iop} mmHg`;
            document.getElementById('targetIOPStageDisplay').textContent = result.glaucoma_stage || '-';
            document.getElementById('targetIOPReduction').textContent = `${result.reduction_percentage}%`;
            document.getElementById('targetIOPAppliedRule').textContent = result.applied_rule || '-';
            document.getElementById('targetIOPTarget').textContent = `${result.target_iop} mmHg`;
            document.getElementById('targetIOPClinicalNote').textContent = result.clinical_note || '-';

            // Scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Refraction Consistency Checker Popover
        let refractionConsistencyPopover = null;

        function createRefractionConsistencyPopover() {
            // Remove existing popover if any
            if (refractionConsistencyPopover) {
                refractionConsistencyPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'refraction-consistency-popover';
            popover.id = 'refractionConsistencyPopover';
            
            popover.innerHTML = `
                <div class="refraction-consistency-popover-content">
                    <div class="refraction-consistency-popover-header">
                        <h5>Refraction Consistency Checker</h5>
                        <button type="button" class="refraction-consistency-close-btn" id="refractionConsistencyCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="refraction-consistency-popover-body">
                        <form id="refractionConsistencyForm" class="refraction-consistency-form">
                            <div class="refraction-section">
                                <h6>Auto-Refraction</h6>
                                <div class="refraction-form-grid">
                                    <div class="refraction-consistency-form-group">
                                        <label for="refractionAutoSphere">Sphere (D) *</label>
                                        <input type="number" id="refractionAutoSphere" name="auto_sphere" step="0.25" min="-20" max="20" required>
                                    </div>
                                    <div class="refraction-consistency-form-group">
                                        <label for="refractionAutoCylinder">Cylinder (D) *</label>
                                        <input type="number" id="refractionAutoCylinder" name="auto_cylinder" step="0.25" min="-6" max="0" required>
                                    </div>
                                    <div class="refraction-consistency-form-group">
                                        <label for="refractionAutoAxis">Axis (degrees) *</label>
                                        <input type="number" id="refractionAutoAxis" name="auto_axis" step="1" min="0" max="180" required>
                                    </div>
                                </div>
                            </div>
                            <div class="refraction-section">
                                <h6>Subjective Refraction</h6>
                                <div class="refraction-form-grid">
                                    <div class="refraction-consistency-form-group">
                                        <label for="refractionSubjectiveSphere">Sphere (D) *</label>
                                        <input type="number" id="refractionSubjectiveSphere" name="subjective_sphere" step="0.25" min="-20" max="20" required>
                                    </div>
                                    <div class="refraction-consistency-form-group">
                                        <label for="refractionSubjectiveCylinder">Cylinder (D) *</label>
                                        <input type="number" id="refractionSubjectiveCylinder" name="subjective_cylinder" step="0.25" min="-6" max="0" required>
                                    </div>
                                    <div class="refraction-consistency-form-group">
                                        <label for="refractionSubjectiveAxis">Axis (degrees) *</label>
                                        <input type="number" id="refractionSubjectiveAxis" name="subjective_axis" step="1" min="0" max="180" required>
                                    </div>
                                </div>
                            </div>
                            <div class="refraction-consistency-form-actions">
                                <button type="button" class="btn btn-primary" id="refractionConsistencyCalculateBtn">
                                    <i class="bi bi-calculator"></i> Check Consistency
                                </button>
                                <button type="button" class="btn btn-secondary" id="refractionConsistencyResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="refractionConsistencyResults" class="refraction-consistency-results" style="display: none;">
                            <div class="refraction-consistency-results-card">
                                <div class="refraction-consistency-result-item">
                                    <label>Delta Sphere</label>
                                    <span id="refractionDeltaSphere">-</span>
                                </div>
                                <div class="refraction-consistency-result-item">
                                    <label>Delta Cylinder</label>
                                    <span id="refractionDeltaCylinder">-</span>
                                </div>
                                <div class="refraction-consistency-result-item">
                                    <label>Delta Axis</label>
                                    <span id="refractionDeltaAxis">-</span>
                                </div>
                                <div class="refraction-consistency-result-item highlight">
                                    <label>Consistency Status</label>
                                    <span id="refractionConsistencyFlag" class="consistency-badge">-</span>
                                </div>
                            </div>
                            <div class="refraction-consistency-clinical-note">
                                <h6>Clinical Message</h6>
                                <p id="refractionConsistencyClinicalMessage">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            refractionConsistencyPopover = popover;
            
            // Position popover
            positionRefractionConsistencyPopover();
            
            // Initialize event listeners
            initRefractionConsistencyEvents();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'refraction-consistency-popover-backdrop';
            backdrop.addEventListener('click', closeRefractionConsistencyPopover);
            document.body.appendChild(backdrop);
        }

        function positionRefractionConsistencyPopover() {
            if (!refractionConsistencyPopover) return;
            
            const visionBtn = document.getElementById('noticeBarVisionBtn');
            if (!visionBtn) {
                refractionConsistencyPopover.style.top = '50%';
                refractionConsistencyPopover.style.left = '50%';
                refractionConsistencyPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            
            const btnRect = visionBtn.getBoundingClientRect();
            refractionConsistencyPopover.style.top = (btnRect.bottom + 10) + 'px';
            refractionConsistencyPopover.style.left = '50%';
            refractionConsistencyPopover.style.transform = 'translateX(-50%)';
        }

        function closeRefractionConsistencyPopover() {
            if (refractionConsistencyPopover) {
                refractionConsistencyPopover.remove();
                refractionConsistencyPopover = null;
            }
            const backdrop = document.querySelector('.refraction-consistency-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initRefractionConsistencyEvents() {
            // Close button
            const closeBtn = document.getElementById('refractionConsistencyCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeRefractionConsistencyPopover);
            }

            // Calculate button
            const calculateBtn = document.getElementById('refractionConsistencyCalculateBtn');
            if (calculateBtn) {
                calculateBtn.addEventListener('click', handleRefractionConsistencyCalculation);
            }

            // Reset button
            const resetBtn = document.getElementById('refractionConsistencyResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    document.getElementById('refractionConsistencyResults').style.display = 'none';
                    document.getElementById('refractionConsistencyForm').reset();
                });
            }

            // Close on escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && refractionConsistencyPopover) {
                    closeRefractionConsistencyPopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (refractionConsistencyPopover) {
                    positionRefractionConsistencyPopover();
                }
            });
        }

        function handleRefractionConsistencyCalculation() {
            const form = document.getElementById('refractionConsistencyForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate inputs
            if (!data.auto_sphere || !data.auto_cylinder || !data.auto_axis || 
                !data.subjective_sphere || !data.subjective_cylinder || !data.subjective_axis) {
                alert('Please fill in all required fields');
                return;
            }

            // Show loading state
            const calculateBtn = document.getElementById('refractionConsistencyCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            // Submit via AJAX
            const params = new URLSearchParams();
            params.append('auto_sphere', data.auto_sphere);
            params.append('auto_cylinder', data.auto_cylinder);
            params.append('auto_axis', data.auto_axis);
            params.append('subjective_sphere', data.subjective_sphere);
            params.append('subjective_cylinder', data.subjective_cylinder);
            params.append('subjective_axis', data.subjective_axis);
            
            fetch('/api/refraction/consistency', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            })
            .then(response => response.json())
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderRefractionConsistencyResults(result);
                } else {
                    alert('Calculation failed: ' + (result.error || result.errors?.join(', ') || 'Unknown error'));
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                alert('Error: ' + error.message);
            });
        }

        function renderRefractionConsistencyResults(result) {
            const resultsContainer = document.getElementById('refractionConsistencyResults');
            if (!resultsContainer) return;

            // Show results
            resultsContainer.style.display = 'block';

            // Update result fields
            document.getElementById('refractionDeltaSphere').textContent = `${result.delta_sphere} D`;
            document.getElementById('refractionDeltaCylinder').textContent = `${result.delta_cylinder} D`;
            document.getElementById('refractionDeltaAxis').textContent = `${result.delta_axis}°`;
            
            // Update consistency flag badge
            const consistencyFlag = document.getElementById('refractionConsistencyFlag');
            consistencyFlag.textContent = result.consistency_flag === 'consistent' ? 'Consistent' : 'Inconsistent';
            consistencyFlag.className = 'consistency-badge ' + (result.consistency_flag === 'consistent' ? 'consistent' : 'inconsistent');
            
            document.getElementById('refractionConsistencyClinicalMessage').textContent = result.clinical_message || '-';

            // Scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Visual Acuity Progress Calculator Popover
        let visualAcuityProgressPopover = null;
        let visitCounter = 0;

        function createVisualAcuityProgressPopover(patientId = null, appointmentId = null) {
            // Remove existing popover if any
            if (visualAcuityProgressPopover) {
                visualAcuityProgressPopover.remove();
            }

            // Get patientId from window if not provided
            if (!patientId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                patientId = window.APPOINTMENT_CONFIG.patientId;
            }
            if (!appointmentId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentId) {
                appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
            }

            visitCounter = 0;

            const popover = document.createElement('div');
            popover.className = 'visual-acuity-popover';
            popover.id = 'visualAcuityProgressPopover';
            
            popover.innerHTML = `
                <div class="visual-acuity-popover-content">
                    <div class="visual-acuity-popover-header">
                        <h5>Visual Acuity Progress Calculator</h5>
                        <button type="button" class="visual-acuity-close-btn" id="visualAcuityCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="visual-acuity-popover-body">
                        <form id="visualAcuityForm" class="visual-acuity-form">
                            <div id="visualAcuityVisitsContainer" class="visual-acuity-visits-container">
                                <!-- Visit entries will be added here -->
                            </div>
                            <div class="visual-acuity-form-actions">
                                <button type="button" class="btn btn-secondary" id="visualAcuityAddVisitBtn">
                                    <i class="bi bi-plus-circle"></i> Add Visit
                                </button>
                                <button type="button" class="btn btn-primary" id="visualAcuityCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate Progress
                                </button>
                                <button type="button" class="btn btn-secondary" id="visualAcuityResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="visualAcuityResults" class="visual-acuity-results" style="display: none;">
                            <div class="visual-acuity-results-content">
                                <div class="va-results-per-eye" id="vaResultsOD">
                                    <h6>OD (Right Eye)</h6>
                                    <div class="va-eye-results" id="vaEyeResultsOD"></div>
                                </div>
                                <div class="va-results-per-eye" id="vaResultsOS">
                                    <h6>OS (Left Eye)</h6>
                                    <div class="va-eye-results" id="vaEyeResultsOS"></div>
                                </div>
                                <div class="va-graph-container">
                                    <canvas id="vaProgressGraph"></canvas>
                                </div>
                                <div class="visual-acuity-clinical-note">
                                    <h6>Summary</h6>
                                    <p id="visualAcuitySummaryNote">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            visualAcuityProgressPopover = popover;
            
            // Position popover
            positionVisualAcuityProgressPopover();
            
            // Initialize event listeners
            initVisualAcuityProgressEvents();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'visual-acuity-popover-backdrop';
            backdrop.addEventListener('click', closeVisualAcuityProgressPopover);
            document.body.appendChild(backdrop);

            // Load Visual Acuity data from appointments if patientId is available
            if (patientId) {
                loadVisualAcuityFromAppointments(patientId, appointmentId);
            } else {
                // Add initial empty visit entries if no patient data
                addVisualAcuityVisit();
                addVisualAcuityVisit();
            }
        }

        function loadVisualAcuityFromAppointments(patientId, excludeAppointmentId = null) {
            const container = document.getElementById('visualAcuityVisitsContainer');
            if (!container) return;

            // Show loading state
            container.innerHTML = '<div class="text-center py-4"><i class="bi bi-hourglass-split text-muted" style="font-size: 2rem;"></i><p class="text-muted mt-2">Loading Visual Acuity data from appointments...</p></div>';

            // Fetch appointment history
            const url = `/api/patients/${patientId}/appointments/history${excludeAppointmentId ? '?exclude=' + excludeAppointmentId : ''}`;
            
            fetch(url, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                visitCounter = 0;

                if (data.ok && data.data && Array.isArray(data.data)) {
                    const visits = [];
                    
                    // Extract Visual Acuity data from appointments
                    data.data.forEach(appointment => {
                        if (appointment.consultation_note) {
                            const note = appointment.consultation_note;
                            const appointmentDate = appointment.date || null;
                            
                            // Add OD (Right Eye) if available
                            if (note.visual_acuity_right && appointmentDate) {
                                // Remove all extra spaces and clean the value
                                const cleanedValue = note.visual_acuity_right.trim().replace(/\s+/g, ' ');
                                visits.push({
                                    eye: 'OD',
                                    va_value: cleanedValue,
                                    va_format: 'snellen', // Assume Snellen format
                                    date: appointmentDate
                                });
                            }
                            
                            // Add OS (Left Eye) if available
                            if (note.visual_acuity_left && appointmentDate) {
                                // Remove all extra spaces and clean the value
                                const cleanedValue = note.visual_acuity_left.trim().replace(/\s+/g, ' ');
                                visits.push({
                                    eye: 'OS',
                                    va_value: cleanedValue,
                                    va_format: 'snellen', // Assume Snellen format
                                    date: appointmentDate
                                });
                            }
                        }
                    });

                    if (visits.length > 0) {
                        // Sort visits by date (oldest first)
                        visits.sort((a, b) => {
                            const dateA = new Date(a.date);
                            const dateB = new Date(b.date);
                            return dateA - dateB;
                        });
                        
                        // Group visits by date to avoid duplicates
                        const visitsByDate = {};
                        visits.forEach(visit => {
                            const key = `${visit.date}_${visit.eye}`;
                            if (!visitsByDate[key]) {
                                visitsByDate[key] = visit;
                            }
                        });
                        
                        const uniqueVisits = Object.values(visitsByDate);
                        
                        // Populate form with visits
                        uniqueVisits.forEach(visit => {
                            addVisualAcuityVisit();
                            const lastEntry = container.querySelector('.visit-entry:last-child');
                            if (lastEntry) {
                                const eyeSelect = lastEntry.querySelector('[name*="[eye]"]');
                                const formatSelect = lastEntry.querySelector('[name*="[va_format]"]');
                                const valueInput = lastEntry.querySelector('[name*="[va_value]"]');
                                const dateInput = lastEntry.querySelector('[name*="[date]"]');
                                
                                if (eyeSelect) eyeSelect.value = visit.eye;
                                if (formatSelect) formatSelect.value = visit.va_format;
                                // Clean the value input - remove extra spaces
                                if (valueInput) {
                                    const cleanedValue = visit.va_value.trim().replace(/\s+/g, ' ');
                                    valueInput.value = cleanedValue;
                                }
                                if (dateInput) dateInput.value = visit.date;
                            }
                        });

                        // Auto-calculate if we have at least 2 visits
                        if (uniqueVisits.length >= 2) {
                            // Wait a bit for DOM to be ready, then auto-calculate
                            setTimeout(() => {
                                const calculateBtn = document.getElementById('visualAcuityCalculateBtn');
                                if (calculateBtn) {
                                    handleVisualAcuityProgressCalculation();
                                }
                            }, 800);
                        } else {
                            // Add empty entry if we have only one visit
                            addVisualAcuityVisit();
                        }
                    } else {
                        // No Visual Acuity data found, add empty entries
                        addVisualAcuityVisit();
                        addVisualAcuityVisit();
                    }
                } else {
                    // No appointments found, add empty entries
                    addVisualAcuityVisit();
                    addVisualAcuityVisit();
                }
            })
            .catch(error => {
                console.error('Error loading Visual Acuity data:', error);
                container.innerHTML = '';
                // Add empty entries on error
                addVisualAcuityVisit();
                addVisualAcuityVisit();
            });
        }

        function addVisualAcuityVisit() {
            visitCounter++;
            const container = document.getElementById('visualAcuityVisitsContainer');
            if (!container) return;

            const visitEntry = document.createElement('div');
            visitEntry.className = 'visit-entry';
            visitEntry.id = `visitEntry${visitCounter}`;
            
            visitEntry.innerHTML = `
                <div class="visit-entry-header">
                    <h6>Visit ${visitCounter}</h6>
                    ${visitCounter > 2 ? '<button type="button" class="btn btn-sm btn-danger visit-remove-btn" data-visit-id="' + visitCounter + '"><i class="bi bi-trash"></i></button>' : ''}
                </div>
                <div class="visit-entry-fields">
                    <div class="visual-acuity-form-group">
                        <label>Eye *</label>
                        <select name="visits[${visitCounter}][eye]" required>
                            <option value="">Select...</option>
                            <option value="OD">OD (Right)</option>
                            <option value="OS">OS (Left)</option>
                        </select>
                    </div>
                    <div class="visual-acuity-form-group">
                        <label>VA Format *</label>
                        <select name="visits[${visitCounter}][va_format]" class="va-format-select" required>
                            <option value="snellen">Snellen</option>
                            <option value="logmar">LogMAR</option>
                        </select>
                    </div>
                    <div class="visual-acuity-form-group">
                        <label>Visual Acuity *</label>
                        <input type="text" name="visits[${visitCounter}][va_value]" placeholder="e.g., 6/6 or 0.0" required>
                        <small class="form-text va-format-hint">Enter Snellen (e.g., 6/6, 6/12) or LogMAR value</small>
                    </div>
                    <div class="visual-acuity-form-group">
                        <label>Date *</label>
                        <input type="date" name="visits[${visitCounter}][date]" required>
                    </div>
                </div>
            `;

            container.appendChild(visitEntry);

            // Add remove button event listener
            const removeBtn = visitEntry.querySelector('.visit-remove-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    visitEntry.remove();
                });
            }
        }

        function positionVisualAcuityProgressPopover() {
            if (!visualAcuityProgressPopover) return;
            
            const visionBtn = document.getElementById('noticeBarVisionBtn');
            if (!visionBtn) {
                visualAcuityProgressPopover.style.top = '50%';
                visualAcuityProgressPopover.style.left = '50%';
                visualAcuityProgressPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            
            const btnRect = visionBtn.getBoundingClientRect();
            visualAcuityProgressPopover.style.top = (btnRect.bottom + 10) + 'px';
            visualAcuityProgressPopover.style.left = '50%';
            visualAcuityProgressPopover.style.transform = 'translateX(-50%)';
        }

        function closeVisualAcuityProgressPopover() {
            if (visualAcuityProgressPopover) {
                visualAcuityProgressPopover.remove();
                visualAcuityProgressPopover = null;
            }
            const backdrop = document.querySelector('.visual-acuity-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initVisualAcuityProgressEvents() {
            // Close button
            const closeBtn = document.getElementById('visualAcuityCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeVisualAcuityProgressPopover);
            }

            // Add visit button
            const addVisitBtn = document.getElementById('visualAcuityAddVisitBtn');
            if (addVisitBtn) {
                addVisitBtn.addEventListener('click', addVisualAcuityVisit);
            }

            // Calculate button
            const calculateBtn = document.getElementById('visualAcuityCalculateBtn');
            if (calculateBtn) {
                calculateBtn.addEventListener('click', handleVisualAcuityProgressCalculation);
            }

            // Reset button
            const resetBtn = document.getElementById('visualAcuityResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    document.getElementById('visualAcuityResults').style.display = 'none';
                    document.getElementById('visualAcuityVisitsContainer').innerHTML = '';
                    visitCounter = 0;
                    addVisualAcuityVisit();
                    addVisualAcuityVisit();
                });
            }

            // Close on escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && visualAcuityProgressPopover) {
                    closeVisualAcuityProgressPopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (visualAcuityProgressPopover) {
                    positionVisualAcuityProgressPopover();
                }
            });
        }

        function handleVisualAcuityProgressCalculation() {
            const form = document.getElementById('visualAcuityForm');
            if (!form) {
                alert('Form not found');
                return;
            }
            
            // Collect visits data
            const visits = [];
            const visitEntries = form.querySelectorAll('.visit-entry');
            
            if (visitEntries.length === 0) {
                alert('No visit entries found. Please add at least 2 visits.');
                return;
            }
            
            visitEntries.forEach((entry, index) => {
                const eyeSelect = entry.querySelector('[name*="[eye]"]');
                const formatSelect = entry.querySelector('[name*="[va_format]"]');
                const valueInput = entry.querySelector('[name*="[va_value]"]');
                const dateInput = entry.querySelector('[name*="[date]"]');
                
                if (!eyeSelect || !formatSelect || !valueInput || !dateInput) {
                    console.warn(`Visit entry ${index + 1} is missing required fields`);
                    return;
                }
                
                const eye = eyeSelect.value ? eyeSelect.value.trim() : '';
                const vaFormat = formatSelect.value ? formatSelect.value.trim() : '';
                // Clean VA value - remove extra spaces
                const vaValue = valueInput.value ? valueInput.value.trim().replace(/\s+/g, ' ') : '';
                const date = dateInput.value ? dateInput.value.trim() : '';
                
                if (eye && vaFormat && vaValue && date) {
                    visits.push({
                        eye: eye,
                        va_format: vaFormat,
                        va_value: vaValue,
                        date: date
                    });
                } else {
                    console.warn(`Visit entry ${index + 1} has incomplete data:`, { eye, vaFormat, vaValue, date });
                }
            });

            if (visits.length < 2) {
                alert('At least 2 visits with complete data are required for progress calculation');
                return;
            }

            // Show loading state
            const calculateBtn = document.getElementById('visualAcuityCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            // Submit via AJAX
            // Ensure visits array is properly formatted
            const visitsData = {
                visits: visits.map(visit => ({
                    eye: String(visit.eye).trim(),
                    va_format: String(visit.va_format).trim(),
                    va_value: String(visit.va_value).trim(),
                    date: String(visit.date).trim()
                }))
            };

            // Validate data before sending
            if (!visitsData.visits || !Array.isArray(visitsData.visits) || visitsData.visits.length === 0) {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                alert('Invalid visits data. Please check your entries.');
                return;
            }

            // Log data for debugging (remove in production if needed)
            console.log('Sending Visual Acuity Progress data:', visitsData);

            fetch('/api/visual-acuity/progress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(visitsData)
            })
            .then(async response => {
                // Get response text first to check if it's valid JSON
                const responseText = await response.text();
                
                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    // If not JSON, it's likely an HTML error page
                    console.error('Invalid JSON response:', responseText.substring(0, 500));
                    throw new Error('Server returned invalid response. Status: ' + response.status);
                }
                
                if (!response.ok) {
                    throw new Error(result.error || result.message || 'Server error (Status: ' + response.status + ')');
                }
                
                return result;
            })
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderVisualAcuityProgressResults(result);
                } else {
                    const errorMsg = result.error || (result.errors && Array.isArray(result.errors) ? result.errors.join(', ') : 'Unknown error');
                    alert('Calculation failed: ' + errorMsg);
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                console.error('Visual Acuity Progress Calculation Error:', error);
                console.error('Visits data sent:', visitsData);
                alert('Error: ' + (error.message || 'Failed to calculate progress. Please check console for details.'));
            });
        }

        function renderVisualAcuityProgressResults(result) {
            const resultsContainer = document.getElementById('visualAcuityResults');
            if (!resultsContainer) return;

            // Show results
            resultsContainer.style.display = 'block';

            // Render OD results
            const odResults = result.od || {};
            const odContainer = document.getElementById('vaEyeResultsOD');
            if (odContainer && odResults.trend !== 'insufficient_data') {
                odContainer.innerHTML = `
                    <div class="va-result-item">
                        <label>Initial VA:</label>
                        <span>${odResults.initial_va_snellen || '-'} (LogMAR: ${odResults.initial_va_logmar || '-'})</span>
                    </div>
                    <div class="va-result-item">
                        <label>Final VA:</label>
                        <span>${odResults.final_va_snellen || '-'} (LogMAR: ${odResults.final_va_logmar || '-'})</span>
                    </div>
                    <div class="va-result-item">
                        <label>Change:</label>
                        <span>${odResults.logmar_change !== null ? (odResults.logmar_change > 0 ? '+' : '') + odResults.logmar_change : '-'} LogMAR</span>
                    </div>
                    <div class="va-result-item">
                        <label>Percentage Change:</label>
                        <span>${odResults.percentage_change !== null ? (odResults.percentage_change > 0 ? '+' : '') + odResults.percentage_change + '%' : '-'}</span>
                    </div>
                    <div class="va-result-item highlight">
                        <label>Trend:</label>
                        <span class="va-trend-badge ${odResults.trend}">${odResults.trend}</span>
                    </div>
                    <div class="va-clinical-note">
                        <p>${odResults.clinical_note || '-'}</p>
                    </div>
                `;
            } else if (odContainer) {
                odContainer.innerHTML = '<p class="text-muted">Insufficient data for OD</p>';
            }

            // Render OS results
            const osResults = result.os || {};
            const osContainer = document.getElementById('vaEyeResultsOS');
            if (osContainer && osResults.trend !== 'insufficient_data') {
                osContainer.innerHTML = `
                    <div class="va-result-item">
                        <label>Initial VA:</label>
                        <span>${osResults.initial_va_snellen || '-'} (LogMAR: ${osResults.initial_va_logmar || '-'})</span>
                    </div>
                    <div class="va-result-item">
                        <label>Final VA:</label>
                        <span>${osResults.final_va_snellen || '-'} (LogMAR: ${osResults.final_va_logmar || '-'})</span>
                    </div>
                    <div class="va-result-item">
                        <label>Change:</label>
                        <span>${osResults.logmar_change !== null ? (osResults.logmar_change > 0 ? '+' : '') + osResults.logmar_change : '-'} LogMAR</span>
                    </div>
                    <div class="va-result-item">
                        <label>Percentage Change:</label>
                        <span>${osResults.percentage_change !== null ? (osResults.percentage_change > 0 ? '+' : '') + osResults.percentage_change + '%' : '-'}</span>
                    </div>
                    <div class="va-result-item highlight">
                        <label>Trend:</label>
                        <span class="va-trend-badge ${osResults.trend}">${osResults.trend}</span>
                    </div>
                    <div class="va-clinical-note">
                        <p>${osResults.clinical_note || '-'}</p>
                    </div>
                `;
            } else if (osContainer) {
                osContainer.innerHTML = '<p class="text-muted">Insufficient data for OS</p>';
            }

            // Render graph
            renderVisualAcuityGraph(result);

            // Update summary note
            document.getElementById('visualAcuitySummaryNote').textContent = result.summary_note || '-';

            // Scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function renderVisualAcuityGraph(result) {
            const canvas = document.getElementById('vaProgressGraph');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            canvas.width = 800;
            canvas.height = 400;

            // Detect theme (dark or light)
            const isDarkMode = document.documentElement.classList.contains('dark');
            
            // Theme-aware colors
            const colors = {
                background: isDarkMode ? '#1e293b' : '#ffffff',
                text: isDarkMode ? '#e2e8f0' : '#1e293b',
                grid: isDarkMode ? '#334155' : '#e2e8f0',
                axis: isDarkMode ? '#64748b' : '#64748b',
                odLine: '#3b82f6', // Blue for OD
                osLine: '#ef4444', // Red for OS
                odPoint: '#3b82f6',
                osPoint: '#ef4444'
            };

            // Clear canvas with background color
            ctx.fillStyle = colors.background;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Set up graph area
            const padding = 60;
            const graphWidth = canvas.width - (padding * 2);
            const graphHeight = canvas.height - (padding * 2);
            const graphX = padding;
            const graphY = padding;

            // Draw axes
            ctx.strokeStyle = colors.axis;
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(graphX, graphY);
            ctx.lineTo(graphX, graphY + graphHeight);
            ctx.lineTo(graphX + graphWidth, graphY + graphHeight);
            ctx.stroke();

            // Draw grid lines
            ctx.strokeStyle = colors.grid;
            ctx.lineWidth = 0.5;
            for (let i = 0; i <= 5; i++) {
                const y = graphY + (graphHeight / 5) * i;
                ctx.beginPath();
                ctx.moveTo(graphX, y);
                ctx.lineTo(graphX + graphWidth, y);
                ctx.stroke();
            }

            // Plot data
            const odVisits = result.od?.visits || [];
            const osVisits = result.os?.visits || [];
            const allVisits = [...odVisits, ...osVisits].sort((a, b) => new Date(a.date) - new Date(b.date));
            
            if (allVisits.length === 0) return;

            // Find min/max LogMAR for scaling
            let minLogMAR = 0;
            let maxLogMAR = 3.0;
            allVisits.forEach(v => {
                if (v.va_logmar < minLogMAR) minLogMAR = v.va_logmar;
                if (v.va_logmar > maxLogMAR) maxLogMAR = v.va_logmar;
            });

            // Plot OD line
            if (odVisits.length > 0) {
                ctx.strokeStyle = colors.odLine;
                ctx.lineWidth = 2;
                ctx.beginPath();
                odVisits.forEach((visit, index) => {
                    const x = graphX + (graphWidth / (odVisits.length - 1)) * index;
                    const y = graphY + graphHeight - ((visit.va_logmar - minLogMAR) / (maxLogMAR - minLogMAR)) * graphHeight;
                    if (index === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                    // Draw point with outline for better visibility
                    ctx.fillStyle = colors.odPoint;
                    ctx.strokeStyle = colors.background;
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.arc(x, y, 5, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.stroke();
                });
                ctx.strokeStyle = colors.odLine;
                ctx.lineWidth = 2;
                ctx.stroke();
            }

            // Plot OS line
            if (osVisits.length > 0) {
                ctx.strokeStyle = colors.osLine;
                ctx.lineWidth = 2;
                ctx.beginPath();
                osVisits.forEach((visit, index) => {
                    const x = graphX + (graphWidth / (osVisits.length - 1)) * index;
                    const y = graphY + graphHeight - ((visit.va_logmar - minLogMAR) / (maxLogMAR - minLogMAR)) * graphHeight;
                    if (index === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                    // Draw point with outline for better visibility
                    ctx.fillStyle = colors.osPoint;
                    ctx.strokeStyle = colors.background;
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.arc(x, y, 5, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.stroke();
                });
                ctx.strokeStyle = colors.osLine;
                ctx.lineWidth = 2;
                ctx.stroke();
            }

            // Draw labels with theme-aware colors
            ctx.fillStyle = colors.text;
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Time', graphX + graphWidth / 2, canvas.height - 10);
            
            ctx.save();
            ctx.translate(15, graphY + graphHeight / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillStyle = colors.text;
            ctx.fillText('LogMAR', 0, 0);
            ctx.restore();

            // Legend with theme-aware colors
            ctx.fillStyle = colors.odLine;
            ctx.fillRect(graphX + graphWidth - 100, graphY + 10, 15, 15);
            ctx.strokeStyle = colors.background;
            ctx.lineWidth = 1;
            ctx.strokeRect(graphX + graphWidth - 100, graphY + 10, 15, 15);
            ctx.fillStyle = colors.text;
            ctx.textAlign = 'left';
            ctx.fillText('OD', graphX + graphWidth - 80, graphY + 22);

            ctx.fillStyle = colors.osLine;
            ctx.fillRect(graphX + graphWidth - 100, graphY + 30, 15, 15);
            ctx.strokeStyle = colors.background;
            ctx.lineWidth = 1;
            ctx.strokeRect(graphX + graphWidth - 100, graphY + 30, 15, 15);
            ctx.fillStyle = colors.text;
            ctx.fillText('OS', graphX + graphWidth - 80, graphY + 42);
        }

        // OSDI Calculator Popover
        let osdiPopover = null;

        function createOSDIPopover(patientId = null, appointmentId = null) {
            // Remove existing popover if any
            if (osdiPopover) {
                osdiPopover.remove();
            }

            // Get patientId and appointmentId from APPOINTMENT_CONFIG if not provided
            if (!patientId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                patientId = window.APPOINTMENT_CONFIG.patientId;
            }
            if (!appointmentId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentId) {
                appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
            }

            // Get appointment date from APPOINTMENT_CONFIG if available
            let appointmentDate = null;
            if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentDate && window.APPOINTMENT_CONFIG.appointmentDate !== 'null') {
                appointmentDate = window.APPOINTMENT_CONFIG.appointmentDate;
            } else {
                // Fallback to today's date
                const today = new Date();
                appointmentDate = today.toISOString().split('T')[0];
            }

            const popover = document.createElement('div');
            popover.className = 'osdi-popover';
            popover.id = 'osdiPopover';
            
            // Standard OSDI questions
            const osdiQuestions = [
                'Eyes that are sensitive to light',
                'Eyes that feel gritty',
                'Painful or sore eyes',
                'Blurred vision',
                'Poor vision',
                'Reading',
                'Driving at night',
                'Working with a computer or bank machine (ATM)',
                'Watching TV',
                'Windy conditions',
                'Places or areas with low humidity (very dry)',
                'Areas that are air conditioned'
            ];

            let questionsHTML = '';
            osdiQuestions.forEach((question, index) => {
                const qNum = index + 1;
                questionsHTML += `
                    <div class="osdi-question-group">
                        <label class="osdi-question-label">${qNum}. ${question}</label>
                        <div class="osdi-radio-group">
                            <label class="osdi-radio-label">
                                <input type="radio" name="question_${qNum}" value="0" class="osdi-radio">
                                <span>None</span>
                            </label>
                            <label class="osdi-radio-label">
                                <input type="radio" name="question_${qNum}" value="1" class="osdi-radio">
                                <span>Some</span>
                            </label>
                            <label class="osdi-radio-label">
                                <input type="radio" name="question_${qNum}" value="2" class="osdi-radio">
                                <span>Half</span>
                            </label>
                            <label class="osdi-radio-label">
                                <input type="radio" name="question_${qNum}" value="3" class="osdi-radio">
                                <span>Most</span>
                            </label>
                            <label class="osdi-radio-label">
                                <input type="radio" name="question_${qNum}" value="4" class="osdi-radio">
                                <span>All</span>
                            </label>
                        </div>
                    </div>
                `;
            });

            popover.innerHTML = `
                <div class="osdi-popover-content">
                    <div class="osdi-popover-header">
                        <h5>Dry Eye Severity Index (OSDI) Calculator</h5>
                        <button type="button" class="osdi-close-btn" id="osdiCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="osdi-popover-body">
                        <form id="osdiForm" class="osdi-form">
                            <div class="osdi-form-group">
                                <label for="osdiMeasurementDate">Measurement Date *</label>
                                <input type="date" id="osdiMeasurementDate" name="measurement_date" value="${appointmentDate || ''}" required>
                            </div>
                            <div class="osdi-questions-container">
                                <h6>Please answer the following questions (0 = None, 1 = Some, 2 = Half, 3 = Most, 4 = All):</h6>
                                ${questionsHTML}
                            </div>
                            <div class="osdi-form-actions">
                                <button type="button" class="btn btn-primary" id="osdiCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate OSDI Score
                                </button>
                                <button type="button" class="btn btn-secondary" id="osdiResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="osdiResults" class="osdi-results" style="display: none;">
                            <div class="osdi-results-card">
                                <div class="osdi-result-item">
                                    <label>OSDI Score</label>
                                    <span id="osdiScoreDisplay">-</span>
                                </div>
                                <div class="osdi-result-item">
                                    <label>Severity Classification</label>
                                    <span id="osdiSeverityDisplay" class="osdi-severity-badge">-</span>
                                </div>
                                <div class="osdi-result-item">
                                    <label>Answered Questions</label>
                                    <span id="osdiAnsweredDisplay">-</span>
                                </div>
                            </div>
                            <div id="osdiFollowUpComparison" class="osdi-followup-comparison" style="display: none;">
                                <h6>Follow-up Comparison</h6>
                                <div id="osdiComparisonContent"></div>
                            </div>
                            <div class="osdi-clinical-note">
                                <h6>Clinical Note</h6>
                                <p id="osdiClinicalNote">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            osdiPopover = popover;

            // Position popover
            positionOSDIPopover();

            // Initialize events
            initOSDIEvents(patientId, appointmentId);

            // Set measurement date automatically if opened from appointment page
            const measurementDateInput = document.getElementById('osdiMeasurementDate');
            if (measurementDateInput && appointmentDate) {
                measurementDateInput.value = appointmentDate;
            }

            // Load previous OSDI results if patientId provided
            if (patientId) {
                loadOSDIHistory(patientId);
            }
        }

        function positionOSDIPopover() {
            if (!osdiPopover) return;
            const corneaBtn = document.getElementById('noticeBarCorneaBtn');
            if (!corneaBtn) {
                osdiPopover.style.top = '50%';
                osdiPopover.style.left = '50%';
                osdiPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            const btnRect = corneaBtn.getBoundingClientRect();
            osdiPopover.style.top = (btnRect.bottom + 10) + 'px';
            osdiPopover.style.left = '50%';
            osdiPopover.style.transform = 'translateX(-50%)';
        }

        function closeOSDIPopover() {
            if (osdiPopover) {
                osdiPopover.remove();
                osdiPopover = null;
            }
            const backdrop = document.querySelector('.osdi-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initOSDIEvents(patientId, appointmentId) {
            // Close button
            document.getElementById('osdiCloseBtn')?.addEventListener('click', closeOSDIPopover);

            // Calculate button
            document.getElementById('osdiCalculateBtn')?.addEventListener('click', () => {
                handleOSDICalculation(patientId, appointmentId);
            });

            // Reset button
            document.getElementById('osdiResetBtn')?.addEventListener('click', () => {
                document.getElementById('osdiForm')?.reset();
                document.getElementById('osdiResults').style.display = 'none';
            });

            // Backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'osdi-popover-backdrop';
            backdrop.addEventListener('click', closeOSDIPopover);
            document.body.appendChild(backdrop);

            // Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && osdiPopover) {
                    closeOSDIPopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (osdiPopover) {
                    positionOSDIPopover();
                }
            });
        }

        function handleOSDICalculation(patientId, appointmentId) {
            const form = document.getElementById('osdiForm');
            if (!form) return;

            const measurementDate = document.getElementById('osdiMeasurementDate').value;
            if (!measurementDate) {
                alert('Please select a measurement date');
                return;
            }

            // Collect question answers
            const questions = {};
            let answeredCount = 0;
            for (let i = 1; i <= 12; i++) {
                const radio = form.querySelector(`input[name="question_${i}"]:checked`);
                if (radio) {
                    questions[i] = parseInt(radio.value);
                    answeredCount++;
                }
            }

            if (answeredCount === 0) {
                alert('Please answer at least one question');
                return;
            }

            const data = {
                questions: questions,
                measurement_date: measurementDate,
                patient_id: patientId,
                appointment_id: appointmentId
            };

            // Show loading
            const calculateBtn = document.getElementById('osdiCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            fetch('/api/osdi/calculate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const result = JSON.parse(text);
                    if (result.success) {
                        renderOSDIResults(result);
                    } else {
                        alert('Error: ' + (result.error || 'Unknown error'));
                    }
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    alert('Error: Invalid response from server');
                }
            })
            .catch(error => {
                console.error('OSDI Calculation Error:', error);
                alert('Error calculating OSDI score. Please try again.');
            })
            .finally(() => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
            });
        }

        function renderOSDIResults(result) {
            document.getElementById('osdiScoreDisplay').textContent = result.osdi_score.toFixed(2);
            
            const severityBadge = document.getElementById('osdiSeverityDisplay');
            severityBadge.textContent = result.severity;
            severityBadge.className = 'osdi-severity-badge osdi-severity-' + result.severity.toLowerCase();
            
            document.getElementById('osdiAnsweredDisplay').textContent = `${result.answered_questions} / ${result.total_questions}`;
            document.getElementById('osdiClinicalNote').textContent = result.clinical_note;

            // Show follow-up comparison if available
            if (result.follow_up_comparison) {
                const comparison = result.follow_up_comparison;
                const comparisonDiv = document.getElementById('osdiFollowUpComparison');
                const comparisonContent = document.getElementById('osdiComparisonContent');
                
                let trendClass = 'osdi-trend-stable';
                let trendIcon = '';
                if (comparison.trend === 'improving') {
                    trendClass = 'osdi-trend-improving';
                    trendIcon = '<i class="bi bi-arrow-down-circle"></i>';
                } else if (comparison.trend === 'worsening') {
                    trendClass = 'osdi-trend-worsening';
                    trendIcon = '<i class="bi bi-arrow-up-circle"></i>';
                }

                comparisonContent.innerHTML = `
                    <div class="osdi-comparison-item">
                        <label>Previous Score</label>
                        <span>${comparison.previous_score.toFixed(2)} (${comparison.previous_date ? new Date(comparison.previous_date).toLocaleDateString() : 'N/A'})</span>
                    </div>
                    <div class="osdi-comparison-item">
                        <label>Current Score</label>
                        <span>${comparison.current_score.toFixed(2)}</span>
                    </div>
                    <div class="osdi-comparison-item ${trendClass}">
                        <label>Trend</label>
                        <span>${trendIcon} ${comparison.trend.charAt(0).toUpperCase() + comparison.trend.slice(1)} (${comparison.score_change > 0 ? '+' : ''}${comparison.score_change.toFixed(2)} points)</span>
                    </div>
                    <div class="osdi-comparison-note">
                        ${comparison.comparison_note}
                    </div>
                `;
                comparisonDiv.style.display = 'block';
            } else {
                document.getElementById('osdiFollowUpComparison').style.display = 'none';
            }

            document.getElementById('osdiResults').style.display = 'block';
            document.getElementById('osdiResults').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function loadOSDIHistory(patientId) {
            if (!patientId) return;

            fetch(`/api/patients/${patientId}/osdi/history`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        // Get most recent score for comparison
                        const mostRecent = data.data[0];
                        // Don't override the date if it was already set from appointment
                        const measurementDateInput = document.getElementById('osdiMeasurementDate');
                        if (measurementDateInput && !measurementDateInput.value) {
                            // Only set if not already set from appointment date
                            const today = new Date().toISOString().split('T')[0];
                            measurementDateInput.value = today;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading OSDI history:', error);
                });
        }

        // Pachymetry-Adjusted IOP Calculator Popover
        let pachymetryAdjustedIOPPopover = null;

        function createPachymetryAdjustedIOPPopover() {
            // Remove existing popover if any
            if (pachymetryAdjustedIOPPopover) {
                pachymetryAdjustedIOPPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'pachymetry-adjusted-iop-popover';
            popover.id = 'pachymetryAdjustedIOPPopover';
            
            popover.innerHTML = `
                <div class="pachymetry-adjusted-iop-popover-content">
                    <div class="pachymetry-adjusted-iop-popover-header">
                        <h5>Pachymetry-Adjusted IOP Calculator</h5>
                        <button type="button" class="pachymetry-adjusted-iop-close-btn" id="pachymetryAdjustedIOPCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="pachymetry-adjusted-iop-popover-body">
                        <form id="pachymetryAdjustedIOPForm" class="pachymetry-adjusted-iop-form">
                            <div class="pachymetry-adjusted-iop-form-group">
                                <label for="pachymetryMeasuredIOP">Measured IOP (mmHg) *</label>
                                <input type="number" id="pachymetryMeasuredIOP" name="measured_iop" step="0.1" min="5" max="60" required>
                                <small class="form-text">Range: 5-60 mmHg</small>
                            </div>
                            <div class="pachymetry-adjusted-iop-form-group">
                                <label for="pachymetryCCT">Central Corneal Thickness (CCT) in microns *</label>
                                <input type="number" id="pachymetryCCT" name="cct" step="1" min="400" max="700" required>
                                <small class="form-text">Range: 400-700 microns</small>
                            </div>
                            <div class="pachymetry-adjusted-iop-form-actions">
                                <button type="button" class="btn btn-primary" id="pachymetryAdjustedIOPCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate
                                </button>
                                <button type="button" class="btn btn-secondary" id="pachymetryAdjustedIOPResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="pachymetryAdjustedIOPResults" class="pachymetry-adjusted-iop-results" style="display: none;">
                            <div class="pachymetry-adjusted-iop-results-card">
                                <div class="pachymetry-adjusted-iop-result-item">
                                    <label>Measured IOP</label>
                                    <span id="pachymetryMeasuredIOPDisplay">-</span>
                                </div>
                                <div class="pachymetry-adjusted-iop-result-item">
                                    <label>CCT</label>
                                    <span id="pachymetryCCTDisplay">-</span>
                                </div>
                                <div class="pachymetry-adjusted-iop-result-item">
                                    <label>CCT Classification</label>
                                    <span id="pachymetryCCTClassificationDisplay">-</span>
                                </div>
                                <div class="pachymetry-adjusted-iop-result-item">
                                    <label>Correction</label>
                                    <span id="pachymetryCorrectionDisplay">-</span>
                                </div>
                                <div class="pachymetry-adjusted-iop-result-item highlight">
                                    <label>Corrected IOP</label>
                                    <span id="pachymetryCorrectedIOPDisplay">-</span>
                                </div>
                            </div>
                            <div class="pachymetry-adjusted-iop-clinical-note">
                                <h6>Clinical Note</h6>
                                <p id="pachymetryAdjustedIOPClinicalNote">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            pachymetryAdjustedIOPPopover = popover;

            // Position popover
            positionPachymetryAdjustedIOPPopover();

            // Initialize events
            initPachymetryAdjustedIOPEvents();
        }

        function positionPachymetryAdjustedIOPPopover() {
            if (!pachymetryAdjustedIOPPopover) return;
            const corneaBtn = document.getElementById('noticeBarCorneaBtn');
            if (!corneaBtn) {
                pachymetryAdjustedIOPPopover.style.top = '50%';
                pachymetryAdjustedIOPPopover.style.left = '50%';
                pachymetryAdjustedIOPPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            const btnRect = corneaBtn.getBoundingClientRect();
            pachymetryAdjustedIOPPopover.style.top = (btnRect.bottom + 10) + 'px';
            pachymetryAdjustedIOPPopover.style.left = '50%';
            pachymetryAdjustedIOPPopover.style.transform = 'translateX(-50%)';
        }

        function closePachymetryAdjustedIOPPopover() {
            if (pachymetryAdjustedIOPPopover) {
                pachymetryAdjustedIOPPopover.remove();
                pachymetryAdjustedIOPPopover = null;
            }
            const backdrop = document.querySelector('.pachymetry-adjusted-iop-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initPachymetryAdjustedIOPEvents() {
            // Close button
            document.getElementById('pachymetryAdjustedIOPCloseBtn')?.addEventListener('click', closePachymetryAdjustedIOPPopover);

            // Calculate button
            document.getElementById('pachymetryAdjustedIOPCalculateBtn')?.addEventListener('click', () => {
                handlePachymetryAdjustedIOPCalculation();
            });

            // Reset button
            document.getElementById('pachymetryAdjustedIOPResetBtn')?.addEventListener('click', () => {
                document.getElementById('pachymetryAdjustedIOPForm')?.reset();
                document.getElementById('pachymetryAdjustedIOPResults').style.display = 'none';
            });

            // Backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'pachymetry-adjusted-iop-popover-backdrop';
            backdrop.addEventListener('click', closePachymetryAdjustedIOPPopover);
            document.body.appendChild(backdrop);

            // Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && pachymetryAdjustedIOPPopover) {
                    closePachymetryAdjustedIOPPopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (pachymetryAdjustedIOPPopover) {
                    positionPachymetryAdjustedIOPPopover();
                }
            });
        }

        function handlePachymetryAdjustedIOPCalculation() {
            const form = document.getElementById('pachymetryAdjustedIOPForm');
            if (!form) return;

            const measuredIOP = parseFloat(document.getElementById('pachymetryMeasuredIOP').value);
            const cct = parseFloat(document.getElementById('pachymetryCCT').value);

            if (!measuredIOP || !cct) {
                alert('Please fill in all required fields');
                return;
            }

            const data = {
                measured_iop: measuredIOP,
                cct: cct
            };

            // Show loading
            const calculateBtn = document.getElementById('pachymetryAdjustedIOPCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            fetch('/api/pachymetry-adjusted-iop/calculate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const result = JSON.parse(text);
                    if (result.success) {
                        renderPachymetryAdjustedIOPResults(result);
                    } else {
                        alert('Error: ' + (result.error || 'Unknown error'));
                    }
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    alert('Error: Invalid response from server');
                }
            })
            .catch(error => {
                console.error('Pachymetry-Adjusted IOP Calculation Error:', error);
                alert('Error calculating corrected IOP. Please try again.');
            })
            .finally(() => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
            });
        }

        function renderPachymetryAdjustedIOPResults(result) {
            document.getElementById('pachymetryMeasuredIOPDisplay').textContent = result.measured_iop.toFixed(1) + ' mmHg';
            document.getElementById('pachymetryCCTDisplay').textContent = Math.round(result.cct) + ' μm';
            document.getElementById('pachymetryCCTClassificationDisplay').textContent = result.cct_classification;
            
            const correctionDisplay = document.getElementById('pachymetryCorrectionDisplay');
            const correctionValue = result.correction > 0 ? '+' + result.correction.toFixed(2) : result.correction.toFixed(2);
            correctionDisplay.textContent = correctionValue + ' mmHg';
            if (result.correction_direction === 'increase') {
                correctionDisplay.className = 'pachymetry-correction-increase';
            } else if (result.correction_direction === 'decrease') {
                correctionDisplay.className = 'pachymetry-correction-decrease';
            } else {
                correctionDisplay.className = '';
            }
            
            document.getElementById('pachymetryCorrectedIOPDisplay').textContent = result.corrected_iop.toFixed(1) + ' mmHg';
            document.getElementById('pachymetryAdjustedIOPClinicalNote').textContent = result.clinical_note;

            document.getElementById('pachymetryAdjustedIOPResults').style.display = 'block';
            document.getElementById('pachymetryAdjustedIOPResults').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Diabetic Retinopathy Risk Estimator Popover
        let diabeticRetinopathyRiskPopover = null;

        function createDiabeticRetinopathyRiskPopover() {
            // Remove existing popover if any
            if (diabeticRetinopathyRiskPopover) {
                diabeticRetinopathyRiskPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'diabetic-retinopathy-risk-popover';
            popover.id = 'diabeticRetinopathyRiskPopover';
            
            popover.innerHTML = `
                <div class="diabetic-retinopathy-risk-popover-content">
                    <div class="diabetic-retinopathy-risk-popover-header">
                        <h5>Diabetic Retinopathy Risk Estimator</h5>
                        <button type="button" class="diabetic-retinopathy-risk-close-btn" id="diabeticRetinopathyRiskCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="diabetic-retinopathy-risk-popover-body">
                        <form id="diabeticRetinopathyRiskForm" class="diabetic-retinopathy-risk-form">
                            <div class="diabetic-retinopathy-risk-form-group">
                                <label for="diabeticRetinopathyDuration">Duration of Diabetes (years) *</label>
                                <input type="number" id="diabeticRetinopathyDuration" name="duration_years" step="0.1" min="0" max="100" required>
                                <small class="form-text">Range: 0-100 years</small>
                            </div>
                            <div class="diabetic-retinopathy-risk-form-group">
                                <label for="diabeticRetinopathyHbA1c">Latest HbA1c (%) *</label>
                                <input type="number" id="diabeticRetinopathyHbA1c" name="hba1c" step="0.1" min="3" max="20" required>
                                <small class="form-text">Range: 3.0-20.0%</small>
                            </div>
                            <div class="diabetic-retinopathy-risk-form-row">
                                <div class="diabetic-retinopathy-risk-form-group">
                                    <label for="diabeticRetinopathySystolicBP">Systolic BP (mmHg) *</label>
                                    <input type="number" id="diabeticRetinopathySystolicBP" name="systolic_bp" step="1" min="50" max="250" required>
                                </div>
                                <div class="diabetic-retinopathy-risk-form-group">
                                    <label for="diabeticRetinopathyDiastolicBP">Diastolic BP (mmHg) *</label>
                                    <input type="number" id="diabeticRetinopathyDiastolicBP" name="diastolic_bp" step="1" min="30" max="150" required>
                                </div>
                            </div>
                            <div class="diabetic-retinopathy-risk-form-group">
                                <label for="diabeticRetinopathyFundusGrade">Fundus Examination Grade *</label>
                                <select id="diabeticRetinopathyFundusGrade" name="fundus_grade" required>
                                    <option value="">Select grade...</option>
                                    <option value="No DR">No DR</option>
                                    <option value="Mild NPDR">Mild NPDR</option>
                                    <option value="Moderate NPDR">Moderate NPDR</option>
                                    <option value="Severe NPDR">Severe NPDR</option>
                                    <option value="PDR">PDR</option>
                                </select>
                                <small class="form-text">Select the fundus examination grade</small>
                            </div>
                            <div class="diabetic-retinopathy-risk-form-actions">
                                <button type="button" class="btn btn-primary" id="diabeticRetinopathyRiskCalculateBtn">
                                    <i class="bi bi-calculator"></i> Estimate Risk
                                </button>
                                <button type="button" class="btn btn-secondary" id="diabeticRetinopathyRiskResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="diabeticRetinopathyRiskResults" class="diabetic-retinopathy-risk-results" style="display: none;">
                            <div class="diabetic-retinopathy-risk-results-card">
                                <div class="diabetic-retinopathy-risk-result-item">
                                    <label>Risk Level</label>
                                    <span id="diabeticRetinopathyRiskLevelDisplay" class="diabetic-retinopathy-risk-badge">-</span>
                                </div>
                                <div class="diabetic-retinopathy-risk-result-item">
                                    <label>Total Risk Score</label>
                                    <span id="diabeticRetinopathyTotalScoreDisplay">-</span>
                                </div>
                                <div class="diabetic-retinopathy-risk-result-item">
                                    <label>Follow-up Interval</label>
                                    <span id="diabeticRetinopathyFollowUpDisplay">-</span>
                                </div>
                            </div>
                            <div class="diabetic-retinopathy-risk-scoring">
                                <h6>Risk Factor Scores</h6>
                                <div class="diabetic-retinopathy-risk-scoring-grid">
                                    <div class="diabetic-retinopathy-risk-scoring-item">
                                        <label>Duration Score</label>
                                        <span id="diabeticRetinopathyDurationScore">-</span>
                                    </div>
                                    <div class="diabetic-retinopathy-risk-scoring-item">
                                        <label>HbA1c Score</label>
                                        <span id="diabeticRetinopathyHbA1cScore">-</span>
                                    </div>
                                    <div class="diabetic-retinopathy-risk-scoring-item">
                                        <label>BP Score</label>
                                        <span id="diabeticRetinopathyBPScore">-</span>
                                    </div>
                                    <div class="diabetic-retinopathy-risk-scoring-item">
                                        <label>Fundus Score</label>
                                        <span id="diabeticRetinopathyFundusScore">-</span>
                                    </div>
                                </div>
                            </div>
                            <div id="diabeticRetinopathyContributingFactors" class="diabetic-retinopathy-contributing-factors" style="display: none;">
                                <h6>Contributing Factors</h6>
                                <ul id="diabeticRetinopathyContributingFactorsList"></ul>
                            </div>
                            <div class="diabetic-retinopathy-clinical-summary">
                                <h6>Clinical Summary</h6>
                                <p id="diabeticRetinopathyClinicalSummary">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            diabeticRetinopathyRiskPopover = popover;

            // Position popover
            positionDiabeticRetinopathyRiskPopover();

            // Initialize events
            initDiabeticRetinopathyRiskEvents();
        }

        function positionDiabeticRetinopathyRiskPopover() {
            if (!diabeticRetinopathyRiskPopover) return;
            const retinaBtn = document.getElementById('noticeBarRetinaBtn');
            if (!retinaBtn) {
                diabeticRetinopathyRiskPopover.style.top = '50%';
                diabeticRetinopathyRiskPopover.style.left = '50%';
                diabeticRetinopathyRiskPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            const btnRect = retinaBtn.getBoundingClientRect();
            diabeticRetinopathyRiskPopover.style.top = (btnRect.bottom + 10) + 'px';
            diabeticRetinopathyRiskPopover.style.left = '50%';
            diabeticRetinopathyRiskPopover.style.transform = 'translateX(-50%)';
        }

        function closeDiabeticRetinopathyRiskPopover() {
            if (diabeticRetinopathyRiskPopover) {
                diabeticRetinopathyRiskPopover.remove();
                diabeticRetinopathyRiskPopover = null;
            }
            const backdrop = document.querySelector('.diabetic-retinopathy-risk-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initDiabeticRetinopathyRiskEvents() {
            // Close button
            document.getElementById('diabeticRetinopathyRiskCloseBtn')?.addEventListener('click', closeDiabeticRetinopathyRiskPopover);

            // Calculate button
            document.getElementById('diabeticRetinopathyRiskCalculateBtn')?.addEventListener('click', () => {
                handleDiabeticRetinopathyRiskCalculation();
            });

            // Reset button
            document.getElementById('diabeticRetinopathyRiskResetBtn')?.addEventListener('click', () => {
                document.getElementById('diabeticRetinopathyRiskForm')?.reset();
                document.getElementById('diabeticRetinopathyRiskResults').style.display = 'none';
            });

            // Backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'diabetic-retinopathy-risk-popover-backdrop';
            backdrop.addEventListener('click', closeDiabeticRetinopathyRiskPopover);
            document.body.appendChild(backdrop);

            // Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && diabeticRetinopathyRiskPopover) {
                    closeDiabeticRetinopathyRiskPopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (diabeticRetinopathyRiskPopover) {
                    positionDiabeticRetinopathyRiskPopover();
                }
            });
        }

        function handleDiabeticRetinopathyRiskCalculation() {
            const form = document.getElementById('diabeticRetinopathyRiskForm');
            if (!form) return;

            const durationYears = parseFloat(document.getElementById('diabeticRetinopathyDuration').value);
            const hba1c = parseFloat(document.getElementById('diabeticRetinopathyHbA1c').value);
            const systolicBP = parseInt(document.getElementById('diabeticRetinopathySystolicBP').value);
            const diastolicBP = parseInt(document.getElementById('diabeticRetinopathyDiastolicBP').value);
            const fundusGrade = document.getElementById('diabeticRetinopathyFundusGrade').value;

            if (!durationYears || !hba1c || !systolicBP || !diastolicBP || !fundusGrade) {
                alert('Please fill in all required fields');
                return;
            }

            const data = {
                duration_years: durationYears,
                hba1c: hba1c,
                systolic_bp: systolicBP,
                diastolic_bp: diastolicBP,
                fundus_grade: fundusGrade
            };

            // Show loading
            const calculateBtn = document.getElementById('diabeticRetinopathyRiskCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';

            fetch('/api/diabetic-retinopathy/risk-estimate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(async response => {
                // Get response text first to check if it's valid JSON
                const responseText = await response.text();
                
                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    // If not JSON, it's likely an HTML error page
                    console.error('Invalid JSON response:', responseText.substring(0, 500));
                    throw new Error('Server returned invalid response. Status: ' + response.status);
                }
                
                if (!response.ok) {
                    throw new Error(result.error || result.message || 'Server error (Status: ' + response.status + ')');
                }
                
                return result;
            })
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderDiabeticRetinopathyRiskResults(result);
                } else {
                    const errorMsg = result.error || (result.errors && Array.isArray(result.errors) ? result.errors.join(', ') : 'Unknown error');
                    alert('Calculation failed: ' + errorMsg);
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                console.error('Diabetic Retinopathy Risk Estimation Error:', error);
                alert('Error estimating risk: ' + (error.message || 'Please try again.'));
            });
        }

        function renderDiabeticRetinopathyRiskResults(result) {
            const riskLevelBadge = document.getElementById('diabeticRetinopathyRiskLevelDisplay');
            riskLevelBadge.textContent = result.risk_level;
            riskLevelBadge.className = 'diabetic-retinopathy-risk-badge diabetic-retinopathy-risk-' + result.risk_level.toLowerCase().replace(' ', '-');
            
            document.getElementById('diabeticRetinopathyTotalScoreDisplay').textContent = result.total_score;
            document.getElementById('diabeticRetinopathyFollowUpDisplay').textContent = result.follow_up_interval;
            
            document.getElementById('diabeticRetinopathyDurationScore').textContent = result.duration_score;
            document.getElementById('diabeticRetinopathyHbA1cScore').textContent = result.hba1c_score;
            document.getElementById('diabeticRetinopathyBPScore').textContent = result.bp_score;
            document.getElementById('diabeticRetinopathyFundusScore').textContent = result.fundus_score;

            // Contributing factors
            if (result.contributing_factors && result.contributing_factors.length > 0) {
                const factorsList = document.getElementById('diabeticRetinopathyContributingFactorsList');
                factorsList.innerHTML = '';
                result.contributing_factors.forEach(factor => {
                    const li = document.createElement('li');
                    li.textContent = factor;
                    factorsList.appendChild(li);
                });
                document.getElementById('diabeticRetinopathyContributingFactors').style.display = 'block';
            } else {
                document.getElementById('diabeticRetinopathyContributingFactors').style.display = 'none';
            }

            document.getElementById('diabeticRetinopathyClinicalSummary').textContent = result.clinical_summary;

            document.getElementById('diabeticRetinopathyRiskResults').style.display = 'block';
            document.getElementById('diabeticRetinopathyRiskResults').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Macular Thickness Trend Analyzer Popover
        let macularThicknessTrendPopover = null;
        let macularThicknessVisitCounter = 0;

        function createMacularThicknessTrendPopover(patientId = null, appointmentId = null) {
            // Remove existing popover if any
            if (macularThicknessTrendPopover) {
                macularThicknessTrendPopover.remove();
            }

            // Get patientId and appointmentId from APPOINTMENT_CONFIG if not provided
            if (!patientId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                patientId = window.APPOINTMENT_CONFIG.patientId;
            }
            if (!appointmentId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentId) {
                appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
            }

            // Get appointment date from APPOINTMENT_CONFIG if available
            let appointmentDate = null;
            if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentDate && window.APPOINTMENT_CONFIG.appointmentDate !== 'null') {
                appointmentDate = window.APPOINTMENT_CONFIG.appointmentDate;
            } else {
                const today = new Date();
                appointmentDate = today.toISOString().split('T')[0];
            }

            macularThicknessVisitCounter = 0;

            const popover = document.createElement('div');
            popover.className = 'macular-thickness-trend-popover';
            popover.id = 'macularThicknessTrendPopover';
            
            popover.innerHTML = `
                <div class="macular-thickness-trend-popover-content">
                    <div class="macular-thickness-trend-popover-header">
                        <h5>Macular Thickness Trend Analyzer</h5>
                        <button type="button" class="macular-thickness-trend-close-btn" id="macularThicknessTrendCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="macular-thickness-trend-popover-body">
                        <form id="macularThicknessTrendForm" class="macular-thickness-trend-form">
                            <div id="macularThicknessVisitsContainer" class="macular-thickness-visits-container">
                                <!-- Visit entries will be added here -->
                            </div>
                            <div class="macular-thickness-form-actions">
                                <button type="button" class="btn btn-secondary" id="macularThicknessAddVisitBtn">
                                    <i class="bi bi-plus-circle"></i> Add Visit
                                </button>
                                <button type="button" class="btn btn-primary" id="macularThicknessCalculateBtn">
                                    <i class="bi bi-calculator"></i> Analyze Trend
                                </button>
                                <button type="button" class="btn btn-secondary" id="macularThicknessResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="macularThicknessTrendResults" class="macular-thickness-trend-results" style="display: none;">
                            <div id="macularThicknessAlerts" class="macular-thickness-alerts" style="display: none;"></div>
                            <div class="macular-thickness-trend-results-card">
                                <div id="macularThicknessODResults" class="macular-thickness-eye-results" style="display: none;">
                                    <h6>OD (Right Eye)</h6>
                                    <div class="macular-thickness-result-item">
                                        <label>Trend</label>
                                        <span id="macularThicknessODTrend" class="macular-thickness-trend-badge">-</span>
                                    </div>
                                    <div class="macular-thickness-result-item">
                                        <label>Baseline Thickness</label>
                                        <span id="macularThicknessODBaseline">-</span>
                                    </div>
                                    <div class="macular-thickness-result-item">
                                        <label>Latest Thickness</label>
                                        <span id="macularThicknessODLatest">-</span>
                                    </div>
                                    <div class="macular-thickness-result-item">
                                        <label>Change</label>
                                        <span id="macularThicknessODChange">-</span>
                                    </div>
                                </div>
                                <div id="macularThicknessOSResults" class="macular-thickness-eye-results" style="display: none;">
                                    <h6>OS (Left Eye)</h6>
                                    <div class="macular-thickness-result-item">
                                        <label>Trend</label>
                                        <span id="macularThicknessOSTrend" class="macular-thickness-trend-badge">-</span>
                                    </div>
                                    <div class="macular-thickness-result-item">
                                        <label>Baseline Thickness</label>
                                        <span id="macularThicknessOSBaseline">-</span>
                                    </div>
                                    <div class="macular-thickness-result-item">
                                        <label>Latest Thickness</label>
                                        <span id="macularThicknessOSLatest">-</span>
                                    </div>
                                    <div class="macular-thickness-result-item">
                                        <label>Change</label>
                                        <span id="macularThicknessOSChange">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="macular-thickness-clinical-summary">
                                <h6>Clinical Summary</h6>
                                <p id="macularThicknessClinicalSummary">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            macularThicknessTrendPopover = popover;

            // Position popover
            positionMacularThicknessTrendPopover();

            // Initialize events
            initMacularThicknessTrendEvents(patientId, appointmentId);

            // Load previous measurements if patientId provided
            if (patientId) {
                loadMacularThicknessFromAppointments(patientId, appointmentId);
            } else {
                // Add initial visit entry if no patient data
                addMacularThicknessVisitEntry(appointmentDate);
            }
        }

        function addMacularThicknessVisitEntry(defaultDate = null) {
            const container = document.getElementById('macularThicknessVisitsContainer');
            if (!container) return;

            const visitIndex = macularThicknessVisitCounter++;
            const visitDate = defaultDate || new Date().toISOString().split('T')[0];

            const visitEntry = document.createElement('div');
            visitEntry.className = 'macular-thickness-visit-entry';
            visitEntry.dataset.visitIndex = visitIndex;
            
            visitEntry.innerHTML = `
                <div class="macular-thickness-visit-header">
                    <h6>Visit ${visitIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger macular-thickness-remove-visit-btn" data-visit-index="${visitIndex}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="macular-thickness-visit-fields">
                    <div class="macular-thickness-form-group">
                        <label>Eye *</label>
                        <select name="visits[${visitIndex}][eye]" required>
                            <option value="">Select...</option>
                            <option value="OD">OD (Right)</option>
                            <option value="OS">OS (Left)</option>
                        </select>
                    </div>
                    <div class="macular-thickness-form-group">
                        <label>Central Macular Thickness (µm) *</label>
                        <input type="number" name="visits[${visitIndex}][central_macular_thickness]" step="0.01" min="100" max="1000" required>
                        <small class="form-text">Range: 100-1000 µm</small>
                    </div>
                    <div class="macular-thickness-form-group">
                        <label>Measurement Date *</label>
                        <input type="date" name="visits[${visitIndex}][date]" value="${visitDate || ''}" required>
                    </div>
                </div>
            `;

            container.appendChild(visitEntry);

            // Add remove button event listener
            visitEntry.querySelector('.macular-thickness-remove-visit-btn')?.addEventListener('click', function() {
                if (container.children.length > 1) {
                    visitEntry.remove();
                } else {
                    alert('At least one visit entry is required');
                }
            });
        }

        function positionMacularThicknessTrendPopover() {
            if (!macularThicknessTrendPopover) return;
            const retinaBtn = document.getElementById('noticeBarRetinaBtn');
            if (!retinaBtn) {
                macularThicknessTrendPopover.style.top = '50%';
                macularThicknessTrendPopover.style.left = '50%';
                macularThicknessTrendPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            const btnRect = retinaBtn.getBoundingClientRect();
            macularThicknessTrendPopover.style.top = (btnRect.bottom + 10) + 'px';
            macularThicknessTrendPopover.style.left = '50%';
            macularThicknessTrendPopover.style.transform = 'translateX(-50%)';
        }

        function closeMacularThicknessTrendPopover() {
            if (macularThicknessTrendPopover) {
                macularThicknessTrendPopover.remove();
                macularThicknessTrendPopover = null;
            }
            const backdrop = document.querySelector('.macular-thickness-trend-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initMacularThicknessTrendEvents(patientId, appointmentId) {
            // Close button
            document.getElementById('macularThicknessTrendCloseBtn')?.addEventListener('click', closeMacularThicknessTrendPopover);

            // Add visit button
            document.getElementById('macularThicknessAddVisitBtn')?.addEventListener('click', () => {
                addMacularThicknessVisitEntry();
            });

            // Calculate button
            document.getElementById('macularThicknessCalculateBtn')?.addEventListener('click', () => {
                handleMacularThicknessTrendCalculation(patientId, appointmentId);
            });

            // Reset button
            document.getElementById('macularThicknessResetBtn')?.addEventListener('click', () => {
                const container = document.getElementById('macularThicknessVisitsContainer');
                if (container) {
                    container.innerHTML = '';
                    macularThicknessVisitCounter = 0;
                    const appointmentDate = window.APPOINTMENT_CONFIG?.appointmentDate || new Date().toISOString().split('T')[0];
                    addMacularThicknessVisitEntry(appointmentDate);
                }
                document.getElementById('macularThicknessTrendResults').style.display = 'none';
            });

            // Backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'macular-thickness-trend-popover-backdrop';
            backdrop.addEventListener('click', closeMacularThicknessTrendPopover);
            document.body.appendChild(backdrop);

            // Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && macularThicknessTrendPopover) {
                    closeMacularThicknessTrendPopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (macularThicknessTrendPopover) {
                    positionMacularThicknessTrendPopover();
                }
            });
        }

        function handleMacularThicknessTrendCalculation(patientId, appointmentId) {
            const form = document.getElementById('macularThicknessTrendForm');
            if (!form) return;

            // Collect visits data
            const visits = [];
            const visitEntries = form.querySelectorAll('.macular-thickness-visit-entry');
            
            if (visitEntries.length === 0) {
                alert('No visit entries found. Please add at least 2 visits.');
                return;
            }

            visitEntries.forEach((entry) => {
                const eyeSelect = entry.querySelector('[name*="[eye]"]');
                const thicknessInput = entry.querySelector('[name*="[central_macular_thickness]"]');
                const dateInput = entry.querySelector('[name*="[date]"]');
                
                if (!eyeSelect || !thicknessInput || !dateInput) {
                    return;
                }

                const eye = eyeSelect.value ? eyeSelect.value.trim() : '';
                const thickness = thicknessInput.value ? thicknessInput.value.trim() : '';
                const date = dateInput.value ? dateInput.value.trim() : '';

                if (eye && thickness && date) {
                    visits.push({
                        eye: eye,
                        central_macular_thickness: parseFloat(thickness),
                        date: date
                    });
                }
            });

            if (visits.length < 2) {
                alert('At least 2 valid visits are required for trend analysis');
                return;
            }

            const data = {
                visits: visits,
                patient_id: patientId,
                appointment_id: appointmentId
            };

            // Show loading
            const calculateBtn = document.getElementById('macularThicknessCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';

            fetch('/api/macular-thickness/trend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const result = JSON.parse(text);
                    if (result.success) {
                        renderMacularThicknessTrendResults(result);
                    } else {
                        alert('Error: ' + (result.error || 'Unknown error'));
                    }
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    alert('Error: Invalid response from server');
                }
            })
            .catch(error => {
                console.error('Macular Thickness Trend Analysis Error:', error);
                alert('Error analyzing trend. Please try again.');
            })
            .finally(() => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
            });
        }

        function renderMacularThicknessTrendResults(result) {
            // Render OD results
            if (result.od && result.od.trend !== 'insufficient_data') {
                document.getElementById('macularThicknessODResults').style.display = 'block';
                const odTrendBadge = document.getElementById('macularThicknessODTrend');
                odTrendBadge.textContent = result.od.trend.charAt(0).toUpperCase() + result.od.trend.slice(1);
                odTrendBadge.className = 'macular-thickness-trend-badge macular-thickness-trend-' + result.od.trend;
                document.getElementById('macularThicknessODBaseline').textContent = result.od.baseline_thickness + ' µm';
                document.getElementById('macularThicknessODLatest').textContent = result.od.latest_thickness + ' µm';
                const odChange = result.od.change_from_baseline > 0 ? '+' + result.od.change_from_baseline : result.od.change_from_baseline;
                document.getElementById('macularThicknessODChange').textContent = `${odChange} µm (${result.od.percent_change > 0 ? '+' : ''}${result.od.percent_change.toFixed(1)}%)`;
            } else {
                document.getElementById('macularThicknessODResults').style.display = 'none';
            }

            // Render OS results
            if (result.os && result.os.trend !== 'insufficient_data') {
                document.getElementById('macularThicknessOSResults').style.display = 'block';
                const osTrendBadge = document.getElementById('macularThicknessOSTrend');
                osTrendBadge.textContent = result.os.trend.charAt(0).toUpperCase() + result.os.trend.slice(1);
                osTrendBadge.className = 'macular-thickness-trend-badge macular-thickness-trend-' + result.os.trend;
                document.getElementById('macularThicknessOSBaseline').textContent = result.os.baseline_thickness + ' µm';
                document.getElementById('macularThicknessOSLatest').textContent = result.os.latest_thickness + ' µm';
                const osChange = result.os.change_from_baseline > 0 ? '+' + result.os.change_from_baseline : result.os.change_from_baseline;
                document.getElementById('macularThicknessOSChange').textContent = `${osChange} µm (${result.os.percent_change > 0 ? '+' : ''}${result.os.percent_change.toFixed(1)}%)`;
            } else {
                document.getElementById('macularThicknessOSResults').style.display = 'none';
            }

            // Render alerts
            if (result.alerts && result.alerts.length > 0) {
                const alertsDiv = document.getElementById('macularThicknessAlerts');
                alertsDiv.innerHTML = '<h6>Alerts</h6>';
                result.alerts.forEach(alert => {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'macular-thickness-alert macular-thickness-alert-' + alert.type;
                    alertDiv.innerHTML = `<strong>${alert.eye}:</strong> ${alert.message}`;
                    alertsDiv.appendChild(alertDiv);
                });
                alertsDiv.style.display = 'block';
            } else {
                document.getElementById('macularThicknessAlerts').style.display = 'none';
            }

            document.getElementById('macularThicknessClinicalSummary').textContent = result.clinical_summary;

            document.getElementById('macularThicknessTrendResults').style.display = 'block';
            document.getElementById('macularThicknessTrendResults').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function loadMacularThicknessFromAppointments(patientId, excludeAppointmentId = null) {
            const container = document.getElementById('macularThicknessVisitsContainer');
            if (!container) return;

            // Show loading state
            container.innerHTML = '<div class="text-center py-4"><i class="bi bi-hourglass-split text-muted" style="font-size: 2rem;"></i><p class="text-muted mt-2">Loading Macular Thickness data from appointments...</p></div>';

            // First try to load from saved macular thickness history
            fetch(`/api/patients/${patientId}/macular-thickness/history`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        // Group by eye and date
                        const visitsByEye = {};
                        data.data.forEach(measurement => {
                            const eye = measurement.eye;
                            if (!visitsByEye[eye]) {
                                visitsByEye[eye] = [];
                            }
                            visitsByEye[eye].push({
                                eye: eye,
                                central_macular_thickness: parseFloat(measurement.central_macular_thickness),
                                date: measurement.measurement_date
                            });
                        });

                        // Add visits to form
                        container.innerHTML = '';
                        macularThicknessVisitCounter = 0;

                        // Add visits for each eye
                        Object.keys(visitsByEye).forEach(eye => {
                            visitsByEye[eye].forEach(visit => {
                                addMacularThicknessVisitEntry(visit.date);
                                const lastEntry = container.lastElementChild;
                                if (lastEntry) {
                                    lastEntry.querySelector(`[name*="[eye]"]`).value = visit.eye;
                                    lastEntry.querySelector(`[name*="[central_macular_thickness]"]`).value = visit.central_macular_thickness;
                                    lastEntry.querySelector(`[name*="[date]"]`).value = visit.date;
                                }
                            });
                        });

                        // If we have at least 2 visits, auto-calculate
                        const totalVisits = Object.values(visitsByEye).reduce((sum, visits) => sum + visits.length, 0);
                        if (totalVisits >= 2) {
                            setTimeout(() => {
                                document.getElementById('macularThicknessCalculateBtn')?.click();
                            }, 500);
                        } else {
                            // If not enough visits, try loading from appointments
                            loadMacularThicknessFromAppointmentNotes(patientId, excludeAppointmentId);
                        }
                    } else {
                        // No saved history, try loading from appointments
                        loadMacularThicknessFromAppointmentNotes(patientId, excludeAppointmentId);
                    }
                })
                .catch(error => {
                    console.error('Error loading macular thickness history:', error);
                    // Try loading from appointments on error
                    loadMacularThicknessFromAppointmentNotes(patientId, excludeAppointmentId);
                });
        }

        function loadMacularThicknessFromAppointmentNotes(patientId, excludeAppointmentId = null) {
            const container = document.getElementById('macularThicknessVisitsContainer');
            if (!container) return;

            // Fetch appointment history
            const url = `/api/patients/${patientId}/appointments/history${excludeAppointmentId ? '?exclude=' + excludeAppointmentId : ''}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                macularThicknessVisitCounter = 0;

                if (data.success && data.appointments && data.appointments.length > 0) {
                    // Extract macular thickness data from consultation notes
                    const macularThicknessVisits = [];
                    
                    data.appointments.forEach(appointment => {
                        if (appointment.consultation_notes && Array.isArray(appointment.consultation_notes)) {
                            appointment.consultation_notes.forEach(note => {
                                // Look for macular thickness in various fields
                                const noteText = JSON.stringify(note).toLowerCase();
                                const appointmentDate = appointment.date || appointment.appointment_date;
                                
                                // Try to extract macular thickness from fundus examination or notes
                                // Common patterns: "macular thickness", "CMT", "central macular", "OCT"
                                if (noteText.includes('macular') || noteText.includes('oct') || noteText.includes('cmt')) {
                                    // Try to extract thickness values (numbers followed by µm or microns)
                                    const thicknessMatch = noteText.match(/(\d+(?:\.\d+)?)\s*(?:µm|microns?|micrometers?)/gi);
                                    if (thicknessMatch) {
                                        thicknessMatch.forEach(match => {
                                            const valueMatch = match.match(/(\d+(?:\.\d+)?)/);
                                            if (valueMatch) {
                                                const thickness = parseFloat(valueMatch[1]);
                                                // Determine eye from context
                                                let eye = 'OD';
                                                if (noteText.includes('os') || noteText.includes('left')) {
                                                    eye = 'OS';
                                                } else if (noteText.includes('od') || noteText.includes('right')) {
                                                    eye = 'OD';
                                                }
                                                
                                                macularThicknessVisits.push({
                                                    eye: eye,
                                                    central_macular_thickness: thickness,
                                                    date: appointmentDate
                                                });
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });

                    if (macularThicknessVisits.length > 0) {
                        // Group by date and eye to avoid duplicates
                        const uniqueVisits = [];
                        const visitMap = new Map();
                        
                        macularThicknessVisits.forEach(visit => {
                            const key = `${visit.date}_${visit.eye}`;
                            if (!visitMap.has(key)) {
                                visitMap.set(key, visit);
                                uniqueVisits.push(visit);
                            }
                        });

                        // Sort by date
                        uniqueVisits.sort((a, b) => new Date(a.date) - new Date(b.date));

                        // Add visits to form
                        uniqueVisits.forEach(visit => {
                            addMacularThicknessVisitEntry(visit.date);
                            const lastEntry = container.lastElementChild;
                            if (lastEntry) {
                                lastEntry.querySelector(`[name*="[eye]"]`).value = visit.eye;
                                lastEntry.querySelector(`[name*="[central_macular_thickness]"]`).value = visit.central_macular_thickness;
                                lastEntry.querySelector(`[name*="[date]"]`).value = visit.date;
                            }
                        });

                        // Auto-calculate if we have at least 2 visits
                        if (uniqueVisits.length >= 2) {
                            setTimeout(() => {
                                const calculateBtn = document.getElementById('macularThicknessCalculateBtn');
                                if (calculateBtn) {
                                    handleMacularThicknessTrendCalculation(patientId, excludeAppointmentId);
                                }
                            }, 800);
                        } else {
                            // Add empty entry if we have only one visit
                            const appointmentDate = window.APPOINTMENT_CONFIG?.appointmentDate || new Date().toISOString().split('T')[0];
                            addMacularThicknessVisitEntry(appointmentDate);
                        }
                    } else {
                        // No macular thickness data found, add empty entries
                        const appointmentDate = window.APPOINTMENT_CONFIG?.appointmentDate || new Date().toISOString().split('T')[0];
                        addMacularThicknessVisitEntry(appointmentDate);
                        addMacularThicknessVisitEntry();
                    }
                } else {
                    // No appointments found, add empty entries
                    const appointmentDate = window.APPOINTMENT_CONFIG?.appointmentDate || new Date().toISOString().split('T')[0];
                    addMacularThicknessVisitEntry(appointmentDate);
                    addMacularThicknessVisitEntry();
                }
            })
            .catch(error => {
                console.error('Error loading Macular Thickness data from appointments:', error);
                container.innerHTML = '';
                // Add empty entries on error
                const appointmentDate = window.APPOINTMENT_CONFIG?.appointmentDate || new Date().toISOString().split('T')[0];
                addMacularThicknessVisitEntry(appointmentDate);
                addMacularThicknessVisitEntry();
            });
        }

        // Cataract Surgery Readiness Score Popover
        let cataractSurgeryReadinessPopover = null;

        function createCataractSurgeryReadinessPopover(patientId = null, appointmentId = null) {
            // Remove existing popover if any
            if (cataractSurgeryReadinessPopover) {
                cataractSurgeryReadinessPopover.remove();
            }

            // Get patientId and appointmentId from APPOINTMENT_CONFIG if not provided
            if (!patientId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                patientId = window.APPOINTMENT_CONFIG.patientId;
            }
            if (!appointmentId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentId) {
                appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
            }

            const popover = document.createElement('div');
            popover.className = 'cataract-surgery-readiness-popover';
            popover.id = 'cataractSurgeryReadinessPopover';
            
            popover.innerHTML = `
                <div class="cataract-surgery-readiness-popover-content">
                    <div class="cataract-surgery-readiness-popover-header">
                        <h5>Cataract Surgery Readiness Score</h5>
                        <button type="button" class="cataract-surgery-readiness-close-btn" id="cataractSurgeryReadinessCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="cataract-surgery-readiness-popover-body">
                        <form id="cataractSurgeryReadinessForm" class="cataract-surgery-readiness-form">
                            <div class="cataract-surgery-readiness-form-group">
                                <label for="cataractReadinessBcvaOd">BCVA OD (Snellen or LogMAR)</label>
                                <input type="text" id="cataractReadinessBcvaOd" name="bcva_od" placeholder="e.g., 6/12 or 0.3">
                                <small class="form-text">Best corrected visual acuity - Right eye</small>
                            </div>
                            <div class="cataract-surgery-readiness-form-group">
                                <label for="cataractReadinessBcvaOs">BCVA OS (Snellen or LogMAR)</label>
                                <input type="text" id="cataractReadinessBcvaOs" name="bcva_os" placeholder="e.g., 6/12 or 0.3">
                                <small class="form-text">Best corrected visual acuity - Left eye</small>
                            </div>
                            <div class="cataract-surgery-readiness-form-group">
                                <label for="cataractReadinessVisualComplaints">Visual Complaints Score (0-10) *</label>
                                <input type="number" id="cataractReadinessVisualComplaints" name="visual_complaints_score" min="0" max="10" step="1" required>
                                <small class="form-text">Patient-reported difficulty with daily activities (0 = no complaints, 10 = severe difficulty)</small>
                            </div>
                            <div class="cataract-surgery-readiness-form-group">
                                <label for="cataractReadinessLensOpacity">Lens Opacity Grade *</label>
                                <select id="cataractReadinessLensOpacity" name="lens_opacity_grade" required>
                                    <option value="">Select grade...</option>
                                    <option value="Grade 1 (Mild)">Grade 1 (Mild)</option>
                                    <option value="Grade 2 (Moderate)">Grade 2 (Moderate)</option>
                                    <option value="Grade 3 (Advanced)">Grade 3 (Advanced)</option>
                                    <option value="Grade 4 (Severe)">Grade 4 (Severe)</option>
                                </select>
                                <small class="form-text">LOCS III or simplified grading</small>
                            </div>
                            <div class="cataract-surgery-readiness-form-group">
                                <label>Complications</label>
                                <div class="cataract-surgery-readiness-complications">
                                    <label class="cataract-surgery-readiness-checkbox-label">
                                        <input type="checkbox" name="complications[]" value="phacomorphic">
                                        <span>Phacomorphic risk</span>
                                    </label>
                                    <label class="cataract-surgery-readiness-checkbox-label">
                                        <input type="checkbox" name="complications[]" value="psc">
                                        <span>PSC affecting vision</span>
                                    </label>
                                    <label class="cataract-surgery-readiness-checkbox-label">
                                        <input type="checkbox" name="complications[]" value="other">
                                        <span>Other complications</span>
                                    </label>
                                </div>
                            </div>
                            <div class="cataract-surgery-readiness-form-actions">
                                <button type="button" class="btn btn-primary" id="cataractReadinessCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate Readiness
                                </button>
                                <button type="button" class="btn btn-secondary" id="cataractReadinessResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="cataractReadinessResults" class="cataract-surgery-readiness-results" style="display: none;">
                            <div class="cataract-surgery-readiness-results-card">
                                <div class="cataract-surgery-readiness-result-item">
                                    <label>Readiness Classification</label>
                                    <span id="cataractReadinessClassificationDisplay" class="cataract-surgery-readiness-badge">-</span>
                                </div>
                                <div class="cataract-surgery-readiness-result-item">
                                    <label>Total Score</label>
                                    <span id="cataractReadinessTotalScoreDisplay">-</span>
                                </div>
                                <div class="cataract-surgery-readiness-result-item">
                                    <label>Recommendation</label>
                                    <span id="cataractReadinessRecommendationDisplay">-</span>
                                </div>
                            </div>
                            <div class="cataract-surgery-readiness-scoring">
                                <h6>Score Breakdown</h6>
                                <div class="cataract-surgery-readiness-scoring-grid">
                                    <div class="cataract-surgery-readiness-scoring-item">
                                        <label>BCVA Score</label>
                                        <span id="cataractReadinessBcvaScore">-</span>
                                    </div>
                                    <div class="cataract-surgery-readiness-scoring-item">
                                        <label>Visual Complaints</label>
                                        <span id="cataractReadinessVisualComplaintsScore">-</span>
                                    </div>
                                    <div class="cataract-surgery-readiness-scoring-item">
                                        <label>Lens Opacity</label>
                                        <span id="cataractReadinessLensOpacityScore">-</span>
                                    </div>
                                    <div class="cataract-surgery-readiness-scoring-item">
                                        <label>Complications</label>
                                        <span id="cataractReadinessComplicationsScore">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="cataract-surgery-readiness-clinical-summary">
                                <h6>Clinical Summary</h6>
                                <p id="cataractReadinessClinicalSummary">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            cataractSurgeryReadinessPopover = popover;

            // Position popover
            positionCataractSurgeryReadinessPopover();

            // Initialize events
            initCataractSurgeryReadinessEvents(patientId, appointmentId);

            // Load BCVA data if patientId provided
            if (patientId) {
                loadCataractReadinessData(patientId, appointmentId);
            }
        }

        function positionCataractSurgeryReadinessPopover() {
            if (!cataractSurgeryReadinessPopover) return;
            const cataractBtn = document.getElementById('noticeBarCataractBtn');
            if (!cataractBtn) {
                cataractSurgeryReadinessPopover.style.top = '50%';
                cataractSurgeryReadinessPopover.style.left = '50%';
                cataractSurgeryReadinessPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            const btnRect = cataractBtn.getBoundingClientRect();
            cataractSurgeryReadinessPopover.style.top = (btnRect.bottom + 10) + 'px';
            cataractSurgeryReadinessPopover.style.left = '50%';
            cataractSurgeryReadinessPopover.style.transform = 'translateX(-50%)';
        }

        function closeCataractSurgeryReadinessPopover() {
            if (cataractSurgeryReadinessPopover) {
                cataractSurgeryReadinessPopover.remove();
                cataractSurgeryReadinessPopover = null;
            }
            const backdrop = document.querySelector('.cataract-surgery-readiness-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initCataractSurgeryReadinessEvents(patientId, appointmentId) {
            // Close button
            document.getElementById('cataractSurgeryReadinessCloseBtn')?.addEventListener('click', closeCataractSurgeryReadinessPopover);

            // Calculate button
            document.getElementById('cataractReadinessCalculateBtn')?.addEventListener('click', () => {
                handleCataractSurgeryReadinessCalculation(patientId, appointmentId);
            });

            // Reset button
            document.getElementById('cataractReadinessResetBtn')?.addEventListener('click', () => {
                document.getElementById('cataractSurgeryReadinessForm')?.reset();
                document.getElementById('cataractReadinessResults').style.display = 'none';
            });

            // Backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'cataract-surgery-readiness-popover-backdrop';
            backdrop.addEventListener('click', closeCataractSurgeryReadinessPopover);
            document.body.appendChild(backdrop);

            // Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && cataractSurgeryReadinessPopover) {
                    closeCataractSurgeryReadinessPopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (cataractSurgeryReadinessPopover) {
                    positionCataractSurgeryReadinessPopover();
                }
            });
        }

        function handleCataractSurgeryReadinessCalculation(patientId, appointmentId) {
            const form = document.getElementById('cataractSurgeryReadinessForm');
            if (!form) return;

            const bcvaOd = document.getElementById('cataractReadinessBcvaOd').value.trim();
            const bcvaOs = document.getElementById('cataractReadinessBcvaOs').value.trim();
            const visualComplaintsScore = parseInt(document.getElementById('cataractReadinessVisualComplaints').value);
            const lensOpacityGrade = document.getElementById('cataractReadinessLensOpacity').value;

            if (!visualComplaintsScore || !lensOpacityGrade) {
                alert('Please fill in all required fields');
                return;
            }

            // Collect complications
            const complications = [];
            form.querySelectorAll('input[name="complications[]"]:checked').forEach(checkbox => {
                complications.push(checkbox.value);
            });

            const data = {
                bcva_od: bcvaOd || null,
                bcva_os: bcvaOs || null,
                visual_complaints_score: visualComplaintsScore,
                lens_opacity_grade: lensOpacityGrade,
                complications: complications,
                patient_id: patientId,
                appointment_id: appointmentId
            };

            // Show loading
            const calculateBtn = document.getElementById('cataractReadinessCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';

            fetch('/api/cataract-surgery/readiness', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(async response => {
                const responseText = await response.text();
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Invalid JSON response:', responseText.substring(0, 500));
                    throw new Error('Server returned invalid response. Status: ' + response.status);
                }
                if (!response.ok) {
                    throw new Error(result.error || result.message || 'Server error (Status: ' + response.status + ')');
                }
                return result;
            })
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderCataractSurgeryReadinessResults(result);
                } else {
                    const errorMsg = result.error || (result.errors && Array.isArray(result.errors) ? result.errors.join(', ') : 'Unknown error');
                    alert('Calculation failed: ' + errorMsg);
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                console.error('Cataract Surgery Readiness Error:', error);
                alert('Error calculating readiness: ' + (error.message || 'Please try again.'));
            });
        }

        function renderCataractSurgeryReadinessResults(result) {
            const classificationBadge = document.getElementById('cataractReadinessClassificationDisplay');
            classificationBadge.textContent = result.readiness_classification;
            classificationBadge.className = 'cataract-surgery-readiness-badge cataract-surgery-readiness-' + result.readiness_classification.toLowerCase().replace(/\s+/g, '-');
            
            document.getElementById('cataractReadinessTotalScoreDisplay').textContent = result.total_score;
            document.getElementById('cataractReadinessRecommendationDisplay').textContent = result.recommendation;
            
            document.getElementById('cataractReadinessBcvaScore').textContent = result.bcva_score;
            document.getElementById('cataractReadinessVisualComplaintsScore').textContent = result.visual_complaints_score_points;
            document.getElementById('cataractReadinessLensOpacityScore').textContent = result.lens_opacity_score;
            document.getElementById('cataractReadinessComplicationsScore').textContent = result.complications_score;

            document.getElementById('cataractReadinessClinicalSummary').textContent = result.clinical_summary;

            document.getElementById('cataractReadinessResults').style.display = 'block';
            document.getElementById('cataractReadinessResults').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function loadCataractReadinessData(patientId, appointmentId) {
            if (!patientId) return;

            // Fetch appointment history to get BCVA
            const url = `/api/patients/${patientId}/appointments/history${appointmentId ? '?exclude=' + appointmentId : ''}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.appointments && data.appointments.length > 0) {
                    // Get latest appointment BCVA
                    const latestAppointment = data.appointments[0];
                    if (latestAppointment.consultation_notes && Array.isArray(latestAppointment.consultation_notes)) {
                        latestAppointment.consultation_notes.forEach(note => {
                            if (note.visual_acuity_right && !document.getElementById('cataractReadinessBcvaOd').value) {
                                document.getElementById('cataractReadinessBcvaOd').value = note.visual_acuity_right.trim();
                            }
                            if (note.visual_acuity_left && !document.getElementById('cataractReadinessBcvaOs').value) {
                                document.getElementById('cataractReadinessBcvaOs').value = note.visual_acuity_left.trim();
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error loading cataract readiness data:', error);
            });
        }

        // Post-Operative Outcome Analyzer Popover
        let postOperativeOutcomePopover = null;

        function createPostOperativeOutcomePopover(patientId = null, appointmentId = null) {
            // Remove existing popover if any
            if (postOperativeOutcomePopover) {
                postOperativeOutcomePopover.remove();
            }

            // Get patientId and appointmentId from APPOINTMENT_CONFIG if not provided
            if (!patientId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                patientId = window.APPOINTMENT_CONFIG.patientId;
            }
            if (!appointmentId && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentId) {
                appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
            }

            // Get appointment date from APPOINTMENT_CONFIG if available
            let appointmentDate = null;
            if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentDate && window.APPOINTMENT_CONFIG.appointmentDate !== 'null') {
                appointmentDate = window.APPOINTMENT_CONFIG.appointmentDate;
            } else {
                const today = new Date();
                appointmentDate = today.toISOString().split('T')[0];
            }

            const popover = document.createElement('div');
            popover.className = 'post-operative-outcome-popover';
            popover.id = 'postOperativeOutcomePopover';
            
            popover.innerHTML = `
                <div class="post-operative-outcome-popover-content">
                    <div class="post-operative-outcome-popover-header">
                        <h5>Post-Operative Outcome Analyzer</h5>
                        <button type="button" class="post-operative-outcome-close-btn" id="postOperativeOutcomeCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="post-operative-outcome-popover-body">
                        <form id="postOperativeOutcomeForm" class="post-operative-outcome-form">
                            <div class="post-operative-outcome-form-group">
                                <label for="postOpEye">Eye *</label>
                                <select id="postOpEye" name="eye" required>
                                    <option value="">Select...</option>
                                    <option value="OD">OD (Right)</option>
                                    <option value="OS">OS (Left)</option>
                                </select>
                            </div>
                            <div class="post-operative-outcome-form-group">
                                <label for="postOpSurgeryDate">Surgery Date *</label>
                                <input type="date" id="postOpSurgeryDate" name="surgery_date" value="${appointmentDate || ''}" required>
                            </div>
                            <div class="post-operative-outcome-section">
                                <h6>Pre-Operative Data</h6>
                                <div class="post-operative-outcome-form-group">
                                    <label for="postOpPreopBcva">Pre-op BCVA (Snellen or LogMAR)</label>
                                    <input type="text" id="postOpPreopBcva" name="preop_bcva" placeholder="e.g., 6/60 or 1.0">
                                </div>
                                <div class="post-operative-outcome-form-row">
                                    <div class="post-operative-outcome-form-group">
                                        <label for="postOpPreopTargetSphere">Target Sphere (D)</label>
                                        <input type="number" id="postOpPreopTargetSphere" name="preop_target_sphere" step="0.01" min="-20" max="20">
                                    </div>
                                    <div class="post-operative-outcome-form-group">
                                        <label for="postOpPreopTargetCylinder">Target Cylinder (D)</label>
                                        <input type="number" id="postOpPreopTargetCylinder" name="preop_target_cylinder" step="0.01" min="-10" max="10">
                                    </div>
                                </div>
                            </div>
                            <div class="post-operative-outcome-section">
                                <h6>Post-Operative Data</h6>
                                <div class="post-operative-outcome-form-group">
                                    <label for="postOpPostopBcva">Post-op BCVA (Snellen or LogMAR) *</label>
                                    <input type="text" id="postOpPostopBcva" name="postop_bcva" placeholder="e.g., 6/6 or 0.0" required>
                                </div>
                                <div class="post-operative-outcome-form-row">
                                    <div class="post-operative-outcome-form-group">
                                        <label for="postOpPostopSphere">Post-op Sphere (D) *</label>
                                        <input type="number" id="postOpPostopSphere" name="postop_sphere" step="0.01" min="-20" max="20" required>
                                    </div>
                                    <div class="post-operative-outcome-form-group">
                                        <label for="postOpPostopCylinder">Post-op Cylinder (D) *</label>
                                        <input type="number" id="postOpPostopCylinder" name="postop_cylinder" step="0.01" min="-10" max="10" required>
                                    </div>
                                </div>
                            </div>
                            <div class="post-operative-outcome-form-actions">
                                <button type="button" class="btn btn-primary" id="postOpCalculateBtn">
                                    <i class="bi bi-calculator"></i> Analyze Outcome
                                </button>
                                <button type="button" class="btn btn-secondary" id="postOpResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="postOpResults" class="post-operative-outcome-results" style="display: none;">
                            <div class="post-operative-outcome-results-card">
                                <div class="post-operative-outcome-result-item">
                                    <label>Refractive Outcome</label>
                                    <span id="postOpRefractiveOutcomeDisplay" class="post-operative-outcome-badge">-</span>
                                </div>
                                <div class="post-operative-outcome-result-item">
                                    <label>Visual Outcome</label>
                                    <span id="postOpVisualOutcomeDisplay" class="post-operative-outcome-badge">-</span>
                                </div>
                                <div class="post-operative-outcome-result-item">
                                    <label>Refractive Error (Sphere)</label>
                                    <span id="postOpRefractiveErrorSphereDisplay">-</span>
                                </div>
                                <div class="post-operative-outcome-result-item">
                                    <label>Refractive Error (Cylinder)</label>
                                    <span id="postOpRefractiveErrorCylinderDisplay">-</span>
                                </div>
                                <div id="postOpVisualImprovementDisplay" class="post-operative-outcome-result-item" style="display: none;">
                                    <label>Visual Improvement</label>
                                    <span id="postOpVisualImprovementValue">-</span>
                                </div>
                            </div>
                            <div class="post-operative-outcome-summary">
                                <h6>Outcome Summary</h6>
                                <p id="postOpOutcomeSummary">-</p>
                            </div>
                            <div class="post-operative-outcome-surgical-summary">
                                <h6>Surgical Summary</h6>
                                <p id="postOpSurgicalSummary">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            postOperativeOutcomePopover = popover;

            // Position popover
            positionPostOperativeOutcomePopover();

            // Initialize events
            initPostOperativeOutcomeEvents(patientId, appointmentId);

            // Load data if patientId provided
            if (patientId) {
                loadPostOperativeOutcomeData(patientId, appointmentId);
            }
        }

        function positionPostOperativeOutcomePopover() {
            if (!postOperativeOutcomePopover) return;
            const cataractBtn = document.getElementById('noticeBarCataractBtn');
            if (!cataractBtn) {
                postOperativeOutcomePopover.style.top = '50%';
                postOperativeOutcomePopover.style.left = '50%';
                postOperativeOutcomePopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            const btnRect = cataractBtn.getBoundingClientRect();
            postOperativeOutcomePopover.style.top = (btnRect.bottom + 10) + 'px';
            postOperativeOutcomePopover.style.left = '50%';
            postOperativeOutcomePopover.style.transform = 'translateX(-50%)';
        }

        function closePostOperativeOutcomePopover() {
            if (postOperativeOutcomePopover) {
                postOperativeOutcomePopover.remove();
                postOperativeOutcomePopover = null;
            }
            const backdrop = document.querySelector('.post-operative-outcome-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initPostOperativeOutcomeEvents(patientId, appointmentId) {
            // Close button
            document.getElementById('postOperativeOutcomeCloseBtn')?.addEventListener('click', closePostOperativeOutcomePopover);

            // Calculate button
            document.getElementById('postOpCalculateBtn')?.addEventListener('click', () => {
                handlePostOperativeOutcomeCalculation(patientId, appointmentId);
            });

            // Reset button
            document.getElementById('postOpResetBtn')?.addEventListener('click', () => {
                document.getElementById('postOperativeOutcomeForm')?.reset();
                document.getElementById('postOpResults').style.display = 'none';
            });

            // Backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'post-operative-outcome-popover-backdrop';
            backdrop.addEventListener('click', closePostOperativeOutcomePopover);
            document.body.appendChild(backdrop);

            // Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape' && postOperativeOutcomePopover) {
                    closePostOperativeOutcomePopover();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (postOperativeOutcomePopover) {
                    positionPostOperativeOutcomePopover();
                }
            });
        }

        function handlePostOperativeOutcomeCalculation(patientId, appointmentId) {
            const form = document.getElementById('postOperativeOutcomeForm');
            if (!form) return;

            const eye = document.getElementById('postOpEye').value;
            const surgeryDate = document.getElementById('postOpSurgeryDate').value;
            const preopBcva = document.getElementById('postOpPreopBcva').value.trim();
            const preopTargetSphere = document.getElementById('postOpPreopTargetSphere').value ? parseFloat(document.getElementById('postOpPreopTargetSphere').value) : null;
            const preopTargetCylinder = document.getElementById('postOpPreopTargetCylinder').value ? parseFloat(document.getElementById('postOpPreopTargetCylinder').value) : null;
            const postopBcva = document.getElementById('postOpPostopBcva').value.trim();
            const postopSphere = parseFloat(document.getElementById('postOpPostopSphere').value);
            const postopCylinder = parseFloat(document.getElementById('postOpPostopCylinder').value);

            if (!eye || !surgeryDate || !postopBcva || isNaN(postopSphere) || isNaN(postopCylinder)) {
                alert('Please fill in all required fields');
                return;
            }

            const data = {
                eye: eye,
                surgery_date: surgeryDate,
                preop_bcva: preopBcva || null,
                preop_target_sphere: preopTargetSphere,
                preop_target_cylinder: preopTargetCylinder,
                postop_bcva: postopBcva,
                postop_sphere: postopSphere,
                postop_cylinder: postopCylinder,
                patient_id: patientId,
                appointment_id: appointmentId
            };

            // Show loading
            const calculateBtn = document.getElementById('postOpCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';

            fetch('/api/cataract-surgery/postop-outcome', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(async response => {
                const responseText = await response.text();
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Invalid JSON response:', responseText.substring(0, 500));
                    throw new Error('Server returned invalid response. Status: ' + response.status);
                }
                if (!response.ok) {
                    throw new Error(result.error || result.message || 'Server error (Status: ' + response.status + ')');
                }
                return result;
            })
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderPostOperativeOutcomeResults(result);
                } else {
                    const errorMsg = result.error || (result.errors && Array.isArray(result.errors) ? result.errors.join(', ') : 'Unknown error');
                    alert('Analysis failed: ' + errorMsg);
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                console.error('Post-Operative Outcome Analysis Error:', error);
                alert('Error analyzing outcome: ' + (error.message || 'Please try again.'));
            });
        }

        function renderPostOperativeOutcomeResults(result) {
            const refractiveBadge = document.getElementById('postOpRefractiveOutcomeDisplay');
            refractiveBadge.textContent = result.refractive_outcome;
            refractiveBadge.className = 'post-operative-outcome-badge post-operative-outcome-' + result.refractive_outcome.toLowerCase().replace(/\s+/g, '-');
            
            if (result.visual_outcome && result.visual_outcome !== 'Not assessed') {
                const visualBadge = document.getElementById('postOpVisualOutcomeDisplay');
                visualBadge.textContent = result.visual_outcome;
                visualBadge.className = 'post-operative-outcome-badge post-operative-outcome-' + result.visual_outcome.toLowerCase();
                visualBadge.parentElement.style.display = 'flex';
            } else {
                document.getElementById('postOpVisualOutcomeDisplay').parentElement.style.display = 'none';
            }

            if (result.refractive_error_sphere !== null) {
                const sphereError = result.refractive_error_sphere > 0 ? '+' + result.refractive_error_sphere.toFixed(2) : result.refractive_error_sphere.toFixed(2);
                document.getElementById('postOpRefractiveErrorSphereDisplay').textContent = sphereError + ' D';
            } else {
                document.getElementById('postOpRefractiveErrorSphereDisplay').textContent = 'N/A';
            }

            if (result.refractive_error_cylinder !== null) {
                const cylinderError = result.refractive_error_cylinder > 0 ? '+' + result.refractive_error_cylinder.toFixed(2) : result.refractive_error_cylinder.toFixed(2);
                document.getElementById('postOpRefractiveErrorCylinderDisplay').textContent = cylinderError + ' D';
            } else {
                document.getElementById('postOpRefractiveErrorCylinderDisplay').textContent = 'N/A';
            }

            if (result.visual_improvement !== null) {
                const improvement = result.visual_improvement > 0 ? '+' + result.visual_improvement.toFixed(1) : result.visual_improvement.toFixed(1);
                document.getElementById('postOpVisualImprovementValue').textContent = improvement + ' lines';
                document.getElementById('postOpVisualImprovementDisplay').style.display = 'flex';
            } else {
                document.getElementById('postOpVisualImprovementDisplay').style.display = 'none';
            }

            document.getElementById('postOpOutcomeSummary').textContent = result.outcome_summary;
            document.getElementById('postOpSurgicalSummary').textContent = result.surgical_summary;

            document.getElementById('postOpResults').style.display = 'block';
            document.getElementById('postOpResults').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function loadPostOperativeOutcomeData(patientId, appointmentId) {
            if (!patientId) return;

            // Fetch appointment history to get BCVA and refraction
            const url = `/api/patients/${patientId}/appointments/history${appointmentId ? '?exclude=' + appointmentId : ''}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.appointments && data.appointments.length > 0) {
                    // Get latest appointment data
                    const latestAppointment = data.appointments[0];
                    if (latestAppointment.consultation_notes && Array.isArray(latestAppointment.consultation_notes)) {
                        latestAppointment.consultation_notes.forEach(note => {
                            // Load BCVA
                            if (note.visual_acuity_right && !document.getElementById('postOpPreopBcva').value) {
                                document.getElementById('postOpPreopBcva').value = note.visual_acuity_right.trim();
                            }
                            // Load refraction
                            if (note.refraction_right && !document.getElementById('postOpPreopTargetSphere').value) {
                                // Try to parse refraction (format: e.g., "-2.00 / -0.50 x 180")
                                const refractionMatch = note.refraction_right.match(/(-?\d+\.?\d*)\s*\/\s*(-?\d+\.?\d*)\s*x\s*(\d+)/i);
                                if (refractionMatch) {
                                    document.getElementById('postOpPreopTargetSphere').value = refractionMatch[1];
                                    document.getElementById('postOpPreopTargetCylinder').value = refractionMatch[2];
                                }
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error loading post-operative outcome data:', error);
            });
        }

        // Corneal Astigmatism Calculator Popover
        let cornealAstigmatismPopover = null;

        function createCornealAstigmatismPopover() {
            // Remove existing popover if any
            if (cornealAstigmatismPopover) {
                cornealAstigmatismPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'corneal-astigmatism-popover';
            popover.id = 'cornealAstigmatismPopover';
            
            popover.innerHTML = `
                <div class="corneal-astigmatism-popover-content">
                    <div class="corneal-astigmatism-popover-header">
                        <h5>Corneal Astigmatism Calculator</h5>
                        <button type="button" class="corneal-astigmatism-close-btn" id="cornealAstigmatismCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="corneal-astigmatism-popover-body">
                        <form id="cornealAstigmatismForm" class="corneal-astigmatism-form">
                            <div class="astigmatism-form-grid">
                                <div class="astigmatism-form-group">
                                    <label for="astigmatismK1">K1 (diopters) *</label>
                                    <input type="number" id="astigmatismK1" name="k1" step="0.01" min="30" max="60" required>
                                    <small class="form-text">Range: 30-60 D</small>
                                </div>
                                <div class="astigmatism-form-group">
                                    <label for="astigmatismK1Axis">K1 Axis (degrees) *</label>
                                    <input type="number" id="astigmatismK1Axis" name="k1_axis" step="0.1" min="0" max="180" required>
                                    <small class="form-text">Range: 0-180°</small>
                                </div>
                                <div class="astigmatism-form-group">
                                    <label for="astigmatismK2">K2 (diopters) *</label>
                                    <input type="number" id="astigmatismK2" name="k2" step="0.01" min="30" max="60" required>
                                    <small class="form-text">Range: 30-60 D (must be ≤ K1)</small>
                                </div>
                                <div class="astigmatism-form-group">
                                    <label for="astigmatismK2Axis">K2 Axis (degrees) *</label>
                                    <input type="number" id="astigmatismK2Axis" name="k2_axis" step="0.1" min="0" max="180" required>
                                    <small class="form-text">Range: 0-180°</small>
                                </div>
                                <div class="astigmatism-form-group">
                                    <label for="astigmatismSIA">SIA (diopters)</label>
                                    <input type="number" id="astigmatismSIA" name="sia" step="0.01" min="0" max="5" value="0">
                                    <small class="form-text">Surgically Induced Astigmatism (optional)</small>
                                </div>
                                <div class="astigmatism-form-group">
                                    <label for="astigmatismSIAAxis">SIA Axis (degrees)</label>
                                    <input type="number" id="astigmatismSIAAxis" name="sia_axis" step="0.1" min="0" max="180" value="0">
                                    <small class="form-text">Range: 0-180° (required if SIA > 0)</small>
                                </div>
                            </div>
                            <div class="astigmatism-form-actions">
                                <button type="button" class="btn btn-primary" id="astigmatismCalculateBtn">
                                    <i class="bi bi-calculator"></i> Calculate
                                </button>
                                <button type="button" class="btn btn-secondary" id="astigmatismResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                        <div id="astigmatismResults" class="astigmatism-results" style="display: none;">
                            <div class="astigmatism-results-grid">
                                <div class="astigmatism-result-card">
                                    <h6>Corneal Astigmatism</h6>
                                    <div class="astigmatism-result-item">
                                        <label>Magnitude</label>
                                        <span id="astigmatismCornealMagnitude">-</span>
                                    </div>
                                    <div class="astigmatism-result-item">
                                        <label>Axis</label>
                                        <span id="astigmatismCornealAxis">-</span>
                                    </div>
                                </div>
                                <div class="astigmatism-result-card">
                                    <h6>Net Astigmatism</h6>
                                    <div class="astigmatism-result-item">
                                        <label>Magnitude</label>
                                        <span id="astigmatismNetMagnitude">-</span>
                                    </div>
                                    <div class="astigmatism-result-item">
                                        <label>Axis</label>
                                        <span id="astigmatismNetAxis">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="astigmatism-recommendation">
                                <h6>Surgical Recommendation</h6>
                                <div id="astigmatismRecommendationBadge" class="astigmatism-badge">-</div>
                                <p id="astigmatismRecommendationMessage">-</p>
                            </div>
                            <div class="astigmatism-clinical-note">
                                <h6>Clinical Notes</h6>
                                <p id="astigmatismClinicalNotes">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            cornealAstigmatismPopover = popover;
            
            // Position popover
            positionCornealAstigmatismPopover();
            
            // Initialize event listeners
            initCornealAstigmatismEvents();
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'corneal-astigmatism-popover-backdrop';
            backdrop.addEventListener('click', closeCornealAstigmatismPopover);
            document.body.appendChild(backdrop);
        }

        function positionCornealAstigmatismPopover() {
            if (!cornealAstigmatismPopover) return;
            
            const iolBtn = document.getElementById('noticeBarIOLBtn');
            if (!iolBtn) {
                cornealAstigmatismPopover.style.top = '50%';
                cornealAstigmatismPopover.style.left = '50%';
                cornealAstigmatismPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            
            const btnRect = iolBtn.getBoundingClientRect();
            cornealAstigmatismPopover.style.top = (btnRect.bottom + 10) + 'px';
            cornealAstigmatismPopover.style.left = '50%';
            cornealAstigmatismPopover.style.transform = 'translateX(-50%)';
        }

        function closeCornealAstigmatismPopover() {
            if (cornealAstigmatismPopover) {
                cornealAstigmatismPopover.remove();
                cornealAstigmatismPopover = null;
            }
            const backdrop = document.querySelector('.corneal-astigmatism-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initCornealAstigmatismEvents() {
            // Close button
            const closeBtn = document.getElementById('cornealAstigmatismCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeCornealAstigmatismPopover);
            }

            // Calculate button
            const calculateBtn = document.getElementById('astigmatismCalculateBtn');
            if (calculateBtn) {
                calculateBtn.addEventListener('click', handleAstigmatismCalculation);
            }

            // Reset button
            const resetBtn = document.getElementById('astigmatismResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    document.getElementById('astigmatismResults').style.display = 'none';
                    document.getElementById('cornealAstigmatismForm').reset();
                });
            }

            // Close on escape key
            const escapeHandlerAstigmatism = (e) => {
                if (e.key === 'Escape' && cornealAstigmatismPopover) {
                    closeCornealAstigmatismPopover();
                    document.removeEventListener('keydown', escapeHandlerAstigmatism);
                }
            };
            document.addEventListener('keydown', escapeHandlerAstigmatism);

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (cornealAstigmatismPopover) {
                    positionCornealAstigmatismPopover();
                }
            });
        }

        function handleAstigmatismCalculation() {
            const form = document.getElementById('cornealAstigmatismForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate inputs
            if (!data.k1 || !data.k1_axis || !data.k2 || !data.k2_axis) {
                alert('Please fill in all required fields');
                return;
            }

            // Validate K2 <= K1
            if (parseFloat(data.k2) > parseFloat(data.k1)) {
                alert('K2 (flatter meridian) must be less than or equal to K1 (steeper meridian)');
                return;
            }

            // Show loading state
            const calculateBtn = document.getElementById('astigmatismCalculateBtn');
            const originalText = calculateBtn.innerHTML;
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';

            // Submit via AJAX
            // Ensure numeric values are properly formatted
            const params = new URLSearchParams();
            params.append('k1', data.k1);
            params.append('k1_axis', data.k1_axis);
            params.append('k2', data.k2);
            params.append('k2_axis', data.k2_axis);
            if (data.sia && data.sia !== '0') {
                params.append('sia', data.sia);
            }
            if (data.sia_axis && data.sia_axis !== '0') {
                params.append('sia_axis', data.sia_axis);
            }
            
            fetch('/api/astigmatism/calculate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            })
            .then(response => response.json())
            .then(result => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;

                if (result.success) {
                    renderAstigmatismResults(result);
                } else {
                    alert('Calculation failed: ' + (result.error || result.errors?.join(', ') || 'Unknown error'));
                }
            })
            .catch(error => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalText;
                alert('Error: ' + error.message);
            });
        }

        function renderAstigmatismResults(result) {
            const resultsContainer = document.getElementById('astigmatismResults');
            if (!resultsContainer) return;

            // Show results
            resultsContainer.style.display = 'block';

            // Update result fields
            document.getElementById('astigmatismCornealMagnitude').textContent = `${result.corneal_astigmatism.magnitude} D`;
            document.getElementById('astigmatismCornealAxis').textContent = `${result.corneal_astigmatism.axis}°`;
            document.getElementById('astigmatismNetMagnitude').textContent = `${result.net_astigmatism.magnitude} D`;
            document.getElementById('astigmatismNetAxis').textContent = `${result.net_astigmatism.axis}°`;

            // Update recommendation badge
            const badge = document.getElementById('astigmatismRecommendationBadge');
            badge.className = 'astigmatism-badge';
            
            if (result.surgical_recommendation === 'standard_iol') {
                badge.classList.add('astigmatism-badge-success');
                badge.textContent = 'Standard IOL';
            } else if (result.surgical_recommendation === 'lri') {
                badge.classList.add('astigmatism-badge-warning');
                badge.textContent = 'LRI (Limbal Relaxing Incisions)';
            } else if (result.surgical_recommendation === 'toric_iol') {
                badge.classList.add('astigmatism-badge-danger');
                badge.textContent = 'Toric IOL';
            }

            document.getElementById('astigmatismRecommendationMessage').textContent = result.recommendation_message || '-';
            document.getElementById('astigmatismClinicalNotes').textContent = result.clinical_notes || '-';

            // Scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // IOP Trend Analyzer Popover
        let iopTrendAnalyzerPopover = null;

        // IOP Trend Analyzer - Patient search variables
        let iopAutocompleteItems = [];
        let iopSelectedAutocompleteIndex = -1;
        let iopSearchTimeout = null;
        let iopSelectedPatientId = null;
        let iopSelectedPatientName = null;

        function createIOPTrendAnalyzerPopover(patientId = null) {
            // Check for patient ID from page context
            if (!patientId && window.currentPatientId) {
                patientId = window.currentPatientId;
            }

            // Remove existing popover if any
            if (iopTrendAnalyzerPopover) {
                iopTrendAnalyzerPopover.remove();
            }

            const popover = document.createElement('div');
            popover.className = 'iop-trend-analyzer-popover';
            popover.id = 'iopTrendAnalyzerPopover';
            
            popover.innerHTML = `
                <div class="iop-trend-analyzer-popover-content">
                    <div class="iop-trend-analyzer-popover-header">
                        <h5>IOP Trend Analyzer</h5>
                        <button type="button" class="iop-trend-analyzer-close-btn" id="iopTrendAnalyzerCloseBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="iop-trend-analyzer-popover-body">
                        <div id="iopTrendAnalyzerForm" class="iop-trend-analyzer-form">
                            <div class="iop-form-group">
                                <label for="iopPatientSearch">Patient *</label>
                                <div class="iop-patient-search-wrapper">
                                    <input type="text" 
                                           id="iopPatientSearch" 
                                           class="form-control" 
                                           placeholder="Search patient by name, phone, or ID..."
                                           autocomplete="off"
                                           ${patientId ? 'style="display:none;"' : ''}
                                           value="${iopSelectedPatientName || ''}">
                                    ${patientId ? `<input type="hidden" id="iopPatientId" value="${patientId}"><div id="iopSelectedPatientName" class="iop-selected-patient">Patient ID: ${patientId}</div>` : ''}
                                    <div id="iopAutocompleteResults" class="iop-autocomplete-results" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="iop-form-actions">
                                <button type="button" class="btn btn-primary" id="iopAnalyzeBtn">
                                    <i class="bi bi-graph-up"></i> Analyze
                                </button>
                                <button type="button" class="btn btn-secondary" id="iopResetBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div id="iopTrendAnalyzerResults" class="iop-trend-analyzer-results" style="display: none;">
                            <div id="iopAlertsSection" class="iop-alerts-section"></div>
                            <div class="iop-summary-cards">
                                <div class="iop-summary-card" id="iopODCard">
                                    <div class="iop-card-header">
                                        <h6>OD (Right Eye)</h6>
                                    </div>
                                    <div class="iop-card-body">
                                        <div class="iop-stat">
                                            <label>Mean IOP</label>
                                            <span id="iopODMean">-</span>
                                        </div>
                                        <div class="iop-stat">
                                            <label>Peak IOP</label>
                                            <span id="iopODPeak">-</span>
                                        </div>
                                        <div class="iop-stat">
                                            <label>Rate of Change</label>
                                            <span id="iopODRate">-</span>
                                        </div>
                                        <div class="iop-stat">
                                            <label>Trend</label>
                                            <span id="iopODTrend" class="iop-trend-badge">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="iop-summary-card" id="iopOSCard">
                                    <div class="iop-card-header">
                                        <h6>OS (Left Eye)</h6>
                                    </div>
                                    <div class="iop-card-body">
                                        <div class="iop-stat">
                                            <label>Mean IOP</label>
                                            <span id="iopOSMean">-</span>
                                        </div>
                                        <div class="iop-stat">
                                            <label>Peak IOP</label>
                                            <span id="iopOSPeak">-</span>
                                        </div>
                                        <div class="iop-stat">
                                            <label>Rate of Change</label>
                                            <span id="iopOSRate">-</span>
                                        </div>
                                        <div class="iop-stat">
                                            <label>Trend</label>
                                            <span id="iopOSTrend" class="iop-trend-badge">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="iop-graph-container">
                                <canvas id="iopTrendGraph" width="800" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popover);
            iopTrendAnalyzerPopover = popover;
            
            // Position popover
            positionIOPTrendAnalyzerPopover();
            
            // Initialize event listeners
            initIOPTrendAnalyzerEvents(patientId);
            
            // Close on backdrop click
            const backdrop = document.createElement('div');
            backdrop.className = 'iop-trend-analyzer-popover-backdrop';
            backdrop.addEventListener('click', closeIOPTrendAnalyzerPopover);
            document.body.appendChild(backdrop);

            // Load patient list if no patient ID provided
            if (!patientId) {
                loadPatientList();
            } else {
                // Auto-load analysis if patient ID provided
                setTimeout(() => {
                    loadIOPReadings(patientId);
                }, 100);
            }
        }

        function positionIOPTrendAnalyzerPopover() {
            if (!iopTrendAnalyzerPopover) return;
            
            const iopBtn = document.getElementById('noticeBarIOPBtn');
            if (!iopBtn) {
                // If opened from patient page, center it
                iopTrendAnalyzerPopover.style.top = '50%';
                iopTrendAnalyzerPopover.style.left = '50%';
                iopTrendAnalyzerPopover.style.transform = 'translate(-50%, -50%)';
                return;
            }
            
            const btnRect = iopBtn.getBoundingClientRect();
            
            // Position below button, centered horizontally
            iopTrendAnalyzerPopover.style.top = (btnRect.bottom + 10) + 'px';
            iopTrendAnalyzerPopover.style.left = '50%';
            iopTrendAnalyzerPopover.style.transform = 'translateX(-50%)';
        }

        function closeIOPTrendAnalyzerPopover() {
            if (iopTrendAnalyzerPopover) {
                iopTrendAnalyzerPopover.remove();
                iopTrendAnalyzerPopover = null;
            }
            const backdrop = document.querySelector('.iop-trend-analyzer-popover-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function initIOPTrendAnalyzerEvents(patientId) {
            // Close button
            const closeBtn = document.getElementById('iopTrendAnalyzerCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeIOPTrendAnalyzerPopover);
            }

            // Patient search autocomplete
            const searchInput = document.getElementById('iopPatientSearch');
            if (searchInput && !patientId) {
                let searchTimeout = null;
                let currentSearchRequest = null;

                searchInput.addEventListener('input', (e) => {
                    const query = e.target.value.trim();
                    
                    // Clear previous timeout
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }

                    // Cancel previous request
                    if (currentSearchRequest) {
                        currentSearchRequest.abort();
                    }

                    const autocompleteResults = document.getElementById('iopAutocompleteResults');
                    if (!autocompleteResults) return;

                    if (query.length < 2) {
                        autocompleteResults.style.display = 'none';
                        iopAutocompleteItems = [];
                        return;
                    }

                    // Debounce search
                    searchTimeout = setTimeout(() => {
                        currentSearchRequest = new AbortController();
                        
                        fetch(`/api/patients/search?q=${encodeURIComponent(query)}`, {
                            signal: currentSearchRequest.signal
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.ok && data.data && data.data.length > 0) {
                                iopAutocompleteItems = data.data;
                                displayIOPAutocompleteResults(data.data, query);
                            } else {
                                autocompleteResults.innerHTML = '<div class="iop-autocomplete-item">No patients found</div>';
                                autocompleteResults.style.display = 'block';
                                iopAutocompleteItems = [];
                            }
                        })
                        .catch(error => {
                            if (error.name !== 'AbortError') {
                                console.error('Search error:', error);
                                autocompleteResults.style.display = 'none';
                            }
                        });
                    }, 300);
                });

                // Handle keyboard navigation
                searchInput.addEventListener('keydown', (e) => {
                    const autocompleteResults = document.getElementById('iopAutocompleteResults');
                    if (!autocompleteResults || autocompleteResults.style.display === 'none') {
                        return;
                    }

                    const items = autocompleteResults.querySelectorAll('.iop-autocomplete-item');
                    if (items.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        iopSelectedAutocompleteIndex = Math.min(iopSelectedAutocompleteIndex + 1, items.length - 1);
                        updateAutocompleteSelection(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        iopSelectedAutocompleteIndex = Math.max(iopSelectedAutocompleteIndex - 1, -1);
                        updateAutocompleteSelection(items);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (iopSelectedAutocompleteIndex >= 0 && iopSelectedAutocompleteIndex < iopAutocompleteItems.length) {
                            selectIOPPatient(iopAutocompleteItems[iopSelectedAutocompleteIndex]);
                        }
                    } else if (e.key === 'Escape') {
                        autocompleteResults.style.display = 'none';
                        iopSelectedAutocompleteIndex = -1;
                    }
                });

                // Close autocomplete when clicking outside
                const clickOutsideHandler = (e) => {
                    const autocompleteResults = document.getElementById('iopAutocompleteResults');
                    if (autocompleteResults && !searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
                        autocompleteResults.style.display = 'none';
                    }
                };
                document.addEventListener('click', clickOutsideHandler);
            }

            // Analyze button
            const analyzeBtn = document.getElementById('iopAnalyzeBtn');
            if (analyzeBtn) {
                analyzeBtn.addEventListener('click', () => {
                    const selectedPatientId = patientId || iopSelectedPatientId || document.getElementById('iopPatientId')?.value;
                    if (!selectedPatientId) {
                        alert('Please select a patient');
                        return;
                    }
                    loadIOPReadings(selectedPatientId);
                });
            }

            // Reset button
            const resetBtn = document.getElementById('iopResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    document.getElementById('iopTrendAnalyzerResults').style.display = 'none';
                    const searchInput = document.getElementById('iopPatientSearch');
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    const autocompleteResults = document.getElementById('iopAutocompleteResults');
                    if (autocompleteResults) {
                        autocompleteResults.style.display = 'none';
                    }
                    const selectedPatientDiv = document.getElementById('iopSelectedPatientName');
                    if (selectedPatientDiv) {
                        selectedPatientDiv.remove();
                    }
                    iopSelectedPatientId = null;
                    iopSelectedPatientName = null;
                    iopSelectedAutocompleteIndex = -1;
                });
            }

            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && iopTrendAnalyzerPopover) {
                    closeIOPTrendAnalyzerPopover();
                }
            });

            // Reposition on window resize
            window.addEventListener('resize', () => {
                if (iopTrendAnalyzerPopover) {
                    positionIOPTrendAnalyzerPopover();
                }
            });
        }

        function displayIOPAutocompleteResults(patients, searchTerm) {
            const autocompleteResults = document.getElementById('iopAutocompleteResults');
            if (!autocompleteResults) return;

            let html = '';
            patients.forEach((patient, index) => {
                const fullName = `${patient.first_name} ${patient.last_name}`;
                const age = patient.dob ? calculateAge(patient.dob) : 'N/A';
                const phone = patient.phone || 'No phone';
                const nationalId = patient.national_id || '';

                html += `
                    <div class="iop-autocomplete-item" data-index="${index}" onclick="selectIOPPatientFromAutocomplete(${patient.id})">
                        <div class="iop-patient-name">${fullName}</div>
                        <div class="iop-patient-info">
                            ${phone ? `<i class="bi bi-telephone me-1"></i>${phone}` : ''}
                            ${nationalId ? ` • <i class="bi bi-card-text me-1"></i>ID: ${nationalId}` : ''}
                            ${age !== 'N/A' ? ` • Age: ${age}` : ''}
                        </div>
                    </div>
                `;
            });

            autocompleteResults.innerHTML = html;
            autocompleteResults.style.display = 'block';
            iopSelectedAutocompleteIndex = -1;
        }

        function updateAutocompleteSelection(items) {
            items.forEach((item, index) => {
                if (index === iopSelectedAutocompleteIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        function selectIOPPatient(patient) {
            iopSelectedPatientId = patient.id;
            iopSelectedPatientName = `${patient.first_name} ${patient.last_name}`;

            const searchInput = document.getElementById('iopPatientSearch');
            const autocompleteResults = document.getElementById('iopAutocompleteResults');
            const formGroup = searchInput?.closest('.iop-form-group');

            if (searchInput && formGroup) {
                searchInput.value = iopSelectedPatientName;
                searchInput.style.display = 'none';
                
                // Create selected patient display
                const selectedDiv = document.createElement('div');
                selectedDiv.id = 'iopSelectedPatientName';
                selectedDiv.className = 'iop-selected-patient';
                selectedDiv.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>
                    ${iopSelectedPatientName}
                    <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="clearIOPPatientSelection()" style="color: inherit;">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                formGroup.querySelector('.iop-patient-search-wrapper').appendChild(selectedDiv);

                // Create hidden input for patient ID
                let hiddenInput = document.getElementById('iopPatientId');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id = 'iopPatientId';
                    formGroup.querySelector('.iop-patient-search-wrapper').appendChild(hiddenInput);
                }
                hiddenInput.value = patient.id;
            }

            if (autocompleteResults) {
                autocompleteResults.style.display = 'none';
            }
        }

        // Global function for onclick handler
        window.selectIOPPatientFromAutocomplete = function(patientId) {
            const patient = iopAutocompleteItems.find(p => p.id === patientId);
            if (patient) {
                selectIOPPatient(patient);
            }
        };

        // Global function to clear patient selection
        window.clearIOPPatientSelection = function() {
            const searchInput = document.getElementById('iopPatientSearch');
            const selectedDiv = document.getElementById('iopSelectedPatientName');
            const hiddenInput = document.getElementById('iopPatientId');

            if (searchInput) {
                searchInput.value = '';
                searchInput.style.display = 'block';
            }
            if (selectedDiv) {
                selectedDiv.remove();
            }
            if (hiddenInput) {
                hiddenInput.remove();
            }

            iopSelectedPatientId = null;
            iopSelectedPatientName = null;
        };

        function calculateAge(dob) {
            const birthDate = new Date(dob);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        function loadPatientList() {
            const select = document.getElementById('iopPatientSelect');
            if (!select) return;

            fetch('/api/patients?limit=100')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.patients) {
                        select.innerHTML = '<option value="">Select a patient...</option>';
                        data.patients.forEach(patient => {
                            const option = document.createElement('option');
                            option.value = patient.id;
                            option.textContent = `${patient.first_name} ${patient.last_name}`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading patients:', error);
                });
        }

        function loadIOPReadings(patientId) {
            const analyzeBtn = document.getElementById('iopAnalyzeBtn');
            const resultsContainer = document.getElementById('iopTrendAnalyzerResults');
            
            if (!analyzeBtn || !resultsContainer) return;

            // Show loading state
            const originalText = analyzeBtn.innerHTML;
            analyzeBtn.disabled = true;
            analyzeBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';

            // Fetch IOP data
            fetch(`/api/iop/analyze?patient_id=${patientId}`)
                .then(response => response.json())
                .then(result => {
                    analyzeBtn.disabled = false;
                    analyzeBtn.innerHTML = originalText;

                    if (result.success) {
                        renderIOPAnalysis(result);
                    } else {
                        alert('Analysis failed: ' + (result.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    analyzeBtn.disabled = false;
                    analyzeBtn.innerHTML = originalText;
                    alert('Error: ' + error.message);
                });
        }

        function renderIOPAnalysis(result) {
            const resultsContainer = document.getElementById('iopTrendAnalyzerResults');
            if (!resultsContainer) return;

            // Show results container
            resultsContainer.style.display = 'block';

            // Render alerts
            renderIOPAlerts(result.alerts || []);

            // Render OD analysis
            if (result.od) {
                renderEyeAnalysis('OD', result.od);
            }

            // Render OS analysis
            if (result.os) {
                renderEyeAnalysis('OS', result.os);
            }

            // Render graph
            renderIOPGraph(result);

            // Scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function renderIOPAlerts(alerts) {
            const alertsSection = document.getElementById('iopAlertsSection');
            if (!alertsSection) return;

            if (alerts.length === 0) {
                alertsSection.innerHTML = '<div class="iop-alert iop-alert-success"><i class="bi bi-check-circle"></i> No clinical alerts</div>';
                return;
            }

            let alertsHTML = '';
            alerts.forEach(alert => {
                let alertClass = 'iop-alert-info';
                let icon = 'bi-info-circle';
                
                if (alert.type === 'spike') {
                    alertClass = 'iop-alert-danger';
                    icon = 'bi-exclamation-triangle';
                } else if (alert.type === 'poor_response') {
                    alertClass = 'iop-alert-warning';
                    icon = 'bi-exclamation-circle';
                } else if (alert.type === 'worsening') {
                    alertClass = 'iop-alert-warning';
                    icon = 'bi-arrow-up-circle';
                }

                alertsHTML += `
                    <div class="iop-alert ${alertClass}">
                        <i class="bi ${icon}"></i>
                        <span><strong>${alert.eye}:</strong> ${alert.message}</span>
                    </div>
                `;
            });

            alertsSection.innerHTML = alertsHTML;
        }

        function renderEyeAnalysis(eye, analysis) {
            const eyeLower = eye.toLowerCase();
            
            // Mean IOP
            const meanEl = document.getElementById(`iop${eye}Mean`);
            if (meanEl && analysis.mean_iop !== null) {
                meanEl.textContent = `${analysis.mean_iop} mmHg`;
            }

            // Peak IOP
            const peakEl = document.getElementById(`iop${eye}Peak`);
            if (peakEl && analysis.peak_iop !== null) {
                peakEl.textContent = `${analysis.peak_iop} mmHg`;
            }

            // Rate of Change
            const rateEl = document.getElementById(`iop${eye}Rate`);
            if (rateEl && analysis.rate_of_change !== null) {
                const sign = analysis.rate_of_change >= 0 ? '+' : '';
                rateEl.textContent = `${sign}${analysis.rate_of_change} mmHg/year`;
            }

            // Trend
            const trendEl = document.getElementById(`iop${eye}Trend`);
            if (trendEl && analysis.trend) {
                let trendClass = 'iop-trend-stable';
                let trendText = 'Stable';
                let trendIcon = 'bi-dash-circle';

                if (analysis.trend === 'improving') {
                    trendClass = 'iop-trend-improving';
                    trendText = 'Improving';
                    trendIcon = 'bi-arrow-down-circle';
                } else if (analysis.trend === 'worsening') {
                    trendClass = 'iop-trend-worsening';
                    trendText = 'Worsening';
                    trendIcon = 'bi-arrow-up-circle';
                }

                trendEl.className = `iop-trend-badge ${trendClass}`;
                trendEl.innerHTML = `<i class="bi ${trendIcon}"></i> ${trendText}`;
            }
        }

        function renderIOPGraph(result) {
            const canvas = document.getElementById('iopTrendGraph');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const width = canvas.width;
            const height = canvas.height;
            const padding = 60;
            const graphWidth = width - (padding * 2);
            const graphHeight = height - (padding * 2);

            // Detect dark mode
            const isDarkMode = document.documentElement.classList.contains('dark') || 
                              window.getComputedStyle(document.body).backgroundColor === 'rgb(11, 18, 32)' ||
                              window.getComputedStyle(document.body).backgroundColor === 'rgb(15, 23, 42)';

            // Color scheme based on theme
            const colors = {
                grid: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : '#e0e0e0',
                axis: isDarkMode ? 'rgba(255, 255, 255, 0.3)' : '#333',
                text: isDarkMode ? 'rgba(255, 255, 255, 0.8)' : '#333',
                textMuted: isDarkMode ? 'rgba(255, 255, 255, 0.5)' : '#666',
                background: isDarkMode ? 'rgba(15, 23, 42, 0.5)' : 'rgba(255, 255, 255, 0.5)',
                odLine: '#22c55e',
                odPoint: '#22c55e',
                osLine: '#3b82f6',
                osPoint: '#3b82f6',
                spike: '#ef4444'
            };

            // Clear canvas
            ctx.clearRect(0, 0, width, height);

            // Fill background
            ctx.fillStyle = colors.background;
            ctx.fillRect(0, 0, width, height);

            // Prepare data
            const odReadings = result.od?.readings || [];
            const osReadings = result.os?.readings || [];
            const allReadings = [...odReadings, ...osReadings];

            if (allReadings.length === 0) {
                ctx.fillStyle = colors.textMuted;
                ctx.font = '14px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('No data available for graph', width / 2, height / 2);
                return;
            }

            // Get date range
            const dates = allReadings.map(r => new Date(r.date)).sort((a, b) => a - b);
            const minDate = dates[0];
            const maxDate = dates[dates.length - 1];
            const dateRange = maxDate - minDate || 1;

            // Get IOP range (0-60 mmHg, but adjust to data)
            const allIOPs = allReadings.map(r => r.iop);
            const minIOP = Math.max(0, Math.min(...allIOPs) - 5);
            const maxIOP = Math.min(60, Math.max(...allIOPs) + 5);
            const iopRange = maxIOP - minIOP || 1;

            // Draw grid lines
            ctx.strokeStyle = colors.grid;
            ctx.lineWidth = 1;
            for (let i = 0; i <= 10; i++) {
                const y = padding + (graphHeight * i / 10);
                ctx.beginPath();
                ctx.moveTo(padding, y);
                ctx.lineTo(width - padding, y);
                ctx.stroke();
            }

            // Draw axes
            ctx.strokeStyle = colors.axis;
            ctx.lineWidth = 2;
            // Y-axis
            ctx.beginPath();
            ctx.moveTo(padding, padding);
            ctx.lineTo(padding, height - padding);
            ctx.stroke();
            // X-axis
            ctx.beginPath();
            ctx.moveTo(padding, height - padding);
            ctx.lineTo(width - padding, height - padding);
            ctx.stroke();

            // Draw axis labels
            ctx.fillStyle = colors.text;
            ctx.font = '12px Arial';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            for (let i = 0; i <= 10; i++) {
                const iopValue = maxIOP - (iopRange * i / 10);
                const y = padding + (graphHeight * i / 10);
                ctx.fillText(iopValue.toFixed(0), padding - 10, y);
            }

            // X-axis labels (dates)
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            ctx.fillStyle = colors.text;
            const dateLabels = 5;
            for (let i = 0; i <= dateLabels; i++) {
                const date = new Date(minDate.getTime() + (dateRange * i / dateLabels));
                const x = padding + (graphWidth * i / dateLabels);
                ctx.fillText(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }), x, height - padding + 10);
            }

            // Draw OD line (green)
            if (odReadings.length > 0) {
                ctx.strokeStyle = '#22c55e';
                ctx.lineWidth = 2;
                ctx.beginPath();
                odReadings.forEach((reading, index) => {
                    const date = new Date(reading.date);
                    const x = padding + ((date - minDate) / dateRange) * graphWidth;
                    const y = padding + graphHeight - ((reading.iop - minIOP) / iopRange) * graphHeight;
                    
                    if (index === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                });
                ctx.stroke();

                // Draw OD points
                ctx.fillStyle = '#22c55e';
                odReadings.forEach(reading => {
                    const date = new Date(reading.date);
                    const x = padding + ((date - minDate) / dateRange) * graphWidth;
                    const y = padding + graphHeight - ((reading.iop - minIOP) / iopRange) * graphHeight;
                    ctx.beginPath();
                    ctx.arc(x, y, 4, 0, Math.PI * 2);
                    ctx.fill();
                });

                // Mark spikes
                if (result.od?.spikes) {
                    ctx.fillStyle = '#ef4444';
                    result.od.spikes.forEach(spike => {
                        const reading = odReadings.find(r => r.date === spike.date);
                        if (reading) {
                            const date = new Date(reading.date);
                            const x = padding + ((date - minDate) / dateRange) * graphWidth;
                            const y = padding + graphHeight - ((reading.iop - minIOP) / iopRange) * graphHeight;
                            ctx.beginPath();
                            ctx.arc(x, y, 6, 0, Math.PI * 2);
                            ctx.fill();
                        }
                    });
                }
            }

            // Draw OS line (blue)
            if (osReadings.length > 0) {
                ctx.strokeStyle = '#3b82f6';
                ctx.lineWidth = 2;
                ctx.beginPath();
                osReadings.forEach((reading, index) => {
                    const date = new Date(reading.date);
                    const x = padding + ((date - minDate) / dateRange) * graphWidth;
                    const y = padding + graphHeight - ((reading.iop - minIOP) / iopRange) * graphHeight;
                    
                    if (index === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                });
                ctx.stroke();

                // Draw OS points
                ctx.fillStyle = '#3b82f6';
                osReadings.forEach(reading => {
                    const date = new Date(reading.date);
                    const x = padding + ((date - minDate) / dateRange) * graphWidth;
                    const y = padding + graphHeight - ((reading.iop - minIOP) / iopRange) * graphHeight;
                    ctx.beginPath();
                    ctx.arc(x, y, 4, 0, Math.PI * 2);
                    ctx.fill();
                });

                // Mark spikes
                if (result.os?.spikes) {
                    ctx.fillStyle = '#ef4444';
                    result.os.spikes.forEach(spike => {
                        const reading = osReadings.find(r => r.date === spike.date);
                        if (reading) {
                            const date = new Date(reading.date);
                            const x = padding + ((date - minDate) / dateRange) * graphWidth;
                            const y = padding + graphHeight - ((reading.iop - minIOP) / iopRange) * graphHeight;
                            ctx.beginPath();
                            ctx.arc(x, y, 6, 0, Math.PI * 2);
                            ctx.fill();
                        }
                    });
                }
            }

            // Draw legend
            ctx.font = '12px Arial';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            
            let legendY = 20;
            
            if (odReadings.length > 0) {
                ctx.fillStyle = colors.odLine;
                ctx.fillRect(width - 150, legendY, 15, 15);
                ctx.fillStyle = colors.text;
                ctx.fillText('OD (Right Eye)', width - 130, legendY + 7);
                legendY += 25;
            }
            
            if (osReadings.length > 0) {
                ctx.fillStyle = colors.osLine;
                ctx.fillRect(width - 150, legendY, 15, 15);
                ctx.fillStyle = colors.text;
                ctx.fillText('OS (Left Eye)', width - 130, legendY + 7);
                legendY += 25;
            }

            if ((result.od?.spikes?.length > 0) || (result.os?.spikes?.length > 0)) {
                ctx.fillStyle = colors.spike;
                ctx.beginPath();
                ctx.arc(width - 150, legendY, 6, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = colors.text;
                ctx.fillText('Spike', width - 130, legendY + 7);
            }
        }

        // Patient Profile Page IOP Trend Button
        const patientIOPBtn = document.getElementById('patientIOPTrendBtn');
        if (patientIOPBtn) {
            patientIOPBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const patientId = patientIOPBtn.getAttribute('data-patient-id');
                if (patientId) {
                    createIOPTrendAnalyzerPopover(patientId);
                }
            });
        }
