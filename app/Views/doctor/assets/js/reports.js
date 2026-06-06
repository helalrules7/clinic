// ============================================
// Mini Sparkline Charts for Stats Cards
// ============================================

function generateSparklineSVG(data) {
    const width = 100;
    const height = 35;
    const padding = 2;

    // Normalize data
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;

    // Generate points
    const points = data.map((value, index) => {
        const x = padding + (index / (data.length - 1)) * (width - padding * 2);
        const y = height - padding - ((value - min) / range) * (height - padding * 2);
        return `${x},${y}`;
    });

    // Create path
    const linePath = `M ${points.join(' L ')}`;

    // Create area path (closed for fill)
    const areaPath = `M ${padding},${height} L ${points.join(' L ')} L ${width - padding},${height} Z`;

    return `
        <svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none">
            <path class="sparkline-area" d="${areaPath}"/>
            <path class="sparkline-path" d="${linePath}"/>
        </svg>
    `;
}

function initMiniStatsCharts() {
    // Generate trend data based on report type
    const reportType = window.REPORTS_CONFIG?.reportType || 'appointments';
    const reportData = window.REPORTS_CONFIG?.reportData || [];
    
    // Chart configurations based on report type
    let chartConfigs = [];
    
    if (reportType === 'appointments') {
        // Extract data from reportData for appointments
        const appointmentsData = reportData.map(r => r.total_appointments || 0);
        const completedData = reportData.map(r => r.completed || 0);
        const missedData = reportData.map(r => r.missed || 0);
        const ratioData = reportData.map(r => {
            const total = r.total_appointments || 1;
            return ((r.completed || 0) / total) * 100;
        });
        
        chartConfigs = [
            { id: 'chartTotalAppointments', trend: appointmentsData.length > 0 ? appointmentsData : [65, 72, 78, 75, 82, 88, 92, 95, 100] },
            { id: 'chartCompleted', trend: completedData.length > 0 ? completedData : [50, 55, 60, 58, 65, 70, 68, 75, 80] },
            { id: 'chartMissed', trend: missedData.length > 0 ? missedData : [15, 17, 18, 17, 17, 18, 24, 20, 20] },
            { id: 'chartCompletionRatio', trend: ratioData.length > 0 ? ratioData : [75, 76, 77, 77, 79, 80, 79, 80, 80] }
        ];
    } else if (reportType === 'revenue') {
        const revenueData = reportData.map(r => (r.daily_revenue || 0) / 1000); // Scale down for visualization
        const transactionsData = reportData.map(r => r.transactions || 0);
        const avgData = reportData.map(r => {
            const trans = r.transactions || 1;
            return ((r.daily_revenue || 0) / trans) / 100; // Scale down
        });
        const discountsData = reportData.map(r => (r.discounts || 0) / 100); // Scale down
        
        chartConfigs = [
            { id: 'chartTotalRevenue', trend: revenueData.length > 0 ? revenueData : [120, 135, 142, 138, 155, 162, 158, 175, 180] },
            { id: 'chartTotalTransactions', trend: transactionsData.length > 0 ? transactionsData : [25, 28, 30, 29, 32, 35, 33, 38, 40] },
            { id: 'chartAvgTransaction', trend: avgData.length > 0 ? avgData : [4.8, 4.8, 4.7, 4.8, 4.8, 4.6, 4.8, 4.6, 4.5] },
            { id: 'chartTotalDiscounts', trend: discountsData.length > 0 ? discountsData : [5, 6, 7, 6, 8, 9, 8, 10, 11] }
        ];
    } else if (reportType === 'patients') {
        const newPatientsData = reportData.map(r => r.new_patients || 0);
        const maleData = reportData.map(r => r.male || 0);
        const femaleData = reportData.map(r => r.female || 0);
        const malePercentData = reportData.map(r => {
            const total = r.new_patients || 1;
            return ((r.male || 0) / total) * 100;
        });
        
        chartConfigs = [
            { id: 'chartTotalNewPatients', trend: newPatientsData.length > 0 ? newPatientsData : [12, 18, 15, 22, 19, 25, 28, 24, 30] },
            { id: 'chartMalePatients', trend: maleData.length > 0 ? maleData : [6, 9, 8, 11, 10, 13, 14, 12, 15] },
            { id: 'chartFemalePatients', trend: femaleData.length > 0 ? femaleData : [6, 9, 7, 11, 9, 12, 14, 12, 15] },
            { id: 'chartMalePercentage', trend: malePercentData.length > 0 ? malePercentData : [50, 50, 53, 50, 53, 52, 50, 50, 50] }
        ];
    } else if (reportType === 'medical_prescriptions') {
        const prescriptionsData = reportData.map(r => r.total_prescriptions || 0);
        const appointmentsData = reportData.map(r => r.appointments_with_prescriptions || 0);
        const patientsData = reportData.map(r => r.patients_count || 0);
        const avgData = reportData.map(r => {
            const apps = r.appointments_with_prescriptions || 1;
            return (r.total_prescriptions || 0) / apps;
        });
        
        chartConfigs = [
            { id: 'chartTotalPrescriptions', trend: prescriptionsData.length > 0 ? prescriptionsData : [20, 25, 22, 28, 26, 30, 32, 29, 35] },
            { id: 'chartAppointmentsWithRx', trend: appointmentsData.length > 0 ? appointmentsData : [15, 18, 16, 20, 19, 22, 24, 21, 25] },
            { id: 'chartPatientsCount', trend: patientsData.length > 0 ? patientsData : [12, 15, 14, 18, 16, 20, 22, 19, 23] },
            { id: 'chartAvgPerAppointment', trend: avgData.length > 0 ? avgData : [1.3, 1.4, 1.4, 1.4, 1.4, 1.4, 1.3, 1.4, 1.4] }
        ];
    } else if (reportType === 'glasses_prescriptions') {
        const prescriptionsData = reportData.map(r => r.total_prescriptions || 0);
        const appointmentsData = reportData.map(r => r.appointments_with_prescriptions || 0);
        const patientsData = reportData.map(r => r.patients_count || 0);
        const lensTypeData = reportData.map(r => r.with_lens_type || 0);
        
        chartConfigs = [
            { id: 'chartGlassesTotalPrescriptions', trend: prescriptionsData.length > 0 ? prescriptionsData : [10, 12, 11, 14, 13, 15, 16, 14, 18] },
            { id: 'chartGlassesAppointments', trend: appointmentsData.length > 0 ? appointmentsData : [8, 10, 9, 12, 11, 13, 14, 12, 15] },
            { id: 'chartGlassesPatients', trend: patientsData.length > 0 ? patientsData : [6, 8, 7, 10, 9, 11, 12, 10, 13] },
            { id: 'chartWithLensType', trend: lensTypeData.length > 0 ? lensTypeData : [5, 6, 6, 7, 7, 8, 8, 7, 9] },
            // Duplicate IDs for the second glasses section
            { id: 'chartGlassesTotalPrescriptions2', trend: prescriptionsData.length > 0 ? prescriptionsData : [10, 12, 11, 14, 13, 15, 16, 14, 18] },
            { id: 'chartGlassesAppointments2', trend: appointmentsData.length > 0 ? appointmentsData : [8, 10, 9, 12, 11, 13, 14, 12, 15] },
            { id: 'chartGlassesPatients2', trend: patientsData.length > 0 ? patientsData : [6, 8, 7, 10, 9, 11, 12, 10, 13] },
            { id: 'chartWithLensType2', trend: lensTypeData.length > 0 ? lensTypeData : [5, 6, 6, 7, 7, 8, 8, 7, 9] }
        ];
    }

    chartConfigs.forEach(config => {
        const container = document.getElementById(config.id);
        if (container) {
            // Ensure we have at least 2 data points
            const trendData = config.trend.length >= 2 ? config.trend : [config.trend[0] || 0, config.trend[0] || 1];
            container.innerHTML = generateSparklineSVG(trendData);
        }
    });
}

