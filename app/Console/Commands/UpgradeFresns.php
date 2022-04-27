<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Helpers\AppHelper;
use App\Utilities\AppUtility;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class UpgradeFresns extends Command
{
    protected $signature = 'fresns:upgrade';

    protected $description = 'upgrade fresns';

    protected $path = 'upgrade';

    protected $file;

    protected $extractPath;

    const STEP_START = 1;
    const STEP_DOWNLOAD = 2;
    const STEP_EXTRACT = 3;
    const STEP_INSTALL = 4;
    const STEP_CLEAR = 5;

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        $this->updateStep(self::STEP_START);

        // Check if an upgrade is needed
        $checkVersion = AppUtility::checkVersion();
        if (! $checkVersion) {
            return $this->info('No new version, Already the latest version of Fresns.');
        }

        try {
            $this->download();
            $this->extractFile();
            $this->composerInstall();
            $this->upgradeCommand();
            $this->upgradeFinish();
        } catch (\Exception $e) {
            $this->info($e->getMessage());
        }

        $this->clear();

        return Command::SUCCESS;
    }

    // output update step info
    public function updateStep(string $step): bool
    {
        // upgrade step
        return Cache::put('upgradeStep', $step);
    }

    // step 1: download upgrade pack(zip)
    public function download(): bool
    {
        logger('upgrade:download');
        $this->updateStep(self::STEP_DOWNLOAD);

        $client = new \GuzzleHttp\Client();

        $downloadUrl = AppUtility::newVersion()['upgradePackage'];

        $filename = basename($downloadUrl);

        $path = \Storage::path($this->path);
        if (! file_exists($path)) {
            \File::makeDirectory($path, 0775, true);
        }

        $file = $path.'/'.$filename;

        $client->request('GET', $downloadUrl, [
            'sink' => $file,
        ]);

        $this->file = $file;

        return true;
    }

    // step 2: unzip and replace the files
    public function extractFile(): bool
    {
        $this->updateStep(self::STEP_EXTRACT);

        if (! $this->file) {
            return false;
        }

        $extractDirName = pathinfo($this->file)['filename'] ?? date('Y-m-d');
        $extractPath = $this->path.'/'.$extractDirName;
        $this->extractPath = $extractPath;

        $zipFile = new \PhpZip\ZipFile();

        if (! file_exists(\Storage::path($extractPath))) {
            \File::makeDirectory(\Storage::path($extractPath), 0775, true);
        }
        $zipFile->openFile($this->file)->extractTo(\Storage::path($extractPath));

        $this->copyMerge(\Storage::path($extractPath.'/fresns'), base_path());

        return true;
    }

    // step 3: composer install
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

    // step 4: execute the version command
    public function upgradeCommand()
    {
        logger('upgrade:install');
        $this->updateStep(self::STEP_INSTALL);

        AppUtility::executeUpgradeCommand();
    }

    // step 5: edit fresns version info
    public function upgradeFinish(): bool
    {
        Cache::forget('currentVersion');
        Cache::forget('upgradeStep');

        $newVersion = AppHelper::VERSION;
        $newVersionInt = AppHelper::VERSION_INT;

        AppUtility::editVersion($newVersion, $newVersionInt);

        return true;
    }

    // step 6: clear cache
    public function clear()
    {
        logger('upgrade:clear');
        $this->updateStep(self::STEP_CLEAR);

        $this->call('cache:clear');
        $this->call('config:clear');

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
