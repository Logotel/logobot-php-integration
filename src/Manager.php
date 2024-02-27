<?php

namespace Logotel\Logobot;


class Manager{

    public static function jwt() : JwtManager{
        return new JwtManager();
    }

    public static function bulkImporter(){
        return "";
    }
}