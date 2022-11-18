<?php

namespace Lazer\Classes\Helpers;

/**
 * Data managing class
 *
 * @category Helpers
 * @author Grzegorz Kuźnik
 * @copyright (c) 2013, Grzegorz Kuźnik
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link https://github.com/Greg0/Lazer-Database GitHub Repository
 */
class Data extends File {

    public static function table($name)
    {
        $file       = new Data;
        $file->name = $name;
        $file->setType('data');

        return $file;
    }

}
