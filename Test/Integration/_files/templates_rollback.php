<?php

function removeDir($dir): bool
{
    if (!file_exists($dir)) {
        return true;
    }

    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $path = $dir . '/' . $file;

        if (is_dir($path)) {
            removeDir($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}


removeDir(BP . '/pub/errors/custom');

if (file_exists(BP . '/pub/errors/local_sample.xml')) {
    unlink(BP . '/pub/errors/local_sample.xml');
}

removeDir(BP . '/vendor/magento/theme-frontend-luma/errors');
