<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class ConversationMessage extends Model
{
    use Traits\FsidTrait;
    use Traits\IsEnabledTrait;

    const TYPE_TEXT = 1;
    const TYPE_FILE = 2;

    public function getFsidKey()
    {
        return 'cmid';
    }

    public function sendUser()
    {
        return $this->belongsTo(User::class, 'send_user_id', 'id');
    }

    public function receiveUser()
    {
        return $this->belongsTo(User::class, 'receive_user_id', 'id');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'message_file_id', 'id');
    }
}
