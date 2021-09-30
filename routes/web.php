<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$routeDirArr = [];

$basePath = base_path();
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
                $routeFile = implode(DIRECTORY_SEPARATOR, [$subPathName, 'FsRouteWeb.php']);
                if (file_exists($routeFile)) {
                    require_once $routeFile;
                }
                $routeFile = implode(DIRECTORY_SEPARATOR, [$subPathName, 'RouteWeb.php']);
                if (file_exists($routeFile)) {
                    require_once $routeFile;
                }
            }
        }
    }
}
