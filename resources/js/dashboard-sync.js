import { createReconciler } from './reconcile';

const table = document.querySelector('[data-dashboard-sync]');
if (table) {
    const initialTime = Number(table.dataset.serverTime);
    const initialTick = performance.now();
    if (initialTime) window.dashboardServerNow = () => initialTime + performance.now() - initialTick;
    let connected = false;
    const status = document.getElementById('realtime-status');
    const displayStatus = (state, text) => {
        if (status) { status.dataset.state = state; status.textContent = text; }
    };
    const reconciler = createReconciler({
        async load() {
            const url = new URL(table.dataset.dashboardSync, location.origin);
            url.searchParams.set('page', new URL(location.href).searchParams.get('page') || '1');
            const before = performance.now();
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin', cache: 'no-store', signal: AbortSignal.timeout(10000),
            });
            if (!response.ok) throw new Error(`Snapshot HTTP ${response.status}`);
            const data = await response.json();
            const after = performance.now();
            data.clockAnchor = { time: data.serverTime + (after - before) / 2, at: after };
            return data;
        },
        apply(data) {
            window.dashboardServerNow = () => data.clockAnchor.time + (performance.now() - data.clockAnchor.at);
            window.renderDashboardSnapshot(data);
            document.querySelectorAll('[data-brand-name]').forEach(element => { element.textContent = data.branding.siteName; });
            document.querySelectorAll('[data-brand-logo]').forEach(element => { element.src = data.branding.siteLogoUrl; });
            document.querySelectorAll('link[rel*="icon"]').forEach(element => { element.href = data.branding.siteIconUrl; });
            const title = table.id === 'archive-table-body' ? 'Archives' : table.id === 'trash-table-body' ? 'Corbeille' : 'Control Panel';
            document.title = `${data.branding.siteName} - ${title}`;
            window.dispatchEvent(new CustomEvent('branding-refreshed', { detail: data.branding }));
            const current = new URL(location.href);
            if ((Number(current.searchParams.get('page')) || 1) !== data.page) {
                if (data.page === 1) current.searchParams.delete('page');
                else current.searchParams.set('page', data.page);
                history.replaceState(null, '', current);
            }
            displayStatus(connected ? 'live' : 'fallback', connected ? 'Temps réel' : 'Synchronisation de secours');
        },
        onError() { displayStatus('error', 'Synchronisation interrompue — nouvelle tentative automatique'); },
    });
    window.refreshDashboard = () => reconciler.refresh();

    const echo = window.Echo;
    if (echo) {
        const inspections = echo.private('inspections-channel');
        const maintenance = echo.private('maintenance-channel');
        inspections.listen('.inspection.changed', window.refreshDashboard)
            .listen('.dashboard.changed', window.refreshDashboard);
        maintenance.listen('.maintenance.changed', window.refreshDashboard);
        const subscribed = () => {
            connected = Boolean(inspections.subscription?.subscribed && maintenance.subscription?.subscribed);
            void window.refreshDashboard();
        };
        inspections.subscribed(subscribed).error(() => displayStatus('error', 'Connexion temps réel indisponible'));
        maintenance.subscribed(subscribed).error(() => displayStatus('error', 'Connexion temps réel indisponible'));
        echo.connector.pusher.connection.bind('state_change', ({ current }) => {
            if (current !== 'connected') {
                connected = false;
                displayStatus('reconnecting', 'Reconnexion…');
            }
        });
    }

    // Reconcile missed events after reconnect/resume, with a bounded HTTP fallback.
    void window.refreshDashboard();
    const poll = setInterval(() => { if (!document.hidden) void window.refreshDashboard(); }, 30000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) void window.refreshDashboard(); });
    window.addEventListener('online', window.refreshDashboard);
    window.addEventListener('pageshow', event => { if (event.persisted) void window.refreshDashboard(); });
    window.addEventListener('pagehide', event => { if (!event.persisted) clearInterval(poll); });
}