// Initialize sparkline charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure CSS is loaded and data is available
    setTimeout(initMiniStatsCharts, 100);
});

// Quick Date Range Buttons Handler
document.querySelectorAll('.quick-date-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const range = this.getAttribute('data-range');
        const today = new Date();
        let startDate, endDate;
        
        // Remove active class from all buttons
        document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
        // Add active class to clicked button
        this.classList.add('active');
        
        switch(range) {
            case 'month':
                // This month - first day of current month
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = today;
                break;
            case 'all':
                // All time - set to a very early date
                startDate = new Date('2000-01-01');
                endDate = today;
                break;
            case 'quarter':
                // Last quarter (3 months)
                startDate = new Date(today);
                startDate.setMonth(today.getMonth() - 3);
                endDate = today;
                break;
            case '6months':
                // Last 6 months
                startDate = new Date(today);
                startDate.setMonth(today.getMonth() - 6);
                endDate = today;
                break;
            case 'year':
                // Last year
                startDate = new Date(today);
                startDate.setFullYear(today.getFullYear() - 1);
                endDate = today;
                break;
            default:
                return;
        }
        
        // Format dates as YYYY-MM-DD
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };
        
        // Update form inputs
        document.getElementById('start_date').value = formatDate(startDate);
        document.getElementById('end_date').value = formatDate(endDate);
        
        // Submit form
        document.getElementById('reportForm').submit();
    });
});

// Set active button on page load based on current dates
(function() {
    // First, remove active class from all buttons
    document.querySelectorAll('.quick-date-btn').forEach(btn => btn.classList.remove('active'));
    
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        endDate.setHours(0, 0, 0, 0);
        startDate.setHours(0, 0, 0, 0);
        
        // Check if end date is today (or very close)
        const daysDiff = Math.floor((today - endDate) / (1000 * 60 * 60 * 24));
        
        // Check if start date is very early (All Time - before 2000-01-01 or very old)
        const allTimeThreshold = new Date('2000-01-01');
        allTimeThreshold.setHours(0, 0, 0, 0);
        const isAllTime = startDate.getTime() <= allTimeThreshold.getTime() && daysDiff <= 1;
        
        if (isAllTime) {
            // All Time is selected
            document.querySelector('.quick-date-btn[data-range="all"]')?.classList.add('active');
        } else if (daysDiff <= 1) {
            // End date is today, check which range matches
            const monthsDiff = (today.getFullYear() - startDate.getFullYear()) * 12 + (today.getMonth() - startDate.getMonth());
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            firstDayOfMonth.setHours(0, 0, 0, 0);
            
            // Check if it's this month (default)
            if (startDate.getTime() === firstDayOfMonth.getTime()) {
                document.querySelector('.quick-date-btn[data-range="month"]')?.classList.add('active');
            } else if (monthsDiff <= 3) {
                document.querySelector('.quick-date-btn[data-range="quarter"]')?.classList.add('active');
            } else if (monthsDiff <= 6) {
                document.querySelector('.quick-date-btn[data-range="6months"]')?.classList.add('active');
            } else if (monthsDiff <= 12) {
                document.querySelector('.quick-date-btn[data-range="year"]')?.classList.add('active');
            } else {
                // If it's more than a year but not All Time, default to All Time
                document.querySelector('.quick-date-btn[data-range="all"]')?.classList.add('active');
            }
        } else {
            // Default to this month if dates don't match any range
            document.querySelector('.quick-date-btn[data-range="month"]')?.classList.add('active');
        }
    } else {
        // If no dates set, default to this month
        document.querySelector('.quick-date-btn[data-range="month"]')?.classList.add('active');
    }
})();

// Auto-submit form when dates change
document.getElementById('start_date').addEventListener('change', function() {
    // Remove active class from all quick date buttons
    document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
    
    if (document.getElementById('end_date').value) {
        document.getElementById('reportForm').submit();
    }
});

document.getElementById('end_date').addEventListener('change', function() {
    // Remove active class from all quick date buttons
    document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
    
    if (document.getElementById('start_date').value) {
        document.getElementById('reportForm').submit();
    }
});

// Validate date range
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    
    if (startDate > endDate) {
        e.preventDefault();
        alert('Start date must be before end date');
    }
});

// Custom Select Menu Logic for Report Type
document.addEventListener('DOMContentLoaded', function() {
    // The report-type <select> has id="type"; resolve its wrapping field via
    // closest() (the previous `:has(#reportType)` selector never matched, which
    // left this rich dropdown unwired and made initCustomSelects warn on it).
    const select = document.getElementById('type');
    const field = select ? select.closest('.field.menu') : null;
    if (!field || !select) return;

    const button = field.querySelector('#type-toggle');
    const menu = field.querySelector('menu');
    const options = menu ? menu.querySelectorAll('li') : [];
    if (!button || !menu || options.length === 0) return;

    // Keep the generic initialiser from double-wiring / warning on this field.
    field.setAttribute('data-initialized', 'dedicated-type');

    // Toggle Menu
    function toggleMenu() {
        if (field.classList.contains('open')) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    function openMenu() {
        field.classList.add('open');
        button.setAttribute('aria-expanded', 'true');
        
        // Fix z-index issue by elevating parent container manually
        // This is a fallback/reinforcement for the CSS :has() selector
        const parentCol = field.closest('.col-md-3');
        if (parentCol) {
            parentCol.style.zIndex = '1005';
            parentCol.style.position = 'relative'; 
        }

        // Focus first selected or first option
        const selected = menu.querySelector('.selected') || options[0];
        if (selected) selected.focus();
    }

    function closeMenu() {
        field.classList.remove('open');
        button.setAttribute('aria-expanded', 'false');
        button.focus();
        
        // Reset parent z-index with a slight delay to allow animation to finish
        const parentCol = field.closest('.col-md-3');
        if (parentCol) {
            setTimeout(() => {
                if (!field.classList.contains('open')) {
                    parentCol.style.zIndex = '';
                    // We don't remove position: relative as it might be needed by bootstrap grid, 
                    // though usually cols are relative by default. Safe to leave or reset if needed.
                    parentCol.style.position = ''; 
                }
            }, 300); // Wait for animation
        }
    }

    // Set Option
    function setOption(optionEl) {
        const value = optionEl.dataset.option;
        const text = optionEl.querySelector('h3').textContent;
        
        // Update hidden select
        select.value = value;
        // Also manually trigger change event if needed by other listeners
        select.dispatchEvent(new Event('change'));
        
        // Update button text
        button.textContent = text;
        
        // Update UI classes
        options.forEach(el => el.classList.remove('selected'));
        optionEl.classList.add('selected');
        
        closeMenu();
        
        // Trigger form submission
        const form = document.getElementById('reportForm');
        if (form) {
            form.submit();
        } else {
            console.error('Report form not found!');
        }
    }

    // Event Listeners
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    button.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openMenu();
        }
    });

    options.forEach(option => {
        option.addEventListener('click', (e) => {
            e.stopPropagation();
            setOption(option);
        });

        option.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                setOption(option);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const next = option.nextElementSibling;
                if (next) next.focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prev = option.previousElementSibling;
                if (prev) prev.focus();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeMenu();
            }
        });
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!field.contains(e.target)) {
            closeMenu();
        }
    });
});

// Chart.js Configuration
const chartColors = {
    primary: '#007bff',
    success: '#28a745',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#17a2b8',
    secondary: '#6c757d',
    light: '#f8f9fa',
    dark: '#343a40'
};

// Dark mode colors
const darkModeColors = {
    primary: '#0d6efd',
    success: '#198754',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#0dcaf0',
    secondary: '#6c757d',
    light: '#f8f9fa',
    dark: '#212529'
};

// Get current theme colors
function getThemeColors() {
    return document.body.classList.contains('dark') ? darkModeColors : chartColors;
}

