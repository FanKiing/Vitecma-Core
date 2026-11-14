<x-guest-layout>
    <x-slot:title>Connexion</x-slot:title>
    <span class="auth-section-label">HEUREUX DE VOUS RETROUVER</span>
    <h1 class="auth-title">Votre journée<br>commence ici.</h1>
    <p class="auth-description">Connectez-vous à votre espace {{ $siteName }}.</p>

    <div id="errorContainer" class="auth-error" role="alert" aria-live="polite" @if (! $errors->any()) hidden @endif><span id="errorMessage">{{ $errors->first() }}</span></div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="loginForm" method="POST" action="{{ route('login.store') }}" data-redirect="{{ route('inspections.index') }}" class="auth-form">
        @csrf
        <div>
            <label for="username">Nom d'utilisateur</label>
            <div class="auth-input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0114 0v2"/></svg>
                <input id="username" name="username" value="{{ old('username') }}" required maxlength="64" autofocus autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Votre identifiant" aria-describedby="errorMessage">
            </div>
        </div>
        <div>
            <label for="password">Mot de passe</label>
            <div class="auth-input-wrap" x-data="{ show: false }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                <input id="password" type="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Votre mot de passe" aria-describedby="errorMessage">
                <button type="button" class="auth-reveal" @click="show = !show" :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" :aria-pressed="show" aria-label="Afficher le mot de passe"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
        </div>
        <div class="auth-options"><label class="auth-remember" for="remember"><input type="checkbox" id="remember" name="remember" value="1">Se souvenir de moi</label><a href="{{ route('password.request') }}">Mot de passe oublié ?</a></div>
        <button type="submit" id="submitBtn" class="auth-submit"><span id="btnText">Se connecter</span><svg id="btnIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg><span id="btnSpinner" class="auth-spinner" hidden aria-hidden="true"></span></button>
    </form>
    <p class="auth-signup">Nouveau membre ? <a href="{{ route('register') }}">Créer un compte</a></p>
    <div class="auth-security-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/><path d="m8 12 3 3 5-6"/></svg><span>Votre session est protégée.<br>Vos accès restent personnels.</span></div>
</x-guest-layout>
