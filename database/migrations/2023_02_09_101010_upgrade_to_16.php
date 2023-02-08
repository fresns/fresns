<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Models\Config;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeTo16 extends Migration
{
    /**
     * Run the migrations.
     *
     * Upgrade to 16 (fresns v2.5.0)
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('files', 'image_handle_position')) {
            Schema::table('files', function (Blueprint $table) {
                $table->string('image_handle_position', 16)->nullable()->after('path');
            });
        }

        // lang pack add key
        $languagePack = Config::where('item_key', 'language_pack')->first();
        if ($languagePack) {
            $packData = $languagePack->item_value;

            $addPackKeys = [
                [
                    'name' => 'previousPage',
                    'canDelete' => false,
                ],
                [
                    'name' => 'nextPage',
                    'canDelete' => false,
                ],
                [
                    'name' => 'listWithoutPage',
                    'canDelete' => false,
                ],
            ];

            $newData = array_merge($packData, $addPackKeys);

            $languagePack->item_value = $newData;
            $languagePack->save();
        }

        // lang pack add content
        $langPackContents = Language::where('table_name', 'configs')->where('table_column', 'item_value')->where('table_key', 'language_pack_contents')->get();
        foreach ($langPackContents as $packContent) {
            $content = (object) json_decode($packContent->lang_content, true);

            $langAddContent = match ($packContent->lang_tag) {
                'en' => [
                    'previousPage' => 'Previous Page',
                    'nextPage' => 'Next Page',
                    'listWithoutPage' => 'No More',
                ],
                'zh-Hans' => [
                    'previousPage' => '上一页',
                    'nextPage' => '下一页',
                    'listWithoutPage' => '没有了',
                ],
                'zh-Hant' => [
                    'previousPage' => '上一頁',
                    'nextPage' => '下一頁',
                    'listWithoutPage' => '沒有了',
                ],
            };

            $langNewContent = (object) array_merge((array) $content, (array) $langAddContent);

            $packContent->lang_content = json_encode($langNewContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            $packContent->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
