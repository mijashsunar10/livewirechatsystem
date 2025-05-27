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
    public $messages;



     
    public function mount()
    {
        $this->users = User::whereNot('id',Auth::id())->latest()->get(); //means select all users except the one who is currently logged in.
      //  You're excluding the logged-in user from the list, presumably because you don't want to show them in the chat user list.

      $this->selectedUser = $this->users->first(); //This automatically selects the first user from the list.

      $this->loadMessages();

     

    }
    //when we render the livewire it call the mount

    public function selectUser($id)
    {
        $this->selectedUser = User::find($id);
         $this->loadMessages();

    }

    public function loadMessages()
    {
         $this->messages = ChatMessage::query()//Start a query builder on the ChatMessage model.
                    ->where(function($q) //This part fetches: Messages I sent to the selected user

                    {
                        $q->where("sender_id",Auth::id())
                            ->where("receiver_id",$this->selectedUser->id);//So if you are User 1 and they are User 2,
                    })
                    ->orwhere(function($q)//This part fetches:Messages they sent to me.
                    {
                        $q->where("sender_id",$this->selectedUser->id)
                            ->where("receiver_id",Auth::id());
                    })
                    ->get();//This executes the query and returns a collection of all matching messages.


    }

    public function submit()
    {
        if(!$this->newMessage) return;// if no any message do nothing

        $message=ChatMessage::create([
            'sender_id' => Auth::id(), //the ID of the currently logged-in user 
            'receiver_id' => $this->selectedUser->id, //he ID of the user you selected in the Livewire component
            'message' => $this->newMessage,//message → the text of the message you just typed
        ]);

        $this->messages->push($message);

        $this->newMessage = '' ; 
        // After saving the message, you clear the input field by resetting the $newMessage property to an empty string.



    }

    public function render()
    {
        return view('livewire.chat');
    }
}
