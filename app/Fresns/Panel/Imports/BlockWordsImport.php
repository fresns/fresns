<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Imports;

use App\Models\BlockWord;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BlockWordsImport implements ToModel, WithHeadingRow
{
    /**
     * @param  array  $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (! isset($row['word'])) {
            return null;
        }

        $exist = BlockWord::where('word', $row['word'])->exists();
        if ($exist) {
            return;
        }

        return new BlockWord([
            'word' => $row['word'],
            'content_mode' => $row['content_mode'],
            'user_mode' => $row['user_mode'],
            'conversation_mode' => $row['conversation_mode'],
            'replace_word' => $row['replace_word'],
        ]);
    }
}
