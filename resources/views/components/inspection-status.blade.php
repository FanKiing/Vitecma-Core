@props(['inspection', 'resultOnly' => false])
@php
    $key = ($resultOnly || $inspection->status === 'valider') && in_array($inspection->result, ['favorable', 'defavorable'], true)
        ? $inspection->result : $inspection->status;
    $label = match ($key) {
        'libre' => 'Libre', 'en_cours' => 'En cours', 'favorable' => 'Favorable',
        'defavorable' => 'Défavorable', 'valider' => 'Validé', 'imprimer' => 'Imprimé', default => '—',
    };
@endphp
<span class="badge inspection-status inspection-status--{{ $key }}" data-inspection-status="{{ $key }}">{{ $label }}</span>
