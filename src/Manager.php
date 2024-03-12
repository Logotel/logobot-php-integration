<?php

namespace Logotel\Logobot;

use BadMethodCallException;

class Manager
{
    public static function jwt(): JwtManager
    {
        return new JwtManager();
    }

    public static function textUpload()
    {
        return new TextUploadManager();
    }

    public static function bulkImporter()
    {
        throw new BadMethodCallException("Method not implemented");
    }
}
