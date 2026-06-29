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

    @vite(['resources/js/app.js'])

    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uplogo.png') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* ── Grille de fond ── */
        .bg-grid-light,
        .bg-grid-dark {
            position: absolute; inset: 0;
            pointer-events: none;
            transition: opacity 0.5s ease;
        }
        .bg-grid-light {
            background-image: radial-gradient(circle, #94a3b8 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.3;
        }
        .bg-grid-dark {
            background-image: radial-gradient(circle, rgba(255,255,255,0.09) 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0;
        }
        html.dark .bg-grid-light { opacity: 0; }
        html.dark .bg-grid-dark  { opacity: 1; }

        /* ── Card ── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(22px) scale(0.985); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-card { animation: fadeSlideUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .login-card {
            transition: box-shadow 0.45s ease, background-color 0.45s ease, border-color 0.45s ease;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04);
        }
        .dark .login-card {
            box-shadow: 0 0 0 1px rgba(255,255,255,0.05),
                        0 20px 60px rgba(0,0,0,0.5),
                        0 0 40px rgba(22,163,74,0.05);
        }

        /* ── Inputs ── */
        .input-group:focus-within svg { color: #16a34a; transition: color 0.25s ease; }
        input:focus, select:focus { box-shadow: 0 0 0 3px rgba(22,163,74,0.13) !important; outline: none; }
        input, select { transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.4s ease; }

        /* ── Shine ── */
        .shine-effect { position: relative; overflow: hidden; }
        .shine-effect::before {
            content: "";
            position: absolute; top: 0; left: -120%;
            width: 50%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,.22), transparent);
            transition: .5s;
        }
        .shine-effect:hover::before { left: 150%; }
    </style>

    <script>tailwind.config = { darkMode: 'class' };</script>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 dark:bg-[#0a0f1a] text-slate-800 dark:text-slate-200 relative overflow-hidden" style="transition: background-color 0.45s ease, color 0.45s ease;">

    {{-- Grid de fond --}}
    <div class="bg-grid-light absolute z-0"></div>
    <div class="bg-grid-dark absolute z-0"></div>

    {{-- Dark Mode Toggle --}}
    <div class="relative z-10 p-6 flex justify-end">
        <button @click="toggleDark()"
                class="p-2.5 rounded-lg border border-slate-200/60 dark:border-slate-700/60 bg-white/40 dark:bg-slate-800/40 backdrop-blur-md hover:bg-white dark:hover:bg-slate-700 transition-all duration-300 shadow-sm hover:shadow-md text-slate-500 dark:text-slate-400">
            <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </button>
    </div>

    {{-- ===== MAIN ===== --}}
    <main class="relative z-10 flex-grow flex items-center justify-center px-4 pb-16">
        <div class="animate-card w-full max-w-[420px]">

            <div class="login-card relative rounded-2xl border border-slate-200/70 dark:border-white/[0.06] overflow-hidden bg-white dark:bg-[#111827]">

                {{-- Barre verte --}}
                <div class="h-[3px] w-full" style="background: linear-gradient(90deg, #15803d 0%, #16a34a 40%, #22c55e 70%, #4ade80 100%);"></div>

                {{-- Header --}}
                <div class="text-center px-8 pt-8 pb-6 border-b border-slate-100 dark:border-white/6">
                    <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-2xl mb-5 bg-gradient-to-br from-green-600/15 to-green-500/5 dark:from-green-500/15 dark:to-green-400/5 border border-green-600/20 dark:border-green-400/20 shadow-sm">
                        <img src="{{ asset('images/logo.png') }}" alt="Vitecma Logo" class="w-10 h-10 object-contain">
                    </div>
                    <h1 class="text-[1.6rem] font-black tracking-tight text-slate-800 dark:text-white leading-tight">Bienvenue</h1>
                    <p class="text-[0.8rem] mt-1.5 text-slate-400 dark:text-slate-500 font-medium tracking-wide">Connectez-vous pour gérer le centre</p>
                </div>

                {{-- Body --}}
                <div class="p-8">
                    {{-- Error --}}
                    <div id="errorContainer" class="mb-6 px-4 py-3 rounded-xl border border-red-100 dark:border-red-900/30 bg-red-50/80 dark:bg-red-900/10 text-red-600 dark:text-red-400 text-sm font-medium transition-all duration-300 flex items-center gap-3 backdrop-blur-sm"
                         style="display: {{ $errors->any() ? 'flex' : 'none' }};">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span id="errorMessage">{{ $errors->first() }}</span>
                    </div>

                    {{-- Form --}}
                    <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        {{-- Compte (select) --}}
                        <div class="space-y-1.5 input-group">
                            <label for="username" class="flex items-center gap-1.5 text-[0.7rem] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Compte
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500 dark:text-slate-500 dark:group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <select id="username" name="username" required
                                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:border-green-500 focus:ring-0 outline-none transition-all duration-300 text-[0.95rem] shadow-sm appearance-none">
                                    @foreach($users as $user)
                                        <option value="{{ $user->username }}" @if($loop->first) selected @endif>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="space-y-1.5 input-group" x-data="{ show: false }">
                            <label for="password" class="flex items-center gap-1.5 text-[0.7rem] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Mot de passe
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500 dark:text-slate-500 dark:group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <input id="password" :type="show ? 'text' : 'password'" name="password" required
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30 text-slate-800 dark:text-slate-100 placeholder:text-slate-400/80 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-900 focus:border-green-500 focus:ring-0 outline-none transition-all duration-300 text-[0.95rem] shadow-sm tracking-wide"
                                       placeholder="••••••••••••">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remember Me --}}
                        <div class="flex items-center justify-between pt-1 pb-2">
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-green-600 focus:ring-green-500/30 transition-all bg-white dark:bg-slate-800 cursor-pointer shadow-sm">
                                <span class="text-[0.75rem] font-bold text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors uppercase tracking-wide">
                                    Se souvenir de moi
                                </span>
                            </label>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" id="submitBtn"
                                class="relative overflow-hidden flex items-center justify-center w-full py-3 rounded-xl font-extrabold text-white text-[0.85rem] tracking-wide bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 shadow-lg shadow-green-600/20 active:scale-[0.98] outline-none focus:ring-2 focus:ring-green-500/30 transition-all duration-300 shine-effect">
                            <svg id="btnSpinner" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="btnText">Se connecter</span>
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-[0.7rem] mt-8 font-bold text-slate-400/80 dark:text-slate-500/80 tracking-wide uppercase">
                © {{ date('Y') }} Vitecma. Tous droits réservés.
            </p>
        </div>
    </main>

    {{-- Script AJAX --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const errorContainer = document.getElementById('errorContainer');
            const errorMessage = document.getElementById('errorMessage');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-90', 'cursor-not-allowed');
                btnText.innerText = 'Connexion...';
                btnSpinner.classList.remove('hidden');
                errorContainer.style.display = 'none';

                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    });

                    if (response.ok || response.redirected) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Connexion réussie',
                            text: 'Redirection vers le tableau de bord...',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('inspections.index') }}";
                        });
                    } else if (response.status === 422) {
                        const data = await response.json();
                        const errors = data.errors;
                        errorMessage.innerText = Object.values(errors)[0][0];
                        errorContainer.style.display = 'flex';
                    } else {
                        errorMessage.innerText = "Nom d'utilisateur ou mot de passe incorrect.";
                        errorContainer.style.display = 'flex';
                    }
                } catch (error) {
                    errorMessage.innerText = "Erreur de connexion au serveur.";
                    errorContainer.style.display = 'flex';
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-90', 'cursor-not-allowed');
                    btnText.innerText = 'Se connecter';
                    btnSpinner.classList.add('hidden');
                }
            });
        });
    </script>

</body>
</html>