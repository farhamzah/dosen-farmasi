@props(['mode' => 'auto', 'tableUrl', 'cardUrl'])
<div class="df-view-switch" data-view-mode="{{ $mode }}" role="group" aria-label="Tampilan">
    <a href="{{ $tableUrl }}" data-view="table"><x-heroicon-o-table-cells class="h-4 w-4" />Tabel</a>
    <a href="{{ $cardUrl }}" data-view="card"><x-heroicon-o-squares-2x2 class="h-4 w-4" />Kartu</a>
</div>
