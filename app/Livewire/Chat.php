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
    public $replyingTo = null;
    

    public function mount()
    {
        $this->loginID = Auth::id();
        $this->authId = Auth::id();
        $this->loadUsersWithUnreadCounts();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
    }

  public function loadUsersWithUnreadCounts()
{
    $this->users = User::whereNot('id', $this->authId)
        ->withCount(['unreadMessages' => function($query) {
            $query->where('receiver_id', $this->authId)
                  ->whereNull('read_at');
        }])
        ->with(['lastConversationMessage' => function($query) {
            $query->where(function($q) {
                $q->where('sender_id', $this->authId)
                  ->orWhere('receiver_id', $this->authId);
            })
            ->orderBy('created_at', 'desc');
        }])
        ->get()
        ->sortByDesc(function($user) {
            return optional($user->lastConversationMessage)->created_at;
        });
        
    foreach ($this->users as $user) {
        $this->unreadCounts[$user->id] = $user->unread_messages_count;
    }
}
public function selectUser($id)
{
    // Before switching users, mark all messages from the current user as read
    if ($this->selectedUser) {
        ChatMessage::where('sender_id', $this->selectedUser->id)
            ->where('receiver_id', $this->authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    $this->selectedUser = User::find($id);
    $this->loadMessages();

    // Mark messages from newly selected user as read (just in case)
    ChatMessage::where('sender_id', $id)
        ->where('receiver_id', $this->authId)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

    // Refresh unread counts
    $this->loadUsersWithUnreadCounts();

    // Force Livewire to rerender frontend
    $this->dispatch('$refresh');

    
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
            
        $this->dispatch('scrollToBottom');
    }

    public function replyTo($messageId)
    {
        $this->replyingTo = ChatMessage::find($messageId);
        $this->dispatch('focusMessageInput');
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
    }

    public function submit()
    {
        if(!$this->newMessage) return;

        $messageData = [
            'sender_id' => $this->authId,
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ];

        if ($this->replyingTo) {
            $messageData['reply_to'] = $this->replyingTo->id;
        }

        $message = ChatMessage::create($messageData);

        $this->messages->prepend($message);
        $this->newMessage = '';
        $this->replyingTo = null;
        
        $this->loadUsersWithUnreadCounts();
        broadcast(new MessageSent($message));
        $this->dispatch('scrollToBottom');
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
        
        // Update the unread count for this user
        $this->loadUsersWithUnreadCounts();
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