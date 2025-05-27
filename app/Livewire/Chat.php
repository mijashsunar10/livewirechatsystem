<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $users; //declare the public proorpty in my livewire component
    public $selectedUser;

     
    public function mount()
    {
        $this->users = User::whereNot('id',Auth::id())->get(); //means select all users except the one who is currently logged in.
      //  You're excluding the logged-in user from the list, presumably because you don't want to show them in the chat user list.

      $this->selectedUser = $this->users->first();

    }
    //when we render the livewire it call the mount

    public function selectUser($id)
    {
        $this->selectedUser = User::find($id);
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
