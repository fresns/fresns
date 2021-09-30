<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$basePath = base_path();

$routeDirArr = [];

$pluginsDirPath = implode(DIRECTORY_SEPARATOR, [$basePath, 'app', 'Http']);
$pluginDirArr = new DirectoryIterator($pluginsDirPath);
foreach ($pluginDirArr as $pluginDir) {
    $currPath = implode(DIRECTORY_SEPARATOR, [$pluginsDirPath, $pluginDir]);
    if (is_dir($currPath)) {
        $pluginDirRealPath = implode(DIRECTORY_SEPARATOR, [$basePath, 'app', 'Http', $pluginDir]);
        $routeDirArr[] = $pluginDirRealPath;
    }
}

$pluginsDirPath = implode(DIRECTORY_SEPARATOR, [$basePath, 'app', 'Plugins']);
$pluginDirArr = new DirectoryIterator($pluginsDirPath);
foreach ($pluginDirArr as $pluginDir) {
    $currPath = implode(DIRECTORY_SEPARATOR, [$pluginsDirPath, $pluginDir]);
    if (is_dir($currPath)) {
        $pluginDirRealPath = implode(DIRECTORY_SEPARATOR, [$basePath, 'app', 'Plugins', $pluginDir]);
        $routeDirArr[] = $pluginDirRealPath;
    }
}

foreach ($routeDirArr as $routeDir) {
    $dir = new DirectoryIterator($routeDir);
    foreach ($dir as $file) {
        if ($file->isDir()) {
            $subDir = new DirectoryIterator($file->getPathname());
            foreach ($subDir as $subFile) {
                $subPathName = $subFile->getPathname();
                $routeFile = implode(DIRECTORY_SEPARATOR, [$subPathName, 'FsRouteApi.php']);
                if (file_exists($routeFile)) {
                    require_once $routeFile;
                }

                $routeFile = implode(DIRECTORY_SEPARATOR, [$subPathName, 'RouteApi.php']);
                if (file_exists($routeFile)) {
                    require_once $routeFile;
                }
            }
        }
    }
}