// Chart.js default configuration
if (Chart.defaults && Chart.defaults.font) {
Chart.defaults.font.family = "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
}

// Update Chart.js defaults based on theme
function updateChartDefaults() {
    const themeColors = getCurrentThemeColors();
    if (Chart.defaults) {
        Chart.defaults.color = themeColors.text;
    }
}

// Initialize defaults
updateChartDefaults();

// Get current theme colors dynamically - same as dashboard.php
function getCurrentThemeColors() {
    const isDark = document.documentElement.classList.contains('dark');
    
    if (isDark) {
    return {
            text: '#ffffff',
            muted: '#ffffff',
            grid: 'rgba(255, 255, 255, 0.15)',
            border: 'rgba(255, 255, 255, 0.3)',
            background: '#1e293b',
            tooltipBg: 'rgba(0, 0, 0, 0.95)',
            tooltipText: '#ffffff'
        };
    } else {
        return {
            text: '#0f172a',
            muted: '#475569',
            grid: 'rgba(0, 0, 0, 0.1)',
            border: 'rgba(0, 0, 0, 0.2)',
            background: '#ffffff',
            tooltipBg: 'rgba(255, 255, 255, 0.95)',
            tooltipText: '#0f172a'
    };
    }
}

// Prepare data for charts
if (window.REPORTS_CONFIG && window.REPORTS_CONFIG.reportData && window.REPORTS_CONFIG.reportData.length > 0) {
const reportData = window.REPORTS_CONFIG.reportData;
const reportType = window.REPORTS_CONFIG.reportType;

// Common chart options
function getCommonOptions() {
    const themeColors = getCurrentThemeColors();
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12,
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    },
                    color: themeColors.text
                }
            },
            tooltip: {
                backgroundColor: themeColors.tooltipBg,
                titleColor: themeColors.tooltipText,
                bodyColor: themeColors.tooltipText,
                borderColor: themeColors.border,
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: true,
                titleFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 12
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: themeColors.grid,
                    drawBorder: false
                },
                ticks: {
                    color: themeColors.text,
                    font: {
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    }
                }
            },
            y: {
                grid: {
                    color: themeColors.grid,
                    drawBorder: false
                },
                ticks: {
                    color: themeColors.text,
                    font: {
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    }
                }
            }
        }
    };
}

// Pie chart options
function getPieOptions() {
    const themeColors = getCurrentThemeColors();
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12,
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    },
                    color: themeColors.text
                }
            },
            tooltip: {
                backgroundColor: themeColors.tooltipBg,
                titleColor: themeColors.tooltipText,
                bodyColor: themeColors.tooltipText,
                borderColor: themeColors.border,
                borderWidth: 1,
                cornerRadius: 8,
                titleFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 12
                },
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    };
}

// Store chart instances for theme updates
window.chartInstances = {
    appointmentsChart: null,
    appointmentsPieChart: null,
    revenueChart: null,
    revenuePieChart: null,
    patientsChart: null,
    genderPieChart: null,
    medicalPrescriptionsChart: null,
    topMedicationsChart: null,
    glassesPrescriptionsChart: null,
    lensTypeChart: null,
    drugsByCompanyChart: null,
    topDrugsChart: null,
    drugTrendChart: null
};

if (reportType === 'appointments') {
// Appointments Line Chart - Same as dashboard.php
const appointmentsCtx = document.getElementById('appointmentsChart');
if (appointmentsCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.appointmentsChart) {
        window.chartInstances.appointmentsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const totalAppointments = reportData.map(item => item.total_appointments || 0);
    const completed = reportData.map(item => item.completed || 0);
    const missed = reportData.map(item => item.missed || 0);
    
    window.chartInstances.appointmentsChart = new Chart(appointmentsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Total Appointments',
                    data: totalAppointments,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Completed',
                    data: completed,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Missed',
                    data: missed,
                    borderColor: '#ef4444',
                    backgroundColor: '#ef4444' + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}

// Appointments Status Pie Chart - Same as dashboard.php
const appointmentsPieCtx = document.getElementById('appointmentsPieChart');
if (appointmentsPieCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.appointmentsPieChart) {
        window.chartInstances.appointmentsPieChart.destroy();
    }
    
    const totalCompleted = window.REPORTS_CONFIG.totalCompleted || 0;
    const totalMissed = window.REPORTS_CONFIG.totalMissed || 0;
    
    window.chartInstances.appointmentsPieChart = new Chart(appointmentsPieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Missed'],
            datasets: [{
                data: [totalCompleted, totalMissed],
                backgroundColor: [
                    chartColors.success,
                    chartColors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: getPieOptions()
    });
}

} else if (reportType === 'revenue') {
// Revenue Line Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.revenueChart) {
        window.chartInstances.revenueChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const dailyRevenue = reportData.map(item => item.daily_revenue);
    const discounts = reportData.map(item => item.discounts);
    
    window.chartInstances.revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('ar-EG', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Daily Revenue (EGP)',
                    data: dailyRevenue,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Discounts (EGP)',
                    data: discounts,
                    borderColor: chartColors.danger,
                    backgroundColor: chartColors.danger + '20',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            ...getCommonOptions(),
            scales: {
                ...getCommonOptions().scales,
                y: {
                    ...getCommonOptions().scales.y,
                    beginAtZero: true,
                    ticks: {
                        ...getCommonOptions().scales.y.ticks,
                        callback: function(value) {
                            return value.toLocaleString('ar-EG') + ' EGP';
                        }
                    }
                }
            }
        }
    });
}

