@forelse($inspections as $inspection)
                        <tr class="archive-row transition-all duration-150 hover:shadow-sm"
                            id="row-{{ $inspection->id }}"
                            data-id="{{ $inspection->id }}"
                            data-plate="{{ strtolower($inspection->plate_number) }}"
                            data-owner="{{ strtolower($inspection->owner_name ?? '') }}"
                            data-category="{{ $inspection->category }}"
                            data-result="{{ $inspection->result ?? '' }}">

                            <td class="font-plate font-bold text-slate-700 dark:text-slate-200 tracking-wider text-lg" dir="ltr">
                                {{ str_replace('|', ' · ', $inspection->plate_number) }}
                            </td>
                            <td class="font-semibold text-slate-600 dark:text-slate-300 text-base">
                                {{ $inspection->owner_name ?? '---' }}
                            </td>
                            <td class="text-center">
                                <x-inspection-category :category="$inspection->category" />
                            </td>
                            <td class="text-center">
                                <x-inspection-status :inspection="$inspection" :result-only="true" />
                            </td>
                            <td class="text-center text-sm font-medium text-slate-600 dark:text-slate-300">
                                {{ $inspection->technician_name ?? '—' }}
                            </td>
                            <td class="text-base font-semibold text-slate-500 dark:text-slate-400 text-right" id="archived-at-{{ $inspection->id }}">
                                {{ $inspection->archived_at ? \Carbon\Carbon::parse($inspection->archived_at)->format('d/m/Y H:i') : '---' }}
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-state-row">
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-400 dark:text-slate-600">L'archive est vide</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
