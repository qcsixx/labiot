@props(['notification'])

@php
    $style = \App\Helpers\NotificationHelper::getBorrowStatusStyle(
        $notification->status,
        false,
        false,
        $notification->message
    );
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start p-4 rounded-lg border ' . ($notification->is_read ? 'bg-gray-50' : 'bg-white border-blue-200')]) }}>
    <!-- Icon -->
    <div class="flex-shrink-0 {{ $style['iconBg'] }} p-2 rounded-full">
        <i data-lucide="{{ $style['icon'] }}" class="w-5 h-5 {{ $style['iconClass'] }}"></i>
    </div>

    <!-- Content -->
    <div class="ml-3 flex-1">
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $style['badge'] }}">
                {{ $style['badgeText'] }}
            </span>
            <span class="text-xs text-gray-500">
                {{ $notification->created_at->diffForHumans() }}
            </span>
        </div>
        <p class="mt-2 text-sm {{ $style['text'] }}">
            {{ $notification->message }}
        </p>
    </div>
</div>
