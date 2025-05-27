<div>
    {{-- Because she competes with no one, no one can compete with her. --}}

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Chat') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    {{-- This code is from livewire/partials/settings-heading.blade.php --}}

            <div class="flex h-[550px] text-sm border rounded-xl shadow overflow-hidden bg-white">
  <!-- Sidebar: User List -->
   

  <!-- Main Chat Section -->
  <div class="w-3/4 flex flex-col">
    <!-- Header -->
    <div class="p-4 border-b bg-gray-50">
      <div class="text-lg font-semibold text-gray-800">{{$selectedUser->name}}</div>
      <div class="text-xs text-gray-500">{{$selectedUser->email}}</div>
    </div>

    <!-- Messages -->
    <div class="flex-1 p-4 overflow-y-auto space-y-2 bg-gray-50">
        @foreach($messages as $message)
      <div class="flex {{$message->sender_id === auth()->id()?'justify-end':'justify-start' }} ">
        <div class="max-w-xs px-4 py-2 rounded-2xl shadow 
        {{$message->sender_id === auth()->id()?'bg-blue-600 text-white':'bg-gray-200 text-gray-800' }}
        ">
          {{$message->message}}
        </div>
      </div>
      @endforeach
    </div>

    <!-- Input -->
    <form wire:submit="submit" class="p-4 border-t bg-white flex items-center gap-2">
      <input 
      wire:model="newMessage"

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
