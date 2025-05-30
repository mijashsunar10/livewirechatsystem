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
                    <div class="flex {{$message->sender_id === auth()->id()?'justify-end':'justify-start' }}">
                        <div class="max-w-xs px-4 py-2 rounded-2xl shadow 
                            {{$message->sender_id === auth()->id()?'bg-blue-600 text-white':'bg-gray-200 text-gray-800' }}
                            {{is_null($message->read_at) && $message->receiver_id === auth()->id() ? 'font-bold' : ''}}">
                            {{$message->message}}
                            <div class="text-xs mt-1 {{$message->sender_id === auth()->id()?'text-blue-200':'text-gray-500'}}">
                                {{$message->created_at->format('h:i A')}}
                                @if($message->sender_id === auth()->id())
                                    @if($message->read_at)
                                        ✓✓
                                    @else
                                        ✓
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="typing-indicator" class="px-4 pb-1 text-xs text-gray-400 italic h-5"></div>
            
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
    // When current user types
    document.addEventListener('Livewire:initialized', () => {
    // When current user types
    Livewire.on('userTyping', (event) => {
        window.Echo.private(`chat.${event.selectedUserID}`)
            .whisper('typing', {
                userId: event.userId,
                userName: event.userName
            });
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

    // Listen for typing events from others
    window.Echo.private(`chat.{{ $loginID }}`)
        .listenForWhisper('typing', (event) => {
            let indicator = document.getElementById("typing-indicator");
            indicator.innerText = `${event.userName} is typing...`;
            
            setTimeout(() => {
                indicator.innerText = '';
            }, 2000);
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