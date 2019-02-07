<?php

namespace App\Services;


class Config
{
    // The client code, user name, and password you have received from Erply.com
    const CLIENT_CODE = 0;
    const USERNAME = 'your_email@your_host.com';
    const PASSWORD = 'secret_word';

    public static function getApiUrl(): string
    {
        return "https://".self::CLIENT_CODE.".erply.com/api/";
    }
}
