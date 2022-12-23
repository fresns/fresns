<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use App\Models\CodeMessage;
use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class Upgrade8Command extends Command
{
    protected $signature = 'fresns:upgrade-8';

    protected $description = 'upgrade to fresns v2.0.0-beta.8';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        logger('upgrade:fresns-8 composerInstall');
        $this->composerInstall();

        logger('upgrade:fresns-8 migrate');
        $this->call('migrate', ['--force' => true]);

        logger('upgrade:fresns-8 updateData');
        $this->updateData();

        return Command::SUCCESS;
    }

    // composer install
    public function composerInstall()
    {
        $composerPath = 'composer';

        if (! $this->commandExists($composerPath)) {
            $composerPath = '/usr/bin/composer';
        }

        $process = new Process([$composerPath, 'install'], base_path());
        $process->setTimeout(0);
        $process->start();

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $this->info("\nRead from stdout: ".$data);
            } else { // $process::ERR === $type
                $this->info("\nRead from stderr: ".$data);
            }
        }
    }

    // check composer
    public function commandExists($commandName)
    {
        ob_start();
        passthru("command -v $commandName", $code);
        ob_end_clean();

        return (0 === $code) ? true : false;
    }

    // update data
    public function updateData(): bool
    {
        // modify
        $topCommentRequire = Config::where('item_key', 'top_comment_require')->first();
        if ($topCommentRequire) {
            $topCommentRequire->update([
                'item_key' => 'preview_post_comment_require',
                'item_value' => 10,
                'is_api' => 0,
            ]);
        }
        $commentPreview = Config::where('item_key', 'comment_preview')->first();
        if ($commentPreview) {
            $commentPreview->update([
                'item_key' => 'preview_sub_comments',
                'is_api' => 0,
            ]);
        }

        // add new
        $previewPostLikeUsers = Config::where('item_key', 'preview_post_like_users')->first();
        if (empty($previewPostLikeUsers)) {
            $newConfig = new Config;
            $newConfig->item_key = 'preview_post_like_users';
            $newConfig->item_value = '0';
            $newConfig->item_type = 'number';
            $newConfig->item_tag = 'interactions';
            $newConfig->is_multilingual = 0;
            $newConfig->is_custom = 0;
            $newConfig->is_api = 0;
            $newConfig->save();
        }

        $previewPostComments = Config::where('item_key', 'preview_post_comments')->first();
        if (empty($previewPostComments)) {
            $newConfig = new Config;
            $newConfig->item_key = 'preview_post_comments';
            $newConfig->item_value = '0';
            $newConfig->item_type = 'number';
            $newConfig->item_tag = 'interactions';
            $newConfig->is_multilingual = 0;
            $newConfig->is_custom = 0;
            $newConfig->is_api = 0;
            $newConfig->save();
        }

        $previewPostCommentSort = Config::where('item_key', 'preview_post_comment_sort')->first();
        if (empty($previewPostCommentSort)) {
            $newConfig = new Config;
            $newConfig->item_key = 'preview_post_comment_sort';
            $newConfig->item_value = 'like';
            $newConfig->item_type = 'string';
            $newConfig->item_tag = 'interactions';
            $newConfig->is_multilingual = 0;
            $newConfig->is_custom = 0;
            $newConfig->is_api = 0;
            $newConfig->save();
        }

        $previewSubCommentSort = Config::where('item_key', 'preview_sub_comment_sort')->first();
        if (empty($previewSubCommentSort)) {
            $newConfig = new Config;
            $newConfig->item_key = 'preview_sub_comment_sort';
            $newConfig->item_value = 'timeAsc';
            $newConfig->item_type = 'string';
            $newConfig->item_tag = 'interactions';
            $newConfig->is_multilingual = 0;
            $newConfig->is_custom = 0;
            $newConfig->is_api = 0;
            $newConfig->save();
        }

        // update post is_allow
        DB::table('post_appends')->update(['is_allow' => 1]);

        // code messages
        $code36113Messages = CodeMessage::where('plugin_unikey', 'Fresns')->where('code', 36113)->get();
        foreach ($code36113Messages as $code) {
            $langContent = match ($code->lang_tag) {
                'en' => 'File size exceeds the set limit',
                'zh-Hans' => '文件尺寸超出设置的限制',
                'zh-Hant' => '文件尺寸超出設置的限制',
            };

            $code->update([
                'message' => $langContent,
            ]);
        }

        $code36114Messages = CodeMessage::where('plugin_unikey', 'Fresns')->where('code', 36114)->get();
        foreach ($code36114Messages as $code) {
            $langContent = match ($code->lang_tag) {
                'en' => 'File time length exceeds the set limit',
                'zh-Hans' => '文件时长超出设置的限制',
                'zh-Hant' => '文件時長超出設置的限制',
            };

            $code->update([
                'message' => $langContent,
            ]);
        }

        $code36115Messages = CodeMessage::where('plugin_unikey', 'Fresns')->where('code', 36115)->get();
        foreach ($code36115Messages as $code) {
            $langContent = match ($code->lang_tag) {
                'en' => 'The number of files exceeds the set limit',
                'zh-Hans' => '文件数量超出设置的限制',
                'zh-Hant' => '文件數量超出設置的限制',
            };

            $code->update([
                'message' => $langContent,
            ]);
        }

        $code36116Messages = CodeMessage::where('plugin_unikey', 'Fresns')->where('code', 36116)->get();
        foreach ($code36116Messages as $code) {
            $langContent = match ($code->lang_tag) {
                'en' => 'Current role has no conversation message permission',
                'zh-Hans' => '当前角色无私信权限',
                'zh-Hant' => '當前角色無私信權限',
            };

            $code->update([
                'message' => $langContent,
            ]);
        }

        $code36117Messages = CodeMessage::where('plugin_unikey', 'Fresns')->where('code', 36117)->get();
        foreach ($code36117Messages as $code) {
            $langContent = match ($code->lang_tag) {
                'en' => 'The current role has reached the upper limit of today download, please download again tomorrow.',
                'zh-Hans' => '当前角色已经达到今天下载次数上限，请明天再下载',
                'zh-Hant' => '當前角色已經達到今天下載次數上限，請明天再下載',
            };

            $code->update([
                'message' => $langContent,
            ]);
        }

        $code36118Messages = CodeMessage::where('plugin_unikey', 'Fresns')->where('code', 36118)->where('lang_tag', 'en')->first();
        if (empty($code36118Messages)) {
            CodeMessage::updateOrCreate([
                'plugin_unikey' => 'Fresns',
                'code' => '36118',
                'lang_tag' => 'en',
            ],
            [
                'message' => 'The current number of characters has reached the maximum number and cannot be added',
            ]);
            CodeMessage::updateOrCreate([
                'plugin_unikey' => 'Fresns',
                'code' => '36118',
                'lang_tag' => 'zh-Hans',
            ],
            [
                'message' => '当前角色已经达到上限数量，无法再添加',
            ]);
            CodeMessage::updateOrCreate([
                'plugin_unikey' => 'Fresns',
                'code' => '36118',
                'lang_tag' => 'zh-Hant',
            ],
            [
                'message' => '當前角色已經達到上限數量，無法再添加',
            ]);
        }

        $code36119Messages = CodeMessage::where('plugin_unikey', 'Fresns')->where('code', 36119)->where('lang_tag', 'en')->first();
        if (empty($code36119Messages)) {
            CodeMessage::updateOrCreate([
                'plugin_unikey' => 'Fresns',
                'code' => '36119',
                'lang_tag' => 'en',
            ],
            [
                'message' => 'Publish too fast, please post again at intervals. Please check the current role settings for details',
            ]);
            CodeMessage::updateOrCreate([
                'plugin_unikey' => 'Fresns',
                'code' => '36119',
                'lang_tag' => 'zh-Hans',
            ],
            [
                'message' => '发表太快，请间隔一段时间再发。详情请查看当前角色的设置',
            ]);
            CodeMessage::updateOrCreate([
                'plugin_unikey' => 'Fresns',
                'code' => '36119',
                'lang_tag' => 'zh-Hant',
            ],
            [
                'message' => '發表太快，請間隔一段時間再發。詳情請查看當前角色的設置',
            ]);
        }

        return true;
    }
}
