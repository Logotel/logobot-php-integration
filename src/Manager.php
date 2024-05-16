<?php

namespace Logotel\Logobot;

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
        return new BulkUploadManager();
    }

    public static function deleteDocument()
    {
        return new DeleteDocumentManager();
    }
}
