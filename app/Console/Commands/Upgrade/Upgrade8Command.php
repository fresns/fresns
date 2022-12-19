<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use App\Models\Config;
use App\Models\PostAppend;
use Illuminate\Console\Command;
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

        $postAppends = PostAppend::get();
        foreach ($postAppends as $append) {
            $append->update([
                'is_allow' => 1,
            ]);
        }

        return true;
    }
}
