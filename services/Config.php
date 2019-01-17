<?php
/**
 * Author: Andrei Babkin <andrei@malachiteden.com>
 * Date: 17.01.2019
 * Time: 8:37
 */

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
