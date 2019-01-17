<?php

namespace Services;


class Config
{
    const CLIENT_CODE = 380579;
    const USERNAME = 'andrei@malachiteden.com';
    const PASSWORD = 'x7bzWBgZqx5cLZSx';

    public static function getApiUrl(){
        return "https://".self::CLIENT_CODE.".erply.com/api/";
    }
}