// Revenue vs Discounts Pie Chart
const revenuePieCtx = document.getElementById('revenuePieChart');
if (revenuePieCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.revenuePieChart) {
        window.chartInstances.revenuePieChart.destroy();
    }
    
    const totalRevenue = window.REPORTS_CONFIG.totalRevenue || 0;
    const totalDiscounts = window.REPORTS_CONFIG.totalDiscounts || 0;
    
    window.chartInstances.revenuePieChart = new Chart(revenuePieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Revenue', 'Discounts'],
            datasets: [{
                data: [totalRevenue, totalDiscounts],
                backgroundColor: [
                    chartColors.success,
                    chartColors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            ...getPieOptions(),
            plugins: {
                ...getPieOptions().plugins,
                tooltip: {
                    ...getPieOptions().plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            return `${label}: ${value.toLocaleString('ar-EG')} EGP`;
                        }
                    }
                }
            }
        }
    });
}

} else if (reportType === 'patients') {
// Patients Line Chart
const patientsCtx = document.getElementById('patientsChart');
if (patientsCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.patientsChart) {
        window.chartInstances.patientsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const newPatients = reportData.map(item => item.new_patients);
    const malePatients = reportData.map(item => item.male);
    const femalePatients = reportData.map(item => item.female);
    
    window.chartInstances.patientsChart = new Chart(patientsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('ar-EG', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'New Patients',
                    data: newPatients,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Male Patients',
                    data: malePatients,
                    borderColor: chartColors.info,
                    backgroundColor: chartColors.info + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Female Patients',
                    data: femalePatients,
                    borderColor: chartColors.warning,
                    backgroundColor: chartColors.warning + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}

// Gender Distribution Pie Chart
const genderPieCtx = document.getElementById('genderPieChart');
if (genderPieCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.genderPieChart) {
        window.chartInstances.genderPieChart.destroy();
    }
    
    const totalMale = window.REPORTS_CONFIG.totalMale || 0;
    const totalFemale = window.REPORTS_CONFIG.totalFemale || 0;
    
    window.chartInstances.genderPieChart = new Chart(genderPieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [totalMale, totalFemale],
                backgroundColor: [
                    chartColors.info,
                    chartColors.warning
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: getPieOptions()
    });
}

} else if (window.REPORTS_CONFIG && window.REPORTS_CONFIG.reportType === 'medical_prescriptions') {
// Medical Prescriptions Trend Chart
const medicalPrescriptionsCtx = document.getElementById('medicalPrescriptionsChart');
if (medicalPrescriptionsCtx) {
    if (window.chartInstances.medicalPrescriptionsChart) {
        window.chartInstances.medicalPrescriptionsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const totalPrescriptions = reportData.map(item => item.total_prescriptions || 0);
    const appointmentsWithPrescriptions = reportData.map(item => item.appointments_with_prescriptions || 0);
    const patientsCount = reportData.map(item => item.patients_count || 0);
    
    window.chartInstances.medicalPrescriptionsChart = new Chart(medicalPrescriptionsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Total Prescriptions',
                    data: totalPrescriptions,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Appointments with Prescriptions',
                    data: appointmentsWithPrescriptions,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Patients',
                    data: patientsCount,
                    borderColor: chartColors.info,
                    backgroundColor: chartColors.info + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}}

// Top Medications Chart
const topMedicationsCtx = document.getElementById('topMedicationsChart');
if (topMedicationsCtx) {
    if (window.chartInstances.topMedicationsChart) {
        window.chartInstances.topMedicationsChart.destroy();
    }
    
    const topMedications = window.REPORTS_CONFIG.topMedications || [];
    if (topMedications.length > 0) {
        const medicationNames = topMedications.map(m => m.drug_name.length > 15 ? m.drug_name.substring(0, 15) + '...' : m.drug_name);
        const usageCounts = topMedications.map(m => parseInt(m.usage_count || 0));
        
        window.chartInstances.topMedicationsChart = new Chart(topMedicationsCtx, {
            type: 'doughnut',
            data: {
                labels: medicationNames,
                datasets: [{
                    data: usageCounts,
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.success,
                        chartColors.info,
                        chartColors.warning,
                        chartColors.danger,
                        '#6c757d',
                        '#17a2b8',
                        '#ffc107',
                        '#28a745',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: getPieOptions()
        });
    }
}
    
if (reportType === 'glasses_prescriptions') {
// Glasses Prescriptions Trend Chart
const glassesPrescriptionsCtx = document.getElementById('glassesPrescriptionsChart');
if (glassesPrescriptionsCtx) {
    if (window.chartInstances.glassesPrescriptionsChart) {
        window.chartInstances.glassesPrescriptionsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const totalPrescriptions = reportData.map(item => item.total_prescriptions || 0);
    const appointmentsWithPrescriptions = reportData.map(item => item.appointments_with_prescriptions || 0);
    const patientsCount = reportData.map(item => item.patients_count || 0);
    const withLensType = reportData.map(item => item.with_lens_type || 0);
    
    window.chartInstances.glassesPrescriptionsChart = new Chart(glassesPrescriptionsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Total Prescriptions',
                    data: totalPrescriptions,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Appointments with Prescriptions',
                    data: appointmentsWithPrescriptions,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Patients',
                    data: patientsCount,
                    borderColor: chartColors.info,
                    backgroundColor: chartColors.info + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'With Lens Type',
                    data: withLensType,
                    borderColor: chartColors.warning,
                    backgroundColor: chartColors.warning + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}

// Lens Type Distribution Chart
const lensTypeCtx = document.getElementById('lensTypeChart');
if (lensTypeCtx) {
    if (window.chartInstances.lensTypeChart) {
        window.chartInstances.lensTypeChart.destroy();
    }
    
    const glassesLensTypeStats = window.REPORTS_CONFIG.glassesLensTypeStats || [];
    if (glassesLensTypeStats.length > 0) {
        const lensTypes = glassesLensTypeStats.map(s => s.lens_type);
        const counts = glassesLensTypeStats.map(s => parseInt(s.count || 0));
        
        window.chartInstances.lensTypeChart = new Chart(lensTypeCtx, {
            type: 'doughnut',
            data: {
                labels: lensTypes,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.success,
                        chartColors.info,
                        chartColors.warning,
                        chartColors.danger,
                        '#6c757d',
                        '#17a2b8',
                        '#ffc107'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: getPieOptions()
        });
    }
}
}

if (reportType === 'drugs') {
    // Demand by company (horizontal bar)
    const companyCtx = document.getElementById('drugsByCompanyChart');
    const companyStats = (window.REPORTS_CONFIG.drugCompanyStats || []).slice(0, 12);
    if (companyCtx && companyStats.length > 0) {
        if (window.chartInstances.drugsByCompanyChart) {
            window.chartInstances.drugsByCompanyChart.destroy();
        }
        const companyOpts = getCommonOptions();
        companyOpts.indexAxis = 'y';
        companyOpts.plugins.legend.display = false;
        window.chartInstances.drugsByCompanyChart = new Chart(companyCtx, {
            type: 'bar',
            data: {
                labels: companyStats.map(c => (c.company || '').length > 20 ? c.company.substring(0, 20) + '…' : (c.company || '')),
                datasets: [{
                    label: 'Prescription writes',
                    data: companyStats.map(c => parseInt(c.total_count || 0)),
                    backgroundColor: chartColors.info + 'cc',
                    borderColor: chartColors.info,
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: companyOpts
        });
    }

    // Most prescribed drugs — stacked new vs continuation (top 10)
    const topDrugsCtx = document.getElementById('topDrugsChart');
    const topDrugs = (reportData || []).slice(0, 10);
    if (topDrugsCtx && topDrugs.length > 0) {
        if (window.chartInstances.topDrugsChart) {
            window.chartInstances.topDrugsChart.destroy();
        }
        const topOpts = getCommonOptions();
        topOpts.scales.x.stacked = true;
        topOpts.scales.y.stacked = true;
        window.chartInstances.topDrugsChart = new Chart(topDrugsCtx, {
            type: 'bar',
            data: {
                labels: topDrugs.map(d => (d.drug_name || '').length > 14 ? d.drug_name.substring(0, 14) + '…' : (d.drug_name || '')),
                datasets: [
                    {
                        label: 'New starts',
                        data: topDrugs.map(d => parseInt(d.new_count || 0)),
                        backgroundColor: chartColors.success + 'cc',
                        borderRadius: 4
                    },
                    {
                        label: 'Continuations',
                        data: topDrugs.map(d => parseInt(d.continuation_count || 0)),
                        backgroundColor: chartColors.warning + 'cc',
                        borderRadius: 4
                    }
                ]
            },
            options: topOpts
        });
    }

    // Monthly trend — top 5 drugs
    const trendCtx = document.getElementById('drugTrendChart');
    const trendData = window.REPORTS_CONFIG.drugTrend || { labels: [], datasets: [] };
    if (trendCtx && trendData.datasets && trendData.datasets.length > 0) {
        if (window.chartInstances.drugTrendChart) {
            window.chartInstances.drugTrendChart.destroy();
        }
        const trendColors = [
            chartColors.primary, chartColors.success, chartColors.warning,
            chartColors.info, chartColors.danger
        ];
        window.chartInstances.drugTrendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: (trendData.labels || []).map(m => {
                    const [y, mo] = m.split('-');
                    const d = new Date(parseInt(y), parseInt(mo) - 1, 1);
                    return d.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
                }),
                datasets: trendData.datasets.map((ds, i) => ({
                    label: (ds.drug_name || '').length > 22 ? ds.drug_name.substring(0, 22) + '…' : (ds.drug_name || ''),
                    data: ds.data || [],
                    borderColor: trendColors[i % trendColors.length],
                    backgroundColor: trendColors[i % trendColors.length] + '20',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 3
                }))
            },
            options: getCommonOptions()
        });
    }
}

// Function to update charts when theme changes - same as dashboard.php
function updateChartsTheme() {
    updateChartDefaults();
    
    // Update chart container background
    document.querySelectorAll('.card-body canvas').forEach(canvas => {
        const container = canvas.closest('.card-body');
        if (container) {
    const themeColors = getCurrentThemeColors();
            container.style.backgroundColor = themeColors.background;
        }
    });
    
    // Reload charts by destroying and recreating them
    const themeColors = getCurrentThemeColors();
    
    // Update all chart instances
    Object.keys(window.chartInstances).forEach(key => {
        const chart = window.chartInstances[key];
        if (chart) {
            // Update chart options
        if (chart.options && chart.options.plugins) {
            if (chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = themeColors.text;
            }
                if (chart.options.plugins.tooltip) {
                    chart.options.plugins.tooltip.backgroundColor = themeColors.tooltipBg;
                    chart.options.plugins.tooltip.titleColor = themeColors.tooltipText;
                    chart.options.plugins.tooltip.bodyColor = themeColors.tooltipText;
                }
            if (chart.options.scales) {
                Object.keys(chart.options.scales).forEach(scaleKey => {
                    const scale = chart.options.scales[scaleKey];
                    if (scale.ticks) {
                        scale.ticks.color = themeColors.text;
                    }
                    if (scale.grid) {
                        scale.grid.color = themeColors.grid;
                    }
                });
            }
        }
        chart.update();
        }
    });
}

// Setup theme change listener - same as dashboard.php
function setupThemeListener() {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            setTimeout(() => {
                updateChartsTheme();
            }, 100);
        });
    }
    
    // Also listen for class changes on documentElement
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                setTimeout(() => {
                    updateChartsTheme();
                }, 100);
        }
    });
});

    observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});
}

