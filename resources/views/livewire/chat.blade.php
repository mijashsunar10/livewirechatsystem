<div>

    <style>
        /* Add to your CSS file */
.fixed {
    position: fixed;
}
.inset-0 {
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}
.z-50 {
    z-index: 50;
}
.object-contain {
    object-fit: contain;
}
.absolute {
    position: absolute;
}
.transform {
    transform: translateX(-50%);
}
.hover\:text-gray-300:hover {
    color: #D1D5DB;
}
    </style>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Chat') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    

    <div class="flex h-[550px] text-sm border rounded-xl shadow overflow-hidden bg-white">
        <!-- Sidebar: User List -->
       <div class="w-1/4 border-r bg-gray-50 overflow-y-auto">
    <div class="p-4 font-bold text-gray-700 border-b">Chats</div>
    <div class="divide-y">
        @foreach($users as $user)
            <div wire:click="selectUser({{$user->id}})" 
                class="p-3 cursor-pointer hover:bg-blue-100 transition
                {{$selectedUser->id === $user->id ? 'bg-blue-50' : ''}}">
                <div class="flex justify-between items-start">
                    <div class="text-gray-800 {{$selectedUser->id !== $user->id && $unreadCounts[$user->id] > 0 ? 'font-bold' : ''}}">
                        {{$user->name}}
                    </div>
                    @if($selectedUser->id !== $user->id && $unreadCounts[$user->id] > 0)
                        <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                            {{$unreadCounts[$user->id]}}
                        </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 truncate">
                    @if($user->lastConversationMessage)
                        @if($user->lastConversationMessage->sender_id == auth()->id())
                            You: {{$user->lastConversationMessage->message}}
                        @else
                            {{$user->lastConversationMessage->message}}
                        @endif
                    @endif
                </div>
                @if($user->lastConversationMessage)
                    <div class="text-xs text-gray-400 mt-1">
                        {{$user->lastConversationMessage->created_at->diffForHumans()}}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

        <!-- Main Chat Section -->
        <div class="w-3/4 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                <div>
                    <div class="text-lg font-semibold text-gray-800">{{$selectedUser->name}}</div>
                    <div class="text-xs text-gray-500">{{$selectedUser->email}}</div>
                </div>
                {{-- @if($unreadCounts[$selectedUser->id] > 0)
                    <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                        {{$unreadCounts[$selectedUser->id]}} unread
                    </span>
                @endif --}}
            </div>

            <!-- Messages -->
            <div class="flex-1 p-4 overflow-y-auto space-y-2 bg-gray-50 flex flex-col-reverse" 
                 id="messages-container">
                @foreach($messages as $message)
    <div class="flex {{$message->sender_id === auth()->id()?'justify-end':'justify-start' }}" 
         x-data="{ showActions: false, showTimestamp: false }" 
         @mouseenter="showActions = true; showTimestamp = true" 
         @mouseleave="showActions = false; showTimestamp = false">
        
        <!-- Left side actions for SENT messages (blue bubbles) -->
        @if($message->sender_id === auth()->id())
            <div class="flex items-center self-end mb-2" x-show="showActions" x-transition>
                <div class="flex space-x-1 mx-1">
                    <!-- Three dots menu -->
                    <div x-data="{ menuOpen: false }" class="relative">
                        <button @click="menuOpen = !menuOpen" class="text-xs p-1 rounded-full bg-gray-100 text-black hover:bg-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div x-show="menuOpen" @click.away="menuOpen = false" 
                             class="absolute right-0 bottom-full mb-2 w-40 bg-white rounded-md shadow-lg z-10 border text-black text-2xl border-gray-200">
                            <div class="py-1">
                                <!-- Edit option (only for text messages) -->
                                @if(!$message->isImage())
                                    <button @click="menuOpen = false; $wire.editMessage('{{$message->id}}', prompt('Edit your message:', '{{$message->message}}'))" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Edit
                                    </button>
                                @endif
                                <!-- Delete for me -->
                                <button @click="menuOpen = false; $wire.deleteMessage('{{$message->id}}')" 
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Delete for me
                                </button>
                                <!-- Delete for everyone -->
                                <button @click="menuOpen = false; $wire.deleteMessage('{{$message->id}}', true)" 
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Delete for everyone
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reply button -->
                    <button wire:click="replyTo('{{$message->id}}')" 
                            class="text-xs p-1 rounded-full bg-gray-100 text-black hover:bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l7 7m-7-7l7-7" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <div class="max-w-xs relative">
            <!-- Timestamp for SENT messages (left side) -->
            @if($message->sender_id === auth()->id())
                <div x-show="showTimestamp" class="absolute bottom-0 left-0 -translate-x-full pl-1 text-xs text-gray-500 whitespace-nowrap">
                    {{$message->created_at->format('h:i A')}}
                    @if($message->read_at)
                        ✓✓
                    @else
                        ✓
                    @endif
                </div>
            @endif

            <!-- Reply preview -->
            @if($message->reply_to)
                <div class="mb-1">
                    <div class="text-xs p-2 bg-gray-200 text-gray-800 rounded-lg shadow">
                        <div class="font-semibold">
                            Replying to {{ $message->repliedMessage->sender_id === auth()->id() ? 'yourself' : $message->repliedMessage->sender->name }}
                        </div>
                        <div class="truncate">
                            @if($message->repliedMessage->isImage())
                                [Image]
                            @else
                                {{ $message->repliedMessage->message }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Main message content -->
            @if($message->isImage())
                <!-- Image message -->
                <div class="px-1 py-1 rounded-2xl shadow {{$message->sender_id === auth()->id() ? 'bg-blue-600' : 'bg-gray-200'}}">
                    <img 
                        src="{{ Storage::url($message->image_path) }}" 
                        class="max-w-full max-h-64 rounded-lg cursor-pointer" 
                        wire:click="showImage('{{ $message->image_path }}')"
                        alt="Chat image"
                    >
                    @if($message->edited_at)
                        <span class="text-xs {{$message->sender_id === auth()->id() ? 'text-blue-200' : 'text-gray-500'}} ml-1">(edited)</span>
                    @endif
                </div>
            @else
                <!-- Text message -->
                <div class="px-4 py-2 rounded-2xl shadow 
                    {{$message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                    {{$message->message}}
                    @if($message->edited_at)
                        <span class="text-xs {{$message->sender_id === auth()->id() ? 'text-blue-200' : 'text-gray-500'}} ml-1">(edited)</span>
                    @endif
                </div>
            @endif

            <!-- Timestamp for RECEIVED messages (right side) -->
            @if($message->sender_id !== auth()->id())
                <div x-show="showTimestamp" class="absolute bottom-0 right-0 translate-x-full pr-1 text-xs text-gray-500 whitespace-nowrap">
                    {{$message->created_at->format('h:i A')}}
                </div>
            @endif
        </div>

        <!-- Right side actions for RECEIVED messages (gray bubbles) -->
        @if($message->sender_id !== auth()->id())
            <div class="flex items-center self-end mb-2" x-show="showActions" x-transition>
                <div class="flex space-x-1 mx-1">
                    <!-- Three dots menu for received messages -->
                    <div x-data="{ menuOpen: false }" class="relative">
                        <button @click="menuOpen = !menuOpen" class="text-xs p-1 rounded-full bg-gray-100 text-black hover:bg-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown menu - only delete option -->
                        <div x-show="menuOpen" @click.away="menuOpen = false" 
                             class="absolute right-0 bottom-full mb-2 w-32 bg-white rounded-md text-black  shadow-lg z-10 border border-gray-200">
                            <div class="py-1">
                                <button @click="menuOpen = false; $wire.deleteMessage('{{$message->id}}')" 
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reply button -->
                    <button wire:click="replyTo('{{$message->id}}')" 
                            class="text-xs p-1 rounded-full bg-gray-100 text-black hover:bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l7 7m-7-7l7-7" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
@endforeach
            </div>
            
            <!-- Reply preview -->
            @if($replyingTo)
                <div class="px-4 pt-2 bg-gray-100 border-t flex justify-between items-center">
                    <div class="text-xs text-gray-600">
                        <div class="font-semibold">
                            Replying to {{ $replyingTo->sender_id === auth()->id() ? 'yourself' : $replyingTo->sender->name }}
                        </div>
                        <div class="truncate">
                            {{ Str::limit($replyingTo->message, 50) }}
                        </div>
                    </div>
                    <button wire:click="cancelReply" class="text-xs text-gray-500 hover:text-gray-700">
                        × Cancel
                    </button>
                </div>
            @endif
            
            <!-- Input -->
            <form wire:submit.prevent="submit" class="p-4 border-t bg-white flex items-center gap-2">
        <!-- Image upload button -->
        <label for="image-upload" class="cursor-pointer text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <input id="image-upload" type="file" wire:model="image" class="hidden" accept="image/*">
        </label>

        <!-- Preview image -->
        @if($image)
            <div class="relative">
                <img src="{{ $image->temporaryUrl() }}" class="h-12 w-12 object-cover rounded">
                <button wire:click="removeImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs">
                    ×
                </button>
            </div>
        @endif

        <input 
            wire:model.live="newMessage"
            type="text"
            class="flex-1 border border-gray-300 text-black rounded-full px-4 py-2 text-sm focus:outline-none"
            placeholder="Type your message..." 
        />
        <button 
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-full"
        >
            Send
        </button>
    </form>
     <!-- Image Modal -->
@if($showImageModal)
<div class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50" 
     x-data
     x-init="
        // Focus the modal when it opens
        $nextTick(() => $el.focus());
        
        // Setup key listeners
        window.addEventListener('keydown', handleKeyDown);
        
        // Cleanup when modal closes
        $wire.on('imageModalClosed', () => {
            window.removeEventListener('keydown', handleKeyDown);
        });
        
        function handleKeyDown(e) {
            if (!@this.showImageModal) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    @this.call('prevImage');
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    @this.call('nextImage');
                    break;
                case 'Escape':
                    e.preventDefault();
                    @this.call('closeImageModal');
                    break;
            }
        }
     "
     wire:key="image-modal-{{ $currentImageIndex }}"
     tabindex="0"
     wire:ignore.self
>
    <!-- Close Button -->
    <button 
        wire:click="closeImageModal"
        class="absolute top-4 right-4 text-white hover:text-gray-300 z-50 focus:outline-none"
        @click.stop
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Navigation Arrows -->
    <div class="absolute inset-0 flex items-center justify-between px-4">
        @if($currentImageIndex > 0)
            <button 
                wire:click="prevImage"
                class="text-white hover:text-gray-300 z-50 focus:outline-none p-2"
                @click.stop
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        @else
            <div class="w-12"></div> <!-- Spacer -->
        @endif

        @if($currentImageIndex < count($chatImages) - 1)
            <button 
                wire:click="nextImage"
                class="text-white hover:text-gray-300 z-50 focus:outline-none p-2"
                @click.stop
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @else
            <div class="w-12"></div> <!-- Spacer -->
        @endif
    </div>

    <!-- Image Container -->
    <div class="relative max-w-4xl max-h-screen flex items-center justify-center p-4">
        <img 
            src="{{ Storage::url($currentImagePath) }}" 
            class="max-w-full max-h-screen object-contain cursor-pointer"
            alt="Chat image"
            draggable="false"
            @click.stop="window.Livewire.dispatch('close-image-modal')"
        >
    </div>

    <!-- Image Counter -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-lg bg-black bg-opacity-50 px-3 py-1 rounded-full">
        {{ $currentImageIndex + 1 }} / {{ count($chatImages) }}
    </div>
</div>

<!-- Add this script to initialize keyboard navigation -->
<script>
document.addEventListener('Livewire:initialized', () => {
    Livewire.on('init-keyboard-navigation', () => {
        // This ensures keyboard events are captured even when modal is not focused
        document.addEventListener('keydown', (e) => {
            if (!@this.showImageModal) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    @this.call('prevImage');
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    @this.call('nextImage');
                    break;
                case 'Escape':
                    e.preventDefault();
                    @this.call('closeImageModal');
                    break;
            }
        });
    });
    
    // Dispatch event when modal closes
    Livewire.on('imageModalClosed', () => {
        // Cleanup if needed
    });
});
</script>
@endif
</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('Livewire:initialized', () => {
    // Focus message input when reply is clicked
    Livewire.on('focusMessageInput', () => {
        document.querySelector('[wire\\:model="newMessage"]').focus();
    });

    // Scroll to bottom function
    Livewire.on('scrollToBottomEvent', () => {
        let container = document.getElementById('messages-container');
        container.scrollTop = container.scrollHeight;
    });

    // Initial scroll to bottom
    Livewire.dispatch('scrollToBottomEvent');
});
</script>