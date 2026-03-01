@props([
    'type' => 'text', // text, circle, rect
    'lines' => 1,
    'width' => 'w-full',
    'height' => 'h-4',
    'class' => ''
])

<div class="animate-pulse space-y-3 {{ $class }}">
    @if($type === 'circle')
        <div class="rounded-full bg-gray-200 dark:bg-gray-700 {{ $width }} {{ $height }}"></div>
    @elseif($type === 'rect')
        <div class="bg-gray-200 dark:bg-gray-700 rounded {{ $width }} {{ $height }}"></div>
    @else
        @for($i = 0; $i < $lines; $i++)
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded {{ $i == $lines - 1 && $lines > 1 ? 'w-3/4' : 'w-full' }}"></div>
        @endfor
    @endif
</div>
