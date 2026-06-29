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
    <title>Vitecma - Corbeille</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>

    @vite(['resources/js/app.js'])

    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uplogo.png') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .font-plate { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }

        /* ── Navbar ── */
        nav.vitecma-nav {
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid #e2e8f0;
        }
        .dark nav.vitecma-nav {
            background: rgba(13,20,36,0.95);
            border-color: rgba(255,255,255,0.07);
        }

        /* ── Table scroll ── */
        .table-container {
            width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 260px);
        }
        .table-container::-webkit-scrollbar { width: 6px; height: 6px; }
        .table-container::-webkit-scrollbar-track { background: transparent; }
        .table-container::-webkit-scrollbar-thumb { background: #ef4444; border-radius: 8px; }
        .dark .table-container::-webkit-scrollbar-thumb { background: #f87171; }

        .table-container table { width: 100%; border-collapse: collapse; }
        .table-container thead { position: sticky; top: 0; z-index: 20; }
        .table-container thead th {
            background: #f1f5f9;
            border-bottom: 2px solid #e2e8f0;
            padding: 1.1rem 1.6rem;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
            color: #64748b;
        }
        .dark .table-container thead th {
            background: #0d1424;
            border-color: rgba(255,255,255,0.08);
            color: #94a3b8;
        }
        .table-container tbody td {
            padding: 1.1rem 1.6rem;
            font-size: 1.05rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            line-height: 1.5;
        }
        .dark .table-container tbody td {
            border-color: rgba(255,255,255,0.06);
            color: #e2e8f0;
        }
        .table-container tbody tr { transition: background 0.15s ease; }
        .table-container tbody tr:hover { background: rgba(239,68,68,0.04); }
        .dark .table-container tbody tr:hover { background: rgba(248,113,113,0.06); }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.3rem 0.85rem; font-size: 0.82rem; font-weight: 700;
            border-radius: 0.375rem; text-transform: uppercase;
            border-width: 1px; white-space: nowrap;
        }

        /* ── Shine ── */
        .shine-effect { position: relative; overflow: hidden; }
        .shine-effect::before {
            content: "";
            position: absolute;
            top: 0; left: -120%;
            width: 50%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: 0.6s;
        }
        .shine-effect:hover::before { left: 150%; }

        /* ── Filter inputs ── */
        .filter-input {
            background: #f8fafc; border: 1.5px solid #e2e8f0; color: #374151;
            border-radius: 0.6rem; padding: 0.5rem 0.9rem; font-size: 0.88rem;
            font-weight: 600; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none; -webkit-appearance: none;
        }
        .filter-input:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.12); }
        .dark .filter-input {
            background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);
            color: #e2e8f0;
        }
        .dark .filter-input:focus { border-color: #f87171; box-shadow: 0 0 0 3px rgba(248,113,113,0.1); }
        .dark .filter-input option { background: #1e293b; }
        .select-wrapper { position: relative; display: inline-flex; align-items: center; }
        .select-wrapper::after {
            content: '▾'; position: absolute; right: 0.7rem; pointer-events: none;
            color: #94a3b8; font-size: 0.8rem;
        }
        .select-wrapper .filter-input { padding-right: 2rem; }

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
            <div class="w-10 h-10 rounded-xl bg-red-600/10 dark:bg-red-500/10 border border-red-600/20 dark:border-red-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div class="flex flex-col leading-tight">
                <span class="text-lg font-extrabold tracking-tight text-slate-800 dark:text-white">VITECMA</span>
                <span class="text-[0.6rem] font-medium text-slate-400 dark:text-slate-500 tracking-widest uppercase">Corbeille — {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 rounded-lg transition-all font-semibold text-sm border border-slate-200/80 dark:border-white/10 shine-effect">
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Tableau de bord</span>
            </a>

            @if($inspections->count() > 0)
            <button onclick="emptyTrash('{{ route('inspections.emptyTrash') }}')"
                    class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-lg shadow-md shadow-red-600/25 transition-all font-bold text-sm shine-effect hover:scale-[1.02] active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Vider la corbeille</span>
            </button>
            @endif

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
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex flex-wrap justify-between items-center gap-3 bg-gradient-to-r from-red-50/80 to-rose-50/60 dark:from-red-950/20 dark:to-rose-950/10">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded-full bg-gradient-to-b from-red-500 to-rose-500"></div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight">Véhicules supprimés</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium tracking-wider">المركبات المحذوفة (يمكن استعادتها)</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 text-sm font-bold border border-red-200/50 dark:border-red-900/50">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    <span id="trash-count">{{ $inspections->total() }}</span> élément(s)
                </span>
            </div>

            <!-- Filter Bar -->
            <div class="px-6 py-3.5 bg-slate-50 dark:bg-white/[0.02] border-b border-slate-100 dark:border-white/10 flex flex-wrap items-center gap-3">
                <div class="relative flex-grow min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" id="trash-search" placeholder="Rechercher plaque ou propriétaire…"
                           oninput="filterTrash()"
                           class="filter-input w-full pl-9 pr-3">
                </div>

                <div class="select-wrapper">
                    <select id="trash-filter-category" onchange="filterTrash()" class="filter-input min-w-[150px]">
                        <option value="">Toutes catégories</option>
                        <option value="VL">🚗 VL — Léger</option>
                        <option value="PL">🚛 PL — Lourd</option>
                    </select>
                </div>

                <button onclick="resetTrashFilters()" title="Réinitialiser"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10 rounded-lg transition-all border border-slate-200 dark:border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Effacer
                </button>

                <span id="trash-no-result" class="hidden text-xs font-semibold text-slate-400 dark:text-slate-500 italic ml-auto">Aucun résultat</span>
            </div>

            <!-- Table -->
            <div class="w-full table-container">
                <table class="w-full min-w-[1000px] text-left">
                    <thead>
                        <tr>
                            <th class="w-[20%]">Plaque d'immatriculation</th>
                            <th class="w-[22%]">Propriétaire</th>
                            <th class="w-[10%] text-center">Catégorie</th>
                            <th class="w-[16%] text-center">Supprimé le</th>
                            <th class="w-[32%] text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="trash-table-body">
                        @forelse($inspections as $inspection)
                        <tr class="trash-row transition-all duration-150 hover:shadow-sm"
                            id="row-{{ $inspection->id }}"
                            data-id="{{ $inspection->id }}"
                            data-plate="{{ strtolower($inspection->plate_number) }}"
                            data-owner="{{ strtolower($inspection->owner_name ?? '') }}"
                            data-category="{{ $inspection->category }}">
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
                            <td class="text-center text-base font-semibold text-slate-500 dark:text-slate-400">
                                {{ $inspection->deleted_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="handleTrashAction({{ $inspection->id }}, '{{ route('inspections.restore', $inspection->id) }}', 'POST', 'Voulez-vous restaurer ce véhicule ?', 'Restauré avec succès')"
                                            class="p-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800/50 rounded-xl transition-all shine-effect hover:scale-105 active:scale-95" title="Restaurer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </button>
                                    <button onclick="handleTrashAction({{ $inspection->id }}, '{{ route('inspections.forceDestroy', $inspection->id) }}', 'DELETE', 'Ce véhicule sera supprimé définitivement.', 'Supprimé définitivement')"
                                            class="p-2.5 bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800/50 rounded-xl transition-all shine-effect hover:scale-105 active:scale-95" title="Supprimer définitivement">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-state-row">
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-400 dark:text-slate-600">La corbeille est vide</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02] flex justify-center [&_.pagination]:flex [&_.pagination]:items-center [&_.pagination]:gap-1 [&_.page-item_.page-link]:px-4 [&_.page-item_.page-link]:py-2 [&_.page-item_.page-link]:rounded-xl [&_.page-item_.page-link]:text-sm [&_.page-item_.page-link]:font-bold [&_.page-item_.page-link]:border [&_.page-item_.page-link]:border-slate-200 dark:[&_.page-item_.page-link]:border-gray-700 [&_.page-item_.page-link]:bg-white dark:[&_.page-item_.page-link]:bg-gray-800 [&_.page-item_.page-link]:text-gray-600 dark:[&_.page-item_.page-link]:text-gray-300 [&_.page-item_.page-link]:transition-all [&_.page-item_.page-link]:hover:bg-slate-100 dark:[&_.page-item_.page-link]:hover:bg-gray-700 [&_.page-item.active_.page-link]:bg-red-600 [&_.page-item.active_.page-link]:border-red-600 [&_.page-item.active_.page-link]:text-white [&_.page-item.disabled_.page-link]:opacity-40">
                {{ $inspections->links() }}
            </div>
        </div>
    </main>

    <!-- JAVASCRIPT -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // ── Filter ─────────────────────────────────────────────────
        function filterTrash() {
            const search  = document.getElementById('trash-search').value.toLowerCase().trim();
            const cat     = document.getElementById('trash-filter-category').value;

            let visibleCount = 0;
            document.querySelectorAll('#trash-table-body tr.trash-row').forEach(row => {
                const plate    = (row.dataset.plate    || '').toLowerCase();
                const owner    = (row.dataset.owner    || '').toLowerCase();
                const rowCat   = row.dataset.category  || '';

                const matchSearch = !search || plate.includes(search) || owner.includes(search);
                const matchCat    = !cat    || rowCat === cat;

                const visible = matchSearch && matchCat;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const noResult = document.getElementById('trash-no-result');
            if (noResult) noResult.classList.toggle('hidden', visibleCount > 0);
        }

        function resetTrashFilters() {
            document.getElementById('trash-search').value          = '';
            document.getElementById('trash-filter-category').value = '';
            filterTrash();
        }

        // ── Counter ────────────────────────────────────────────────
        function updateTrashCount(delta) {
            const el = document.getElementById('trash-count');
            if (el) el.textContent = Math.max(0, parseInt(el.textContent, 10) + delta);
        }

        // ── Actions ────────────────────────────────────────────────
        async function handleTrashAction(id, url, method, textMsg, successMsg) {
            const confirmed = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: textMsg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: method === 'POST' ? '#10b981' : '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Oui, confirmer',
                cancelButtonText: 'Annuler'
            });

            if (confirmed.isConfirmed) {
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const row = document.getElementById('row-' + id);
                        if (row) {
                            gsap.to(row, {
                                opacity: 0,
                                x: method === 'POST' ? -30 : 30,
                                duration: 0.3,
                                onComplete: () => {
                                    row.remove();
                                    updateTrashCount(-1);
                                    checkEmptyState();
                                }
                            });
                        }
                        Swal.fire({ icon: 'success', title: successMsg, timer: 1200, showConfirmButton: false });
                    }
                } catch (error) {
                    Swal.fire('Erreur', 'Une erreur est survenue', 'error');
                }
            }
        }

        async function emptyTrash(url) {
            const confirmed = await Swal.fire({
                title: 'Vider la corbeille ?',
                text: "Tous les véhicules seront supprimés définitivement.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Oui, tout supprimer',
                cancelButtonText: 'Annuler'
            });

            if (confirmed.isConfirmed) {
                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        document.querySelectorAll('.trash-row').forEach(row => row.remove());
                        document.getElementById('trash-count').innerText = '0';
                        checkEmptyState();
                        Swal.fire({ icon: 'success', title: 'Corbeille vidée', timer: 1200, showConfirmButton: false });
                    }
                } catch (error) {
                    Swal.fire('Erreur', 'Une erreur est survenue', 'error');
                }
            }
        }

        function checkEmptyState() {
            const tbody = document.getElementById('trash-table-body');
            if (tbody && document.querySelectorAll('.trash-row').length === 0 && !document.getElementById('empty-state-row')) {
                tbody.innerHTML = `
                    <tr id="empty-state-row">
                        <td colspan="5" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <p class="text-sm font-medium text-slate-400 dark:text-slate-600">La corbeille est vide</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

        // ── Reverb real-time sync ──────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Echo === 'undefined') return;

            Echo.channel('inspections-channel')
                .listen('.inspection.changed', (data) => {
                    const inspection = data.inspection;
                    const actionType = data.actionType;
                    const row = document.getElementById(`row-${inspection.id}`);

                    if (actionType === 'delete' && !row) {
                        if (inspection.deleted_at) {
                            appendTrashRow(inspection);
                            updateTrashCount(1);
                        }
                    }

                    if (actionType === 'force_delete' && row) {
                        row.remove();
                        updateTrashCount(-1);
                        checkEmptyState();
                    }

                    if (actionType === 'restore' && row) {
                        row.remove();
                        updateTrashCount(-1);
                        checkEmptyState();
                    }

                    if (actionType === 'empty_trash') {
                        document.querySelectorAll('.trash-row').forEach(r => r.remove());
                        document.getElementById('trash-count').textContent = '0';
                        checkEmptyState();
                    }
                });
        });

        function appendTrashRow(inspection) {
            const tbody = document.getElementById('trash-table-body');
            if (!tbody) return;

            const emptyRow = document.getElementById('empty-state-row');
            if (emptyRow) emptyRow.remove();

            const newRow = document.createElement('tr');
            newRow.className = 'trash-row transition-all duration-150';
            newRow.id = `row-${inspection.id}`;
            newRow.dataset.id = inspection.id;
            newRow.dataset.plate    = inspection.plate_number.toLowerCase();
            newRow.dataset.owner    = (inspection.owner_name || '').toLowerCase();
            newRow.dataset.category = inspection.category;

            const formattedPlate = inspection.plate_number.includes('|')
                ? inspection.plate_number.split('|').map(p => p.trim()).join(' · ')
                : inspection.plate_number;

            const catClass = inspection.category === 'PL'
                ? 'bg-orange-50 dark:bg-orange-950/20 text-orange-600 border-orange-200 dark:border-orange-900/40'
                : 'bg-green-50 dark:bg-green-950/20 text-green-600 border-green-200 dark:border-green-900/40';

            const deletedAt = inspection.deleted_at
                ? new Date(inspection.deleted_at).toLocaleString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })
                : '---';

            newRow.innerHTML = `
                <td class="font-plate font-bold text-slate-700 dark:text-slate-200 tracking-wider text-lg" dir="ltr">${formattedPlate}</td>
                <td class="font-semibold text-slate-600 dark:text-slate-300 text-base">${inspection.owner_name || '---'}</td>
                <td class="text-center"><span class="badge ${catClass}">${inspection.category === 'PL' ? '🚛' : '🚗'} ${inspection.category}</span></td>
                <td class="text-center text-base font-semibold text-slate-500 dark:text-slate-400">${deletedAt}</td>
                <td class="text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="handleTrashAction(${inspection.id}, '/inspections/${inspection.id}/restore', 'POST', 'Voulez-vous restaurer ce véhicule ?', 'Restauré avec succès')"
                                class="p-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800/50 rounded-xl transition-all shine-effect hover:scale-105 active:scale-95" title="Restaurer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </button>
                        <button onclick="handleTrashAction(${inspection.id}, '/inspections/${inspection.id}/force', 'DELETE', 'Ce véhicule sera supprimé définitivement.', 'Supprimé définitivement')"
                                class="p-2.5 bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800/50 rounded-xl transition-all shine-effect hover:scale-105 active:scale-95" title="Supprimer définitivement">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            `;

            tbody.insertBefore(newRow, tbody.firstChild);
        }
    </script>

</body>
</html>