@php
$size = $size ?? 'md';
$variant = $variant ?? 'default';
$classes = match($size) {
    'sm' => 'h-8 w-8',
    'md' => 'h-10 w-10',
    'lg' => 'h-16 w-16',
    default => 'h-10 w-10',
};
@endphp

@if($variant === 'card')
<div class="grid {{ $classes }} place-items-center rounded-lg bg-white p-1">
    <img src="/images/logo.png" alt="Auspice Market — Bien-être bio" class="h-full w-full object-contain rounded-lg">
</div>
@else
<img src="/images/logo.png" alt="Auspice Market — Bien-être bio" class="{{ $classes }} object-contain rounded-lg">
@endif
