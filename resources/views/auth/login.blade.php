<!DOCTYPE html>
<html lang="fr" dir="ltr"
    x-data="{
        darkMode: localStorage.getItem('dark') === 'true',
        toggleDark() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('dark', this.darkMode);
        }
    }"
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
    <title>Connexion — Vitecma</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>

    @vite(['resources/js/app.js'])

    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uplogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-vt  { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }

        html, body { min-height: 100svh; }

        /* ── Ambient orb (single, quiet) ── */
        .orb {
            position: absolute;
            width: 480px; height: 480px;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            background: radial-gradient(circle, rgba(22,163,74,0.10) 0%, transparent 70%);
            top: -180px; left: 50%;
            transform: translateX(-50%);
        }
        .dark .orb { background: radial-gradient(circle, rgba(34,197,94,0.07) 0%, transparent 70%); }

        /* ── Card shadow ── */
        .vt-card {
            box-shadow: 0 24px 60px rgba(0,0,0,0.10), 0 2px 8px rgba(0,0,0,0.04);
        }
        .dark .vt-card {
            box-shadow: 0 0 0 1px rgba(255,255,255,0.05), 0 24px 70px rgba(0,0,0,0.55), 0 0 40px rgba(22,163,74,0.05);
        }

        /* ── Status pulse dot ── */
        @keyframes sPulse {
            0%, 100% { box-shadow: 0 0 0 0   rgba(74,222,128,0.5); }
            60%       { box-shadow: 0 0 0 5px rgba(74,222,128,0);   }
        }
        .s-dot { animation: sPulse 2.4s ease-in-out infinite; }

        /* ── Inputs ── */
        .vt-field { transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.3s ease; }
        .vt-field:focus {
            border-color: #16a34a !important;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.14) !important;
            background: #fff !important;
            outline: none;
        }
        .dark .vt-field:focus {
            border-color: #22c55e !important;
            box-shadow: 0 0 0 3px rgba(34,197,94,0.12) !important;
            background: rgba(15,23,42,0.85) !important;
        }

        /* ── Custom account dropdown ── */
        .vt-combo-trigger { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .vt-combo-trigger.is-open,
        .vt-combo-trigger:focus {
            border-color: #16a34a !important;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.14) !important;
            outline: none;
        }
        .dark .vt-combo-trigger.is-open,
        .dark .vt-combo-trigger:focus {
            border-color: #22c55e !important;
            box-shadow: 0 0 0 3px rgba(34,197,94,0.12) !important;
        }
        .vt-combo-panel {
            box-shadow: 0 12px 28px rgba(0,0,0,0.14), 0 2px 8px rgba(0,0,0,0.06);
        }
        .dark .vt-combo-panel {
            box-shadow: 0 16px 36px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.06);
        }
        .vt-combo-option { transition: background-color 0.12s ease, color 0.12s ease; }

        /* ── Submit shine ── */
        .btn-submit { position: relative; overflow: hidden; }
        .btn-submit::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 55%; height: 100%;
            background: linear-gradient(110deg, transparent, rgba(255,255,255,0.18), transparent);
            transition: 0.55s ease;
        }
        .btn-submit:hover::before { left: 150%; }

        /* ── Divider ── */
        .form-divider { height: 1px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent); }
        .dark .form-divider { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent); }
    </style>
    <script>tailwind.config = { darkMode: 'class' };</script>
</head>

