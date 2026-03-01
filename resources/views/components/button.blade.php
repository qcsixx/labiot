@props([
    'type' => 'submit',
    'class' => '',
    'variant' => 'primary', // primary, secondary, danger, outline
    'loadingText' => 'Memproses...',
    'disabled' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-[#0F4C81] hover:bg-[#0A3B64] focus:ring-[#0F4C81]',
        'secondary' => 'bg-gray-500 hover:bg-gray-600 focus:ring-gray-500',
        'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'outline' => 'bg-transparent border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-indigo-500',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . $class;
@endphp

<button type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        :disabled="loading"
        {{ $disabled ? 'disabled' : '' }}>

    <!-- Loading Spinner -->
    <svg x-show="loading"
         class="animate-spin -ml-1 mr-2 h-4 w-4 text-current"
         xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         style="display: none;">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <!-- Normal Content -->
    <span x-show="!loading">{{ $slot }}</span>

    <!-- Loading Text -->
    <span x-show="loading" style="display: none;">{{ $loadingText }}</span>
</button>
