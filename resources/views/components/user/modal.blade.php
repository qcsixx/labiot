<!-- Modal Component using Alpine.js -->
<div x-data="modalHandler()" 
     x-on:keydown.escape.window="closeModal()"
     x-cloak>
    
    <template x-teleport="body">
        <!-- Modal Backdrop -->
        <div x-show="isOpen" 
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <!-- Modal Content -->
            <div x-show="isOpen" 
                @click.away="closeModal()"
                class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Modal Header -->
                <div class="p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="title"></h3>
                </div>
                
                <!-- Modal Body -->
                <div class="p-4">
                    <p class="text-gray-600" x-text="message"></p>
                </div>
                
                <!-- Modal Footer -->
                <div class="p-4 border-t flex justify-end space-x-3">
                    <button @click="closeModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
                        x-text="cancelText">
                    </button>
                    <button @click="confirm()"
                        class="px-4 py-2 text-white rounded-lg hover:opacity-90 transition-colors"
                        :class="confirmClass"
                        x-text="confirmText">
                    </button>
                </div>
            </div>
        </div>
    </template>
</div> 