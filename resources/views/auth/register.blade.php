<x-guest-layout>
    <h1 class="text-xl font-bold mb-4">Créer un compte</h1>
    <x-input-error :messages="$errors->all()" class="mb-4" />
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div><x-input-label for="name" value="Nom" /><x-text-input id="name" name="name" :value="old('name')" required autofocus autocomplete="name" class="block w-full" /></div>
        <div><x-input-label for="username" value="Nom d'utilisateur" /><x-text-input id="username" name="username" :value="old('username')" required maxlength="64" autocomplete="username" class="block w-full" /><p class="text-sm text-gray-600">Lettres minuscules, chiffres, points, tirets et traits de soulignement.</p></div>
        <div><x-input-label for="email" value="Email de récupération" /><x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="email" class="block w-full" /></div>
        <div><x-input-label for="password" value="Mot de passe (8 caractères minimum)" /><x-text-input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="block w-full" /></div>
        <div><x-input-label for="password_confirmation" value="Confirmer le mot de passe" /><x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="block w-full" /></div>
        <x-primary-button>Créer le compte</x-primary-button>
        <a href="{{ route('login') }}" class="underline text-sm">Se connecter</a>
    </form>
</x-guest-layout>
