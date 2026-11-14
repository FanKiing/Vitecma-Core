<div wire:poll.30s.visible="refreshState" data-livewire-inspections data-scope="{{ $scope }}" data-initial-state="{{ json_encode(collect($state)->except(['html', 'paginationHtml'])->all()) }}" data-server-time="{{ $state['serverTime'] }}">
    @include('inspections.partials.'.$scope.'-header')
        <tbody id="{{ $scope === 'dashboard' ? 'inspections' : $scope }}-table-body">{!! $state['html'] !!}</tbody>
    </table>
    <div id="inspection-pagination">{!! $state['paginationHtml'] !!}</div>
</div>
