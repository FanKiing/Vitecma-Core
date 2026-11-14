<x-guest-layout>
    <h1 class="text-xl font-bold mb-4">Confirmer votre mot de passe</h1>
    <x-input-error :messages="$errors->all()" class="mb-4" />
    <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-4">
        @csrf
        <x-input-label for="password" value="Mot de passe" />
        <x-text-input id="password" name="password" type="password" required autofocus autocomplete="current-password" class="block w-full" />
        <x-primary-button>Confirmer</x-primary-button>
    </form>
</x-guest-layout>
