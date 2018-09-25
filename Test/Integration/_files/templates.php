<?php

function full_copy($source, $target)
{
    @mkdir($target);
    $d = dir($source);

    while (($file = $d->read()) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $filePath = $source . '/' . $file;

        if (is_dir($filePath)) {
            full_copy($filePath, $target . '/' . $file);
            continue;
        }

        copy($filePath, $target . '/' . $file);
    }

    $d->close();
}


$files = __DIR__ . '/errors';

full_copy($files, BP . '/vendor/magento/theme-frontend-luma/errors');