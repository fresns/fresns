<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Extend extends Model
{
    const TYPE_TEXT_BOX = 1;
    const TYPE_INFO_BOX = 3;
    const TYPE_INTERACTIVE_BOX = 2;

    const INFO_BOX_SQUARE = 1;
    const INFO_BOX_SQUARE_BIG = 2;
    const INFO_BOX_PORTRAIT = 3;
    const INFO_BOX_LANDSCAPE = 4;

    use Traits\IsEnableTrait;
    use Traits\FsidTrait;
    use Traits\ExtendServiceTrait;

    public function getFsidKey()
    {
        return 'eid';
    }
}
