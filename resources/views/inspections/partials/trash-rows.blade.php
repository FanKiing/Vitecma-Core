@forelse($inspections as $inspection)
                        <tr class="trash-row transition-all duration-150 hover:shadow-sm"
                            id="row-{{ $inspection->id }}"
                            data-id="{{ $inspection->id }}"
                            data-plate="{{ strtolower($inspection->plate_number) }}"
                            data-owner="{{ strtolower($inspection->owner_name ?? '') }}"
                            data-category="{{ $inspection->category }}">
                            <td class="font-plate font-bold text-slate-700 dark:text-slate-200 tracking-wider text-lg" dir="ltr">
                                {{ str_replace('|', ' · ', $inspection->plate_number) }}
                            </td>
                            <td class="font-semibold text-slate-600 dark:text-slate-300 text-base">
                                {{ $inspection->owner_name ?? '---' }}
                            </td>
                            <td class="text-center">
                                <x-inspection-category :category="$inspection->category" />
                            </td>
                            <td class="text-center text-base font-semibold text-slate-500 dark:text-slate-400">
                                {{ $inspection->deleted_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="handleTrashAction({{ $inspection->id }}, '{{ route('inspections.restore', $inspection->id) }}', 'POST', 'Voulez-vous restaurer ce véhicule ?', 'Restauré avec succès')"
                                            class="p-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800/50 rounded-xl transition-all shine-effect hover:scale-105 active:scale-95" title="Restaurer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </button>
                                    <button onclick="handleTrashAction({{ $inspection->id }}, '{{ route('inspections.forceDestroy', $inspection->id) }}', 'DELETE', 'Ce véhicule sera supprimé définitivement.', 'Supprimé définitivement')"
                                            class="p-2.5 bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800/50 rounded-xl transition-all shine-effect hover:scale-105 active:scale-95" title="Supprimer définitivement">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-state-row">
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-400 dark:text-slate-600">La corbeille est vide</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
