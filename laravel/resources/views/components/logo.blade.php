@php
$size = $size ?? 'md';
$classes = match($size) {
    'sm' => 'h-8 w-8',
    'md' => 'h-10 w-10',
    'lg' => 'h-16 w-16',
    default => 'h-10 w-10',
};
@endphp

<img src="/images/logo.png" alt="Auspice Market — Bien-être bio" class="{{ $classes }} object-contain">
