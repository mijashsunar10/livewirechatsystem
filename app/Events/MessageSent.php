<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
//ShouldBroadcast →When you fire the event, Laravel queues it —meaning it gets pushed to the queue system (like Redis, database, etc.)and is only broadcast after a queue worker picks it up.
//  ShouldBroadcastNow →When you fire the event, Laravel immediately broadcasts it without waiting for the queue, even if no queue worker is running.
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    // Dispatchable → lets you easily dispatch (fire) the event using
    // InteractsWithSockets → helps manage the socket connections so you can exclude the sender if needed
    // SerializesModels → ensures Eloquent models (like $this->message) are properly serialized for broadcasting and then unserialized when received.


    /**
     * Create a new event instance.
     */
    public function __construct(public ChatMessage $message)
    // This is the data you pass into the event when you fire it.
    // you inject the $message — an instance of your ChatMessage model — making it available throughout the event.
    {
        //
        
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    // his tells Laravel which channel(s) to broadcast the event on.
    {
        return [
            new PrivateChannel('chat.'.$this->message->receiver_id),
        ];
        //Broadcasting on PrivateChannel('chat.{receiver_id}')
        // This is a private channel → meaning it requires authentication (using Laravel’s broadcasting guards) to ensure only the right user (the receiver) can listen on that channel.
    }


    public function broadcastWith()//This controls what data is sent to the frontend
    {
        return[
            "id" => $this->message->id,
             "sender_id" => $this->message->sender_id,
              "receiver_id" => $this->message->receiver_id,
              "message" => $this->message->message,
        ];
    }
    //Without this, Laravel would just send the entire event object, but here you’re customizing the payload:message_id,sender_id, receiver_id and message



}
