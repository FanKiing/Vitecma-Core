<x-guest-layout>
    <h1 class="text-xl font-bold mb-4">Vérifier votre email</h1>
    <p class="mb-4">Utilisez le lien envoyé à votre adresse email pour la vérifier.</p>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('verification.send') }}">@csrf <x-primary-button>Renvoyer le lien</x-primary-button></form>
    <a href="{{ route('profile.edit') }}" class="block mt-4 underline">Modifier mon email</a>
    <form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf <x-primary-button>Se déconnecter</x-primary-button></form>
</x-guest-layout>
