<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    /**
     * Determine if the user can view the chat
     */
    public function view(User $user, Chat $chat): bool
    {
        // Admin can view all chats
        if ($user->is_admin) {
            return true;
        }

        // User can view their own chat
        return $chat->user_id === $user->id;
    }

    /**
     * Determine if the user is admin
     */
    public function admin(User $user): bool
    {
        return $user->is_admin === true;
    }
}
