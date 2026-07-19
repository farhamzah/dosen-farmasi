@props([
    'interactive' => false,
])

<div {{ $attributes->merge(['class' => 'df-card '.($interactive ? 'df-interactive' : '')]) }}>
    {{ $slot }}
</div>
