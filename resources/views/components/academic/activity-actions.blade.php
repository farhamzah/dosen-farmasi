@props(['activity', 'context' => 'table'])
@php($menuId = 'activity-actions-'.$context.'-'.$activity->id)
<button type="button" class="df-menu-button" popovertarget="{{ $menuId }}" aria-label="Aksi {{ $activity->title }}" title="Aksi kegiatan">
    <x-heroicon-o-ellipsis-horizontal class="h-5 w-5" />
</button>
<div id="{{ $menuId }}" popover class="df-action-menu">
    <a href="{{ route('dosen.portfolio.show', $activity) }}">Lihat detail</a>
    @can('update', $activity)<a href="{{ route('dosen.portfolio.edit', $activity) }}">Edit kegiatan</a>@endcan
</div>
