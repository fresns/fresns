<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Utilities\AppUtility;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UpgradeFresns extends Command
{
    protected $signature = 'fresns:upgrade';

    protected $description = 'automatic upgrade fresns';

    protected $path = 'upgrade';

    protected $file;

    protected $extractPath;

    const STEP_FAILURE = 0;
    const STEP_START = 1;
    const STEP_DOWNLOAD = 2;
    const STEP_EXTRACT = 3;
    const STEP_INSTALL = 4;
    const STEP_CLEAR = 5;
    const STEP_DONE = 6;

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        Cache::forget('autoUpgradeTip');
        $this->updateStep(self::STEP_START);

        // Check if an upgrade is needed
        $checkVersion = AppUtility::checkVersion();
        if (! $checkVersion) {
            $checkVersionTip = 'No new version, Already the latest version of Fresns.';

            $this->info($checkVersionTip);
            $this->info('Step --: Upgrade end');

            Cache::put('autoUpgradeStep', self::STEP_DONE);
            Cache::put('autoUpgradeTip', $checkVersionTip);

            return Command::SUCCESS;
        }

        try {
            $this->download();
            if (! $this->extractFile()) {
                $extractFileTip = 'Failed to download upgrade package.';

                $this->error($extractFileTip);

                Cache::put('autoUpgradeStep', self::STEP_FAILURE);
                Cache::put('autoUpgradeTip', $extractFileTip);

                return Command::FAILURE;
            }

            if (! $this->upgradeCommand()) {
                $extractFileTip = 'Failed to execute the version command';

                $this->error($extractFileTip);

                Cache::put('autoUpgradeStep', self::STEP_FAILURE);
                Cache::put('autoUpgradeTip', $extractFileTip);

                return Command::FAILURE;
            }

            $this->upgradeFinish();
        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->error($e->getMessage());
            $this->updateStep(self::STEP_FAILURE);

            return Command::FAILURE;
        }

        $this->clear();
        $this->updateStep(self::STEP_DONE);

        return Command::SUCCESS;
    }

    // output update step info
    public function updateStep(int $step)
    {
        $stepInfo = match ($step) {
            self::STEP_FAILURE => 'Step --: Upgrade failure',
            self::STEP_START => 'Step 1/6: Initialization verification',
            self::STEP_DOWNLOAD => 'Step 2/6: Download upgrade package',
            self::STEP_EXTRACT => 'Step 3/6: Unzip the upgrade package',
            self::STEP_INSTALL => 'Step 4/6: Run the upgrade package to install the new version',
            self::STEP_CLEAR => 'Step 5/6: Clear cache',
            self::STEP_DONE => 'Step 6/6: Done',
            default => 'Step --: Upgrade end',
        };

        // upgrade step
        return $this->updateOutput($stepInfo, $step);
    }

    public function updateOutput($content, $step)
    {
        if ($step == self::STEP_FAILURE) {
            $this->error($content);
        } else {
            $this->info($content);
        }

        $output = cache('autoUpgradeTip')."\n";
        $output .= $content;

        Cache::put('autoUpgradeStep', $step);
        Cache::put('autoUpgradeTip', $content);
    }

    // step 2: download upgrade pack(zip)
    public function download(): bool
    {
        $this->updateStep(self::STEP_DOWNLOAD);
        logger('upgrade:fresns download zip');

        $client = new \GuzzleHttp\Client();

        $downloadUrl = AppUtility::newVersion()['upgradePackage'];

        $filename = basename($downloadUrl);

        $path = Storage::path($this->path);
        if (! file_exists($path)) {
            File::makeDirectory($path, 0775, true);
        }

        $file = $path.'/'.$filename;

        $client->request('GET', $downloadUrl, [
            'sink' => $file,
        ]);

        $this->file = $file;

        logger('upgrade:fresns download done');

        return true;
    }

    // step 3: unzip and replace the files
    public function extractFile(): bool
    {
        $this->updateStep(self::STEP_EXTRACT);
        logger('upgrade:fresns unzip file');

        if (! $this->file) {
            return false;
        }

        $extractDirName = pathinfo($this->file)['filename'] ?? date('Y-m-d');
        $extractPath = $this->path.'/'.$extractDirName;
        $this->extractPath = $extractPath;

        $zipFile = new \PhpZip\ZipFile();

        if (! file_exists(Storage::path($extractPath))) {
            File::makeDirectory(Storage::path($extractPath), 0775, true);
        }
        $zipFile->openFile($this->file)->extractTo(Storage::path($extractPath));

        $this->copyMerge(Storage::path($extractPath.'/fresns'), base_path());

        logger('upgrade:fresns unzip done');

        return true;
    }

    // step 4-1: execute the version command
    public function upgradeCommand()
    {
        $this->updateStep(self::STEP_INSTALL);

        logger('upgrade:fresns upgrade command');

        return AppUtility::executeUpgradeCommand();
    }

    // step 4-2: edit fresns version info
    public function upgradeFinish(): bool
    {
        logger('upgrade:fresns edit version');

        $newVersion = AppHelper::VERSION;
        $newVersionInt = AppHelper::VERSION_INT;

        AppUtility::editVersion($newVersion, $newVersionInt);

        return true;
    }

    // step 5: clear cache
    public function clear()
    {
        $this->updateStep(self::STEP_CLEAR);

        CacheHelper::clearConfigCache('fresnsSystem');
        CacheHelper::clearConfigCache('fresnsConfig');
        CacheHelper::clearConfigCache('fresnsView');
        CacheHelper::clearConfigCache('fresnsRoute');
        CacheHelper::clearConfigCache('fresnsEvent');
        CacheHelper::clearConfigCache('fresnsSchedule');
        CacheHelper::clearConfigCache('frameworkConfig');

        if ($this->path) {
            $file = new Filesystem;
            $file->cleanDirectory('storage/app/'.$this->path);
        }
    }

    // unzip and replace the files
    public function copyMerge($source, $target)
    {
        // Path processing
        $source = preg_replace('#/\\\\#', DIRECTORY_SEPARATOR, $source);
        $target = preg_replace('#\/#', DIRECTORY_SEPARATOR, $target);
        $source = rtrim($source, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $target = rtrim($target, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        // Record how many documents were processed
        $count = 0;
        // If the target directory does not exist, it is created.
        if (! is_dir($target)) {
            mkdir($target, 0755, true);
            $count++;
        }
        // Search all files in the directory
        foreach (glob($source.'*') as $filename) {
            if (is_dir($filename)) {
                // If it is a directory, recursively merge the files in the subdirectory.
                $count += $this->copyMerge($filename, $target.basename($filename));
            } elseif (is_file($filename)) {
                // If it is a file, determine whether the current file is the same as the target file, and copy and overwrite if it is not.
                // The consistency judgment used here is the file md5.
                // md5 is reliable but low performance, and should be adjusted to the actual situation.
                if (! file_exists($target.basename($filename)) || md5(file_get_contents($filename)) != md5(file_get_contents($target.basename($filename)))) {
                    copy($filename, $target.basename($filename));
                    $count++;
                }
            }
        }

        // Returns how many files were processed
        return $count;
    }
}
