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
    <title>Connection — Vitecma</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    @vite(['resources/js/app.js'])

    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uplogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-vt  { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }

        html, body {
            min-height: 100vh;
            background: #f1f5f9;
            transition: background-color 0.4s ease;
        }
        .dark html, .dark body {
            background: #0a0f1a;
        }

        /* ── Card entrance animation (GSAP will also apply) ── */
        .card-enter {
            opacity: 0;
            transform: translateY(20px) scale(0.97);
        }

        /* ── Custom select ── */
        .select-custom {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2364748b' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 12px;
            padding-right: 2.8rem;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
        .dark .select-custom {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E");
        }
        .select-custom:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
            outline: none;
        }
        .dark .select-custom:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34,197,94,0.12);
        }

        /* ── Submit shine ── */
        .btn-submit {
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(110deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s ease;
        }
        .btn-submit:hover::before {
            left: 150%;
        }

        /* ── Background grid (subtle) ── */
        .bg-grid {
            background-image: radial-gradient(circle, #94a3b8 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: 0.15;
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .dark .bg-grid {
            background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
            opacity: 1;
        }
    </style>
    <script>tailwind.config = { darkMode: 'class' };</script>
</head>

<body class="flex items-center justify-center p-4 transition-colors duration-300 relative" style="min-height:100vh;">

    {{-- Background grid --}}
    <div class="bg-grid"></div>

    {{-- Dark mode toggle --}}
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
         CARD
    ══════════════════════════════════ --}}
    <div id="loginCard" class="card-enter w-full max-w-[400px] bg-white dark:bg-[#111827] rounded-2xl shadow-xl border border-slate-200/50 dark:border-white/[0.07] p-8 transition-colors duration-300 relative z-10">

        {{-- Brand --}}
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-green-600/10 dark:bg-green-500/10 border border-green-600/20 dark:border-green-500/20 flex items-center justify-center mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Vitecma" class="w-9 h-9 object-contain">
            </div>
            <h1 class="font-vt text-2xl font-black text-slate-800 dark:text-white tracking-tight">Vitecma</h1>
            <p class="font-vt text-[0.7rem] font-medium text-slate-400 dark:text-slate-500 tracking-[0.15em] uppercase mt-0.5">Centre de Visite Technique</p>
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

            {{-- Account --}}
            <div class="space-y-1.5">
                <label for="username" class="block font-vt text-[0.7rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-[0.12em]">Compte</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <select id="username" name="username" required
                            class="select-custom w-full pl-10 pr-10 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700/70 bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 text-sm font-semibold">
                        @foreach($users as $user)
                            <option value="{{ $user->username }}" @if($loop->first) selected @endif>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Password --}}
            <div class="space-y-1.5" x-data="{ show: false }">
                <label for="password" class="block font-vt text-[0.7rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-[0.12em]">Mot de passe</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input id="password" :type="show ? 'text' : 'password'" name="password" required
                           class="w-full pl-10 pr-11 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700/70 bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 text-sm font-medium tracking-wider placeholder:text-slate-300 dark:placeholder:text-slate-600 focus:border-green-500 focus:ring-0 transition-colors"
                           placeholder="••••••••••••">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-green-600 dark:hover:text-green-400 transition-colors duration-150">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Remember & submit --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 text-green-600 focus:ring-green-500/20 bg-white dark:bg-slate-800 cursor-pointer accent-green-600">
                    <span class="font-vt text-[0.65rem] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-[0.1em] group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors">Se souvenir</span>
                </label>
            </div>

            <button type="submit" id="submitBtn"
                    class="btn-submit w-full py-3 rounded-xl font-extrabold text-white text-sm tracking-wide bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 transition-all duration-200 shadow-md hover:shadow-lg hover:shadow-green-600/25 active:scale-[0.98] flex items-center justify-center gap-2.5 focus:outline-none focus:ring-2 focus:ring-green-500/25 mt-1">
                <svg id="btnSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <svg id="btnIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span id="btnText">Se connecter</span>
            </button>
        </form>

        <p class="text-center font-vt text-[0.6rem] font-medium text-slate-400 dark:text-slate-600 mt-6 tracking-[0.1em]">
            © {{ date('Y') }} Vitecma. Tous droits réservés.
        </p>
    </div>

    {{-- ── GSAP Animation ── --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Animate card entrance
            gsap.to('#loginCard', {
                duration: 0.8,
                opacity: 1,
                y: 0,
                scale: 1,
                ease: 'power3.out',
                delay: 0.1,
            });
        });
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
                    } else {
                        errMsg.innerText = "Identifiant ou mot de passe incorrect.";
                        errBox.style.display = 'flex';
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