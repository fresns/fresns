<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Helpers\FileHelper;
use App\Http\Center\Helper\InstallHelper;
use App\Http\FresnsPanel\FsService;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class UpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade main program';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('upgrade start');
        $this->line('step1: check version');
        $versionInfo = FsService::getVersionInfo();
        if (! $versionInfo['canUpgrade']) {
            $this->error('there is no new version');
        }
        $this->line('step2: download package');
        $downloadDir = storage_path('app/upgrade/v_'.$versionInfo['upgradeVersion'].'/');
        $filename = date('YmdH').'.zip';
        $downloadFile = $downloadDir.$filename;
        FileHelper::assetDir($downloadFile);
        FsService::downFile($versionInfo['upgradePackage'], $downloadFile);
        $fileSize = File::size($downloadFile);
        if ($fileSize < 10) {
            $this->error('download package fail');
        }
        $this->line('step3: unzip package');
        $status = FileHelper::unzip($downloadFile, $downloadDir);
        if ($status == false) {
            $this->error('unzip package fail');
        }
        $this->line('step4: copy document');
        $coverPath = ['Base', 'Console', 'Exceptions', 'Helpers', 'Http', 'Listeners', 'Providers', 'Traits', 'static', 'views', 'lang', 'migrations', 'seeders'];
        foreach ($coverPath as $subDir) {
            if (in_array($subDir, ['Base', 'Console', 'Exceptions', 'Helpers', 'Http', 'Listeners', 'Providers', 'Traits'])) {
                $upDir = implode(DIRECTORY_SEPARATOR, [$downloadDir, $subDir]);
                (new Filesystem)->copyDirectory($upDir, app_path($subDir));
            } elseif ($subDir == 'static') {
                $upDir = implode(DIRECTORY_SEPARATOR, [$downloadDir, $subDir]);
                (new Filesystem)->copyDirectory($upDir, public_path($subDir));
            } elseif (in_array($subDir, ['views', 'lang'])) {
                $upDir = implode(DIRECTORY_SEPARATOR, [$downloadDir, $subDir]);
                (new Filesystem)->copyDirectory($upDir, resource_path($subDir));
            } elseif (in_array($subDir, ['migrations', 'seeders'])) {
                $upDir = implode(DIRECTORY_SEPARATOR, [$downloadDir, $subDir]);
                (new Filesystem)->copyDirectory($upDir, database_path($subDir));
            }
        }
        $this->line('step5: clear file cache');
        InstallHelper::freshSystem();
        $this->line('step6: run database migrate');
        //Artisan::call('migrate', ['--force' => true]);
        $this->line('step7: run database seed');
        Artisan::call('db:seed', ['--force' => true, '--class'=>'Database\Seeders\UpgradeSeeder']);
        $this->line('step8: run program script');
        $upClass = '\\App\\Http\\UpgradeController';
        if (class_exists($upClass)) {
            (new $upClass)->init();
        }
        $this->info('upgrade end');

        return Command::SUCCESS;
    }
}
