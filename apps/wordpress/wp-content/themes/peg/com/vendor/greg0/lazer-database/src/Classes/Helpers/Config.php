<?php

namespace Lazer\Classes\Helpers;

/**
 * Config managing class
 *
 * @category Helpers
 * @author Grzegorz Kuźnik
 * @copyright (c) 2013, Grzegorz Kuźnik
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link https://github.com/Greg0/Lazer-Database GitHub Repository
 */
class Config extends File {

    /**
     * Get key from returned config
     * @param string $field key
     * @param bool $assoc
     * @return mixed
     */
    public function getKey($field, $assoc = false)
    {
        return $assoc ? $this->get($assoc)[$field] : $this->get($assoc)->{$field};
    }

    public static function table($name)
    {
        $file       = new Config;
        $file->name = $name;
        $file->setType('config');

        return $file;
    }

    /**
     * Return array with names of fields
     * @return array
     */
    public function fields()
    {
        return array_keys($this->getKey('schema', true));
    }

    /**
     * Return relations configure
     * @param mixed $tableName null-all tables;array-few tables;string-one table relation informations
     * @param boolean $assoc Object or associative array
     * @return array|object
     */
    public function relations($tableName = null, $assoc = false)
    {
        if (is_array($tableName))
        {
            $relations = $this->getKey('relations', $assoc);
            if ($assoc)
            {
                return array_intersect_key($relations, array_flip($tableName));
            }
            else
            {
                return (object) array_intersect_key((array) $relations, array_flip($tableName));
            }
        }
        elseif ($tableName !== null)
        {
            return $assoc ? $this->getKey('relations', $assoc)[$tableName] : $this->getKey('relations', $assoc)->{$tableName};
        }

        return $this->getKey('relations', $assoc);
    }

    /**
     * Returning assoc array with types of fields
     * @return array
     */
    public function schema()
    {
        return $this->getKey('schema', true);
    }

    /**
     * Returning last ID from table
     * @return integer
     */
    public function lastId()
    {
        return $this->getKey('last_id');
    }

}
