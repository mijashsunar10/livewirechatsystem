<div>
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
            <button @click="menuOpen = !menuOpen" class="text-xs p-1 rounded-full bg-gray-100 hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01" />
                </svg>
            </button>
            
            <!-- Dropdown menu -->
            <div x-show="menuOpen" @click.away="menuOpen = false" 
                 class="absolute right-0 bottom-full mb-2 w-40 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                <div class="py-1">
                    <!-- Edit option -->
                    <button @click="menuOpen = false; $wire.editMessage('{{$message->id}}', prompt('Edit your message:', '{{$message->message}}'))" 
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Edit
                    </button>
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
                class="text-xs p-1 rounded-full bg-gray-100 hover:bg-gray-200">
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
                                            {{ $message->repliedMessage->message }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Main message bubble -->
                           <!-- Main message bubble -->
                    <div class="px-4 py-2 rounded-2xl shadow 
                        {{$message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                        {{$message->message}}
                        @if($message->edited_at)
                            <span class="text-xs {{$message->sender_id === auth()->id() ? 'text-blue-200' : 'text-gray-500'}} ml-1">(edited)</span>
                        @endif
                    </div>

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
            <button @click="menuOpen = !menuOpen" class="text-xs p-1 rounded-full bg-gray-100 hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01" />
                </svg>
            </button>
            
            <!-- Dropdown menu - only delete option -->
            <div x-show="menuOpen" @click.away="menuOpen = false" 
                 class="absolute right-0 bottom-full mb-2 w-32 bg-white rounded-md shadow-lg z-10 border border-gray-200">
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
                class="text-xs p-1 rounded-full bg-gray-100 hover:bg-gray-200">
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
            <form wire:submit="submit" class="p-4 border-t bg-white flex items-center gap-2">
                <input 
                    wire:model.live="newMessage"
                    type="text"
                    class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none"
                    placeholder="Type your message..." 
                />
                <button 
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-full"
                >
                    Send
                </button>
            </form>
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