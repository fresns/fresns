<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Exports;

use App\Models\BlockWord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BlockWordsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return BlockWord::all();
    }

    public function headings(): array
    {
        return [
            'word',
            'content_mode',
            'user_mode',
            'conversation_mode',
            'replace_word',
        ];
    }

    public function map($word): array
    {
        return [
            $word->word,
            $word->content_mode,
            $word->user_mode,
            $word->conversation_mode,
            $word->replace_word,
        ];
    }
}
