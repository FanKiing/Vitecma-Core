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
    <title>{{ $siteName }} - Archives</title>


    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="shortcut icon" href="{{ $siteIconUrl }}">

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
        .table-container tbody tr:hover { background:rgba(22,101,52,0.04); }
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
        .filter-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(22,101,52,0.12); }
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
    @livewireStyles
</head>
<body class="bg-slate-50 dark:bg-[#0a0f1a] text-slate-800 dark:text-slate-200 min-h-screen flex flex-col transition-colors duration-300">

    <!-- NAVBAR -->
    <nav class="vitecma-nav sticky top-0 z-40 px-6 py-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-600/10 dark:bg-green-500/10 border border-green-600/20 dark:border-green-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div class="flex flex-col leading-tight">
                <span class="text-lg font-extrabold tracking-tight text-slate-800 dark:text-white" data-brand-name>{{ $siteName }}</span>
                <span class="text-[0.6rem] font-medium text-slate-400 dark:text-slate-500 tracking-widest uppercase">Archives — {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <span id="realtime-status" role="status" class="text-xs text-slate-500" data-state="connecting">Synchronisation…</span>
            @if (auth()->user()->role === 'admin')
                <button type="button" @click="$dispatch('open-branding')" class="px-4 py-2 rounded-lg bg-green-600/10 text-green-700 dark:text-green-400 border border-green-600/20 text-sm font-semibold hover:bg-green-600/20">Nom et logo</button>
            @endif
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
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex flex-wrap justify-between items-center gap-3 bg-gradient-to-r from-green-50/80 to-indigo-50/60 dark:from-green-950/20 dark:to-indigo-950/10">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded-full bg-gradient-to-b from-green-500 to-indigo-500"></div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight">Historique des inspections</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium tracking-wider">أرشيف الفحوصات المكتملة والمطبوعة</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 text-sm font-bold border border-green-200/50 dark:border-green-900/50">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
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
                <livewire:inspection-table scope="archive" />
            </div>

            <!-- Pagination -->

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
        window.renderInspectionState = function(data) {
            document.getElementById('archive-count').textContent = data.totalCount;
            const emptyButton = document.getElementById('empty-trash-btn');
            if (emptyButton) emptyButton.style.display = data.totalCount ? '' : 'none';
            filterArchive();
        };
    </script>

<x-branding-modal />
    @livewireScriptConfig
</body>
</html>
