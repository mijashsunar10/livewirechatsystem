<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{receiver_id}', function ($user, $receiver_id) {
    return (int) $user->id === (int) $receiver_id;
});
//This file defines who is authorized to listen on certain private or presence channels.
// By default, anyone could “connect” to a public channel — but private and presence channels are protected.

// Whenever a user tries to subscribe to a private channel, Laravel runs this rule to check:Is this user allowed to listen on this channel?

// Laravel receives the subscription request for private-chat.{receiver_id}

// Looks in channels.php:
// Runs the check.

// Only if user->id === receiver_id, the user is allowed to subscribe.

// ✅ Result:

// User securely receives only the broadcasts meant for them.