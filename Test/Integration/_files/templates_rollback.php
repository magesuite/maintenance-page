<?php

function removeDir($dir)
{
    if(!file_exists($dir)) {
        return;
    }

    $files = scandir($dir);

    foreach ($files as $file) {
        if($file == '.' or $file == '..'){
            continue;
        }

        $path = $dir . '/' . $file;

        if(is_dir($path)){
            removeDir($path);
        }else{
            unlink($path);
        }
    }

    return rmdir($dir);
}


removeDir(BP . '/pub/errors/custom');

if(file_exists(BP . '/pub/errors/local.xml')) {
    unlink(BP . '/pub/errors/local.xml');
}

removeDir(BP . '/vendor/magento/theme-frontend-luma/errors');