@props([
    'name' => 'home',
])

@php($common = 'h-5 w-5')

@switch($name)
    @case('home')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        @break
    @case('tridharma')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M12 4v16M5 7.5c2.6 0 5 .9 7 2.5 2-1.6 4.4-2.5 7-2.5v10c-2.6 0-5 .9-7 2.5-2-1.6-4.4-2.5-7-2.5v-10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        @break
    @case('portfolio')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M8 7V5.8C8 4.8 8.8 4 9.8 4h4.4c1 0 1.8.8 1.8 1.8V7m-10 4h12M5 7h14a1 1 0 0 1 1 1v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5V8a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        @break
    @case('document')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M14 4H7.5A1.5 1.5 0 0 0 6 5.5v13A1.5 1.5 0 0 0 7.5 20h9a1.5 1.5 0 0 0 1.5-1.5V8l-4-4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 4v4h4M9 13h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        @break
    @case('inbox')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M5 5h14v10l-3 4H8l-3-4V5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M5 15h4l1.5 2h3L15 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        @break
    @case('calendar')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M7 4v3m10-3v3M5 9h14M6.5 6h11A1.5 1.5 0 0 1 19 7.5v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 5 18.5v-11A1.5 1.5 0 0 1 6.5 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        @break
    @case('bell')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M18 10a6 6 0 1 0-12 0c0 6-2 6.5-2 8h16c0-1.5-2-2-2-8ZM10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        @break
    @case('profile')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.5 20a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        @break
    @case('plus')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
        @break
    @case('search')
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="m20 20-4.2-4.2M18 11a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        @break
    @default
        <svg {{ $attributes->merge(['class' => $common]) }} aria-hidden="true" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="7" stroke="currentColor" stroke-width="1.8"/></svg>
@endswitch
