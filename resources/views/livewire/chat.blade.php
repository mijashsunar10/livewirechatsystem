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
                          @foreach($users->sortByDesc(function($user) { return optional($user->lastMessage)->created_at; }) as $user)
                <div wire:click="selectUser({{$user->id}})" 
                    class="p-3 cursor-pointer hover:bg-blue-100 transition
                    {{$selectedUser->id === $user->id ? 'bg-blue-50' : ''}}">
                    <div class="flex justify-between items-start">
                        <div class="text-gray-800 {{$selectedUser->id !== $user->id && $unreadCounts[$user->id] > 0 ? 'font-bold' : ''}}">
                            {{$user->name}}
                            <!-- Show typing indicator if user is typing -->
                            <span id="typing-{{$user->id}}" class="text-xs text-gray-500 italic hidden">
                                typing...
                            </span>
                        </div>
                        <!-- Only show unread count if not the currently selected user -->
                                        @if($selectedUser->id !== $user->id && $unreadCounts[$user->id] > 0)
                        <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                            {{$unreadCounts[$user->id]}}
                        </span>
                    @endif

                    </div>
                        <div class="text-xs text-gray-500 truncate">
                            @if($user->lastMessage)
                                @if($user->lastMessage->sender_id == auth()->id())
                                    You: {{$user->lastMessage->message}}
                                @else
                                    {{$user->lastMessage->message}}
                                @endif
                            @endif
                        </div>
                        @if($user->lastMessage)
                            <div class="text-xs text-gray-400 mt-1">
                                {{$user->lastMessage->created_at->diffForHumans()}}
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
            @if($unreadCounts[$selectedUser->id] > 0)
                <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                    {{$unreadCounts[$selectedUser->id]}} unread
                </span>
            @endif
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
                <button wire:click="replyTo('{{$message->id}}')" 
                        class="text-xs p-1 rounded-full bg-gray-100 hover:bg-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l7 7m-7-7l7-7" />
                    </svg>
                </button>
                <button wire:click="toggleReactionPicker('{{$message->id}}')" 
                        class="text-xs p-1 rounded-full bg-gray-100 hover:bg-gray-200">
                    😊
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
            <div class="px-4 py-2 rounded-2xl shadow 
                {{$message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                
                {{$message->message}}
                
                <!-- Reactions -->
                @if($message->reactions->count() > 0)
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($message->getGroupedReactions() as $reaction)
                            <span class="text-xs bg-{{$message->sender_id === auth()->id() ? 'blue-700' : 'gray-300'}} rounded-full px-1">
                                {{$reaction->reaction}} {{$reaction->count}}
                            </span>
                        @endforeach
                    </div>
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
                <button wire:click="toggleReactionPicker('{{$message->id}}')" 
                        class="text-xs p-1 rounded-full bg-gray-100 hover:bg-gray-200">
                    😊
                </button>
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

        <div id="typing-indicator" class="px-4 pb-1 text-xs text-gray-400 italic h-5"></div>
        
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

<script>
document.addEventListener('Livewire:initialized', () => {
    // When current user types
    Livewire.on('userTyping', (event) => {
        window.Echo.private(`chat.${event.selectedUserID}`)
            .whisper('typing', {
                userId: event.userId,
                userName: event.userName
            });
    });

    // Focus message input when reply is clicked
    Livewire.on('focusMessageInput', () => {
        document.querySelector('[wire\\:model="newMessage"]').focus();
    });

    // Listen for typing events from others
    window.Echo.private(`chat.{{ $loginID }}`)
        .listenForWhisper('typing', (event) => {
            let typingIndicator = document.getElementById(`typing-${event.userId}`);
            if (typingIndicator) {
                typingIndicator.classList.remove('hidden');
                
                // Hide after 2 seconds of no typing
                setTimeout(() => {
                    typingIndicator.classList.add('hidden');
                }, 2000);
            }
        });

    // Clear typing indicator when switching users
    Livewire.on('clearTypingIndicator', () => {
        document.querySelectorAll('[id^="typing-"]').forEach(el => {
            el.classList.add('hidden');
        });
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

</div>