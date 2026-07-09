<!DOCTYPE html>
<html lang="fr" dir="ltr"
    x-data="{ darkMode: localStorage.getItem('dark') === 'true', showModal: false, showSummaryModal: false }"
    x-init="
        if (darkMode) document.documentElement.classList.add('dark');
        $watch('darkMode', val => {
            localStorage.setItem('dark', val);
            val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
        });
    "
    @close-inspection-modal.window="showModal = false"
    @close-summary-modal.window="showSummaryModal = false"
    :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitecma - Control Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .font-plate { font-family: 'JetBrains Mono', monospace; }
        .font-vt { font-family: 'JetBrains Mono', monospace; }

        nav.vitecma-nav { position: relative; }
        nav.vitecma-nav::after {
            content: '';
            position: absolute; bottom: -1px; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, #15803d 0%, #16a34a 40%, #22c55e 70%, #4ade80 100%);
        }

        [x-cloak] { display: none !important; }

        .status-en-cours { animation: pulse-green 2s cubic-bezier(0.4,0,0.6,1) infinite; }
        @keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.60; } }

        .table-container {
            width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 260px);
        }
        .table-container::-webkit-scrollbar { width: 6px; height: 6px; }
        .table-container::-webkit-scrollbar-track { background: transparent; }
        .table-container::-webkit-scrollbar-thumb { background: #16a34a; border-radius: 8px; }
        .dark .table-container::-webkit-scrollbar-thumb { background: #22c55e; }

        .table-container table { width: 100%; border-collapse: collapse; }

        .table-container thead { position: sticky; top: 0; z-index: 20; }
        .table-container thead th {
            background: #f1f5f9;
            border-bottom: 2px solid #e2e8f0;
            padding: 1.1rem 1.6rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
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
        .table-container tbody tr:hover { background: rgba(22,163,74,0.04); }
        .dark .table-container tbody tr:hover { background: rgba(34,197,94,0.06); }

        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.28rem 0.75rem; font-size: 0.72rem; font-weight: 700;
            border-radius: 0.4rem; text-transform: uppercase; letter-spacing: 0.03em;
            border-width: 1px; white-space: nowrap;
        }

        .action-btn {
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.88rem; padding: 0.45rem 1.3rem;
            border-radius: 0.5rem; transition: all 0.15s ease;
            min-width: 95px; white-space: nowrap; cursor: pointer; border: none;
        }
        .icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.45rem; border-radius: 0.5rem; transition: all 0.2s ease;
            cursor: pointer; border: 1px solid transparent; background: transparent;
        }
        .icon-btn svg { width: 1.2rem; height: 1.2rem; transition: transform 0.2s ease; }
        .icon-btn:hover svg { transform: scale(1.15); }
        .icon-btn:active { transform: scale(0.92); }

        .shine-effect { position: relative; overflow: hidden; }
        .shine-effect::before {
            content: ""; position: absolute; top: 0; left: -120%;
            width: 50%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: 0.6s;
        }
        .shine-effect:hover::before { left: 150%; }

        nav.vitecma-nav {
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid #e2e8f0;
        }
        .dark nav.vitecma-nav {
            background: rgba(13,20,36,0.95);
            border-color: rgba(255,255,255,0.07);
        }

        .filter-input {
            background: #f8fafc; border: 1.5px solid #e2e8f0; color: #374151;
            border-radius: 0.65rem; padding: 0.55rem 0.9rem; font-size: 0.85rem;
            font-weight: 600; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none; -webkit-appearance: none;
        }
        .filter-input:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,0.13); background: #fff; }
        .dark .filter-input {
            background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.1);
            color: #e2e8f0;
        }
        .dark .filter-input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); background: rgba(15,23,42,0.7); }
        .dark .filter-input option { background: #1e293b; }

        .select-wrapper { position: relative; display: inline-flex; align-items: center; }
        .select-wrapper::after {
            content: '▾'; position: absolute; right: 0.7rem; pointer-events: none;
            color: #94a3b8; font-size: 0.8rem;
        }
        .select-wrapper .filter-input { padding-right: 2rem; }

        /* Custom checkbox styling */
        .vt-checkbox {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            width: 1.15rem; height: 1.15rem; flex-shrink: 0;
            border: 1.5px solid #cbd5e1; border-radius: 0.35rem;
            background: #fff; cursor: pointer; position: relative;
            transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .dark .vt-checkbox { background: #0d1424; border-color: #475569; }
        .vt-checkbox:hover { border-color: #16a34a; }
        .dark .vt-checkbox:hover { border-color: #22c55e; }
        .vt-checkbox:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(22,163,74,0.25); }
        .vt-checkbox:checked {
            background: #16a34a; border-color: #16a34a;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='white' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='3.5 8.5 6.5 11.5 12.5 4.5'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: center; background-size: 0.8rem;
        }
        .dark .vt-checkbox:checked { background-color: #22c55e; border-color: #22c55e; }
        .vt-checkbox:indeterminate {
            background: #16a34a; border-color: #16a34a;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='white' stroke-width='2.5' stroke-linecap='round'%3E%3Cline x1='3.5' y1='8' x2='12.5' y2='8'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: center; background-size: 0.8rem;
        }
        .dark .vt-checkbox:indeterminate { background-color: #22c55e; border-color: #22c55e; }

        @keyframes rowHighlightFlash {
            0%   { background-color: rgba(22, 163, 74, 0.18); }
            100% { background-color: transparent; }
        }
        .row-highlight { animation: rowHighlightFlash 1.2s ease-out; }

        /* Start Modal Styles */
        #startInspectionModal .modal-card {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #startInspectionModal select,
        #startInspectionModal input {
            transition: all 0.3s ease;
        }
        #startInspectionModal select:focus,
        #startInspectionModal input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
        }
        .dark #startInspectionModal select:focus,
        .dark #startInspectionModal input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34,197,94,0.1);
        }
    </style>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uplogo.png') }}">
</head>
<body @class(['bg-slate-50', 'dark:bg-[#0a0f1a]', 'text-slate-800', 'dark:text-slate-200', 'transition-colors', 'duration-300', 'min-h-screen', 'flex', 'flex-col'])>

    @if(auth()->user()->role !== 'admin')
    <!-- MAINTENANCE OVERLAY (écran uniquement) -->
    <div id="maintenance-overlay"
         class="fixed inset-0 z-[999] flex-col items-center justify-center gap-4 bg-slate-950/97 backdrop-blur-sm text-center px-6 {{ $maintenanceMode ? 'flex' : 'hidden' }}">
        <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L1.5 3l1.5-1.5L7.5 4.5v1.409l4.26 4.26"/></svg>
        </div>
        <h1 class="font-vt text-xl sm:text-2xl font-bold text-white tracking-wide">Maintenance en cours</h1>
        <p id="maintenance-message" class="text-sm text-slate-400 max-w-sm">
            {{ $maintenanceMessage ?: "L'écran est temporairement en maintenance. Merci de patienter." }}
        </p>
    </div>
    @endif

    <!-- NAVBAR -->
    <nav @class(['vitecma-nav', 'sticky', 'top-0', 'z-40', 'px-6', 'py-3', 'flex', 'flex-wrap', 'items-center', 'justify-between', 'gap-3', 'shadow-sm'])>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-600/10 dark:bg-green-500/10 border border-green-600/20 dark:border-green-500/20 flex items-center justify-center overflow-hidden">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-8 h-8 object-contain">
            </div>
            <div class="flex flex-col leading-tight">
                <span class="font-vt text-lg font-bold tracking-[0.04em] text-slate-800 dark:text-white">VITECMA</span>
                <span class="font-vt text-[0.58rem] font-semibold text-green-600/70 dark:text-green-400/60 tracking-[0.2em] uppercase">Centre de Visite Technique</span>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <div class="hidden sm:flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400 bg-slate-100/50 dark:bg-white/5 px-4 py-1.5 rounded-full border border-slate-200/60 dark:border-white/10">
                <span>{{ date('Y-m-d') }}</span>
                <span class="w-px h-4 bg-slate-300 dark:bg-white/20"></span>
                <span class="font-bold text-green-600 dark:text-green-400" id="total-inspections-count">{{ $inspections->total() }}</span>
                <span class="text-[0.6rem] uppercase tracking-widest opacity-60">véhicules</span>
            </div>

            <button @click="showSummaryModal = true; fetchDailyStats()"
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 transition-all active:scale-90 text-slate-500 dark:text-slate-400"
                    title="Résumé du jour">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </button>

            @if(auth()->user()->role === 'admin')
                <button type="button" id="maintenance-toggle-btn" onclick="toggleMaintenanceMode()"
                        data-enabled="{{ $maintenanceMode ? '1' : '0' }}"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-lg transition-all font-semibold text-sm border shine-effect {{ $maintenanceMode ? 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 border-amber-500/30' : 'bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-white/10' }}"
                        title="Activer/désactiver la maintenance pour l'écran">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L1.5 3l1.5-1.5L7.5 4.5v1.409l4.26 4.26"/></svg>
                    <span id="maintenance-toggle-label">{{ $maintenanceMode ? 'Maintenance : ON' : 'Maintenance : OFF' }}</span>
                </button>

                <a href="{{ route('inspections.trash') }}"
                   class="flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 rounded-lg transition-all font-semibold text-sm border border-slate-200/80 dark:border-white/10 shine-effect">
                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Corbeille</span>
                </a>

                <a href="{{ route('inspections.archive') }}"
                   class="flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 rounded-lg transition-all font-semibold text-sm border border-slate-200/80 dark:border-white/10 shine-effect">
                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <span>Archives</span>
                </a>

                <button @click="showModal = true"
                        class="flex items-center gap-1.5 px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-md shadow-green-600/25 transition-all font-bold text-sm hover:scale-[1.02] active:scale-95 shine-effect">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Ajouter</span>
                </button>
            @endif

            <div class="h-6 w-px bg-slate-200 dark:bg-white/10 mx-1 hidden sm:block"></div>

            <button @click="darkMode = !darkMode; localStorage.setItem('dark', darkMode)"
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 transition-all active:scale-90 text-slate-500 dark:text-slate-400">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-bold text-red-500 hover:text-white hover:bg-red-500 dark:hover:bg-red-600 rounded-lg transition-all border border-transparent hover:border-red-500 shine-effect">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Déconnexion</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- MAIN -->
    <main class="w-full px-4 py-4 max-w-full mx-auto flex-grow">
        <div class="w-full max-w-full mx-auto bg-white dark:bg-[#111827] rounded-2xl shadow-lg border border-slate-200 dark:border-white/10 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex flex-wrap justify-between items-center gap-3 bg-white dark:bg-[#111827]">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-7 rounded-full bg-green-500"></div>
                    <div>
                        <div class="font-vt text-[0.62rem] font-bold text-green-600 dark:text-green-400 tracking-[0.2em] uppercase mb-0.5">Terminal · Inspections</div>
                        <h2 class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight leading-none">Liste des Inspections</h2>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="px-6 py-3.5 bg-slate-50 dark:bg-white/[0.02] border-b border-slate-100 dark:border-white/10 flex flex-wrap items-center gap-3">
                <div class="relative flex-grow min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" id="search-input" placeholder="Rechercher plaque ou propriétaire…"
                           oninput="filterTable()"
                           class="filter-input w-full pl-9 pr-3">
                </div>

                <div class="select-wrapper">
                    <select id="filter-category" onchange="filterTable()" class="filter-input min-w-[150px]">
                        <option value="">Toutes catégories</option>
                        <option value="VL">🚗 VL — Léger</option>
                        <option value="PL">🚛 PL — Lourd</option>
                    </select>
                </div>

                <div class="select-wrapper">
                    <select id="filter-status" onchange="filterTable()" class="filter-input min-w-[160px]">
                        <option value="">Tous les statuts</option>
                        <option value="libre">Libre</option>
                        <option value="en_cours">En cours</option>
                        <option value="favorable">Favorable</option>
                        <option value="defavorable">Défavorable</option>
                    </select>
                </div>

                <button onclick="resetFilters()" title="Réinitialiser les filtres"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10 rounded-lg transition-all border border-slate-200 dark:border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Effacer
                </button>

                @if(auth()->user()->role === 'admin')
                <button onclick="deleteAllLibre()" id="delete-all-btn"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-red-500 hover:text-white hover:bg-red-500 dark:hover:bg-red-600 rounded-lg transition-all border border-red-200 dark:border-red-800/50 shine-effect">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Tout supprimer
                </button>

<button onclick="bulkDelete()" id="bulk-delete-btn" 
        class="hidden items-center gap-1.5 px-3 py-2 text-sm font-semibold text-red-500 hover:text-white hover:bg-red-500 dark:hover:bg-red-600 rounded-lg transition-all border border-red-200 dark:border-red-800/50 shine-effect">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
    </svg>
    <span id="bulk-delete-count">0</span>
</button>
                @endif

                <span id="filter-no-result" class="hidden text-xs font-semibold text-slate-400 dark:text-slate-500 italic ml-auto">Aucun résultat</span>
            </div>

            <!-- Table -->
            <div class="w-full table-container">
                <table class="w-full min-w-[1200px] text-left">
                    <thead>
                        <tr>
                            @if(auth()->user()->role === 'admin')
                            <th class="w-[3%] text-center">
                                <input type="checkbox" id="select-all" onchange="toggleAllCheckboxes()" class="vt-checkbox">
                            </th>
                            @endif
                            <th class="w-[14%]">Numéro de plaque</th>
                            <th class="w-[14%]">Nom Complet</th>
                            <th class="w-[7%] text-center">Cat.</th>
                            <th class="w-[11%] text-center">Statut</th>
                            <th class="w-[9%] text-center">Temps</th>
                            <th class="w-[12%] text-center">Technicien</th>
                            <th class="w-[8%] text-center">Line</th>
                            @if(auth()->user()->role === 'admin')
                            <th class="w-[22%] text-center">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="inspections-table-body">
                        @forelse($inspections as $inspection)
                        @php
                            $filterStatus = $inspection->status === 'valider'
                                ? ($inspection->result ?? 'valider')
                                : $inspection->status;
                        @endphp
                        <tr class="group transition-all duration-200"
                            id="row-{{ $inspection->id }}"
                            data-plate="{{ strtolower($inspection->plate_number) }}"
                            data-owner="{{ strtolower($inspection->owner_name ?? '') }}"
                            data-category="{{ $inspection->category }}"
                            data-filter-status="{{ $filterStatus }}">

                            @if(auth()->user()->role === 'admin')
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox vt-checkbox" data-id="{{ $inspection->id }}" data-status="{{ $inspection->status }}">
                            </td>
                            @endif
                            <td class="font-plate font-bold text-green-600 dark:text-green-400 tracking-wider text-lg" dir="ltr">
                                {{ str_replace('|', ' · ', $inspection->plate_number) }}
                            </td>
                            <td class="font-semibold text-slate-700 dark:text-slate-200 text-base truncate max-w-[150px]">
                                {{ $inspection->owner_name ?? '---' }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $inspection->category == 'PL' ? 'text-orange-600 bg-orange-50 border-orange-200 dark:text-orange-400 dark:bg-orange-950/30 dark:border-orange-900/50' : 'text-green-700 bg-green-50 border-green-200 dark:text-green-400 dark:bg-green-950/30 dark:border-green-900/50' }}">
                                    {{ $inspection->category == 'PL' ? '🚛' : '🚗' }} {{ $inspection->category }}
                                </span>
                            </td>
                            <td class="text-center status-text" id="status-{{ $inspection->id }}">
                                @if($inspection->status === 'libre')
                                    <span class="badge bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/15">Libre</span>
                                @elseif($inspection->status === 'en_cours')
                                    <span class="badge bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/60 status-en-cours">En cours</span>
                                @elseif($inspection->status === 'valider' && $inspection->result === 'favorable')
                                    <span class="badge bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">Favorable</span>
                                @elseif($inspection->status === 'valider' && $inspection->result === 'defavorable')
                                    <span class="badge bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60">Défavorable</span>
                                @elseif($inspection->status === 'valider')
                                    <span class="badge bg-purple-50 dark:bg-violet-950/60 text-purple-600 dark:text-violet-400 border border-purple-200 dark:border-violet-800/60">Validé</span>
                                @else
                                    <span class="badge bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400">{{ __($inspection->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="timer-display font-plate text-lg font-bold text-slate-400 dark:text-slate-500 tabular-nums transition-colors duration-300"
                                     id="timer-{{ $inspection->id }}"
                                     data-id="{{ $inspection->id }}"
                                     data-start="{{ $inspection->started_at ? $inspection->started_at->toIso8601String() : 'invalid' }}"
                                     data-duration="{{ $inspection->category === 'VL' ? 20 : 30 }}"
                                     data-active="{{ $inspection->status === 'en_cours' ? 'true' : 'false' }}">
                                    00:00
                                </div>
                            </td>
                            <td class="text-center text-sm font-medium text-slate-600 dark:text-slate-300">
                                {{ $inspection->technician_name ?? '—' }}
                            </td>
                            <td class="text-center">
                                @if($inspection->lane)
                                    <span class="badge {{ $inspection->lane == 'VL2' ? 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50' : ($inspection->lane == 'VL4' ? 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-900/50' : 'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-900/50') }}">
                                        {{ $inspection->lane }}
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            @if(auth()->user()->role === 'admin')
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button"
                                            onclick="confirmRevert({{ $inspection->id }})"
                                            id="btn-revert-{{ $inspection->id }}"
                                            style="{{ $inspection->status === 'libre' ? 'display:none;' : 'display:inline-flex;' }}"
                                            title="Retour à l'état précédent"
                                            class="icon-btn border border-slate-200 dark:border-white/15 bg-white dark:bg-white/5 text-slate-500 dark:text-slate-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 hover:text-amber-600 dark:hover:text-amber-400 hover:border-amber-200 dark:hover:border-amber-800/50 shine-effect">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </button>
                                    <button type="button"
                                            onclick="openEditModal({{ $inspection->id }})"
                                            data-id="{{ $inspection->id }}"
                                            data-plate="{{ $inspection->plate_number }}"
                                            data-owner="{{ $inspection->owner_name ?? '' }}"
                                            data-category="{{ $inspection->category }}"
                                            title="Modifier"
                                            class="edit-btn icon-btn border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 hover:border-amber-300 dark:hover:border-amber-800/70 shine-effect">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button"
                                            onclick="handleStatusUpdate({{ $inspection->id }}, this.dataset.status)"
                                            data-status="{{ $inspection->status }}"
                                            class="action-btn
                                                {{ $inspection->status == 'libre' ? 'bg-slate-800 hover:bg-slate-700 dark:bg-white/15 dark:hover:bg-white/25 text-white dark:text-slate-200' : '' }}
                                                {{ $inspection->status == 'en_cours' ? 'bg-green-600 hover:bg-green-700 text-white shadow-sm shadow-green-600/30' : '' }}
                                                {{ $inspection->status == 'valider' ? 'bg-slate-900 dark:bg-slate-800 text-green-400 border border-green-500/40 hover:border-green-400 hover:text-green-300' : '' }}
                                                {{ $inspection->status == 'imprimer' ? 'bg-blue-700 hover:bg-blue-800 text-white shadow-sm shadow-blue-600/30' : '' }}
                                                hover:scale-105 active:scale-95 shine-effect">
                                        {{ $inspection->status === 'libre' ? 'Démarrer' : ($inspection->status === 'en_cours' ? 'Valider' : 'Imprimer') }}
                                    </button>

                                    <button type="button"
                                            onclick="confirmTrash({{ $inspection->id }})"
                                            title="Supprimer"
                                            class="icon-btn border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-950/20 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 hover:border-red-300 dark:hover:border-red-800/60 shine-effect">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr id="empty-state-row">
                            <td colspan="{{ auth()->user()->role === 'admin' ? 9 : 7 }}" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2m0 0h10m-10 0h10m1-10v4l2 2m0 0h1m-1 0v2"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-400 dark:text-slate-600">Aucun véhicule trouvé</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inspections->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02] flex justify-center [&_.pagination]:flex [&_.pagination]:items-center [&_.pagination]:gap-1 [&_.page-item_.page-link]:px-4 [&_.page-item_.page-link]:py-2 [&_.page-item_.page-link]:rounded-xl [&_.page-item_.page-link]:text-sm [&_.page-item_.page-link]:font-bold [&_.page-item_.page-link]:border [&_.page-item_.page-link]:border-slate-200 dark:[&_.page-item_.page-link]:border-gray-700 [&_.page-item_.page-link]:bg-white dark:[&_.page-item_.page-link]:bg-gray-800 [&_.page-item_.page-link]:text-gray-600 dark:[&_.page-item_.page-link]:text-gray-300 [&_.page-item_.page-link]:transition-all [&_.page-item_.page-link]:hover:bg-slate-100 dark:[&_.page-item_.page-link]:hover:bg-gray-700 [&_.page-item.active_.page-link]:bg-green-600 [&_.page-item.active_.page-link]:border-green-600 [&_.page-item.active_.page-link]:text-white [&_.page-item.disabled_.page-link]:opacity-40">
                {{ $inspections->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- SUMMARY MODAL -->
    <div x-show="showSummaryModal" x-cloak
         x-transition:enter="transition duration-300 ease-out"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition duration-200 ease-in"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/65 backdrop-blur-md" @click="showSummaryModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-[#0f172a] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="h-1.5 w-full bg-gradient-to-r from-green-500 via-emerald-400 to-green-600"></div>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-white/10 bg-slate-50/60 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-green-50 dark:bg-green-950/40 border border-green-100 dark:border-green-900/40">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <h3 class="text-base font-black text-slate-800 dark:text-white tracking-tight">Résumé du jour</h3>
                        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500">{{ date('l d/m/Y') }}</p>
                    </div>
                </div>
                <button @click="showSummaryModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/10 rounded-lg transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-5 space-y-4">
                <div class="flex items-center gap-4 bg-gradient-to-br from-green-50 to-emerald-50/60 dark:from-green-950/30 dark:to-emerald-950/20 rounded-xl p-4 border border-green-100 dark:border-green-900/40">
                    <div class="w-12 h-12 rounded-xl bg-green-600/10 dark:bg-green-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-widest mb-0.5">Total véhicules</div>
                        <div class="text-4xl font-black text-green-700 dark:text-green-300 leading-none tabular-nums" id="daily-total">0</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-xs text-green-600/60 dark:text-green-500/60 font-semibold">Aujourd'hui</div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-amber-50 dark:bg-amber-950/25 rounded-xl p-3.5 border border-amber-100 dark:border-amber-900/40 text-center space-y-2">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 tabular-nums leading-none" id="daily-en-cours">0</div>
                        <div class="text-[0.65rem] font-bold text-amber-500 dark:text-amber-500/80 uppercase tracking-wider leading-tight">En cours</div>
                        <div class="h-1 rounded-full bg-amber-100 dark:bg-amber-900/50 overflow-hidden">
                            <div id="bar-en-cours" class="h-full bg-amber-500 dark:bg-amber-400 rounded-full transition-all duration-700" style="width:0%"></div>
                        </div>
                    </div>

                    <div class="bg-emerald-50 dark:bg-emerald-950/25 rounded-xl p-3.5 border border-emerald-100 dark:border-emerald-900/40 text-center space-y-2">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums leading-none" id="daily-favorable">0</div>
                        <div class="text-[0.65rem] font-bold text-emerald-500 dark:text-emerald-500/80 uppercase tracking-wider leading-tight">Favorable</div>
                        <div class="h-1 rounded-full bg-emerald-100 dark:bg-emerald-900/50 overflow-hidden">
                            <div id="bar-favorable" class="h-full bg-emerald-500 dark:bg-emerald-400 rounded-full transition-all duration-700" style="width:0%"></div>
                        </div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-950/25 rounded-xl p-3.5 border border-red-100 dark:border-red-900/40 text-center space-y-2">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div class="text-2xl font-black text-red-600 dark:text-red-400 tabular-nums leading-none" id="daily-defavorable">0</div>
                        <div class="text-[0.65rem] font-bold text-red-500 dark:text-red-500/80 uppercase tracking-wider leading-tight">Défavorable</div>
                        <div class="h-1 rounded-full bg-red-100 dark:bg-red-900/50 overflow-hidden">
                            <div id="bar-defavorable" class="h-full bg-red-500 dark:bg-red-400 rounded-full transition-all duration-700" style="width:0%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2 pt-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                    <p class="text-center text-xs text-slate-400 dark:text-slate-500 font-semibold">Statistiques en temps réel</p>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editInspectionModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="display:none;">
        <div onclick="closeEditModal()" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="editModalOverlay"></div>
        <div id="editModalContent" class="relative w-full max-w-md bg-white dark:bg-[#111827] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
            <div class="h-1 w-full bg-gradient-to-r from-amber-400 to-orange-500"></div>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/50">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-white">Modifier l'inspection</h3>
                        <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">Modifier les informations du véhicule</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="editInspectionForm" onsubmit="submitEditForm(event)" class="px-6 py-5 space-y-4">
                <input type="hidden" id="edit_inspection_id">
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Numéro de plaque
                    </label>
                    <input type="text" id="edit_plate_number" required placeholder="12345|A|1" oninput="this.value = this.value.toUpperCase()" class="w-full px-4 py-3 font-mono font-black text-lg rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-amber-400 dark:focus:border-amber-500 focus:ring-0 outline-none transition-all text-blue-600 dark:text-blue-400 tracking-widest placeholder:text-gray-300 dark:placeholder:text-gray-600">
                </div>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Nom du propriétaire
                    </label>
                    <input type="text" id="edit_owner_name" placeholder="Nom Complet" class="w-full px-4 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-amber-400 dark:focus:border-amber-500 focus:ring-0 outline-none transition-all text-gray-800 dark:text-gray-100 font-semibold placeholder:text-gray-300 dark:placeholder:text-gray-600">
                </div>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Catégorie</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="edit_cat_radio" value="VL" class="sr-only peer" onchange="document.getElementById('edit_category').value='VL'">
                            <div class="flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 peer-checked:border-emerald-400 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-950/30 dark:peer-checked:border-emerald-500 transition-all font-black text-sm text-gray-500 dark:text-gray-400 peer-checked:text-emerald-700 dark:peer-checked:text-emerald-300">
                                <span class="text-xl">🚗</span> VL — Léger
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="edit_cat_radio" value="PL" class="sr-only peer" onchange="document.getElementById('edit_category').value='PL'">
                            <div class="flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 peer-checked:border-orange-400 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-950/30 dark:peer-checked:border-orange-500 transition-all font-black text-sm text-gray-500 dark:text-gray-400 peer-checked:text-orange-700 dark:peer-checked:text-orange-300">
                                <span class="text-xl">🚛</span> PL — Lourd
                            </div>
                        </label>
                    </div>
                    <select id="edit_category" class="sr-only"><option value="VL">VL</option><option value="PL">PL</option></select>
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-100 dark:border-gray-800 mt-2">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-3 text-sm font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-2xl transition-all active:scale-95">Annuler</button>
                    <button type="submit" id="edit-submit-btn" class="flex-1 py-3 bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white text-sm font-black rounded-2xl shadow-lg shadow-amber-500/20 transition-all active:scale-95 hover:scale-[1.02] flex items-center justify-center gap-2 shine-effect">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD MODAL -->
    @if(auth()->user()->role === 'admin')
    <div x-show="showModal" x-cloak
         x-transition:enter="transition duration-300 ease-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-200 ease-in"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-[#111827] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="h-1 w-full bg-gradient-to-r from-green-500 to-emerald-500"></div>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/50">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-white">Nouveau Véhicule</h3>
                        <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">Ajouter un nouveau véhicule</p>
                    </div>
                </div>
                <button @click="showModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('inspections.store') }}" method="POST" class="px-6 py-5 space-y-4" id="add-inspection-form">
                @csrf
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Numéro de plaque
                    </label>
                    <input type="text" name="plate_number" required placeholder="12345|A|1" oninput="this.value = this.value.toUpperCase()" class="w-full px-4 py-3 font-mono font-black text-lg rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-emerald-400 dark:focus:border-emerald-500 focus:ring-0 outline-none transition-all text-blue-600 dark:text-blue-400 tracking-widest placeholder:text-gray-300 dark:placeholder:text-gray-600">
                </div>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Nom du propriétaire
                    </label>
                    <input type="text" name="owner_name" placeholder="Nom Complet" class="w-full px-4 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-emerald-400 dark:focus:border-emerald-500 focus:ring-0 outline-none transition-all text-gray-800 dark:text-gray-100 font-semibold placeholder:text-gray-300 dark:placeholder:text-gray-600">
                </div>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Catégorie</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="category" value="VL" checked class="sr-only peer">
                            <div class="flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 peer-checked:border-emerald-400 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-950/30 dark:peer-checked:border-emerald-500 transition-all font-black text-sm text-gray-500 dark:text-gray-400 peer-checked:text-emerald-700 dark:peer-checked:text-emerald-300">
                                <span class="text-xl">🚗</span> VL — Léger
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="category" value="PL" class="sr-only peer">
                            <div class="flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 peer-checked:border-orange-400 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-950/30 dark:peer-checked:border-orange-500 transition-all font-black text-sm text-gray-500 dark:text-gray-400 peer-checked:text-orange-700 dark:peer-checked:text-orange-300">
                                <span class="text-xl">🚛</span> PL — Lourd
                            </div>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-100 dark:border-gray-800 mt-2">
                    <button type="button" @click="showModal = false" class="flex-1 py-3 text-sm font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-2xl transition-all active:scale-95">Annuler</button>
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-br from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white text-sm font-black rounded-2xl shadow-lg shadow-emerald-500/20 transition-all active:scale-95 hover:scale-[1.02] flex items-center justify-center gap-2 shine-effect">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ═══════════════════════ START INSPECTION MODAL ═══════════════════════ -->
    <div id="startInspectionModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="display:none;">
        <div onclick="closeStartModal()" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="startModalOverlay"></div>
        <div id="startModalContent" class="relative w-full max-w-md bg-white dark:bg-[#111827] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
            <div class="h-1 w-full bg-gradient-to-r from-green-500 to-emerald-500"></div>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-green-50 dark:bg-green-950/40 border border-green-100 dark:border-green-900/50">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-white">Démarrer le contrôle</h3>
                        <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">Confirmer le démarrage de l'inspection</p>
                    </div>
                </div>
                <button type="button" onclick="closeStartModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="startInspectionForm" onsubmit="submitStartForm(event)" class="px-6 py-5 space-y-4">
                <input type="hidden" id="start_inspection_id">
                
                <!-- Sélection du technicien -->
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Technicien
                    </label>
                    <select id="start_technician" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-green-500 focus:ring-0 outline-none transition-all text-gray-800 dark:text-gray-100 font-medium text-sm">
                        <option value="">Sélectionner un technicien...</option>
                    </select>
                </div>

                <!-- Mot de passe du technicien -->
                <div class="space-y-1.5" x-data="{ showPass: false }">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Mot de passe
                    </label>
                    <div class="relative">
                        <input id="start_password" :type="showPass ? 'text' : 'password'" required placeholder="••••••••••••"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-green-500 focus:ring-0 outline-none transition-all text-gray-800 dark:text-gray-100 text-sm tracking-wider">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Sélection de la voie -->
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-4-4 4-4m6 8l4-4-4-4M4 4h16"/></svg>
                        Voie (Lane)
                    </label>
                    <select id="start_lane" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-green-500 focus:ring-0 outline-none transition-all text-gray-800 dark:text-gray-100 font-medium text-sm">
                        <option value="">Sélectionner un m...</option>
                        <option value="VL2">🚗 VL2</option>
                        <option value="VL4">🚗 VL4</option>
                        <option value="PL1">🚛 PL1</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-2 border-t border-gray-100 dark:border-gray-800 mt-2">
                    <button type="button" onclick="closeStartModal()" class="flex-1 py-3 text-sm font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-2xl transition-all active:scale-95">Annuler</button>
                    <button type="submit" id="start-submit-btn" class="flex-1 py-3 bg-gradient-to-br from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-sm font-black rounded-2xl shadow-lg shadow-green-500/20 transition-all active:scale-95 hover:scale-[1.02] flex items-center justify-center gap-2 shine-effect">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Démarrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        const activeIntervals = {};
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // ── Shared AJAX helper ───────────────────────────────────────
        // Wraps fetch with CSRF header, JSON handling, and consistent error messages.
        async function apiFetch(url, { method = 'GET', body = null } = {}) {
            const headers = {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (body !== null) headers['Content-Type'] = 'application/json';

            const response = await fetch(url, {
                method,
                headers,
                body: body !== null ? JSON.stringify(body) : undefined
            });

            let data = null;
            try { data = await response.json(); } catch { /* no JSON body */ }

            if (!response.ok) {
                const message = data?.message || (data?.errors ? Object.values(data.errors)[0][0] : null) || 'Une erreur est survenue.';
                throw new Error(message);
            }
            return data;
        }

        function filterTable() {
            const search  = document.getElementById('search-input').value.toLowerCase().trim();
            const cat     = document.getElementById('filter-category').value;
            const status  = document.getElementById('filter-status').value;

            let visibleCount = 0;
            document.querySelectorAll('#inspections-table-body tr[id^="row-"]').forEach(row => {
                const plate     = (row.dataset.plate    || '').toLowerCase();
                const owner     = (row.dataset.owner    || '').toLowerCase();
                const rowCat    = row.dataset.category  || '';
                const rowStatus = row.dataset.filterStatus || '';

                const matchSearch = !search || plate.includes(search) || owner.includes(search);
                const matchCat    = !cat    || rowCat === cat;
                const matchStatus = !status || rowStatus === status;

                const visible = matchSearch && matchCat && matchStatus;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const noResult = document.getElementById('filter-no-result');
            if (noResult) noResult.classList.toggle('hidden', visibleCount > 0);

            // Uncheck rows hidden by the filter so they don't get silently included in bulk actions
            document.querySelectorAll('#inspections-table-body tr[id^="row-"]').forEach(row => {
                if (row.style.display === 'none') {
                    const cb = row.querySelector('.row-checkbox');
                    if (cb) cb.checked = false;
                }
            });
            updateBulkDeleteBtn();
            syncSelectAllState();
        }

        function resetFilters() {
            document.getElementById('search-input').value       = '';
            document.getElementById('filter-category').value   = '';
            document.getElementById('filter-status').value     = '';
            filterTable();
        }

        // Moves a row to the top of the table (used when an inspection starts)
        function moveRowToTop(row) {
            const tableBody = document.getElementById('inspections-table-body');
            if (!tableBody || !row) return;
            if (tableBody.firstElementChild === row) return;
            tableBody.insertBefore(row, tableBody.firstElementChild);
            row.classList.add('row-highlight');
            setTimeout(() => row.classList.remove('row-highlight'), 1200);
        }

        // Active/désactive le mode maintenance pour les utilisateurs "écran"
        async function toggleMaintenanceMode() {
            const btn = document.getElementById('maintenance-toggle-btn');
            if (!btn) return;
            const currentlyEnabled = btn.dataset.enabled === '1';
            const nextEnabled = !currentlyEnabled;

            let message = null;
            if (nextEnabled) {
                const { value } = await Swal.fire({
                    title: 'Activer la maintenance',
                    input: 'text',
                    inputPlaceholder: "Message affiché à l'écran (optionnel)",
                    showCancelButton: true,
                    confirmButtonText: 'Activer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#f59e0b',
                });
                if (value === undefined) return; // annulé
                message = value || null;
            } else {
                const confirm = await Swal.fire({
                    title: 'Désactiver la maintenance ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Désactiver',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#16a34a',
                });
                if (!confirm.isConfirmed) return;
            }

            try {
                const data = await apiFetch('/maintenance/toggle', {
                    method: 'POST',
                    body: { enabled: nextEnabled, message }
                });
                if (data.success) {
                    btn.dataset.enabled = data.enabled ? '1' : '0';
                    const label = document.getElementById('maintenance-toggle-label');
                    if (label) label.textContent = data.enabled ? 'Maintenance : ON' : 'Maintenance : OFF';
                    btn.classList.toggle('bg-amber-500/10', data.enabled);
                    btn.classList.toggle('hover:bg-amber-500/20', data.enabled);
                    btn.classList.toggle('text-amber-500', data.enabled);
                    btn.classList.toggle('border-amber-500/30', data.enabled);
                    btn.classList.toggle('bg-slate-100', !data.enabled);
                    btn.classList.toggle('dark:bg-white/5', !data.enabled);
                    btn.classList.toggle('text-slate-600', !data.enabled);
                    btn.classList.toggle('dark:text-slate-300', !data.enabled);
                    btn.classList.toggle('border-slate-200/80', !data.enabled);
                    btn.classList.toggle('dark:border-white/10', !data.enabled);
                    Swal.fire({ icon: 'success', title: data.enabled ? 'Maintenance activée' : 'Maintenance désactivée', timer: 1200, showConfirmButton: false });
                }
            } catch (err) {
                Swal.fire('Erreur', err.message || "Impossible de changer l'état de maintenance.", 'error');
            }
        }

        // Sets the navbar total count directly (value comes from the server:
        // either the AJAX response's `totalCount` field, or the Reverb
        // broadcast's `totalCount` field — there is no separate endpoint).
        function setTotalCount(n) {
            if (n === undefined || n === null) return;
            const el = document.getElementById('total-inspections-count');
            if (el) el.textContent = n;
        }

        // Re-inserts the "Aucun véhicule trouvé" placeholder row whenever the
        // table body no longer has any real data rows (e.g. after deleting
        // the last visible vehicle).
        function checkEmptyTable() {
            const tableBody = document.getElementById('inspections-table-body');
            if (!tableBody) return;
            const hasRows = tableBody.querySelector('tr[id^="row-"]');
            if (!hasRows && !document.getElementById('empty-state-row')) {
                const colspan = document.querySelectorAll('thead th').length || 7;
                const tr = document.createElement('tr');
                tr.id = 'empty-state-row';
                tr.innerHTML = `
                    <td colspan="${colspan}" class="px-8 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2m0 0h10m-10 0h10m1-10v4l2 2m0 0h1m-1 0v2"/></svg>
                            </div>
                            <p class="text-sm font-medium text-slate-400 dark:text-slate-600">Aucun véhicule trouvé</p>
                        </div>
                    </td>`;
                tableBody.appendChild(tr);
            }
        }

        function startSingleTimer(timer) {
            const id = timer.dataset.id;
            if (activeIntervals[id]) clearInterval(activeIntervals[id]);

            let startRaw = timer.dataset.start;
            if (startRaw && !isNaN(startRaw)) startRaw = parseInt(startRaw, 10);

            const startTime  = new Date(startRaw).getTime();
            const duration   = parseInt(timer.dataset.duration, 10);
            const isActive   = timer.dataset.active === 'true';

            if (isNaN(startTime) || isNaN(duration) || !isActive) {
                timer.innerText = '00:00';
                timer.classList.remove('text-amber-500');
                timer.classList.add('text-gray-400', 'dark:text-gray-500');
                return;
            }

            const durationMs  = duration * 60 * 1000;
            const updateTimer = () => {
                const remaining = durationMs - (Date.now() - startTime);
                if (remaining <= 0) {
                    clearInterval(activeIntervals[id]);
                    timer.innerText = '00:00';
                    timer.classList.remove('text-amber-500');
                    timer.classList.add('text-gray-400');
                    return;
                }
                const m = Math.floor(remaining / 60000);
                const s = Math.floor((remaining % 60000) / 1000);
                timer.innerText = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
                timer.classList.remove('text-gray-400','text-gray-500');
                timer.classList.add('text-amber-500');
            };
            updateTimer();
            activeIntervals[id] = setInterval(updateTimer, 1000);
        }

        function initTimers() {
            document.querySelectorAll('.timer-display').forEach(timer => {
                if (timer.dataset.active === 'true') startSingleTimer(timer);
            });
        }

        function showAlert(type, title, text) {
            Swal.fire({ icon: type, title, text, timer: type === 'success' ? 1500 : undefined, showConfirmButton: type === 'error' });
        }

        // ── Escape user-supplied text before injecting into innerHTML ───
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        // ── Edit Modal ─────────────────────────────────────────────
        function openEditModal(id) {
            const modal   = document.getElementById('editInspectionModal');
            const card    = document.getElementById('editModalContent');
            const overlay = document.getElementById('editModalOverlay');
            const btn     = document.querySelector(`.edit-btn[data-id="${id}"]`);

            const plate    = btn?.dataset.plate    || '';
            const owner    = btn?.dataset.owner    || '';
            const category = btn?.dataset.category || 'VL';

            document.getElementById('edit_inspection_id').value = id;
            document.getElementById('edit_plate_number').value  = plate;
            document.getElementById('edit_owner_name').value    = owner;
            document.getElementById('edit_category').value      = category;
            document.querySelectorAll('input[name="edit_cat_radio"]').forEach(r => { r.checked = (r.value === category); });

            modal.style.display = 'flex';
            void modal.offsetWidth;
            setTimeout(() => {
                overlay?.classList.add('opacity-100');
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeEditModal() {
            const modal   = document.getElementById('editInspectionModal');
            const card    = document.getElementById('editModalContent');
            const overlay = document.getElementById('editModalOverlay');

            overlay?.classList.remove('opacity-100');
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.style.display = 'none';
                document.getElementById('editInspectionForm').reset();
                document.querySelectorAll('input[name="edit_cat_radio"]').forEach(r => r.checked = false);
            }, 300);
        }

        function submitEditForm(event) {
            event.preventDefault();
            const id        = document.getElementById('edit_inspection_id').value;
            const submitBtn = document.getElementById('edit-submit-btn');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.innerText = 'Enregistrement…'; }

            apiFetch(`/inspections/${id}`, {
                method: 'PATCH',
                body: {
                    plate_number: document.getElementById('edit_plate_number').value,
                    owner_name:   document.getElementById('edit_owner_name').value,
                    category:     document.getElementById('edit_category').value
                }
            })
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Modifié', 'Les informations ont été mises à jour avec succès.');
                    closeEditModal();
                    const row = document.getElementById(`row-${id}`);
                    if (row && data.inspection) updateRowData(row, data.inspection);
                }
            })
            .catch(err => showAlert('error', 'Erreur', err.message))
            .finally(() => { if (submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'Enregistrer'; } });
        }

        function updateRowData(row, inspection) {
            const sep  = inspection.plate_number.includes('|') ? '|' : '-';
            const parts = inspection.plate_number.split(sep);
            const formatted = parts.length === 3 ? `${parts[0]} · ${parts[1]} · ${parts[2]}` : inspection.plate_number;

            const cells = row.getElementsByTagName('td');
            // Indices : 0=checkbox, 1=plaque, 2=propriétaire, 3=cat, 4=statut, 5=temps, 6=technicien, 7=lane
            if (cells.length >= 8) {
                cells[1].innerText = formatted;
                cells[2].innerText = inspection.owner_name || '---';

                // Mise à jour des data attributes
                row.dataset.plate    = inspection.plate_number.toLowerCase();
                row.dataset.owner    = (inspection.owner_name || '').toLowerCase();
                row.dataset.category = inspection.category;

                // Mise à jour du badge catégorie
                const badge = cells[3].querySelector('span');
                if (badge) {
                    badge.innerText   = inspection.category;
                    badge.className   = `badge ${inspection.category === 'PL' ? 'text-orange-600 bg-orange-50 border border-orange-200 dark:text-orange-400 dark:bg-orange-950/30 dark:border-orange-900/50' : 'text-green-700 bg-green-50 border border-green-200 dark:text-green-400 dark:bg-green-950/30 dark:border-green-900/50'}`;
                }

                // Mise à jour du technicien et de la ligne
                cells[6].textContent = inspection.technician_name || '—';
                if (inspection.lane) {
                    const laneColors = {
                        'VL2': 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50',
                        'VL4': 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-900/50',
                        'PL1': 'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-900/50'
                    };
                    cells[7].innerHTML = `<span class="badge ${laneColors[inspection.lane] || ''}">${inspection.lane}</span>`;
                } else {
                    cells[7].innerHTML = '<span class="text-sm text-slate-400 dark:text-slate-500">—</span>';
                }
            }

            const editBtn = row.querySelector('.edit-btn');
            if (editBtn) {
                editBtn.dataset.plate    = inspection.plate_number;
                editBtn.dataset.owner    = inspection.owner_name || '';
                editBtn.dataset.category = inspection.category;
            }
            const timer = row.querySelector('.timer-display');
            if (timer) {
                timer.dataset.duration = inspection.category === 'VL' ? 20 : 30;
                if (timer.dataset.active === 'true') startSingleTimer(timer);
            }
        }

        // ── Bulk Delete ─────────────────────────────────────────────────
        function toggleAllCheckboxes() {
            const checked = document.getElementById('select-all').checked;
            document.querySelectorAll('#inspections-table-body tr[id^="row-"]').forEach(row => {
                if (row.style.display === 'none') return; // ignore filtered-out rows
                const cb = row.querySelector('.row-checkbox');
                if (cb) cb.checked = checked;
            });
            updateBulkDeleteBtn();
        }

        function syncSelectAllState() {
            const visibleRows = Array.from(document.querySelectorAll('#inspections-table-body tr[id^="row-"]'))
                .filter(row => row.style.display !== 'none');
            const visibleChecks = visibleRows.map(row => row.querySelector('.row-checkbox')).filter(Boolean);
            const selectAll = document.getElementById('select-all');
            if (!selectAll) return;
            const total   = visibleChecks.length;
            const checked = visibleChecks.filter(cb => cb.checked).length;
            selectAll.checked       = total > 0 && checked === total;
            selectAll.indeterminate = checked > 0 && checked < total;
        }

        function updateBulkDeleteBtn() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const btn = document.getElementById('bulk-delete-btn');
    const count = document.getElementById('bulk-delete-count');
    if (!btn || !count) return; // absent for non-admin users

    if (checked.length > 0) {
        btn.classList.remove('hidden');
        btn.classList.add('flex'); // affiche le bouton en mode flex
        count.textContent = checked.length;
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('flex'); // masque le bouton
    }
}

        // Sync "select all" state whenever any row checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('row-checkbox')) {
                updateBulkDeleteBtn();
                syncSelectAllState();
            }
        });

        async function performBulkDelete(libreIds) {
            try {
                const data = await apiFetch('/inspections/bulk-delete', {
                    method: 'DELETE',
                    body: { ids: libreIds }
                });

                if (data.success) {
                    libreIds.forEach(id => {
                        const row = document.getElementById(`row-${id}`);
                        if (row) {
                            gsap.to(row, {
                                opacity: 0, x: 40, scaleY: 0.85,
                                duration: 0.4, ease: 'power3.in',
                                onComplete: () => {
                                    row.remove();
                                    checkEmptyTable();
                                }
                            });
                        }
                    });

                    const selectAll = document.getElementById('select-all');
                    if (selectAll) selectAll.checked = false;
                    updateBulkDeleteBtn();
                    syncSelectAllState();
                    setTotalCount(data.totalCount);

                    Swal.fire('Terminé', data.message || `${libreIds.length} véhicule(s) supprimé(s)`, 'success');
                }
            } catch (error) {
                Swal.fire('Erreur', error.message || "Une erreur est survenue lors de la suppression.", 'error');
            }
        }

        async function bulkDelete() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checked).map(cb => parseInt(cb.dataset.id));

            // On ne peut supprimer (déplacer vers la corbeille) que les véhicules à l'état Libre
            const libreIds = [];
            const nonLibreIds = [];

            checked.forEach(cb => {
                if (cb.dataset.status === 'libre') {
                    libreIds.push(parseInt(cb.dataset.id));
                } else {
                    nonLibreIds.push(parseInt(cb.dataset.id));
                }
            });

            if (libreIds.length === 0) {
                Swal.fire('Attention', 'Aucun véhicule sélectionné n\'est à l\'état Libre.', 'warning');
                return;
            }

            if (nonLibreIds.length > 0) {
                const confirm = await Swal.fire({
                    title: 'Attention',
                    text: `${nonLibreIds.length} véhicule(s) ne sont pas à l'état Libre. Supprimer uniquement ceux qui le sont ?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer les Libre',
                    cancelButtonText: 'Annuler'
                });
                if (!confirm.isConfirmed) return;
            }

            const result = await Swal.fire({
                title: `Supprimer ${libreIds.length} véhicule(s) ?`,
                text: 'Ils seront déplacés vers la corbeille',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            performBulkDelete(libreIds);
        }

        // ── Delete all (visible + Libre) ──────────────────────────────
        async function deleteAllLibre() {
            const libreIds = Array.from(document.querySelectorAll('#inspections-table-body tr[id^="row-"]'))
                .filter(row => row.style.display !== 'none')
                .map(row => row.querySelector('.row-checkbox'))
                .filter(cb => cb && cb.dataset.status === 'libre')
                .map(cb => parseInt(cb.dataset.id));

            if (libreIds.length === 0) {
                Swal.fire('Attention', "Aucun véhicule à l'état Libre à supprimer.", 'warning');
                return;
            }

            const result = await Swal.fire({
                title: `Supprimer tous les véhicules Libre (${libreIds.length}) ?`,
                text: 'Cette action déplace tous les véhicules visibles à l\'état Libre vers la corbeille.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Oui, tout supprimer',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            performBulkDelete(libreIds);
        }

        // ── Start Inspection Modal ──────────────────────────────────────
        let startInspectionId = null;

        function openStartModal(id) {
            startInspectionId = id;
            document.getElementById('start_inspection_id').value = id;
            
            const modal = document.getElementById('startInspectionModal');
            const card = document.getElementById('startModalContent');
            const overlay = document.getElementById('startModalOverlay');
            
            // Récupération de la liste des techniciens actifs
            apiFetch('/technicians/active')
                .then(data => {
                    const select = document.getElementById('start_technician');
                    select.innerHTML = '<option value="">Sélectionner un technicien...</option>';
                    if (data.success && data.technicians) {
                        data.technicians.forEach(tech => {
                            const opt = document.createElement('option');
                            opt.value = tech.identifier;
                            opt.textContent = `${tech.name} (${tech.identifier})`;
                            select.appendChild(opt);
                        });
                    }
                })
                .catch(() => {});
            
            modal.style.display = 'flex';
            void modal.offsetWidth;
            setTimeout(() => {
                overlay?.classList.add('opacity-100');
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeStartModal() {
            const modal = document.getElementById('startInspectionModal');
            const card = document.getElementById('startModalContent');
            const overlay = document.getElementById('startModalOverlay');
            
            overlay?.classList.remove('opacity-100');
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.style.display = 'none';
                document.getElementById('startInspectionForm').reset();
                startInspectionId = null;
            }, 300);
        }

        async function submitStartForm(event) {
            event.preventDefault();

            const id = document.getElementById('start_inspection_id').value;
            const technicianIdentifier = document.getElementById('start_technician').value;
            const technicianPassword = document.getElementById('start_password').value;
            const lane = document.getElementById('start_lane').value;

            if (!technicianIdentifier || !technicianPassword || !lane) {
                Swal.fire('Attention', 'Veuillez remplir tous les champs.', 'warning');
                return;
            }

            const submitBtn = document.getElementById('start-submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Vérification…';

            try {
                const data = await apiFetch(`/inspections/${id}/status`, {
                    method: 'PATCH',
                    body: {
                        status: 'en_cours',
                        technician_identifier: technicianIdentifier,
                        technician_password: technicianPassword,
                        lane: lane
                    }
                });

                if (data.success) {
                    closeStartModal();
                    Swal.fire('Succès', 'Le contrôle a démarré avec succès.', 'success');

                    const row = document.getElementById(`row-${id}`);
                    if (row && data.inspection) {
                        updateRowData(row, data.inspection);
                        moveRowToTop(row);
                        const btn = row.querySelector('.action-btn');
                        if (btn) {
                            btn.textContent = 'Valider';
                            btn.dataset.status = 'en_cours';
                            btn.className = 'action-btn bg-green-600 hover:bg-green-700 text-white shadow-sm shadow-green-600/30 hover:scale-105 active:scale-95 shine-effect';
                        }
                        row.dataset.filterStatus = 'en_cours';
                        // Mise à jour du badge de statut
                        const statusCell = row.querySelector('.status-text');
                        if (statusCell) {
                            statusCell.innerHTML = '<span class="badge bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/60 status-en-cours">En cours</span>';
                        }
                        // Affichage du bouton de retour
                        const revertBtn = document.getElementById(`btn-revert-${id}`);
                        if (revertBtn) revertBtn.style.display = 'inline-flex';
                    }
                }
            } catch (error) {
                Swal.fire('Erreur', error.message || 'Identifiants du technicien incorrects.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Démarrer';
            }
        }

        // ── Status update ──────────────────────────────────────────
        function handleStatusUpdate(id, currentStatus) {
            if (currentStatus === 'en_cours') {
                Swal.fire({
                    title: 'Validation',
                    text: "Sélectionnez le résultat de l'inspection",
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: '✅ Favorable',
                    denyButtonText: '❌ Défavorable',
                    cancelButtonText: 'Annuler',
                }).then(result => {
                    if (result.isConfirmed)  updateStatus(id, currentStatus, 'favorable');
                    else if (result.isDenied) updateStatus(id, currentStatus, 'defavorable');
                });
                return;
            }
            
            if (currentStatus === 'libre') {
                openStartModal(id);
                return;
            }
            
            const nextLabel = currentStatus === 'valider' ? 'Imprimer' : 'Imprimer';
            Swal.fire({
                title: 'Confirmation',
                text: `Voulez-vous passer à l'étape : ${nextLabel} ?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui',
                cancelButtonText: 'Annuler'
            }).then(result => { if (result.isConfirmed) updateStatus(id, currentStatus); });
        }

        function updateStatus(id, currentStatus, result = null) {
            const nextStatus = currentStatus === 'libre' ? 'en_cours' : (currentStatus === 'en_cours' ? 'valider' : 'imprimer');
            const payload    = { status: nextStatus };
            if (result) payload.result = result;

            apiFetch(`/inspections/${id}/status`, { method: 'PATCH', body: payload })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'La mise à jour a échoué.');

                const row       = document.getElementById(`row-${id}`);
                const btn       = row?.querySelector('.action-btn');
                const revertBtn = document.getElementById(`btn-revert-${id}`);

                if (row) {
                    const fs = nextStatus === 'valider' ? (result || 'valider') : nextStatus;
                    row.dataset.filterStatus = fs;
                }

                if (btn) {
                    const newLabel = nextStatus === 'libre' ? 'Démarrer' : (nextStatus === 'en_cours' ? 'Valider' : 'Imprimer');
                    btn.textContent  = newLabel;
                    btn.dataset.status = nextStatus;
                    btn.className    = 'action-btn hover:scale-105 active:scale-95 transition-all duration-200 shine-effect ';
                    if      (nextStatus === 'libre')    btn.classList.add('bg-slate-800','hover:bg-slate-700','dark:bg-white/15','dark:hover:bg-white/25','text-white');
                    else if (nextStatus === 'en_cours') btn.classList.add('bg-green-600','hover:bg-green-700','text-white','shadow-sm','shadow-green-600/30');
                    else if (nextStatus === 'valider')  btn.classList.add('bg-slate-900','dark:bg-slate-800','text-green-400','border','border-green-500/40','hover:border-green-400','hover:text-green-300');
                    else if (nextStatus === 'imprimer') btn.classList.add('bg-blue-700','hover:bg-blue-800','text-white','shadow-sm','shadow-blue-600/30');
                }

                const statusCell = row?.querySelector('.status-text');
                if (statusCell) {
                    if      (nextStatus === 'en_cours')                    statusCell.innerHTML = '<span class="badge bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/60 status-en-cours">En cours</span>';
                    else if (nextStatus === 'valider' && result === 'favorable')    statusCell.innerHTML = '<span class="badge bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">Favorable</span>';
                    else if (nextStatus === 'valider' && result === 'defavorable')  statusCell.innerHTML = '<span class="badge bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60">Défavorable</span>';
                }

                if (revertBtn) revertBtn.style.display = nextStatus === 'libre' ? 'none' : 'inline-flex';

                const timer = row?.querySelector('.timer-display');
                if (timer) {
                    if (nextStatus === 'en_cours') {
                        timer.dataset.start  = data.inspection?.started_at || new Date().toISOString();
                        timer.dataset.active = 'true';
                        startSingleTimer(timer);
                    } else {
                        timer.dataset.active = 'false';
                        if (activeIntervals[id]) clearInterval(activeIntervals[id]);
                        timer.innerText = '00:00';
                        timer.classList.remove('text-amber-500');
                        timer.classList.add('text-gray-400');
                    }
                }

                if (nextStatus === 'imprimer' && row) {
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity    = '0';
                    row.style.transform  = 'translateX(30px)';
                    setTimeout(() => { row.remove(); checkEmptyTable(); }, 500);
                }

                setTotalCount(data.totalCount);
                showAlert('success', 'Mis à jour', "Le statut de l'inspection a été mis à jour avec succès.");
            })
            .catch(err => { console.error(err); showAlert('error', 'Erreur', 'Une erreur est survenue lors de la mise à jour du statut.'); });
        }

        // ── Corbeille ─────────────────────────────────────────────
        function confirmTrash(id) {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Ce véhicule sera déplacé vers la corbeille',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then(result => {
                if (result.isConfirmed) {
                    apiFetch(`/inspections/${id}`, { method: 'DELETE' })
                    .then(data => {
                        if (data.success) {
                            const row = document.getElementById(`row-${id}`);
                            if (row) {
                                gsap.to(row, {
                                    opacity: 0, x: 40, scaleY: 0.85, duration: 0.4, ease: 'power3.in',
                                    onComplete: () => { row.remove(); checkEmptyTable(); }
                                });
                            }
                            setTotalCount(data.totalCount);
                            showAlert('success', 'Succès', data.message);
                        }
                    })
                    .catch(err => showAlert('error', 'Erreur', err.message));
                }
            });
        }

        // ── Retour à l'état précédent ────────────────────────────────
        function confirmRevert(id) {
            Swal.fire({
                title: "Annuler la dernière étape ?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui',
                cancelButtonText: 'Non'
            }).then(result => {
                if (result.isConfirmed) {
                    apiFetch(`/inspections/${id}/revert`, { method: 'POST' })
                    .then(data => {
                        if (data.success) {
                            const row        = document.getElementById(`row-${id}`);
                            const actionBtn  = row?.querySelector('.action-btn');
                            const revertBtn  = document.getElementById(`btn-revert-${id}`);
                            const statusCell = row?.querySelector('.status-text');
                            const newStatus  = data.inspection?.status ?? 'libre';

                            if (row) row.dataset.filterStatus = newStatus;

                            if (actionBtn) {
                                actionBtn.className = 'action-btn hover:scale-105 active:scale-95 transition-all duration-200 shine-effect ';
                                if (newStatus === 'libre') {
                                    actionBtn.textContent = 'Démarrer';
                                    actionBtn.classList.add('bg-slate-800','hover:bg-slate-700','dark:bg-white/15','text-white');
                                    statusCell && (statusCell.innerHTML = '<span class="badge bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/15">Libre</span>');
                                } else if (newStatus === 'en_cours') {
                                    actionBtn.textContent = 'Valider';
                                    actionBtn.classList.add('bg-green-600','hover:bg-green-700','text-white');
                                    statusCell && (statusCell.innerHTML = '<span class="badge bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/60 status-en-cours">En cours</span>');
                                } else if (newStatus === 'valider') {
                                    actionBtn.textContent = 'Imprimer';
                                    actionBtn.classList.add('bg-slate-900','dark:bg-slate-800','text-green-400','border','border-green-500/40','hover:border-green-400','hover:text-green-300');
                                } else if (newStatus === 'imprimer') {
                                    actionBtn.textContent = 'Imprimer';
                                    actionBtn.classList.add('bg-blue-700','hover:bg-blue-800','text-white','shadow-sm','shadow-blue-600/30');
                                }
                                actionBtn.dataset.status = newStatus;
                            }

                            if (revertBtn) revertBtn.style.display = newStatus === 'libre' ? 'none' : 'inline-flex';

                            const timer = row?.querySelector('.timer-display');
                            if (timer) {
                                if (newStatus === 'en_cours') {
                                    timer.dataset.start  = data.inspection?.started_at || new Date().toISOString();
                                    timer.dataset.active = 'true';
                                    startSingleTimer(timer);
                                } else {
                                    timer.dataset.active = 'false';
                                    if (activeIntervals[id]) clearInterval(activeIntervals[id]);
                                    timer.innerText = '00:00';
                                    timer.classList.remove('text-amber-500');
                                    timer.classList.add('text-gray-500');
                                }
                            }

                            setTotalCount(data.totalCount);
                            Swal.fire({ icon: 'success', title: 'Succès', timer: 1000, showConfirmButton: false });
                        }
                    })
                    .catch(err => showAlert('error', 'Erreur', err.message));
                }
            });
        }

        // ── Formulaire d'ajout ───────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('add-inspection-form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) { submitBtn.disabled = true; submitBtn.innerText = 'Enregistrement…'; }

                const formData = new FormData(form);
                const payload  = Object.fromEntries(formData.entries());

                apiFetch(form.action, { method: 'POST', body: payload })
                .then(data => {
                    if (data.success) {
                        form.reset();
                        window.dispatchEvent(new CustomEvent('close-inspection-modal'));
                        appendInspectionToTable(data.inspection);
                        setTotalCount(data.totalCount);
                        Swal.fire({ icon: 'success', title: 'Succès', text: data.message || 'Le véhicule a été ajouté avec succès.', timer: 1500, showConfirmButton: false });
                    }
                })
                .catch(err => showAlert('error', 'Erreur', err.message || 'Veuillez vérifier les informations saisies.'))
                .finally(() => { if (submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'Enregistrer'; } });
            });
        });

        // ── Append new row ─────────────────────────────────────────
        function appendInspectionToTable(inspection) {
            const tableBody = document.getElementById('inspections-table-body');
            if (!tableBody) return;
            if (document.getElementById(`row-${inspection.id}`)) return;
            const empty = document.getElementById('empty-state-row');
            if (empty) empty.remove();

            const rawPlate = inspection.plate_number;
            const sep      = rawPlate.includes('|') ? '|' : (rawPlate.includes('-') ? '-' : null);
            let formatted  = rawPlate;
            if (sep) {
                const parts = rawPlate.split(sep).map(p => p.trim());
                if (parts.length === 3) formatted = `${parts[0]} · ${parts[1]} · ${parts[2]}`;
            }

            const catClass = inspection.category === 'PL'
                ? 'text-orange-600 bg-orange-50 border border-orange-200 dark:text-orange-400 dark:bg-orange-950/30 dark:border-orange-900/50'
                : 'text-green-700 bg-green-50 border border-green-200 dark:text-green-400 dark:bg-green-950/30 dark:border-green-900/50';

            const laneColors = {
                'VL2': 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50',
                'VL4': 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-900/50',
                'PL1': 'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-900/50'
            };

            const newRow = document.createElement('tr');
            newRow.id        = `row-${inspection.id}`;
            newRow.className = 'group transition-all duration-200';
            newRow.dataset.plate        = rawPlate.toLowerCase();
            newRow.dataset.owner        = (inspection.owner_name || '').toLowerCase();
            newRow.dataset.category     = inspection.category;
            newRow.dataset.filterStatus = 'libre';

            const safeFormatted = escapeHtml(formatted);
            const safeOwner     = escapeHtml(inspection.owner_name || '---');
            const safePlate     = escapeHtml(inspection.plate_number);

            newRow.innerHTML = `
                @if(auth()->user()->role === 'admin')
                <td class="text-center">
                    <input type="checkbox" class="row-checkbox vt-checkbox" data-id="${inspection.id}" data-status="libre">
                </td>
                @endif
                <td class="font-plate font-bold text-green-600 dark:text-green-400 tracking-wider text-lg" dir="ltr">${safeFormatted}</td>
                <td class="font-semibold text-slate-700 dark:text-slate-200 text-base truncate max-w-[150px]">${safeOwner}</td>
                <td class="text-center">
                    <span class="badge ${catClass}">${inspection.category === 'PL' ? '🚛' : '🚗'} ${inspection.category}</span>
                </td>
                <td class="text-center status-text" id="status-${inspection.id}">
                    <span class="badge bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/15">Libre</span>
                </td>
                <td class="text-center">
                    <div class="timer-display font-plate text-lg font-bold text-slate-400 dark:text-slate-500 tabular-nums"
                         id="timer-${inspection.id}"
                         data-id="${inspection.id}"
                         data-start="invalid"
                         data-duration="${inspection.category === 'VL' ? 20 : 30}"
                         data-active="false">00:00</div>
                </td>
                <td class="text-center text-sm font-medium text-slate-600 dark:text-slate-300">—</td>
                <td class="text-center"><span class="text-sm text-slate-400 dark:text-slate-500">—</span></td>
                @if(auth()->user()->role === 'admin')
                <td class="text-center">
                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                        <button type="button" onclick="confirmRevert(${inspection.id})" id="btn-revert-${inspection.id}" style="display:none;"
                                class="icon-btn border border-slate-200 dark:border-white/15 bg-white dark:bg-white/5 text-slate-500 dark:text-slate-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 hover:text-amber-600 dark:hover:text-amber-400 hover:border-amber-200 dark:hover:border-amber-800/50 shine-effect"
                                title="Retour à l'état précédent">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </button>
                        <button type="button" onclick="openEditModal(${inspection.id})"
                                data-id="${inspection.id}"
                                data-plate="${safePlate}"
                                data-owner="${safeOwner}"
                                data-category="${inspection.category}"
                                class="edit-btn icon-btn border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 hover:border-amber-300 dark:hover:border-amber-800/70 shine-effect"
                                title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button type="button" onclick="handleStatusUpdate(${inspection.id}, this.dataset.status)" data-status="libre"
                                class="action-btn bg-slate-800 hover:bg-slate-700 dark:bg-white/15 dark:hover:bg-white/25 text-white hover:scale-105 active:scale-95 shine-effect">
                            Démarrer
                        </button>
                        <button type="button" onclick="confirmTrash(${inspection.id})" title="Supprimer"
                                class="icon-btn border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-950/20 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 hover:border-red-300 dark:hover:border-red-800/60 shine-effect">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
                @endif
            `;

            tableBody.insertBefore(newRow, tableBody.firstChild);
            initTimers();
        }

        // ── Reverb real-time sync ──────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            initTimers();

            if (typeof Echo === 'undefined') return;

            // Écran de maintenance en temps réel (n'affecte pas l'admin)
            const isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
            Echo.channel('maintenance-channel')
                .listen('.maintenance.changed', (data) => {
                    if (isAdmin) return; // l'admin n'est jamais bloqué par la maintenance
                    const overlay = document.getElementById('maintenance-overlay');
                    if (!overlay) return;
                    const msgEl = document.getElementById('maintenance-message');
                    if (msgEl && data.message) msgEl.textContent = data.message;
                    overlay.classList.toggle('hidden', !data.enabled);
                    overlay.classList.toggle('flex', data.enabled);
                });

            Echo.channel('inspections-channel')
                .listen('.inspection.changed', (data) => {
                    const inspection = data.inspection;
                    const actionType = data.actionType;
                    const row        = document.getElementById(`row-${inspection.id}`);

                    if (actionType === 'delete' && row) {
                        gsap.to(row, {
                            opacity: 0, x: 30, duration: 0.35, ease: 'power2.in',
                            onComplete: () => {
                                row.remove();
                                checkEmptyTable();
                                setTotalCount(data.totalCount);
                                updateBulkDeleteBtn();
                                syncSelectAllState();
                            }
                        });
                        return;
                    }

                    if ((actionType === 'update' || actionType === 'revert') && row) {
                        const fs = inspection.status === 'valider' ? (inspection.result || 'valider') : inspection.status;
                        row.dataset.filterStatus = fs;

                        if (inspection.status === 'en_cours') {
                            moveRowToTop(row);
                        }

                        const statusCell = row.querySelector('.status-text');
                        if (statusCell) {
                            if      (inspection.status === 'en_cours')                               statusCell.innerHTML = '<span class="badge bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/60 status-en-cours">En cours</span>';
                            else if (inspection.status === 'valider' && inspection.result === 'favorable')   statusCell.innerHTML = '<span class="badge bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">Favorable</span>';
                            else if (inspection.status === 'valider' && inspection.result === 'defavorable') statusCell.innerHTML = '<span class="badge bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60">Défavorable</span>';
                            else if (inspection.status === 'libre')                                  statusCell.innerHTML = '<span class="badge bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/15">Libre</span>';
                        }

                        const btn = row.querySelector('.action-btn');
                        if (btn) {
                            const newLabel = inspection.status === 'libre' ? 'Démarrer' : (inspection.status === 'en_cours' ? 'Valider' : 'Imprimer');
                            btn.textContent    = newLabel;
                            btn.dataset.status = inspection.status;
                            btn.className = 'action-btn hover:scale-105 active:scale-95 transition-all duration-200 shine-effect ';
                            if      (inspection.status === 'libre')    btn.classList.add('bg-slate-800','hover:bg-slate-700','dark:bg-white/15','text-white');
                            else if (inspection.status === 'en_cours') btn.classList.add('bg-green-600','hover:bg-green-700','text-white');
                            else if (inspection.status === 'valider')  btn.classList.add('bg-slate-900','dark:bg-slate-800','text-green-400','border','border-green-500/40');
                            else if (inspection.status === 'imprimer') btn.classList.add('bg-blue-700','hover:bg-blue-800','text-white','shadow-sm','shadow-blue-600/30');
                        }

                        const revertBtn = document.getElementById(`btn-revert-${inspection.id}`);
                        if (revertBtn) revertBtn.style.display = inspection.status === 'libre' ? 'none' : 'inline-flex';

                        const timer = row.querySelector('.timer-display');
                        if (timer) {
                            if (inspection.status === 'en_cours' && inspection.started_at) {
                                timer.dataset.start  = inspection.started_at;
                                timer.dataset.active = 'true';
                                startSingleTimer(timer);
                            } else {
                                timer.dataset.active = 'false';
                                if (activeIntervals[inspection.id]) clearInterval(activeIntervals[inspection.id]);
                                timer.innerText = '00:00';
                                timer.classList.remove('text-amber-500');
                                timer.classList.add('text-gray-400');
                            }
                        }

                        // Mise à jour de la colonne technicien et voie
                        const cells = row.getElementsByTagName('td');
                        if (cells.length >= 8) {
                            cells[6].textContent = inspection.technician_name || '—';
                            if (inspection.lane) {
                                const laneColors = {
                                    'VL2': 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50',
                                    'VL4': 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-900/50',
                                    'PL1': 'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-900/50'
                                };
                                cells[7].innerHTML = `<span class="badge ${laneColors[inspection.lane] || ''}">${inspection.lane}</span>`;
                            } else {
                                cells[7].innerHTML = '<span class="text-sm text-slate-400 dark:text-slate-500">—</span>';
                            }
                        }

                        if (inspection.status === 'imprimer') {
                            row.style.transition = 'all 0.5s ease';
                            row.style.opacity    = '0';
                            row.style.transform  = 'translateX(30px)';
                            setTimeout(() => {
                                row.remove();
                                checkEmptyTable();
                                updateBulkDeleteBtn();
                                syncSelectAllState();
                            }, 500);
                        }

                        setTotalCount(data.totalCount);
                    } else if (actionType === 'create') {
                        if (typeof appendInspectionToTable === 'function') {
                            appendInspectionToTable(inspection);
                            setTotalCount(data.totalCount);
                        }
                    } else if (actionType === 'bulk_delete') {
                        // Chaque véhicule est déjà retiré via son propre événement 'delete';
                        // on force une resynchronisation finale du compteur et des cases à cocher.
                        setTotalCount(data.totalCount);
                        checkEmptyTable();
                        updateBulkDeleteBtn();
                        syncSelectAllState();
                    }
                });
        });

        // ── Fetch daily stats ─────────────────────────────────────────
        window.fetchDailyStats = async function () {
            const els = {
                total:       document.getElementById('daily-total'),
                en_cours:    document.getElementById('daily-en-cours'),
                favorable:   document.getElementById('daily-favorable'),
                defavorable: document.getElementById('daily-defavorable'),
            };
            Object.values(els).forEach(el => { if (el) { el.textContent = '…'; el.style.opacity = '0.4'; } });

            try {
                const data = await apiFetch('/inspections/daily-stats');
                if (data.success) {
                    const s = data.stats;
                    const set = (el, val) => { if (el) { el.textContent = val ?? 0; el.style.opacity = '1'; } };
                    set(els.total,       s.total);
                    set(els.en_cours,    s.en_cours);
                    set(els.favorable,   s.favorable);
                    set(els.defavorable, s.defavorable);

                    if (s.total > 0) {
                        const pct = el => Math.round(((s[el] || 0) / s.total) * 100);
                        const favBar  = document.getElementById('bar-favorable');
                        const defBar  = document.getElementById('bar-defavorable');
                        const encBar  = document.getElementById('bar-en-cours');
                        if (favBar)  favBar.style.width  = pct('favorable')   + '%';
                        if (defBar)  defBar.style.width  = pct('defavorable') + '%';
                        if (encBar)  encBar.style.width  = pct('en_cours')    + '%';
                    }
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
                Object.values(els).forEach(el => { if (el) { el.textContent = '–'; el.style.opacity = '1'; } });
            }
        };
    </script>

</body>
</html>