@props(['label', 'type' => 'default'])

@php
$typeClasses = [
    'rating' => 'bg-yellow-500 dark:bg-yellow-600 text-black dark:text-white',
    'new' => 'bg-green-500 dark:bg-green-600 text-white',
    'upcoming' => 'bg-blue-500 dark:bg-blue-600 text-white',
    'default' => 'bg-slate-700 dark:bg-slate-600 text-slate-100 dark:text-slate-200',
];
$classes = $typeClasses[$type] ?? $typeClasses['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 rounded-full font-bold text-xs uppercase tracking-widest shadow-md {$classes}"]) }}>
    {{ $label }}
</span>
