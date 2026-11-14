<x-guest-layout>
    <h1 class="text-xl font-bold mb-4">Mot de passe oublié</h1>
    <p class="text-sm text-gray-600 mb-4">Indiquez l'email de récupération associé à votre compte. Si vous n'en avez pas, contactez votre administrateur.</p>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <x-input-error :messages="$errors->all()" class="mb-4" />
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" :value="old('email')" required autofocus autocomplete="email" class="block w-full" />
        <x-primary-button>Envoyer le lien</x-primary-button>
        <a href="{{ route('login') }}" class="underline text-sm">Se connecter</a>
    </form>
</x-guest-layout>
