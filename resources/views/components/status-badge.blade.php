@props(['status', 'isLate' => false, 'isToday' => false, 'text' => null])

@php
    $style = \App\Helpers\StatusHelper::getBadgeClass($status, $isLate, $isToday);
    $displayText = $text ?? ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ' . $style['badge']]) }}>
    <i data-lucide="{{ $style['icon'] }}" class="w-4 h-4 mr-1.5 {{ $style['iconClass'] }}"></i>
    {{ $displayText }}
</span>
