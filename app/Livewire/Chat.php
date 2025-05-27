<?php

namespace App\Livewire;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $users; //declare the public proorpty in my livewire component
    public $selectedUser;
    public $newMessage;


     
    public function mount()
    {
        $this->users = User::whereNot('id',Auth::id())->get(); //means select all users except the one who is currently logged in.
      //  You're excluding the logged-in user from the list, presumably because you don't want to show them in the chat user list.

      $this->selectedUser = $this->users->first(); //This automatically selects the first user from the list.

    }
    //when we render the livewire it call the mount

    public function selectUser($id)
    {
        $this->selectedUser = User::find($id);
    }

    public function submit()
    {
        if(!$this->newMessage) return;// if no any message do nothing

        ChatMessage::create([
            'sender_id' => Auth::id(), //the ID of the currently logged-in user 
            'receiver_id' => $this->selectedUser->id, //he ID of the user you selected in the Livewire component
            'message' => $this->newMessage,//message → the text of the message you just typed
        ]);

        $this->newMessage = '' ; 
        // After saving the message, you clear the input field by resetting the $newMessage property to an empty string.



    }

    public function render()
    {
        return view('livewire.chat');
    }
}
