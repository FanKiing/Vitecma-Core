@forelse($inspections as $inspection)
                        @php
                            $filterStatus = $inspection->status === 'valider'
                                ? ($inspection->result ?? 'valider')
                                : $inspection->status;
                        @endphp
                        <tr class="group transition-all duration-200"
                            id="row-{{ $inspection->id }}"
                            data-plate="{{ strtolower($inspection->plate_number) }}"
                            data-owner="{{ strtolower($inspection->owner_name ?? '') }}"
                            data-category="{{ $inspection->category }}"
                            data-filter-status="{{ $filterStatus }}">

                            @if(auth()->user()->role === 'admin')
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox vt-checkbox" data-id="{{ $inspection->id }}" data-status="{{ $inspection->status }}">
                            </td>
                            @endif
                            <td class="font-plate font-bold text-green-600 dark:text-green-400 tracking-wider text-lg" dir="ltr">
                                {{ str_replace('|', ' · ', $inspection->plate_number) }}
                            </td>
                            <td class="font-semibold text-slate-700 dark:text-slate-200 text-base truncate max-w-[150px]">
                                {{ $inspection->owner_name ?? '---' }}
                            </td>
                            <td class="text-center">
                                <x-inspection-category :category="$inspection->category" />
                            </td>
                            <td class="text-center status-text" id="status-{{ $inspection->id }}">
                                <x-inspection-status :inspection="$inspection" />
                            </td>
                            <td class="text-center">
                                <div class="timer-display font-plate text-lg font-bold text-slate-400 dark:text-slate-500 tabular-nums transition-colors duration-300"
                                     id="timer-{{ $inspection->id }}"
                                     data-id="{{ $inspection->id }}"
                                     data-start="{{ $inspection->started_at ? $inspection->started_at->toIso8601String() : 'invalid' }}"
                                     data-duration="{{ $inspection->category === 'VL' ? 20 : 30 }}"
                                     data-active="{{ $inspection->status === 'en_cours' ? 'true' : 'false' }}">
                                    00:00
                                </div>
                            </td>
                            <td class="text-center text-sm font-medium text-slate-600 dark:text-slate-300">
                                {{ $inspection->technician_name ?? '—' }}
                            </td>
                            <td class="text-center">
                                @if($inspection->lane)
                                    <span class="badge {{ $inspection->lane == 'VL2' ? 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50' : ($inspection->lane == 'VL4' ? 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-900/50' : 'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-900/50') }}">
                                        {{ $inspection->lane }}
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            @if(auth()->user()->role === 'admin')
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button"
                                            onclick="confirmRevert({{ $inspection->id }})"
                                            id="btn-revert-{{ $inspection->id }}"
                                            style="{{ $inspection->status === 'libre' ? 'display:none;' : 'display:inline-flex;' }}"
                                            title="Retour à l'état précédent"
                                            class="icon-btn border border-slate-200 dark:border-white/15 bg-white dark:bg-white/5 text-slate-500 dark:text-slate-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 hover:text-amber-600 dark:hover:text-amber-400 hover:border-amber-200 dark:hover:border-amber-800/50 shine-effect">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </button>
                                    <button type="button"
                                            onclick="openEditModal({{ $inspection->id }})"
                                            data-id="{{ $inspection->id }}"
                                            data-plate="{{ $inspection->plate_number }}"
                                            data-owner="{{ $inspection->owner_name ?? '' }}"
                                            data-category="{{ $inspection->category }}"
                                            title="Modifier"
                                            class="edit-btn icon-btn border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 hover:border-amber-300 dark:hover:border-amber-800/70 shine-effect">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button"
                                            onclick="handleStatusUpdate({{ $inspection->id }}, this.dataset.status)"
                                            data-status="{{ $inspection->status }}"
                                            class="action-btn
                                                {{ $inspection->status == 'libre' ? 'bg-slate-800 hover:bg-slate-700 dark:bg-white/15 dark:hover:bg-white/25 text-white dark:text-slate-200' : '' }}
                                                {{ $inspection->status == 'en_cours' ? 'bg-green-600 hover:bg-green-700 text-white shadow-sm shadow-green-600/30' : '' }}
                                                {{ $inspection->status == 'valider' ? 'bg-slate-900 dark:bg-slate-800 text-green-400 border border-green-500/40 hover:border-green-400 hover:text-green-300' : '' }}
                                                {{ $inspection->status == 'imprimer' ? 'bg-blue-700 hover:bg-blue-800 text-white shadow-sm shadow-blue-600/30' : '' }}
                                                hover:scale-105 active:scale-95 shine-effect">
                                        {{ $inspection->status === 'libre' ? 'Démarrer' : ($inspection->status === 'en_cours' ? 'Valider' : 'Imprimer') }}
                                    </button>

                                    <button type="button"
                                            onclick="confirmTrash({{ $inspection->id }})"
                                            title="Supprimer"
                                            class="icon-btn border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-950/20 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 hover:border-red-300 dark:hover:border-red-800/60 shine-effect">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr id="empty-state-row">
                            <td colspan="{{ auth()->user()->role === 'admin' ? 9 : 7 }}" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2m0 0h10m-10 0h10m1-10v4l2 2m0 0h1m-1 0v2"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-400 dark:text-slate-600">Aucun véhicule trouvé</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
