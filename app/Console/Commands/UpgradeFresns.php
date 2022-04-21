<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Utilities\AppUtility;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class UpgradeFresns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fresns:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade fresns';

    protected $path = 'upgrade';

    protected $file;

    protected $extractPath;

    const STEP_START = 1;
    const STEP_DOWNLOAD = 2;
    const STEP_EXTRACT = 3;
    const STEP_INSTALL = 4;
    const STEP_CLEAR = 5;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function commandExists($commandName)
    {
        return (null === shell_exec("command -v $commandName")) ? false : true;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->updateStep(self::STEP_START);
        // Check if an upgrade is needed
        if (! $this->checkVersion()) {
            return $this->info('Already the latest version of Fresns');
        }

        try {
            $this->download();
            $this->extractFile();
            $this->install();
            $this->migrate();
        } catch (\Exception $e) {
            $this->info($e->getMessage());
        }

        $this->clear();

        $this->upgradeFinish();

        return Command::SUCCESS;
    }

    public function checkVersion(): bool
    {
        $newVersion = AppUtility::newVersion();
        $currentVersion = AppUtility::currentVersion();

        if (($currentVersion['versionInt'] ?? 0) >= ($newVersion['versionInt'] ?? 0)) {
            return false;
        }

        return true;
    }

    public function updateStep(string $step): bool
    {
        // upgrade step
        return Cache::put('upgradeStep', $step);
    }

    public function download(): bool
    {
        logger('upgrade:download');
        $this->updateStep(self::STEP_DOWNLOAD);

        $client = new \GuzzleHttp\Client();

        $newVersion = AppUtility::newVersion();
        $downloadUrl = $newVersion['upgradePackage'];

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

    public function install()
    {
        logger('upgrade:install');
        $this->updateStep(self::STEP_INSTALL);

        $this->composerInstall();
        $this->migrate();
        $this->upgradeCommand();
    }

    public function composerInstall()
    {
        logger('upgrade:composer install');
        $composerPath = 'composer';

        if (! $this->commandExists($composerPath)) {
            $composerPath = '/usr/bin/composer';
        }

        $process = new Process([$composerPath, 'install'], base_path());
        $process->setTimeout(600);
        $process->start();

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $this->info("\nRead from stdout: ".$data);
            } else { // $process::ERR === $type
                $this->info("\nRead from stderr: ".$data);
            }
        }
    }

    public function upgradeFinish(): bool
    {
        Cache::forget('currentVersion');
        Cache::forget('upgradeStep');

        return true;
    }

    public function migrate()
    {
        logger('upgrade:migrate');
        $this->call('migrate');
    }

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

    public function upgradeCommand()
    {
        logger('upgrade:upgrade command');

        $currentVersionInt = AppUtility::currentVersion()['versionInt'] ?? 0;
        $newVersionInt = AppUtility::newVersion()['versionInt'] ?? 0;

        if (! $currentVersionInt || ! $newVersionInt) {
            return false;
        }

        $versionInt = $currentVersionInt;
        while ($versionInt < $newVersionInt) {
            $versionInt++;
            $command = 'fresns:upgrade-'.$versionInt;
            if (\Artisan::has($command)) {
                $this->call($command);
            }
        }

        $this->call('migrate');
    }

    public function replaceFile()
    {
        logger('upgrade:replace file');
    }

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
