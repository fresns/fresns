<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use Illuminate\Support\Str;

trait ArchiveServiceTrait
{
    public function getArchiveInfo(?string $langTag = null): array
    {
        $archiveData = $this;

        $fileExtName = ConfigHelper::fresnsConfigByItemKeys([
            'image_extension_names',
            'video_extension_names',
            'audio_extension_names',
            'document_extension_names',
        ]);

        $fileExt = match ($archiveData->file_type) {
            1 => $fileExtName['image_extension_names'],
            2 => $fileExtName['video_extension_names'],
            3 => $fileExtName['audio_extension_names'],
            4 => $fileExtName['document_extension_names'],
            default => null,
        };

        $info['plugin'] = $archiveData->plugin_fskey;
        $info['name'] = LanguageHelper::fresnsLanguageByTableId('archives', 'name', $archiveData->id, $langTag) ?? $archiveData->name;
        $info['description'] = LanguageHelper::fresnsLanguageByTableId('archives', 'description', $archiveData->id, $langTag) ?? $archiveData->description;
        $info['code'] = $archiveData->code;
        $info['formElement'] = $archiveData->form_element;
        $info['elementType'] = $archiveData->element_type;
        $info['elementOptions'] = $archiveData->element_options;
        $info['isMultiple'] = (bool) $archiveData->is_multiple;
        $info['isRequired'] = (bool) $archiveData->is_required;
        $info['fileType'] = $archiveData->file_type;
        $info['fileExt'] = Str::lower($fileExt);
        $info['fileAccept'] = FileHelper::fresnsFileAcceptByType($archiveData->file_type);
        $info['inputPattern'] = $archiveData->input_pattern;
        $info['inputMax'] = $archiveData->input_max;
        $info['inputMin'] = $archiveData->input_min;
        $info['inputMaxlength'] = $archiveData->input_maxlength;
        $info['inputMinlength'] = $archiveData->input_minlength;
        $info['inputSize'] = $archiveData->input_size;
        $info['inputStep'] = $archiveData->input_step;
        $info['valueType'] = $archiveData->value_type;

        return $info;
    }
}
