<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $users;
    public $selectedUser;
    public $newMessage;
    public $messages;
    public $authId;
    public $loginID;
    public $unreadCounts = [];

    public function mount()
    {
        $this->loginID = Auth::id();
        $this->authId = Auth::id();
        
        // Get users with their last message and unread counts
        $this->loadUsersWithUnreadCounts();
        
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
    }

    public function loadUsersWithUnreadCounts()
    {
        $this->users = User::whereNot('id', $this->authId)
            ->withCount(['unreadMessages' => function($query) {
                $query->where('receiver_id', $this->authId);
            }])
            ->with(['lastMessage' => function($query) {
                $query->where(function($q) {
                    $q->where('sender_id', $this->authId)
                      ->orWhere('receiver_id', $this->authId);
                })->orderBy('created_at', 'desc');
            }])
            ->orderByDesc(function($query) {
                $query->select('created_at')
                    ->from('chat_messages')
                    ->whereColumn('sender_id', 'users.id')
                    ->orWhereColumn('receiver_id', 'users.id')
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
            })
            ->get();
            
        // Store unread counts separately for easy access
        foreach ($this->users as $user) {
            $this->unreadCounts[$user->id] = $user->unread_messages_count;
        }
    }

   public function selectUser($id)
{
    $this->selectedUser = User::find($id);
    $this->loadMessages();
    
    // Mark messages as read when selecting a user
    ChatMessage::where('sender_id', $id)
        ->where('receiver_id', $this->authId)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);
        
    // Refresh unread counts
    $this->loadUsersWithUnreadCounts();
    
    // Clear typing indicator when switching users
    $this->dispatch('clearTypingIndicator');
}

    public function loadMessages()
    {
        $this->messages = ChatMessage::query()
            ->where(function($q) {
                $q->where("sender_id", $this->authId)
                    ->where("receiver_id", $this->selectedUser->id);
            })
            ->orWhere(function($q) {
                $q->where("sender_id", $this->selectedUser->id)
                    ->where("receiver_id", $this->authId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Scroll to bottom after loading messages
        $this->dispatch('scrollToBottom');
    }

    public function submit()
    {
        if(!$this->newMessage) return;

        $message = ChatMessage::create([
            'sender_id' => $this->authId,
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ]);

        $this->messages->prepend($message); // Add to beginning to maintain reverse order
        $this->newMessage = '';
        
        // Refresh users to update last message order
        $this->loadUsersWithUnreadCounts();
        
        broadcast(new MessageSent($message));
        $this->dispatch('scrollToBottom');
    }

    public function updatedNewMessage($value)
    {
        $this->dispatch("userTyping", 
            userId: $this->loginID, 
            userName: Auth::user()->name, 
            selectedUserID: $this->selectedUser->id
        );
    }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->loginID},MessageSent" => "newMessageNotification",
            "scrollToBottom" => "scrollToBottom",
        ];
    }

            
        public function newMessageNotification($message)
        {
            // If message is from currently selected user
            if($message['sender_id'] == $this->selectedUser->id) {
                $messageObj = ChatMessage::find($message['id']);
                $this->messages->prepend($messageObj);
                $this->dispatch('scrollToBottom');
                
                // Mark as read immediately since user is viewing the chat
                $messageObj->update(['read_at' => now()]);
            } else {
                // If message is from another user, update unread count
                $this->loadUsersWithUnreadCounts();
            }
        }

    public function scrollToBottom()
    {
        $this->dispatch('scrollToBottomEvent');
    }

    public function render()
    {
        return view('livewire.chat');
    }
}