<?php

namespace App\Service;

class StaticService
{
    private static $static_content = [
        'name' => 'John Doe',
        'active' => true,
        'age' => 30,
    ];

    public static function staticValue()
    {
        return self::$static_content;
    }
}