<?php

namespace Lazer\Classes\Helpers;

use Lazer\Classes\LazerException;

interface FileInterface {

    /**
     * Setting name of table
     * @param string $name
     * @return File
     */
    public static function table($name);

    /**
     * Set the file type
     * @param string $type File type (data|config)
     */
    public function setType($type);

    /**
     * Returning path to file
     * @return string Path to file
     * @throws LazerException You must specify the type of file
     */
    public function getPath();

    /**
     * Return decoded JSON
     * @param boolean $assoc Returns object if false; array if true
     * @return mixed (object|array)
     */
    public function get($assoc = false);

    /**
     * Saving encoded JSON to file
     * @param object|array $data
     * @return boolean
     */
    public function put($data);

    /**
     * Checking that file exists
     * @return boolean
     */
    public function exists();

    /**
     * Removing file
     * @return boolean
     * @throws LazerException If file doesn't exists or there's problems with deleting files
     */
    public function remove();
}
