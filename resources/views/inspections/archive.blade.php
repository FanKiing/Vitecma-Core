<!DOCTYPE html>
<html lang="fr" dir="ltr"
    x-data="{ darkMode: localStorage.getItem('dark') === 'true' }"
    x-init="
        if (darkMode) document.documentElement.classList.add('dark');
        $watch('darkMode', val => {
            localStorage.setItem('dark', val);
            val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
        });
    "
    :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitecma - Archives</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/js/app.js'])

    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uplogo.png') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .font-plate { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }

        nav.vitecma-nav {
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid #e2e8f0;
        }
        .dark nav.vitecma-nav {
            background: rgba(13,20,36,0.95);
            border-color: rgba(255,255,255,0.07);
        }

        .table-container { width:100%; overflow-x:auto; overflow-y:auto; max-height: calc(100vh - 260px); }
        .table-container::-webkit-scrollbar { width:6px; height:6px; }
        .table-container::-webkit-scrollbar-track { background:transparent; }
        .table-container::-webkit-scrollbar-thumb { background:#2563eb; border-radius:8px; }
        .dark .table-container::-webkit-scrollbar-thumb { background:#3b82f6; }
        .table-container table { width:100%; border-collapse:collapse; }
        .table-container thead { position:sticky; top:0; z-index:20; }
        .table-container thead th {
            background:#f1f5f9; border-bottom:2px solid #e2e8f0;
            padding:1.1rem 1.6rem; font-size:0.78rem; font-weight:800;
            text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap; color:#64748b;
        }
        .dark .table-container thead th { background:#0d1424; border-color:rgba(255,255,255,0.08); color:#94a3b8; }
        .table-container tbody td { padding:1.1rem 1.6rem; font-size:1.05rem; border-bottom:1px solid #e2e8f0; vertical-align:middle; line-height:1.5; }
        .dark .table-container tbody td { border-color:rgba(255,255,255,0.06); color:#e2e8f0; }
        .table-container tbody tr { transition:background 0.15s ease; }
        .table-container tbody tr:hover { background:rgba(37,99,235,0.04); }
        .dark .table-container tbody tr:hover { background:rgba(59,130,246,0.06); }

        .badge { display:inline-flex; align-items:center; gap:0.3rem; padding:0.3rem 0.85rem; font-size:0.82rem; font-weight:700; border-radius:0.375rem; text-transform:uppercase; border-width:1px; white-space:nowrap; }

        .shine-effect { position:relative; overflow:hidden; }
        .shine-effect::before { content:""; position:absolute; top:0; left:-120%; width:50%; height:100%; background:linear-gradient(120deg,transparent,rgba(255,255,255,0.25),transparent); transition:0.6s; }
        .shine-effect:hover::before { left:150%; }

        .filter-input {
            background:#f8fafc; border:1.5px solid #e2e8f0; color:#374151;
            border-radius:0.6rem; padding:0.5rem 0.9rem; font-size:0.88rem;
            font-weight:600; outline:none; transition:border-color 0.2s, box-shadow 0.2s;
            appearance:none; -webkit-appearance:none;
        }
        .filter-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.12); }
        .dark .filter-input { background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.1); color:#e2e8f0; }
        .dark .filter-input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.1); }
        .dark .filter-input option { background:#1e293b; }
        .select-wrapper { position:relative; display:inline-flex; align-items:center; }
        .select-wrapper::after { content:'▾'; position:absolute; right:0.7rem; pointer-events:none; color:#94a3b8; font-size:0.8rem; }
        .select-wrapper .filter-input { padding-right:2rem; }

        /* ── Animations ── */
        .fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script>tailwind.config = { darkMode: 'class' };</script>
</head>
<body class="bg-slate-50 dark:bg-[#0a0f1a] text-slate-800 dark:text-slate-200 min-h-screen flex flex-col transition-colors duration-300">

    <!-- NAVBAR -->
    <nav class="vitecma-nav sticky top-0 z-40 px-6 py-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600/10 dark:bg-blue-500/10 border border-blue-600/20 dark:border-blue-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div class="flex flex-col leading-tight">
                <span class="text-lg font-extrabold tracking-tight text-slate-800 dark:text-white">VITECMA</span>
                <span class="text-[0.6rem] font-medium text-slate-400 dark:text-slate-500 tracking-widest uppercase">Archives — {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 rounded-lg transition-all font-semibold text-sm border border-slate-200/80 dark:border-white/10 shine-effect">
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Tableau de bord</span>
            </a>

            <div class="h-6 w-px bg-slate-200 dark:bg-white/10 mx-1 hidden sm:block"></div>

            <button @click="darkMode = !darkMode"
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 transition-all active:scale-90 text-slate-500 dark:text-slate-400">
                <span x-show="!darkMode" class="text-xl">🌙</span>
                <span x-show="darkMode" x-cloak class="text-xl">☀️</span>
            </button>
        </div>
    </nav>

    <!-- MAIN -->
    <main class="w-full px-4 py-4 max-w-full mx-auto flex-grow">
        <div class="w-full max-w-full mx-auto bg-white dark:bg-[#111827] rounded-2xl shadow-lg border border-slate-200 dark:border-white/10 overflow-hidden">

            <!-- Card Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex flex-wrap justify-between items-center gap-3 bg-gradient-to-r from-blue-50/80 to-indigo-50/60 dark:from-blue-950/20 dark:to-indigo-950/10">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded-full bg-gradient-to-b from-blue-500 to-indigo-500"></div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight">Historique des inspections</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium tracking-wider">أرشيف الفحوصات المكتملة والمطبوعة</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 text-sm font-bold border border-blue-200/50 dark:border-blue-900/50">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span id="archive-count">{{ $inspections->total() }}</span> dossier(s)
                </span>
            </div>

            <!-- Filter Bar -->
            <div class="px-6 py-3.5 bg-slate-50 dark:bg-white/[0.02] border-b border-slate-100 dark:border-white/10 flex flex-wrap items-center gap-3">
                <div class="relative flex-grow min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" id="archive-search" placeholder="Rechercher plaque ou propriétaire…"
                           oninput="filterArchive()"
                           class="filter-input w-full pl-9 pr-3">
                </div>

                <div class="select-wrapper">
                    <select id="archive-filter-category" onchange="filterArchive()" class="filter-input min-w-[150px]">
                        <option value="">Toutes catégories</option>
                        <option value="VL">🚗 VL — Léger</option>
                        <option value="PL">🚛 PL — Lourd</option>
                    </select>
                </div>

                <div class="select-wrapper">
                    <select id="archive-filter-result" onchange="filterArchive()" class="filter-input min-w-[160px]">
                        <option value="">Tous les résultats</option>
                        <option value="favorable">✅ Favorable</option>
                        <option value="defavorable">❌ Défavorable</option>
                    </select>
                </div>

                <button onclick="resetArchiveFilters()" title="Réinitialiser"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10 rounded-lg transition-all border border-slate-200 dark:border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Effacer
                </button>

                <span id="archive-no-result" class="hidden text-xs font-semibold text-slate-400 dark:text-slate-500 italic ml-auto">Aucun résultat</span>
            </div>

            <!-- Table -->
            <div class="w-full table-container">
                <table class="w-full min-w-[1100px] text-left">
                    <thead>
                        <tr>
                            <th class="w-[18%]">Plaque d'immatriculation</th>
                            <th class="w-[20%]">Propriétaire</th>
                            <th class="w-[10%] text-center">Catégorie</th>
                            <th class="w-[14%] text-center">Résultat</th>
                            <th class="w-[16%] text-center">Technicien</th>
                            <th class="w-[22%] text-right">Date d'archivage</th>
                        </tr>
                    </thead>
                    <tbody id="archive-table-body">
                        @forelse($inspections as $inspection)
                        <tr class="archive-row transition-all duration-150 hover:shadow-sm"
                            id="row-{{ $inspection->id }}"
                            data-id="{{ $inspection->id }}"
                            data-plate="{{ strtolower($inspection->plate_number) }}"
                            data-owner="{{ strtolower($inspection->owner_name ?? '') }}"
                            data-category="{{ $inspection->category }}"
                            data-result="{{ $inspection->result ?? '' }}">

                            <td class="font-plate font-bold text-slate-700 dark:text-slate-200 tracking-wider text-lg" dir="ltr">
                                {{ str_replace('|', ' · ', $inspection->plate_number) }}
                            </td>
                            <td class="font-semibold text-slate-600 dark:text-slate-300 text-base">
                                {{ $inspection->owner_name ?? '---' }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $inspection->category === 'PL' ? 'bg-orange-50 dark:bg-orange-950/20 text-orange-600 border-orange-200 dark:border-orange-900/40' : 'bg-green-50 dark:bg-green-950/20 text-green-600 border-green-200 dark:border-green-900/40' }}">
                                    {{ $inspection->category === 'PL' ? '🚛' : '🚗' }} {{ $inspection->category }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($inspection->result === 'favorable')
                                    <span class="badge bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/40">✅ Favorable</span>
                                @elseif($inspection->result === 'defavorable')
                                    <span class="badge bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/40">❌ Défavorable</span>
                                @else
                                    <span class="text-sm font-medium text-slate-400">---</span>
                                @endif
                            </td>
                            <td class="text-center text-sm font-medium text-slate-600 dark:text-slate-300">
                                {{ $inspection->technician_name ?? '—' }}
                            </td>
                            <td class="text-base font-semibold text-slate-500 dark:text-slate-400 text-right" id="archived-at-{{ $inspection->id }}">
                                {{ $inspection->archived_at ? \Carbon\Carbon::parse($inspection->archived_at)->format('d/m/Y H:i') : '---' }}
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-state-row">
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-400 dark:text-slate-600">L'archive est vide</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02] flex justify-center [&_.pagination]:flex [&_.pagination]:items-center [&_.pagination]:gap-1 [&_.page-item_.page-link]:px-4 [&_.page-item_.page-link]:py-2 [&_.page-item_.page-link]:rounded-xl [&_.page-item_.page-link]:text-sm [&_.page-item_.page-link]:font-bold [&_.page-item_.page-link]:border [&_.page-item_.page-link]:border-slate-200 dark:[&_.page-item_.page-link]:border-gray-700 [&_.page-item_.page-link]:bg-white dark:[&_.page-item_.page-link]:bg-gray-800 [&_.page-item_.page-link]:text-gray-600 dark:[&_.page-item_.page-link]:text-gray-300 [&_.page-item_.page-link]:transition-all [&_.page-item_.page-link]:hover:bg-slate-100 dark:[&_.page-item_.page-link]:hover:bg-gray-700 [&_.page-item.active_.page-link]:bg-blue-600 [&_.page-item.active_.page-link]:border-blue-600 [&_.page-item.active_.page-link]:text-white [&_.page-item.disabled_.page-link]:opacity-40">
                {{ $inspections->links() }}
            </div>
        </div>
    </main>

    <!-- JAVASCRIPT -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // ── Filter ─────────────────────────────────────────────────
        function filterArchive() {
            const search  = document.getElementById('archive-search').value.toLowerCase().trim();
            const cat     = document.getElementById('archive-filter-category').value;
            const result  = document.getElementById('archive-filter-result').value;

            let visibleCount = 0;
            document.querySelectorAll('#archive-table-body tr.archive-row').forEach(row => {
                const plate    = (row.dataset.plate    || '').toLowerCase();
                const owner    = (row.dataset.owner    || '').toLowerCase();
                const rowCat   = row.dataset.category  || '';
                const rowResult = row.dataset.result   || '';

                const matchSearch = !search || plate.includes(search) || owner.includes(search);
                const matchCat    = !cat    || rowCat === cat;
                const matchResult = !result || rowResult === result;

                const visible = matchSearch && matchCat && matchResult;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const noResult = document.getElementById('archive-no-result');
            if (noResult) noResult.classList.toggle('hidden', visibleCount > 0);
        }

        function resetArchiveFilters() {
            document.getElementById('archive-search').value           = '';
            document.getElementById('archive-filter-category').value  = '';
            document.getElementById('archive-filter-result').value    = '';
            filterArchive();
        }

        // ── Counter ────────────────────────────────────────────────
        function updateArchiveCount(delta) {
            const el = document.getElementById('archive-count');
            if (el) el.textContent = Math.max(0, parseInt(el.textContent, 10) + delta);
        }

        // ── Row helpers ────────────────────────────────────────────
        function updateArchiveRow(row, inspection) {
            const sep  = inspection.plate_number.includes('|') ? '|' : '-';
            const parts = inspection.plate_number.split(sep).map(p => p.trim());
            const formatted = parts.length === 3 ? `${parts[0]} · ${parts[1]} · ${parts[2]}` : inspection.plate_number;

            const cells = row.getElementsByTagName('td');
            if (cells.length < 6) return;

            cells[0].textContent = formatted;
            cells[1].textContent = inspection.owner_name || '---';

            // Category badge
            const catSpan = cells[2].querySelector('span');
            if (catSpan) {
                catSpan.textContent = `${inspection.category === 'PL' ? '🚛' : '🚗'} ${inspection.category}`;
                catSpan.className   = `badge ${inspection.category === 'PL' ? 'bg-orange-50 dark:bg-orange-950/20 text-orange-600 border-orange-200 dark:border-orange-900/40' : 'bg-green-50 dark:bg-green-950/20 text-green-600 border-green-200 dark:border-green-900/40'}`;
            }

            // Result badge
            let resultHtml = '';
            if      (inspection.result === 'favorable')   resultHtml = '<span class="badge bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/40">✅ Favorable</span>';
            else if (inspection.result === 'defavorable') resultHtml = '<span class="badge bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/40">❌ Défavorable</span>';
            else                                          resultHtml = '<span class="text-sm font-medium text-slate-400">---</span>';
            cells[3].innerHTML = resultHtml;

            // Technicien
            cells[4].textContent = inspection.technician_name || '—';

            // Date
            cells[5].textContent = inspection.archived_at
                ? new Date(inspection.archived_at).toLocaleString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })
                : '---';

            // Update data attrs for filter
            row.dataset.plate    = inspection.plate_number.toLowerCase();
            row.dataset.owner    = (inspection.owner_name || '').toLowerCase();
            row.dataset.category = inspection.category;
            row.dataset.result   = inspection.result || '';
        }

        function appendArchiveRow(inspection) {
            const tbody = document.getElementById('archive-table-body');
            if (!tbody) return;

            const empty = document.getElementById('empty-state-row');
            if (empty) empty.remove();

            const sep  = inspection.plate_number.includes('|') ? '|' : '-';
            const parts = inspection.plate_number.split(sep).map(p => p.trim());
            const formatted = parts.length === 3 ? `${parts[0]} · ${parts[1]} · ${parts[2]}` : inspection.plate_number;

            const catClass = inspection.category === 'PL'
                ? 'bg-orange-50 dark:bg-orange-950/20 text-orange-600 border-orange-200 dark:border-orange-900/40'
                : 'bg-green-50 dark:bg-green-950/20 text-green-600 border-green-200 dark:border-green-900/40';

            let resultHtml = '';
            if      (inspection.result === 'favorable')   resultHtml = '<span class="badge bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/40">✅ Favorable</span>';
            else if (inspection.result === 'defavorable') resultHtml = '<span class="badge bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/40">❌ Défavorable</span>';
            else                                          resultHtml = '<span class="text-sm font-medium text-slate-400">---</span>';

            const archivedAt = inspection.archived_at
                ? new Date(inspection.archived_at).toLocaleString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })
                : '---';

            const newRow = document.createElement('tr');
            newRow.className   = 'archive-row transition-all duration-150 hover:shadow-sm';
            newRow.id          = `row-${inspection.id}`;
            newRow.dataset.id  = inspection.id;
            newRow.dataset.plate    = inspection.plate_number.toLowerCase();
            newRow.dataset.owner    = (inspection.owner_name || '').toLowerCase();
            newRow.dataset.category = inspection.category;
            newRow.dataset.result   = inspection.result || '';

            newRow.innerHTML = `
                <td class="font-plate font-bold text-slate-700 dark:text-slate-200 tracking-wider text-lg" dir="ltr">${formatted}</td>
                <td class="font-semibold text-slate-600 dark:text-slate-300 text-base">${inspection.owner_name || '---'}</td>
                <td class="text-center"><span class="badge ${catClass}">${inspection.category === 'PL' ? '🚛' : '🚗'} ${inspection.category}</span></td>
                <td class="text-center">${resultHtml}</td>
                <td class="text-center text-sm font-medium text-slate-600 dark:text-slate-300">${inspection.technician_name || '—'}</td>
                <td class="text-base font-semibold text-slate-500 dark:text-slate-400 text-right" id="archived-at-${inspection.id}">${archivedAt}</td>
            `;

            tbody.insertBefore(newRow, tbody.firstChild);
        }

        // ── Reverb real-time sync ──────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Echo === 'undefined') return;

            Echo.channel('inspections-channel')
                .listen('.inspection.changed', (data) => {
                    const inspection = data.inspection;
                    const actionType = data.actionType;
                    const row        = document.getElementById(`row-${inspection.id}`);

                    if (actionType === 'update' || actionType === 'revert') {
                        if (inspection.status === 'imprimer') {
                            if (!row) {
                                appendArchiveRow(inspection);
                                updateArchiveCount(1);
                            } else {
                                updateArchiveRow(row, inspection);
                            }
                        } else if (row) {
                            updateArchiveRow(row, inspection);
                        }
                    }

                    if (['delete', 'force_delete', 'restore'].includes(actionType) && row) {
                        gsap.to(row, {
                            opacity: 0,
                            x: -20,
                            duration: 0.3,
                            onComplete: () => {
                                row.remove();
                                updateArchiveCount(-1);
                                if (document.querySelectorAll('.archive-row').length === 0) {
                                    const tbody = document.getElementById('archive-table-body');
                                    if (tbody && !document.getElementById('empty-state-row')) {
                                        tbody.innerHTML = `
                                            <tr id="empty-state-row">
                                                <td colspan="6" class="px-8 py-16 text-center">
                                                    <div class="flex flex-col items-center gap-3">
                                                        <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                                        </div>
                                                        <p class="text-sm font-medium text-slate-400 dark:text-slate-600">L'archive est vide</p>
                                                    </div>
                                                </td>
                                            </tr>`;
                                    }
                                }
                            }
                        });
                    }

                    if (actionType === 'create' && inspection.status === 'imprimer') {
                        if (!document.getElementById(`row-${inspection.id}`)) {
                            appendArchiveRow(inspection);
                            updateArchiveCount(1);
                        }
                    }
                });
        });
    </script>

</body>
</html>