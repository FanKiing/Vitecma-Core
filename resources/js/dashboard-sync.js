import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { createReconciler } from './reconcile';

const root = document.querySelector('[data-livewire-inspections]');
if (root) {
    const wire = () => Livewire.find(root.getAttribute('wire:id'));
    const initialTick = performance.now();
    const initialTime = Number(root.dataset.serverTime);
    window.dashboardServerNow = () => initialTime + performance.now() - initialTick;
    let connected = false;
    let selected = new Set();
    const status = document.getElementById('realtime-status');
    const displayStatus = (state, text) => { if (status) { status.dataset.state = state; status.textContent = text; } };
    const reconciler = createReconciler({
        load: () => wire().refreshState(),
        apply: () => {}, // Livewire alone morphs the rows and pagination.
        onError: () => displayStatus('error', 'Synchronisation interrompue — nouvelle tentative automatique'),
    });
    window.refreshDashboard = () => reconciler.refresh();
    Livewire.hook('request', ({ options }) => {
        const socket = window.Echo?.socketId();
        if (socket) options.headers['X-Socket-ID'] = socket;
    });
    Livewire.hook('morph', ({ el }) => {
        if (el !== root) return;
        selected = new Set(Array.from(root.querySelectorAll('.row-checkbox:checked'), box => box.dataset.id));
    });
    const apply = (data) => requestAnimationFrame(() => {
        const tick = performance.now();
        window.dashboardServerNow = () => data.serverTime + performance.now() - tick;
        root.querySelectorAll('.row-checkbox').forEach(box => { box.checked = selected.has(box.dataset.id) && box.dataset.status === 'libre'; });
        window.renderInspectionState(data);
        document.querySelectorAll('[data-brand-name]').forEach(element => { element.textContent = data.branding.siteName; });
        document.querySelectorAll('[data-brand-logo]').forEach(element => { element.src = data.branding.siteLogoUrl; });
        document.querySelectorAll('link[rel*="icon"]').forEach(element => { element.href = data.branding.siteIconUrl; });
        document.title = `${data.branding.siteName} - ${root.dataset.scope === 'archive' ? 'Archives' : root.dataset.scope === 'trash' ? 'Corbeille' : 'Control Panel'}`;
        window.dispatchEvent(new CustomEvent('branding-refreshed', { detail: data.branding }));
        displayStatus(connected ? 'live' : 'fallback', connected ? 'Temps réel' : 'Synchronisation de secours');
    });
    window.addEventListener('inspection-state', event => apply(event.detail.state));
    document.addEventListener('livewire:initialized', () => apply(JSON.parse(root.dataset.initialState)));

    // The existing dialogs call explicit Livewire actions; no mutation Fetch competes with morphing.
    window.inspectionCommand = async (url, method, payload = {}) => {
        const path = new URL(url, location.origin).pathname;
        let operation, id = null;
        if (path === '/inspections' && method === 'POST') operation = 'create';
        else if (path === '/inspections/bulk-delete' && method === 'DELETE') operation = 'bulk-delete';
        else if (path === '/inspections/trash/empty' && method === 'DELETE') operation = 'empty-trash';
        else if (path === '/maintenance/toggle' && method === 'POST') operation = 'maintenance';
        else {
            const match = path.match(/^\/inspections\/(\d+)(?:\/(status|revert|restore|permanent-delete))?$/);
            if (!match) throw new Error('Opération non prise en charge.');
            id = Number(match[1]);
            operation = match[2] ? ({ status: 'status', revert: 'revert', restore: 'restore', 'permanent-delete': 'force-delete' })[match[2]] : method === 'DELETE' ? 'delete' : 'edit';
        }
        const result = await wire().command(operation, id, payload || {});
        if (!result?.success) throw new Error(result?.message || 'La modification a échoué.');
        return result;
    };
    const echo = window.Echo;
    if (echo) {
        // Echo listeners declared on the PHP component refresh it through Livewire.
        const inspections = echo.private('inspections-channel');
        const maintenance = echo.private('maintenance-channel');
        const subscribed = () => {
            connected = Boolean(inspections.subscription?.subscribed && maintenance.subscription?.subscribed);
            void window.refreshDashboard();
        };
        inspections.subscribed(subscribed).error(() => displayStatus('error', 'Connexion temps réel indisponible'));
        maintenance.subscribed(subscribed).error(() => displayStatus('error', 'Connexion temps réel indisponible'));
        echo.connector.pusher.connection.bind('state_change', ({ current }) => {
            if (current !== 'connected') { connected = false; displayStatus('reconnecting', 'Reconnexion…'); }
        });
    }
    document.addEventListener('visibilitychange', () => { if (!document.hidden) void window.refreshDashboard(); });
    window.addEventListener('online', window.refreshDashboard);
    window.addEventListener('pageshow', event => { if (event.persisted) void window.refreshDashboard(); });
}
