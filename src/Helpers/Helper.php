<?php

namespace MenqzAdmin\Admin\Helpers;

class Helper
{
    static function addVersionFile($fileName)
    {
        $jsPath = public_path($fileName);
        $version = file_exists($jsPath) ? filemtime($jsPath) : time();
        return asset($fileName) . '?v=' . $version;
    }
}
