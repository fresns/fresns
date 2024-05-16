<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\StrHelper;
use App\Models\File;
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
            File::TYPE_IMAGE => $fileExtName['image_extension_names'],
            File::TYPE_VIDEO => $fileExtName['video_extension_names'],
            File::TYPE_AUDIO => $fileExtName['audio_extension_names'],
            File::TYPE_DOCUMENT => $fileExtName['document_extension_names'],
            default => null,
        };

        $info['fskey'] = $archiveData->app_fskey;
        $info['name'] = StrHelper::languageContent($archiveData->name, $langTag); // Multilingual
        $info['description'] = StrHelper::languageContent($archiveData->description, $langTag); // Multilingual
        $info['code'] = $archiveData->code;
        $info['formElement'] = $archiveData->form_element;
        $info['elementType'] = $archiveData->element_type;
        $info['elementOptions'] = StrHelper::languageContent($archiveData->element_options, $langTag); // Multilingual
        $info['isTreeOption'] = (bool) $archiveData->is_tree_option;
        $info['isMultiple'] = (bool) $archiveData->is_multiple;
        $info['isRequired'] = (bool) $archiveData->is_required;
        $info['fileType'] = $archiveData->file_type;
        $info['fileExtensions'] = Str::lower($fileExt);
        $info['fileAccept'] = FileHelper::fresnsFileAcceptByType($archiveData->file_type);
        $info['inputPattern'] = $archiveData->input_pattern;
        $info['inputMax'] = $archiveData->input_max;
        $info['inputMin'] = $archiveData->input_min;
        $info['inputMaxlength'] = $archiveData->input_maxlength;
        $info['inputMinlength'] = $archiveData->input_minlength;

        return $info;
    }
}
