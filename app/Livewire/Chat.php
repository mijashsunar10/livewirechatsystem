<?php

namespace App\Livewire;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithFileUploads as LivewireWithFileUploads;

class Chat extends Component
{
      use LivewireWithFileUploads;

   public $users;
    public $selectedUser;
    public $newMessage;
    public $messages;
    public $authId;
    public $loginID;
    public $unreadCounts = [];
    public $replyingTo = null;
    public $image;
    public $showImageModal = false;
    public $imagePath;
    // public $showImageModal = false;
    public $currentImagePath;
    public $currentImageIndex = 0;
    public $chatImages = [];
    public $editingMessageId = null;
    public $editingMessageContent = '';
    

    protected $listeners = [];


    public function mount()
        {
            $this->loginID = Auth::id();
            $this->authId = Auth::id();
            
            $this->listeners = [
                "echo-private:chat.{$this->loginID},MessageSent" => 'newMessageNotification',
                "echo-private:chat.{$this->loginID},MessageUpdated" => 'messageUpdated',
                "echo-private:chat.{$this->loginID},MessageDeleted" => 'messageDeleted',
                'scrollToBottom' => 'scrollToBottom',
                'prev-image' => 'prevImage',
                'next-image' => 'nextImage',
                'close-image-modal' => 'closeImageModal',
            ];


               $this->dispatch('init-keyboard-navigation');
            
            $this->loadUsersWithUnreadCounts();

            $this->selectedUser = $this->users->first();
            $this->loadMessages();
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

            // Mark messages from newly selected user as read
            ChatMessage::where('sender_id', $id)
                ->where('receiver_id', $this->authId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            // Refresh unread counts
            $this->loadUsersWithUnreadCounts();
            
            // Force Livewire to refresh the view
            $this->dispatch('$refresh');
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


                
        public function loadMessages()
        {
            $this->messages = ChatMessage::query()
                ->where(function($q) {
                    $q->where("sender_id", $this->authId)
                        ->where("receiver_id", $this->selectedUser->id)
                        ->where('deleted_for_sender', false);
                })
                ->orWhere(function($q) {
                    $q->where("sender_id", $this->selectedUser->id)
                        ->where("receiver_id", $this->authId)
                        ->where('deleted_for_receiver', false);
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
            if(!$this->newMessage && !$this->image) return;

            $messageData = [
                'sender_id' => $this->authId,
                'receiver_id' => $this->selectedUser->id,
            ];

            if ($this->newMessage) {
                $messageData['message'] = $this->newMessage;
            }

            if ($this->image) {
                $path = $this->image->store('chat-images', 'public');
                $messageData['image_path'] = $path;
            }

            if ($this->replyingTo) {
                $messageData['reply_to'] = $this->replyingTo->id;
            }

            $message = ChatMessage::create($messageData);

            $this->messages->prepend($message);
            $this->newMessage = '';
            $this->image = null;
            $this->replyingTo = null;
            
            $this->loadUsersWithUnreadCounts();
            broadcast(new MessageSent($message));
            $this->dispatch('scrollToBottom');
        }


        public function getListeners()
        {
            return [
                "echo-private:chat.{$this->loginID},MessageSent" => 'newMessageNotification',
                "echo-private:chat.{$this->loginID},MessageUpdated" => 'messageUpdated',
                "echo-private:chat.{$this->loginID},MessageDeleted" => 'messageDeleted',
                'scrollToBottom' => 'scrollToBottom',
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
        // Add these methods to your Chat.php Livewire component
        // Add these methods to your component


        public function messageUpdated($payload)
        {
            // Only update if the message is in the current conversation
            if ($payload['sender_id'] == $this->selectedUser->id || 
                $payload['receiver_id'] == $this->selectedUser->id) {
                $this->loadMessages();
                $this->dispatch('scrollToBottom');
            }
        }


        public function messageDeleted($payload)
        {
            // Only update if the message is in the current conversation
            if ($payload['sender_id'] == $this->selectedUser->id || 
                $payload['receiver_id'] == $this->selectedUser->id) {
                $this->loadMessages();
                $this->dispatch('scrollToBottom');
            }
        }


        // Update your edit and delete methods to broadcast events
        public function editMessage($messageId, $newContent)
        {
            if (!$newContent) return;
            
            $message = ChatMessage::findOrFail($messageId);
            
            if ($message->sender_id === $this->authId) {
                $message->update([
                    'message' => $newContent,
                    'edited_at' => now()
                ]);
                
                // Broadcast to both sender and receiver channels
                broadcast(new MessageUpdated($message))->toOthers();
                $this->dispatch('scrollToBottom');
            }
        }


        public function deleteMessage($messageId, $forEveryone = false)
        {
            $message = ChatMessage::findOrFail($messageId);
            
            if ($message->sender_id === $this->authId) {
                if ($forEveryone) {
                    $message->delete();
                    // Broadcast to both sender and receiver channels
                    broadcast(new MessageDeleted($message))->toOthers();
                } else {
                    $message->update(['deleted_for_sender' => true]);
                    // Still broadcast but with different handling
                    broadcast(new MessageDeleted($message))->toOthers();
                }
            } else {
                $message->update(['deleted_for_receiver' => true]);
                // Broadcast to both sender and receiver channels
                broadcast(new MessageDeleted($message))->toOthers();
            }
            
            $this->loadMessages();
        }


        public function removeImage()
            {
                $this->image = null;
            }


                // Add this method to handle image click
             public function showImage($imagePath)
            {
                $this->chatImages = $this->messages
                    ->filter(fn($msg) => $msg->image_path)
                    ->pluck('image_path')
                    ->toArray();

                $this->currentImageIndex = array_search($imagePath, $this->chatImages);
                $this->currentImagePath = $imagePath;
                $this->showImageModal = true;
                
                // Dispatch browser event to setup key listeners
                $this->dispatch('image-modal-opened');
            }

                   // Add these methods to your Chat component
            public function closeImageModal()
            {
                $this->showImageModal = false;
                $this->dispatch('imageModalClosed');
            }

            public function prevImage()
            {
                if ($this->currentImageIndex > 0) {
                    $this->currentImageIndex--;
                    $this->currentImagePath = $this->chatImages[$this->currentImageIndex];
                }
            }

            public function nextImage()
            {
                if ($this->currentImageIndex < count($this->chatImages) - 1) {
                    $this->currentImageIndex++;
                    $this->currentImagePath = $this->chatImages[$this->currentImageIndex];
                }
            }


            public function handleKeydown($event)
            {
                if (!$this->showImageModal) return;

                switch ($event['key']) {
                    case 'ArrowLeft':
                        $this->prevImage();
                        break;
                    case 'ArrowRight':
                        $this->nextImage();
                        break;
                    case 'Escape':
                        $this->closeImageModal();
                        break;
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

                        // Update your edit method
            public function startEditing($messageId)
            {
                $message = ChatMessage::find($messageId);
                
                if ($message && $message->sender_id === $this->authId) {
                    $this->editingMessageId = $messageId;
                    $this->editingMessageContent = $message->message;
                    $this->dispatch('focus-message-input');
                }
            }
public function saveEdit()
{
    if (!$this->editingMessageId) return;
    
    $message = ChatMessage::findOrFail($this->editingMessageId);
    
    if ($message->sender_id === $this->authId) {
        $message->update([
            'message' => $this->editingMessageContent,
            'edited_at' => now()
        ]);
        
        broadcast(new MessageUpdated($message))->toOthers();
        
        // Reset all relevant properties
        $this->editingMessageId = null;
        $this->editingMessageContent = '';
        $this->newMessage = ''; // Clear regular message input too
        
        // Reload messages
        $this->loadMessages();
        
        // Dispatch event to reset input
        $this->dispatch('edit-completed');
    }
}

public function cancelEdit()
{
    $this->editingMessageId = null;
    $this->editingMessageContent = '';
    $this->dispatch('edit-cancelled');
}
}