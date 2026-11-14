@if (auth()->check() && auth()->user()->role === 'admin')
    @if (session('status') === 'branding-updated')
        <div role="status" class="fixed top-5 left-1/2 -translate-x-1/2 z-50 rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-sm text-green-800 shadow-lg" x-data="{ visible: true }" x-show="visible" x-init="setTimeout(() => visible = false, 5000)">Le nom et le logo ont été enregistrés.</div>
    @endif
    <div x-data="{ preview: null, savedName: @js($siteName), savedLogo: @js($siteLogoUrl) }" x-init="@if ($errors->branding->any() || session('open-branding')) $nextTick(() => $refs.brandingDialog.showModal()) @endif" @branding-refreshed.window="savedName = $event.detail.siteName; savedLogo = $event.detail.siteLogoUrl; if (!$refs.brandingDialog.open) $refs.brandingName.value = savedName"
         @open-branding.window="preview = null; $refs.brandingName.value = savedName; $refs.brandingFile.value = ''; if ($refs.brandingRemove) $refs.brandingRemove.checked = false; $refs.brandingDialog.showModal()">
        <dialog x-ref="brandingDialog" id="branding-modal" aria-labelledby="branding-title" class="branding-dialog w-full max-w-lg rounded-2xl border-0 bg-white dark:bg-slate-900 p-0 text-slate-800 dark:text-slate-100 shadow-2xl" @click="if ($event.target === $refs.brandingDialog) $refs.brandingDialog.close()">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 dark:border-slate-800 px-6 py-5">
                <div><p class="text-xs font-semibold uppercase tracking-widest text-green-700 dark:text-green-400">Personnalisation</p><h2 id="branding-title" class="mt-1 text-xl font-bold">Nom et logo du centre</h2></div>
                <button type="button" @click="$refs.brandingDialog.close()" aria-label="Fermer la personnalisation" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
            </div>
            <form method="POST" action="{{ route('branding.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="space-y-5 px-6 py-5">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Ces informations apparaissent sur la page de connexion et dans toute l'interface.</p>
                    <x-input-error :messages="$errors->branding->all()" />
                    <div class="flex items-center gap-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-white p-2"><img :src="preview || savedLogo" src="{{ $siteLogoUrl }}" alt="Aperçu du logo" class="max-h-full max-w-full object-contain"></div>
                        <div class="min-w-0"><p class="text-xs uppercase tracking-widest text-slate-500">Votre centre</p><p class="mt-1 break-words font-semibold" dir="auto" data-brand-name>{{ $siteName }}</p></div>
                    </div>
                    <div>
                        <label for="branding-name" class="block text-sm font-semibold">Nom du centre</label>
                        <input id="branding-name" x-ref="brandingName" name="name" value="{{ old('name', $siteName) }}" required maxlength="80" class="mt-2 block w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-green-600 focus:ring-green-600" dir="auto" autocomplete="organization">
                        <p class="mt-1 text-xs text-slate-500">80 caractères maximum.</p>
                    </div>
                    <div>
                        <label for="branding-logo" class="block text-sm font-semibold">Nouveau logo</label>
                        <input id="branding-logo" x-ref="brandingFile" name="logo" type="file" accept="image/png,image/jpeg,image/webp" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" class="mt-2 block w-full rounded-lg border border-slate-200 dark:border-slate-700 p-3 text-sm" aria-describedby="branding-logo-help">
                        <p id="branding-logo-help" class="mt-2 text-xs leading-relaxed text-slate-500">PNG, JPEG ou WebP · 2 Mo maximum · 2 000 × 2 000 pixels.<br>Laissez vide pour conserver votre logo actuel.</p>
                    </div>
                    @if ($hasCustomLogo)
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-ref="brandingRemove" name="remove_logo" value="1" class="rounded border-slate-300 text-green-700 focus:ring-green-600">Revenir au logo par défaut (sans nouveau fichier)</label>
                    @endif
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 px-6 py-4">
                    <button type="button" @click="$refs.brandingDialog.close()" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2.5 text-sm font-semibold">Annuler</button>
                    <button type="submit" class="rounded-lg bg-green-800 hover:bg-green-900 px-5 py-2.5 text-sm font-semibold text-white focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Enregistrer</button>
                </div>
            </form>
        </dialog>
    </div>
@endif
