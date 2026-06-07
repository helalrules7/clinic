// Secretary payments page — mini-stat cards + auto-refresh
(function (global) {
    'use strict';

    var refreshInterval = null;
    var POLL_MS = 30000;

    global.SEC_PAYMENTS_MINI_CARDS = [
        { chartId: 'chartPayOpening', trendId: 'trendPayOpening', trendKey: 'opening', statKey: 'opening_balance', valueId: 'secPayStatOpening', format: 'money2' },
        { chartId: 'chartPayReceived', trendId: 'trendPayReceived', trendKey: 'received', statKey: 'total_received', valueId: 'secPayStatReceived', format: 'money2' },
        { chartId: 'chartPayExpenses', trendId: 'trendPayExpenses', trendKey: 'expenses', statKey: 'total_expenses', valueId: 'secPayStatExpenses', format: 'money2', invert: true },
        { chartId: 'chartPayCurrent', trendId: 'trendPayCurrent', trendKey: 'current', statKey: 'current_balance', valueId: 'secPayStatCurrent', format: 'money2' },
        { chartId: 'chartPayTx', trendId: 'trendPayTx', trendKey: 'transactions', statKey: 'transactions_count', valueId: 'secPayStatTx' }
    ];

    function balanceToStats(balance) {
        if (!balance) return {};
        return {
            opening_balance: balance.opening_balance || 0,
            total_received: balance.total_received || 0,
            total_expenses: balance.total_expenses || 0,
            current_balance: balance.current_balance || 0,
            transactions_count: balance.transactions_count || 0
        };
    }

    function updatePaymentTypes(paymentTypes) {
        if (!paymentTypes) return;
        Object.keys(paymentTypes).forEach(function (type) {
            var card = document.querySelector('[data-payment-type="' + type + '"]');
            if (!card) return;
            var amountEl = card.querySelector('.payment-type-amount');
            if (amountEl) {
                var v = Number(paymentTypes[type] || 0);
                amountEl.textContent = v.toLocaleString('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        });
    }

    function updatePaymentsStats(balance, trends, trendDeltas) {
        if (!balance || !global.secMiniStats) return;
        var payload = balanceToStats(balance);
        global.secMiniStats.updateStatValues(global.SEC_PAYMENTS_MINI_CARDS, payload);
        var t = trends || global.secMiniStats.readJsonScript('secPaymentsTrends') || {};
        var d = trendDeltas || global.secMiniStats.readJsonScript('secPaymentsTrendDeltas') || {};
        global.secMiniStats.refresh(global.SEC_PAYMENTS_MINI_CARDS, t, payload, d, {
            trendsId: 'secPaymentsTrends',
            statsId: 'secPaymentsStatsInitial',
            deltasId: 'secPaymentsTrendDeltas'
        });
    }

    function initPaymentsMiniStats() {
        if (!global.secMiniStats) return;
        global.secMiniStats.init(global.SEC_PAYMENTS_MINI_CARDS, {
            trendsId: 'secPaymentsTrends',
            statsId: 'secPaymentsStatsInitial',
            deltasId: 'secPaymentsTrendDeltas'
        });
    }

    function refreshPaymentsData() {
        return fetch('/api/secretary/payments', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok || !data.data) return;
                if (data.data.dailyBalance) {
                    updatePaymentsStats(data.data.dailyBalance, data.data.trends, data.data.trendDeltas);
                }
                if (data.data.paymentTypes) {
                    updatePaymentTypes(data.data.paymentTypes);
                }
            })
            .catch(function (err) {
                console.error('Error refreshing payments data:', err);
            });
    }

    function isModalOpen() {
        return document.querySelector('.modal.show') !== null;
    }

    function getAutoRefreshState() {
        var saved = localStorage.getItem('paymentsAutoRefresh');
        return saved === null ? true : saved === 'true';
    }

    function saveAutoRefreshState(enabled) {
        localStorage.setItem('paymentsAutoRefresh', enabled ? 'true' : 'false');
    }

    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    function startAutoRefresh() {
        stopAutoRefresh();
        refreshInterval = setInterval(function () {
            if (!isModalOpen()) {
                refreshPaymentsData();
            }
        }, POLL_MS);
    }

    function toggleAutoRefresh(enabled) {
        saveAutoRefreshState(enabled);
        if (enabled) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    }

    // Backward compat — inline handlers in payments.php call this after mutations
    global.updateDashboardCards = function () {
        return refreshPaymentsData();
    };

    global.togglePaymentsAutoRefresh = toggleAutoRefresh;

    document.addEventListener('DOMContentLoaded', function () {
        initPaymentsMiniStats();

        var toggle = document.getElementById('paymentsAutoRefresh');
        var enabled = getAutoRefreshState();
        if (toggle) {
            toggle.checked = enabled;
        }
        if (enabled) {
            startAutoRefresh();
        }
    });

    global.secPaymentsPage = {
        init: initPaymentsMiniStats,
        refresh: refreshPaymentsData,
        updateStats: updatePaymentsStats,
        updatePaymentTypes: updatePaymentTypes,
        startAutoRefresh: startAutoRefresh,
        stopAutoRefresh: stopAutoRefresh
    };
})(typeof window !== 'undefined' ? window : this);
