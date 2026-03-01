<div x-data="{
    notifications: [],
    add(message, type = 'success') {
        const id = Date.now();
        this.notifications.push({ id, message, type });
        setTimeout(() => this.remove(id), 5000);
    },
    remove(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    }
}"
@notify.window="add($event.detail.message, $event.detail.type)"
class="fixed bottom-5 right-5 z-[100] flex flex-col gap-3 pointer-events-none">

    <template x-for="notification in notifications" :key="notification.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="pointer-events-auto flex items-center w-full max-w-sm overflow-hidden bg-white rounded-lg shadow-lg border-l-4"
             :class="{
                 'border-green-500': notification.type === 'success',
                 'border-red-500': notification.type === 'error',
                 'border-blue-500': notification.type === 'info',
                 'border-yellow-500': notification.type === 'warning'
             }">

            <div class="p-4 flex items-start">
                <div class="flex-shrink-0">
                    <!-- Success Icon -->
                    <svg x-show="notification.type === 'success'" class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Error Icon -->
                    <svg x-show="notification.type === 'error'" class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Info Icon -->
                    <svg x-show="notification.type === 'info'" class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Warning Icon -->
                    <svg x-show="notification.type === 'warning'" class="w-6 h-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900" x-text="notification.type.charAt(0).toUpperCase() + notification.type.slice(1)"></p>
                    <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
                </div>

                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="remove(notification.id)" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary)]">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Handle PHP Session Flash Messages -->
    @if(session()->has('success'))
        <div x-init="$nextTick(() => add('{{ session('success') }}', 'success'))"></div>
    @endif

    @if(session()->has('error'))
        <div x-init="$nextTick(() => add('{{ session('error') }}', 'error'))"></div>
    @endif

    @if(session()->has('info'))
        <div x-init="$nextTick(() => add('{{ session('info') }}', 'info'))"></div>
    @endif

    @if(session()->has('warning'))
        <div x-init="$nextTick(() => add('{{ session('warning') }}', 'warning'))"></div>
    @endif
</div>