// Initialize theme listener
setupThemeListener();

} // Close the main if statement for reportData

// Report Data Pagination
const reportDataArray = window.REPORTS_CONFIG ? (window.REPORTS_CONFIG.reportData || []) : [];
const reportTypeStr = window.REPORTS_CONFIG ? (window.REPORTS_CONFIG.reportType || '') : '';

let reportCurrentPage = 1;
let reportPerPage = 20;

// =========================================
// Custom Select Menu Logic
// =========================================

function initCustomSelects() {
    const customSelects = document.querySelectorAll('.field.menu:not([data-initialized])');

    customSelects.forEach(field => {
        const select = field.querySelector('select');
        const button = field.querySelector('.custom-select-toggle');
        const menu = field.querySelector('menu');
        const options = menu ? menu.querySelectorAll('li') : [];

        if (!select || !button || !menu || options.length === 0) {
            console.warn('Missing elements for custom select initialization:', field);
            return;
        }
        
        // Mark as initialized to prevent duplicate event listeners
        field.setAttribute('data-initialized', 'true');

        // Set initial button text
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            const correspondingLi = Array.from(options).find(li => li.dataset.option === selectedOption.value);
            if (correspondingLi) {
                button.textContent = correspondingLi.querySelector('h3')?.textContent || selectedOption.textContent;
                correspondingLi.classList.add('selected');
            } else {
                button.textContent = selectedOption.textContent;
            }
        } else {
            button.textContent = 'Select an option';
        }

        function openMenu() {
            // Close any other open menus first
            document.querySelectorAll('.field.menu.open').forEach(openField => {
                if (openField !== field) {
                    const openButton = openField.querySelector('.custom-select-toggle');
                    openField.classList.remove('open');
                    if (openButton) openButton.setAttribute('aria-expanded', 'false');
                    const openParent = openField.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
                    if (openParent && !openParent.classList.contains('modal')) {
                        openParent.style.zIndex = '';
                        openParent.style.position = '';
                    } else {
                        const openModal = openField.closest('.modal');
                        if (openModal) {
                            openModal.style.zIndex = '';
                        }
                    }
                }
            });

            field.classList.add('open');
            button.setAttribute('aria-expanded', 'true');

            // Fix z-index issue by elevating parent containers manually
            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                parent.style.zIndex = '1000002';
                parent.style.position = 'relative';
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    modal.style.zIndex = '1000002';
                }
            }

            const selected = menu.querySelector('.selected') || options[0];
            if (selected) {
                selected.focus();
                
                // Scroll to selected item if menu has many options
                setTimeout(() => {
                    selected.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 150);
            }
        }

        function closeMenu() {
            field.classList.remove('open');
            button.setAttribute('aria-expanded', 'false');
            if (document.activeElement === document.body || document.activeElement === null) {
                button.focus();
            }

            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                setTimeout(() => {
                    if (!field.classList.contains('open')) {
                        parent.style.zIndex = '';
                        parent.style.position = '';
                    }
                }, 300);
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    setTimeout(() => {
                        if (!field.classList.contains('open')) {
                            modal.style.zIndex = '';
                        }
                    }, 300);
                }
            }
        }

        function setOption(optionEl) {
            const value = optionEl.dataset.option;
            const text = optionEl.querySelector('h3')?.textContent || optionEl.textContent;

            select.value = value;
            select.dispatchEvent(new Event('change'));

            button.textContent = text;

            options.forEach(el => el.classList.remove('selected'));
            optionEl.classList.add('selected');

            closeMenu();
        }

        button.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            if (field.classList.contains('open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        button.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openMenu();
            }
        });

        // Prevent clicks on menu from closing modal
        menu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                setOption(option);
            });

            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setOption(option);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = option.nextElementSibling;
                    if (next) next.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = option.previousElementSibling;
                    if (prev) prev.focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeMenu();
                }
            });
        });

        // Close menu when clicking outside, but prevent modal from closing
        const handleOutsideClick = (e) => {
            const target = e.target;
            const isInteractiveElement = target.tagName === 'INPUT' || 
                                        target.tagName === 'TEXTAREA' || 
                                        target.tagName === 'SELECT' ||
                                        target.isContentEditable ||
                                        target.closest('input, textarea, select, [contenteditable]');
            
            if (isInteractiveElement) {
                return;
            }
            
            if (field.classList.contains('open') && !field.contains(target)) {
                const modal = field.closest('.modal');
                if (modal && target === modal) {
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                closeMenu();
            }
        };
        
        // Store handler for cleanup
        field._outsideClickHandler = handleOutsideClick;
        document.addEventListener('click', handleOutsideClick, false);
    });
}

// Initialize report pagination
function initializeReportPagination() {
    // Initialize custom select for reportPerPage
    initCustomSelects();
    
    const perPageSelect = document.getElementById('reportPerPage');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            reportPerPage = this.value === 'all' ? 'all' : parseInt(this.value);
            reportCurrentPage = 1;
            renderReportTable();
            renderReportPagination();
            
            // Update custom select button text
            const field = perPageSelect.closest('.field.menu');
            if (field) {
                const button = field.querySelector('.custom-select-toggle');
                const selectedLi = field.querySelector(`menu li[data-option="${this.value}"]`);
                if (button && selectedLi) {
                    button.textContent = selectedLi.querySelector('h3')?.textContent || this.options[this.selectedIndex].textContent;
                }
            }
        });
    }
    
    renderReportTable();
    renderReportPagination();
}