<body class="bg-slate-50 dark:bg-[#060c18] flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-500"
      style="min-height: 100svh;">

    <div class="orb"></div>

    {{-- ── Dark mode toggle ── --}}
    <div class="fixed top-5 right-5 z-50">
        <button @click="toggleDark()"
                class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/70 dark:border-white/10 shadow-sm hover:shadow-md text-slate-500 dark:text-slate-400 transition-all duration-200 hover:scale-105 active:scale-95">
            <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
    </div>

    {{-- ══════════════════════════════════
         LOGIN CARD
    ══════════════════════════════════ --}}
    <div id="loginCard" class="vt-card w-full max-w-[420px] rounded-2xl overflow-hidden border border-slate-200/50 dark:border-white/[0.06] bg-white dark:bg-[#0f172a] relative z-10"
         style="opacity: 0;">

        <div class="h-[3px] w-full" style="background: linear-gradient(90deg, #15803d 0%, #16a34a 40%, #22c55e 70%, #4ade80 100%);"></div>

        <div class="px-8 pt-9 pb-8">

            {{-- Header --}}
            <div class="text-center mb-7">
                <div class="mx-auto w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                     style="background: rgba(22,163,74,0.10); border: 1px solid rgba(22,163,74,0.20);">
                    <img src="{{ asset('images/logo.png') }}" alt="Vitecma" class="w-8 h-8 object-contain">
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg mb-4"
                     style="background: rgba(22,163,74,0.07); border: 1px solid rgba(22,163,74,0.15);">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-500 s-dot"></div>
                    <span class="font-vt text-green-700 dark:text-green-400 text-[0.6rem] font-bold tracking-[0.2em] uppercase">Terminal d'accès</span>
                </div>
                <h1 class="text-[1.55rem] font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Connexion
                </h1>
                <p class="text-[0.82rem] text-slate-400 dark:text-slate-500 mt-1.5 font-medium">
                    Accédez au système de gestion Vitecma
                </p>
            </div>

            {{-- Error banner --}}
            <div id="errorContainer"
                 class="mb-5 items-start gap-3 px-4 py-3 rounded-xl border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-[0.83rem] font-semibold"
                 style="display: {{ $errors->any() ? 'flex' : 'none' }};">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="errorMessage">{{ $errors->first() }}</span>
            </div>

            {{-- Form --}}
            <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- ── Compte : custom dropdown (Alpine) ── --}}
                <div class="space-y-1.5" x-data="{
                        open: false,
                        selected: null,
                        users: {{ $users->map(fn($u) => ['value' => $u->username, 'label' => $u->name])->values()->toJson() }},
                        select(u) { this.selected = u; this.open = false; }
                     }" x-init="if (users.length) selected = users[0]" @keydown.escape="open = false" @click.outside="open = false">
                    <label class="flex items-center gap-1.5">
                        <span class="text-green-500 font-vt text-[0.8rem] font-bold leading-none">&rsaquo;</span>
                        <span class="font-vt text-[0.63rem] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]">Compte</span>
                    </label>

                    <div class="relative">
                        <input type="hidden" name="username" :value="selected ? selected.value : ''">

                        <button type="button" @click="open = !open"
                                :class="open ? 'is-open' : ''"
                                class="vt-combo-trigger w-full flex items-center gap-3 pl-3.5 pr-3.5 py-[0.7rem] rounded-xl border-2 border-slate-200 dark:border-slate-700/70 bg-slate-50 dark:bg-slate-900/50 text-left">
                            <svg class="w-[1.05rem] h-[1.05rem] text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="flex-1 text-[0.9rem] font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="selected ? selected.label : 'Sélectionner un compte'"></span>
                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-cloak
                             x-transition:enter="transition duration-150 ease-out"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition duration-100 ease-in"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="vt-combo-panel absolute z-20 mt-2 w-full rounded-xl border border-slate-200 dark:border-slate-700/70 bg-white dark:bg-[#111c33] overflow-hidden py-1.5">
                            <template x-for="u in users" :key="u.value">
                                <button type="button" @click="select(u)"
                                        class="vt-combo-option w-full flex items-center gap-2.5 px-4 py-2.5 text-left text-[0.88rem] font-semibold"
                                        :class="selected && selected.value === u.value
                                            ? 'bg-green-600 text-white'
                                            : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/[0.06]'">
                                    <svg class="w-3.5 h-3.5 shrink-0" :class="selected && selected.value === u.value ? 'opacity-100' : 'opacity-0'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="u.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="space-y-1.5" x-data="{ show: false }">
                    <label for="password" class="flex items-center gap-1.5">
                        <span class="text-green-500 font-vt text-[0.8rem] font-bold leading-none">&rsaquo;</span>
                        <span class="font-vt text-[0.63rem] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]">Mot de passe</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-[1.05rem] h-[1.05rem] text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" :type="show ? 'text' : 'password'" name="password" required
                               class="vt-field w-full pl-10 pr-11 py-[0.75rem] rounded-xl border-2 border-slate-200 dark:border-slate-700/70 bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 text-[0.9rem] font-medium tracking-wider placeholder:text-slate-300 dark:placeholder:text-slate-600"
                               placeholder="••••••••••••">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-green-600 dark:hover:text-green-400 transition-colors duration-150">
                            <svg x-show="!show" class="w-[1.05rem] h-[1.05rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="w-[1.05rem] h-[1.05rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Divider + remember --}}
                <div class="form-divider"></div>
                <div class="flex items-center gap-2.5 -mt-1">
                    <input type="checkbox" id="remember" name="remember"
                           class="w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 text-green-600 focus:ring-green-500/20 bg-white dark:bg-slate-800 cursor-pointer accent-green-600">
                    <label for="remember" class="font-vt text-[0.63rem] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-[0.16em] cursor-pointer hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                        Se souvenir de moi
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" id="submitBtn"
                        class="btn-submit w-full py-3.5 rounded-xl font-extrabold text-white text-[0.88rem] tracking-wide bg-green-600 hover:bg-green-700 dark:hover:bg-green-500 transition-all duration-200 hover:shadow-lg hover:shadow-green-600/25 active:scale-[0.99] flex items-center justify-center gap-2.5 focus:outline-none focus:ring-2 focus:ring-green-500/25 mt-2">
                    <svg id="btnSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <svg id="btnIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span id="btnText">Se connecter</span>
                </button>
            </form>
        </div>

        <div class="px-8 py-4 border-t border-slate-100 dark:border-white/[0.06] text-center">
            <p class="font-vt text-[0.58rem] font-medium text-slate-400 dark:text-slate-600 tracking-[0.18em] uppercase">
                © {{ date('Y') }} Vitecma · Tous droits réservés
            </p>
        </div>
    </div>

    {{-- ── GSAP entrance ── --}}
    <script>
        gsap.fromTo('#loginCard',
            { opacity: 0, y: 26, scale: 0.97 },
            { opacity: 1, y: 0, scale: 1, duration: 0.75, ease: 'power3.out' }
        );
    </script>

    {{-- ── AJAX Script ── --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form        = document.getElementById('loginForm');
            const submitBtn   = document.getElementById('submitBtn');
            const btnText     = document.getElementById('btnText');
            const btnIcon     = document.getElementById('btnIcon');
            const btnSpinner  = document.getElementById('btnSpinner');
            const errBox      = document.getElementById('errorContainer');
            const errMsg      = document.getElementById('errorMessage');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                btnText.innerText = 'Vérification…';
                btnSpinner.classList.remove('hidden');
                btnIcon.classList.add('hidden');
                errBox.style.display = 'none';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: new FormData(form)
                    });

                    if (response.ok || response.redirected) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Accès autorisé',
                            text: 'Chargement du tableau de bord…',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('inspections.index') }}";
                        });
                    } else if (response.status === 422) {
                        const data = await response.json();
                        errMsg.innerText = Object.values(data.errors)[0][0];
                        errBox.style.display = 'flex';
                        gsap.fromTo('#errorContainer', { x: -6 }, { x: 0, duration: 0.4, ease: 'elastic.out(1, 0.4)' });
                    } else {
                        errMsg.innerText = "Identifiant ou mot de passe incorrect.";
                        errBox.style.display = 'flex';
                        gsap.fromTo('#errorContainer', { x: -6 }, { x: 0, duration: 0.4, ease: 'elastic.out(1, 0.4)' });
                    }
                } catch {
                    errMsg.innerText = "Erreur de connexion au serveur.";
                    errBox.style.display = 'flex';
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    btnText.innerText = 'Se connecter';
                    btnSpinner.classList.add('hidden');
                    btnIcon.classList.remove('hidden');
                }
            });
        });
    </script>

</body>
</html>