<?php

namespace CF;

class Utils
{
    /*
     * @return string
     */
    public static function getCurrentDate()
    {
        // Format ISO 8601
        return date('c');
    }
}
