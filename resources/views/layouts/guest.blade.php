<!DOCTYPE html>
<html lang="fr" x-data="{ darkMode: localStorage.getItem('dark') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ?? 'Accès au compte' }} — {{ $siteName }}</title>
    <link rel="icon" href="{{ $siteIconUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-shell">
        <aside class="auth-brand" aria-label="{{ $siteName }}">
            <a href="{{ route('login') }}" class="auth-wordmark"><span class="auth-logo"><img src="{{ $siteLogoUrl }}" alt=""></span><span class="auth-name" dir="auto">{{ $siteName }}</span><span class="auth-core">CORE</span></a>
            <div class="auth-brand-content">
                <span class="auth-eyebrow"><span class="auth-live-dot"></span> CENTRE DE VISITE TECHNIQUE</span>
                <h2>Chaque visite,<br>un suivi <em>précis.</em></h2>
                <p>Votre espace pour organiser les inspections et garder une longueur d'avance.</p>
                <div class="auth-workflow" aria-label="Suivi des inspections">
                    <div><span>01</span><strong>Accueil</strong><i></i></div>
                    <div><span>02</span><strong>Inspection</strong><i></i></div>
                    <div><span>03</span><strong>Validation</strong><i></i></div>
                </div>
            </div>
            <div class="auth-brand-footer"><span class="auth-live-dot"></span> Une équipe. Un espace. Tout simplement.</div>
        </aside>
        <section class="auth-main">
            <header class="auth-topbar"><span>ESPACE DE TRAVAIL</span><button type="button" class="auth-theme" @click="darkMode = !darkMode; localStorage.setItem('dark', darkMode)" :aria-pressed="darkMode" aria-label="Changer le thème"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M20.9 13A9 9 0 0111 3.1 9 9 0 1020.9 13Z"/></svg></button></header>
            <div class="auth-panel">{{ $slot }}</div>
            <footer class="auth-footer"><span>© {{ date('Y') }} {{ $siteName }}</span><span>Gestion des visites techniques</span></footer>
        </section>
    </main>
</body>
</html>