// Render report table with pagination
function renderReportTable() {
    const tbody = document.getElementById('reportDataTableBody');
    if (!tbody) return;
    
    const startIndex = reportPerPage === 'all' ? 0 : (reportCurrentPage - 1) * reportPerPage;
    const endIndex = reportPerPage === 'all' ? reportDataArray.length : startIndex + reportPerPage;
    const currentData = reportDataArray.slice(startIndex, endIndex);
    
    let html = '';
    currentData.forEach(row => {
        const date = new Date(row.date);
        const formattedDate = date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
        
        html += '<tr>';
        html += `<td><strong>${formattedDate}</strong></td>`;
        
        if (reportTypeStr === 'appointments') {
            html += `<td>${parseInt(row.total_appointments || 0).toLocaleString()}</td>`;
            html += `<td><span class="badge bg-success">${parseInt(row.completed || 0).toLocaleString()}</span></td>`;
            html += `<td><span class="badge bg-danger">${parseInt(row.missed || 0).toLocaleString()}</span></td>`;
        } else if (reportTypeStr === 'revenue') {
            html += `<td><strong>${parseFloat(row.daily_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} EGP</strong></td>`;
            html += `<td>${parseInt(row.transactions || 0).toLocaleString()}</td>`;
            html += `<td>${parseFloat(row.discounts || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} EGP</td>`;
        } else if (reportTypeStr === 'patients') {
            html += `<td>${parseInt(row.new_patients || 0).toLocaleString()}</td>`;
            html += `<td><span class="badge bg-primary">${parseInt(row.male || 0).toLocaleString()}</span></td>`;
            html += `<td><span class="badge bg-info">${parseInt(row.female || 0).toLocaleString()}</span></td>`;
        } else if (reportTypeStr === 'medical_prescriptions') {
            html += `<td>${parseInt(row.total_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.appointments_with_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.patients_count || 0).toLocaleString()}</td>`;
            const drugsList = (row.drugs_list || '').substring(0, 50);
            html += `<td><small>${drugsList}${(row.drugs_list || '').length > 50 ? '...' : ''}</small></td>`;
        } else if (reportTypeStr === 'glasses_prescriptions') {
            html += `<td>${parseInt(row.total_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.appointments_with_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.patients_count || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.with_lens_type || 0).toLocaleString()}</td>`;
        }
        
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

// Render pagination
function renderReportPagination() {
    const paginationNav = document.getElementById('reportPaginationNav');
    const paginationList = document.getElementById('reportPaginationList');
    
    if (!paginationNav || !paginationList) return;
    
    if (reportPerPage === 'all' || reportDataArray.length <= reportPerPage) {
        paginationNav.style.display = 'none';
        paginationList.innerHTML = '';
        return;
    }
    
    const totalPages = Math.ceil(reportDataArray.length / reportPerPage);
    
    if (totalPages <= 1) {
        paginationNav.style.display = 'none';
        paginationList.innerHTML = '';
        return;
    }
    
    paginationNav.style.display = 'block';
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${reportCurrentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${reportCurrentPage - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, reportCurrentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === reportCurrentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${totalPages}); return false;">${totalPages}</a></li>`;
    }
    
    // Next button
    html += `
        <li class="page-item ${reportCurrentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${reportCurrentPage + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationList.innerHTML = html;
}

// Change report page
function changeReportPage(page) {
    const totalPages = Math.ceil(reportDataArray.length / reportPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    reportCurrentPage = page;
    renderReportTable();
    renderReportPagination();
    
    // Scroll the table top just below the fixed header so the first row shows
    const _rt = document.getElementById('reportDataTable');
    const _rtCard = (_rt && _rt.closest('.card')) || _rt;
    if (window.scrollListToTop) window.scrollListToTop(_rtCard);
    else if (_rtCard) _rtCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Make function global
window.changeReportPage = changeReportPage;

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeReportPagination();
    });
} else {
    initializeReportPagination();
}

// PDF Export Functionality
// Make exportToPDF function global
window.exportToPDF = async function exportToPDF() {
    const btn = document.getElementById('exportPdfBtn');
    const originalText = btn.innerHTML;
    
    // Store original display states of controls
    const hiddenElements = [];
    
    try {
        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating PDF...';
        
        // Hide all controls (dropdowns, buttons, etc.) before export
        const controlsToHide = document.querySelectorAll('select, button, .quick-date-btn, .card-header .d-flex');
        controlsToHide.forEach(el => {
            if (el.style.display !== 'none') {
                hiddenElements.push({ element: el, display: el.style.display });
                el.style.display = 'none';
            }
        });
        
        // Wait for charts to be fully rendered
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 10;
        const contentWidth = pageWidth - (margin * 2);
        let yPosition = margin;
        
        // Helper function to add a new page if needed
        function checkPageBreak(neededHeight) {
            if (yPosition + neededHeight > pageHeight - margin) {
                pdf.addPage();
                yPosition = margin;
                return true;
            }
            return false;
        }
        
        // Add title
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        const reportTypeTitle = window.REPORTS_CONFIG.reportType === 'appointments' ? 'Appointments' : window.REPORTS_CONFIG.reportType === 'revenue' ? 'Revenue' : window.REPORTS_CONFIG.reportType === 'patients' ? 'Patients' : window.REPORTS_CONFIG.reportType === 'medical_prescriptions' ? 'Medical Prescriptions' : window.REPORTS_CONFIG.reportType === 'glasses_prescriptions' ? 'Glasses Prescriptions' : window.REPORTS_CONFIG.reportType === 'drugs' ? 'Drug' : 'Report';
        pdf.text(reportTypeTitle + ' Report', pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 10;
        
        // Add date range
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'normal');
        const dateRange = window.REPORTS_CONFIG.startDate + ' - ' + window.REPORTS_CONFIG.endDate;
        pdf.text('Date Range: ' + dateRange, pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 10;
        
        pdf.text('Generated: ' + new Date().toLocaleString(), pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 15;

        // ===== Drug Reports use a bespoke layout. The generic selectors below
        // (.stats-grid summary, #chartsSection, #reportDataTable) intentionally
        // don't match this report type, so without this branch the PDF came out
        // empty (title + footer only). =====
        if (window.REPORTS_CONFIG.reportType === 'drugs') {
            const cfg = window.REPORTS_CONFIG;

            // NOTE: html2canvas (1.4.1) throws on the theme's modern CSS color()
            // function, so we deliberately avoid DOM capture here. KPIs are drawn
            // as text, charts are exported via Chart.js' own toBase64Image(), and
            // tables are rendered manually below.
            const addChartImage = (chart, titleText) => {
                if (!chart) return;
                try {
                    const data = chart.toBase64Image('image/png', 1);
                    const w = chart.width || 600;
                    const h = chart.height || 300;
                    let imgW = contentWidth;
                    let imgH = (h * imgW) / w;
                    if (imgH > 95) { imgH = 95; imgW = (w * imgH) / h; }
                    checkPageBreak(imgH + 10);
                    pdf.setFontSize(12);
                    pdf.setFont('helvetica', 'bold');
                    pdf.text(titleText, margin, yPosition);
                    yPosition += 6;
                    pdf.addImage(data, 'PNG', margin, yPosition, imgW, imgH);
                    yPosition += imgH + 8;
                } catch (e) {
                    // If a chart can't be serialised, skip it rather than aborting.
                }
            };

            // Draw a paginated data table from arrays (manual = no clipping on long lists).
            const drawDataTable = (headers, colWidths, rows) => {
                const headerHeight = 9;
                const rowHeight = 7;
                const drawHeader = (y) => {
                    pdf.setFillColor(51, 51, 51);
                    pdf.rect(margin, y - 5, contentWidth, headerHeight, 'F');
                    pdf.setTextColor(255, 255, 255);
                    pdf.setFontSize(9);
                    pdf.setFont('helvetica', 'bold');
                    let x = margin + 1;
                    headers.forEach((h, idx) => { pdf.text(String(h), x, y); x += colWidths[idx]; });
                    pdf.setTextColor(0, 0, 0);
                    return y + headerHeight;
                };
                checkPageBreak(headerHeight + rowHeight * 2);
                yPosition = drawHeader(yPosition);
                pdf.setFontSize(8);
                pdf.setFont('helvetica', 'normal');
                rows.forEach(cells => {
                    if (yPosition + rowHeight > pageHeight - 15) {
                        pdf.addPage();
                        yPosition = margin;
                        yPosition = drawHeader(yPosition);
                        pdf.setFontSize(8);
                        pdf.setFont('helvetica', 'normal');
                    }
                    let x = margin + 1;
                    cells.forEach((c, idx) => {
                        let txt = String(c ?? '');
                        const maxChars = Math.max(4, Math.floor(colWidths[idx] / 1.7));
                        if (txt.length > maxChars) txt = txt.substring(0, maxChars - 1) + '…';
                        pdf.text(txt, x, yPosition);
                        x += colWidths[idx];
                    });
                    pdf.setDrawColor(220, 220, 220);
                    pdf.line(margin, yPosition + 2, pageWidth - margin, yPosition + 2);
                    yPosition += rowHeight;
                });
                yPosition += 6;
            };

            const drugRows = cfg.reportData || [];
            const companyStats = cfg.drugCompanyStats || [];
            const appliedFilters = cfg.drugFilters || {};

            // Active filters note
            const filterParts = [];
            if (appliedFilters.company) filterParts.push('Company: ' + appliedFilters.company);
            if (appliedFilters.category) filterParts.push('Category: ' + appliedFilters.category);
            if (appliedFilters.route) filterParts.push('Route: ' + appliedFilters.route);
            if (appliedFilters.continuation_window && appliedFilters.continuation_window !== 90) {
                filterParts.push('Continuation window: ' + appliedFilters.continuation_window + 'd');
            }
            if (filterParts.length) {
                pdf.setFontSize(9); pdf.setFont('helvetica', 'italic');
                pdf.text('Filters: ' + filterParts.join(' | '), margin, yPosition);
                yPosition += 8;
            }

            // 1) KPI summary (computed + drawn as text)
            const kTotal = drugRows.reduce((s, d) => s + parseInt(d.total_count || 0), 0);
            const kCont = drugRows.reduce((s, d) => s + parseInt(d.continuation_count || 0), 0);
            const kNew = drugRows.reduce((s, d) => s + parseInt(d.new_count || 0), 0);
            const kEst = drugRows.reduce((s, d) => s + parseFloat(d.estimated_units || 0), 0);
            const kEstRx = drugRows.reduce((s, d) => s + parseInt(d.estimated_units_rx_count || 0), 0);
            const kEstTpl = drugRows.reduce((s, d) => s + parseInt(d.estimated_units_template_count || 0), 0);
            const kRate = kTotal > 0 ? (kCont * 100 / kTotal).toFixed(1) : '0';
            const kWindow = appliedFilters.continuation_window || 90;
            pdf.setFontSize(14); pdf.setFont('helvetica', 'bold');
            pdf.text('Summary', margin, yPosition); yPosition += 8;
            pdf.setFontSize(11); pdf.setFont('helvetica', 'normal');
            const summaryLines = [
                ['Prescription writes', kTotal.toLocaleString()],
                ['Unique drugs', drugRows.length.toLocaleString()],
                ['Companies', companyStats.length.toLocaleString()],
                ['Continuation rate (' + kWindow + 'd)', kRate + '%  (' + kNew.toLocaleString() + ' new starts)']
            ];
            if (kEst > 0) {
                let estNote = kEst.toLocaleString(undefined, { maximumFractionDigits: 1 }) + ' (estimated)';
                if (kEstRx > 0 || kEstTpl > 0) {
                    estNote += ' — ' + kEstRx + ' Rx';
                    if (kEstTpl > 0) estNote += ', ' + kEstTpl + ' template';
                }
                summaryLines.push(['Est. dispensed units', estNote]);
            }
            summaryLines.forEach(k => {
                pdf.text(k[0] + ':', margin, yPosition);
                pdf.text(String(k[1]), margin + 60, yPosition);
                yPosition += 7;
            });
            yPosition += 6;

            // 2) Charts via Chart.js native export
            addChartImage(window.chartInstances && window.chartInstances.drugTrendChart, 'Monthly Trend — Top 5 Drugs');
            addChartImage(window.chartInstances && window.chartInstances.drugsByCompanyChart, 'Demand by Company');
            addChartImage(window.chartInstances && window.chartInstances.topDrugsChart, 'Most Prescribed Drugs (new vs continuation)');

            // 3) Demand-by-company table
            if (companyStats.length) {
                checkPageBreak(20);
                pdf.setFontSize(14); pdf.setFont('helvetica', 'bold');
                pdf.text('Demand by Company', margin, yPosition); yPosition += 8;
                drawDataTable(
                    ['Company', 'Writes', 'Drugs'],
                    [120, 35, 30],
                    companyStats.map(c => [
                        c.company,
                        parseInt(c.total_count || 0).toLocaleString(),
                        parseInt(c.drug_count || 0).toLocaleString()
                    ])
                );
            }

            // 4) Per-drug table
            if (drugRows.length) {
                checkPageBreak(20);
                pdf.setFontSize(14); pdf.setFont('helvetica', 'bold');
                pdf.text('Most Prescribed Drugs', margin, yPosition); yPosition += 8;
                drawDataTable(
                    ['#', 'Drug', 'Co.', 'Wr', 'Pt', 'New', 'Ct', 'Ct%', 'EstU'],
                    [8, 42, 32, 14, 12, 12, 12, 12, 16],
                    drugRows.map((d, i) => [
                        i + 1,
                        d.drug_name || '',
                        d.company || '-',
                        parseInt(d.total_count || 0).toLocaleString(),
                        parseInt(d.patient_count || 0).toLocaleString(),
                        parseInt(d.new_count || 0).toLocaleString(),
                        parseInt(d.continuation_count || 0).toLocaleString(),
                        (d.continuation_rate ?? 0) + '%',
                        d.estimated_units != null ? parseFloat(d.estimated_units).toFixed(1) : '—'
                    ])
                );
            }

            // 5) Regimen breakdown (written dose combos)
            const regimenRows = cfg.drugRegimenBreakdown || [];
            if (regimenRows.length) {
                checkPageBreak(20);
                pdf.setFontSize(14); pdf.setFont('helvetica', 'bold');
                pdf.text('Dose Regimen Breakdown', margin, yPosition); yPosition += 8;
                drawDataTable(
                    ['Drug', 'Dose', 'Freq', 'Dur', 'Wr', 'Est/Rx'],
                    [38, 22, 28, 22, 12, 18],
                    regimenRows.map(rb => [
                        rb.drug_name || '',
                        rb.dose || '—',
                        rb.frequency || '—',
                        rb.duration || '—',
                        parseInt(rb.write_count || 0).toLocaleString(),
                        rb.estimated_units != null ? parseFloat(rb.estimated_units).toFixed(1) : '—'
                    ])
                );
            }
        } else if (window.REPORTS_CONFIG.reportType !== 'drugs') {
        // --- generic report types only (drugs handled above) ---
        
        // Export Summary Statistics Card
        const summaryCard = document.querySelector('.stats-grid')?.closest('.card');
        if (summaryCard) {
            checkPageBreak(60);
            
            // Add section title as text
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'bold');
            const summaryTitle = summaryCard.querySelector('.card-header h5')?.textContent?.trim() || 'Summary Statistics';
            pdf.text(summaryTitle, margin, yPosition);
            yPosition += 8;
            
            const summaryCanvas = await html2canvas(summaryCard.querySelector('.card-body'), {
                backgroundColor: null,
                scale: 2,
                logging: false,
                useCORS: true,
                removeContainer: false
            });
            const summaryImg = summaryCanvas.toDataURL('image/png');
            const imgWidth = contentWidth;
            const imgHeight = (summaryCanvas.height * imgWidth) / summaryCanvas.width;
            checkPageBreak(imgHeight);
            pdf.addImage(summaryImg, 'PNG', margin, yPosition, imgWidth, imgHeight);
            yPosition += imgHeight + 10;
        }
        
        // Export Charts
        const chartsSection = document.getElementById('chartsSection');
        if (chartsSection) {
            // Add section title
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Visual Analytics', margin, yPosition);
            yPosition += 10;
            
            // Find all chart containers
            const chartContainers = chartsSection.querySelectorAll('canvas');
            
            for (let i = 0; i < chartContainers.length; i++) {
                const chartCard = chartContainers[i].closest('.card');
                if (chartCard) {
                    checkPageBreak(80);
                    
                    // Get chart title from card header
                    const chartTitle = chartCard.querySelector('.card-header h6')?.textContent?.trim() || 'Chart';
                    pdf.setFontSize(12);
                    pdf.setFont('helvetica', 'bold');
                    pdf.text(chartTitle, margin, yPosition);
                    yPosition += 6;
                    
                    // Capture only the chart body (canvas area)
                    const chartBody = chartCard.querySelector('.card-body');
                    if (chartBody) {
                        const chartCanvas = await html2canvas(chartBody, {
                            backgroundColor: null,
                            scale: 2,
                            logging: false,
                            useCORS: true,
                            allowTaint: true,
                            removeContainer: false
                        });
                        
                        const chartImg = chartCanvas.toDataURL('image/png');
                        const imgWidth = contentWidth;
                        const imgHeight = (chartCanvas.height * imgWidth) / chartCanvas.width;
                        
                        checkPageBreak(imgHeight);
                        pdf.addImage(chartImg, 'PNG', margin, yPosition, imgWidth, imgHeight);
                        yPosition += imgHeight + 10;
                    }
                }
            }
        }
        
        // Export Detailed Table - Build table manually to include all data and repeated headers
        const tableCard = document.getElementById('reportDataTable')?.closest('.card');
        if (tableCard) {
            checkPageBreak(60);
            
            // Add table title as text
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Detailed Report Data', margin, yPosition);
            yPosition += 8;
            
            // Get all data (not paginated) and report type
            const allData = window.REPORTS_CONFIG ? (window.REPORTS_CONFIG.reportData || []) : [];
            const reportTypeStr = window.REPORTS_CONFIG ? (window.REPORTS_CONFIG.reportType || '') : '';
            if (allData && allData.length > 0) {
                // Get table headers
                const tableHeaders = [];
                const thead = tableCard.querySelector('thead tr');
                if (thead) {
                    thead.querySelectorAll('th').forEach(th => {
                        tableHeaders.push(th.textContent.trim());
                    });
                }
                
                // Calculate row height
                const rowHeight = 8;
                const headerHeight = 10;
                const footerHeight = 15; // Reserve space for footer
                const maxHeightPerPage = pageHeight - margin - yPosition - footerHeight;
                let currentPageStartRow = 0;
                
                // Function to add footer to current page
                function addFooter() {
                    const footerY = pageHeight - 10;
                    pdf.setFontSize(8);
                    pdf.setFont('helvetica', 'normal');
                    const clinicName = window.REPORTS_CONFIG.clinicName || 'Clinic';
                    const doctorName = window.REPORTS_CONFIG.doctorName || 'Doctor';
                    pdf.text(clinicName, margin, footerY);
                    pdf.text(`Exported by: ${doctorName}`, pageWidth - margin, footerY, { align: 'right' });
                }
                
                // Function to draw table header
                function drawTableHeader(startY) {
                    pdf.setFontSize(10);
                    pdf.setFont('helvetica', 'bold');
                    let xPos = margin;
                    const colWidths = [];
                    
                    // Calculate column widths based on content
                    if (reportTypeStr === 'appointments') {
                        colWidths.push(40, 35, 30, 30);
                    } else if (reportTypeStr === 'revenue') {
                        colWidths.push(40, 40, 35, 30);
                    } else if (reportTypeStr === 'patients') {
                        colWidths.push(40, 35, 30, 30);
                    } else if (reportTypeStr === 'medical_prescriptions') {
                        colWidths.push(30, 30, 25, 60);
                    } else if (reportTypeStr === 'glasses_prescriptions') {
                        colWidths.push(30, 30, 25, 30);
                    } else {
                        // Default equal widths
                        const defaultWidth = contentWidth / tableHeaders.length;
                        tableHeaders.forEach(() => colWidths.push(defaultWidth));
                    }
                    
                    // Draw header background and text
                    pdf.setFillColor(51, 51, 51);
                    pdf.rect(margin, startY - 5, contentWidth, headerHeight, 'F');
                    pdf.setTextColor(255, 255, 255);
                    
                    tableHeaders.forEach((header, idx) => {
                        if (xPos + colWidths[idx] <= pageWidth - margin) {
                            pdf.text(header, xPos, startY);
                            xPos += colWidths[idx];
                        }
                    });
                    
                    pdf.setTextColor(0, 0, 0);
                    return startY + headerHeight;
                }
                
                // Draw first header
                yPosition = drawTableHeader(yPosition);
                
                // Draw table rows
                pdf.setFontSize(9);
                pdf.setFont('helvetica', 'normal');
                
                for (let i = 0; i < allData.length; i++) {
                    const row = allData[i];
                    
                    // Check if we need a new page
                    if (yPosition + rowHeight > pageHeight - footerHeight) {
                        addFooter();
                        pdf.addPage();
                        yPosition = margin;
                        // Draw header again on new page
                        yPosition = drawTableHeader(yPosition);
                    }
                    
                    // Format date
                    const date = new Date(row.date);
                    const formattedDate = date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric',
                        year: 'numeric'
                    });
                    
                    let xPos = margin;
                    const colWidths = [];
                    
                    if (reportTypeStr === 'appointments') {
                        colWidths.push(40, 35, 30, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.total_appointments || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.completed || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.missed || 0).toLocaleString(), xPos, yPosition);
                    } else if (reportTypeStr === 'revenue') {
                        colWidths.push(40, 40, 35, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseFloat(row.daily_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' EGP', xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.transactions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseFloat(row.discounts || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' EGP', xPos, yPosition);
                    } else if (reportTypeStr === 'patients') {
                        colWidths.push(40, 35, 30, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.new_patients || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.male || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.female || 0).toLocaleString(), xPos, yPosition);
                    } else if (reportTypeStr === 'medical_prescriptions') {
                        colWidths.push(30, 30, 25, 60);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.total_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.appointments_with_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.patients_count || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[3];
                        const drugsList = (row.drugs_list || '').substring(0, 40);
                        pdf.text(drugsList + ((row.drugs_list || '').length > 40 ? '...' : ''), xPos, yPosition);
                    } else if (reportTypeStr === 'glasses_prescriptions') {
                        colWidths.push(30, 30, 25, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.total_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.appointments_with_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.patients_count || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[3];
                        pdf.text(parseInt(row.with_lens_type || 0).toLocaleString(), xPos, yPosition);
                    }
                    
                    // Draw row border
                    pdf.setDrawColor(200, 200, 200);
                    pdf.line(margin, yPosition + 2, pageWidth - margin, yPosition + 2);
                    
                    yPosition += rowHeight;
                }
                
                // Add footer to last page after all rows are drawn
                addFooter();
            }
        }
        } // end generic (non-drugs) PDF export
        
        // Ensure footer is added to all pages (in case some pages were added without footer)
        const totalPages = pdf.internal.getNumberOfPages();
        const clinicName = window.REPORTS_CONFIG.clinicName || 'Clinic';
        const doctorName = window.REPORTS_CONFIG.doctorName || 'Doctor';
        
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            const footerY = pageHeight - 10;
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'normal');
            pdf.text(clinicName, margin, footerY);
            pdf.text(`Exported by: ${doctorName}`, pageWidth - margin, footerY, { align: 'right' });
            pdf.text(`Page ${i} of ${totalPages}`, pageWidth / 2, footerY, { align: 'center' });
        }
        
        // Generate filename
        const reportType = window.REPORTS_CONFIG.reportType || 'Report';
        const filename = `${reportType}_report_${window.REPORTS_CONFIG.startDate}_to_${window.REPORTS_CONFIG.endDate}.pdf`;
        
        // Save PDF
        pdf.save(filename);
        
        // Restore hidden elements
        hiddenElements.forEach(item => {
            item.element.style.display = item.display || '';
        });
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = originalText;
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        
        // Restore hidden elements even on error
        hiddenElements.forEach(item => {
            item.element.style.display = item.display || '';
        });
        
        alert('Error generating PDF. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

// Initialize PDF export button
document.addEventListener('DOMContentLoaded', function() {
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function() {
            if (typeof window.exportToPDF === 'function') {
                window.exportToPDF();
            } else {
                console.error('exportToPDF function is not defined');
                alert('PDF export function is not available. Please refresh the page.');
            }
        });
    }
});
