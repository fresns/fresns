<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateContentConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hashtag_length' => 'int|required',
            'hashtag_regexp' => 'array|required',
            'nearby_length_km' => 'int|required',
            'nearby_length_mi' => 'int|required',
            'post_brief_length' => 'int|required',
            'comment_brief_length' => 'int|required',
            'post_edit_time_limit' => 'int|nullable',
            'comment_edit_time_limit' => 'int|nullable',
        ];
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
