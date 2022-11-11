<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Conversation extends Model
{
    public function aUser()
    {
        return $this->belongsTo(User::class, 'a_user_id', 'id');
    }

    public function bUser()
    {
        return $this->belongsTo(User::class, 'b_user_id', 'id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ConversationMessage::class)->isEnable()->latest();
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class)->isEnable();
    }
}
