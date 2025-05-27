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
  <div class="w-1/4 border-r bg-gray-50">
    <div class="p-4 font-bold text-gray-700 border-b">Users</div>
    <div class="divide-y">

        @foreach($users as $user)
       
        <div wire:click="selectUser({{$user->id}})" 
            {{-- wire:click="selectUser({{$user->id}})" → when you click, it calls the Livewire selectUser() method with the specific user’s ID. --}}
             class="p-3 cursor-pointer hover:bg-blue-100 transitionname
             {{$selectedUser->id === $user->id ? 'bg-blue-50 font-semibold' : ''}}">
             {{--  this checks if the currently selected user matches this looped user if yes became bold if no same --}}
            <div class="text-gray-800">{{$user->name}}</div>
            <div class="text-xs text-gray-500">{{$user->email}}</div>
        </div>
       @endforeach
    </div>
  </div>

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
      <div class="flex justify-end">
        <div class="max-w-xs px-4 py-2 rounded-2xl shadow bg-blue-600 text-white">
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
