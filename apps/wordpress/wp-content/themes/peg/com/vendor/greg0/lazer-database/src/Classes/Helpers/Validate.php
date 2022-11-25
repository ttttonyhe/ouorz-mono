<?php

namespace Lazer\Classes\Helpers;

use Lazer\Classes\LazerException;
use Lazer\Classes\Relation;

/**
 * Validation for tables
 *
 * @category Helpers
 * @author Grzegorz Kuźnik
 * @copyright (c) 2013, Grzegorz Kuźnik
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link https://github.com/Greg0/Lazer-Database GitHub Repository
 */
class Validate {

    /**
     * Name of table
     * @var string
     */
    private $name;

    /**
     * Table name
     * @param string $name
     * @return Validate
     */
    public static function table($name)
    {
        $validate       = new Validate();
        $validate->name = $name;
        return $validate;
    }

    /**
     * Checking that field type is numeric
     * @param string $type
     * @return boolean
     */
    public static function isNumeric($type)
    {
        $defined = array('integer', 'double');

        if (in_array($type, $defined))
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Checking that types from array matching with [boolean, integer, string, double]
     * @param array $types Indexed array
     * @return bool
     * @throws LazerException
     */
    public static function types(array $types)
    {
        $defined = array('boolean', 'integer', 'string', 'double');
        $diff    = array_diff($types, $defined);

        if (empty($diff))
        {
            return TRUE;
        }
        throw new LazerException('Wrong types: "' . implode(', ', $diff) . '". Available "boolean, integer, string, double"');
    }

    /**
     * Delete ID field from arrays
     * @param array $fields
     * @return array Fields without ID
     */
    public static function filter(array $fields)
    {
        if (array_values($fields) === $fields)
        {
            if (($key = array_search('id', $fields)) !== false)
            {
                unset($fields[$key]);
            }
        }
        else
        {
            unset($fields['id']);
        }
        return $fields;
    }

    /**
     * Change keys and values case to lower
     * @param array $array
     * @return array
     */
    public static function arrToLower(array $array)
    {
        $array = array_change_key_case($array);
        $array = array_map('strtolower', $array);

        return $array;
    }

    /**
     * Checking that typed fields really exist in table
     * @param array $fields Indexed array
     * @return boolean
     * @throws LazerException If field(s) does not exist
     */
    public function fields(array $fields)
    {
        $fields = self::filter($fields);
        $diff   = array_diff($fields, Config::table($this->name)->fields());

        if (empty($diff))
        {
            return TRUE;
        }
        throw new LazerException('Field(s) "' . implode(', ', $diff) . '" does not exists in table "' . $this->name . '"');
    }

    /**
     * Checking that typed field really exist in table
     * @param string $name
     * @return boolean
     * @throws LazerException If field does not exist
     */
    public function field($name)
    {
        if (in_array($name, Config::table($this->name)->fields()))
        {
            return TRUE;
        }
        throw new LazerException('Field ' . $name . ' does not exists in table "' . $this->name . '"');
    }

    /**
     * Checking that Table and Config exists and throw exceptions if not
     * @return boolean
     * @throws LazerException
     */
    public function exists()
    {
        if (!Data::table($this->name)->exists())
            throw new LazerException('Table "' . $this->name . '" does not exists');

        if (!Config::table($this->name)->exists())
            throw new LazerException('Config "' . $this->name . '" does not exists');

        return TRUE;
    }

    /**
     * Checking that typed field have correct type of value
     * @param string $name
     * @param mixed $value
     * @return boolean
     * @throws LazerException If type is wrong
     */
    public function type($name, $value)
    {
        $schema = Config::table($this->name)->schema();
        if (array_key_exists($name, $schema) && (null === $value || $schema[$name] == gettype($value)) || (self::isNumeric($schema[$name]) === self::isNumeric(gettype($value))))
        {
            return TRUE;
        }

        throw new LazerException('Wrong data type');
    }

    /**
     * Checking that relation between tables exists
     * @param string $local local table
     * @param string $foreign related table
     * @return bool relation exists
     * @throws LazerException
     */
    public static function relation($local, $foreign)
    {
        $relations = Config::table($local)->relations();
        if (isset($relations->{$foreign}))
        {
            return TRUE;
        }

        throw new LazerException('Relation "' . $local . '" to "' . $foreign . '" doesn\'t exist');
    }

    /**
     * Checking that relation type is correct
     * @param string $type 
     * @return bool relation type
     * @throws LazerException Wrong relation type
     */
    public static function relationType($type)
    {
        if (in_array($type, Relation::relations()))
        {
            return true;
        }

        throw new LazerException('Wrong relation type');
    }

}
