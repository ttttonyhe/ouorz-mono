<?php

namespace Lazer\Classes;

use Lazer\Classes\Helpers;

/**
 * Core class of Lazer.
 *
 * There are classes to use JSON files like file database.
 *
 * Using style was inspired by ORM classes.
 *
 * @category Core
 * @author Grzegorz Kuźnik
 * @copyright (c) 2013, Grzegorz Kuźnik
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link https://github.com/Greg0/Lazer-Database GitHub Repository
 */
class Database implements \IteratorAggregate, \Countable {

    /**
     * Contain returned data from file as object or array of objects
     * @var mixed Data from table
     */
    protected $data;

    /**
     * Name of file (table)
     * @var string Name of table
     */
    protected $name;

    /**
     * Object with setted data
     * @var object Setted data
     */
    protected $set;

    /**
     * ID of current row if setted
     * @var integer Current ID
     */
    protected $currentId;

    /**
     * Key if current row if setted
     * @var integer Current key
     */
    protected $currentKey;

    /**
     * Pending functions with values
     * @see \Lazer\Classes\Core_Database::setPending()
     * @var array
     */
    protected $pending;

    /**
     * Information about to reset keys in array or not to
     * @var integer
     */
    protected $resetKeys = 1;

    /**
     * Factory pattern
     * @param string $name Name of table
     * @return self
     * @throws LazerException If there's problems with load file
     */
    public static function table($name)
    {
        Helpers\Validate::table($name)->exists();

        $self       = new self();
        $self->name = $name;

        $self->setFields();
        $self->setPending();

        return $self;
    }

    /**
     * Get rows from table
     * @uses \Lazer\Classes\Helpers\Data::get() to get data from file
     * @return array
     */
    protected function getData()
    {
        return Helpers\Data::table($this->name)->get();
    }

    /**
     * Setting data to Database::$data
     */
    protected function setData()
    {
        $this->data = $this->getData();
    }

    /**
     * Returns array key of row with specified ID
     * @param integer $id Row ID
     * @return integer Row key
     * @throws LazerException If there's no data with that ID
     */
    protected function getRowKey($id)
    {
        foreach ($this->getData() as $key => $data)
        {
            if ($data->id == $id)
            {
                return $key;
                break;
            }
        }
        throw new LazerException('No data found with ID: ' . $id);
    }

    /**
     * Set NULL for currentId and currentKey
     */
    protected function clearKeyInfo()
    {
        $this->currentId  = $this->currentKey = NULL;
    }

    /**
     * Setting fields with default values
     * @uses \Lazer\Classes\Helpers\Validate::isNumeric() to check if type of field is numeric
     */
    protected function setFields()
    {
        $this->set = new \stdClass();
        $schema    = $this->schema();

        foreach ($schema as $field => $type)
        {
            if (Helpers\Validate::isNumeric($type) && $field != 'id')
            {
                $this->setField($field, 0);
            }
            else
            {
                $this->setField($field, null);
            }
        }
    }

    /**
     * Set pending functions in right order with default values (Empty).
     */
    protected function setPending()
    {
        $this->pending = array(
            'where'   => array(),
            'orderBy' => array(),
            'limit'   => array(),
            'with'    => array(),
            'groupBy' => array(),
        );
    }

    /**
     * Clear info about previous queries
     */
    protected function clearQuery()
    {
        $this->setPending();
        $this->clearKeyInfo();
    }

    /**
     * Validating array and setting variables to current operations
     *
     * @uses \Lazer\Classes\Database::setField() to set field value
     * @param array $data key value pair
     * @throws LazerException
     */
    public function set(array $data)
    {
        foreach ($data as $name => $value) {
            $this->setField($name, $value);
        }
    }

    /**
     * Validating array and setting variables to current operations
     *
     * @uses \Lazer\Classes\Database::setField() to set field value
     * @param $name
     * @param $value
     * @throws LazerException
     */
    public function __set($name, $value)
    {
        $this->setField($name, $value);
    }


    /**
     * Validating fields and setting variables to current operations
     *
     * @uses \Lazer\Classes\Helpers\Validate::field() to check that field exist
     * @uses \Lazer\Classes\Helpers\Validate::type() to check that field type is correct
     * @param string $name  Field name
     * @param mixed  $value Field value
     * @return self
     * @throws LazerException
     */
    public function setField($name, $value)
    {
        if (Helpers\Validate::table($this->name)->field($name) && Helpers\Validate::table($this->name)->type($name, $value))
        {
            $this->set->{$name} = is_string($value) && false === mb_check_encoding($value, 'UTF-8')
                ? utf8_encode($value)
                : $value;
        }

        return $this;
    }

    /**
     * Returning variable from Object
     * @param string $name Field name
     * @return mixed Field value
     * @throws LazerException
     */
    public function getField($name)
    {
        if ($this->issetField($name)) {
            return $this->set->{$name};
        }

        throw new LazerException('There is no data');
    }

    /**
     * Check if the given field exists
     * @param string $name Field name
     * @return boolean True if the field exists, false otherwise
     */
    public function issetField($name)
    {
        return property_exists($this->set, $name);
    }

    /**
     * Returning variable from Object
     * @param string $name Field name
     * @return mixed Field value
     * @throws LazerException
     */
    public function __get($name)
    {
        return $this->getField($name);
    }

    /**
     * Check if the given field exists
     * @param string $name Field name
     * @return boolean True if the field exists, false otherwise
     */
    public function __isset($name)
    {
        return $this->issetField($name);
    }

    /**
     * Execute pending functions
     */
    protected function pending()
    {
        $this->setData();
        foreach ($this->pending as $func => $args)
        {
            if (!empty($args))
            {
                call_user_func(array($this, $func . 'Pending'));
            }
        }

        //clear pending values after executed query
        $this->clearQuery();
    }

    /**
     * Creating new table
     *
     * For example few fields:
     *
     * Database::create('news', array(
     *  'title' => 'string',
     *  'content' => 'string',
     *  'rating' => 'double',
     *  'author' => 'integer'
     * ));
     *
     * Types of field:
     * - boolean
     * - integer
     * - string
     * - double (also for float type)
     *
     * ID field isn't required (it will be created automatically) but you can specify it at first place.
     *
     * @uses \Lazer\Classes\Helpers\Data::arrToLower() to lower case keys and values of array
     * @uses \Lazer\Classes\Helpers\Data::exists() to check if data file exists
     * @uses \Lazer\Classes\Helpers\Config::exists() to check if config file exists
     * @uses \Lazer\Classes\Helpers\Validate::types() to check if type of fields are correct
     * @uses \Lazer\Classes\Helpers\Data::put() to save data file
     * @uses \Lazer\Classes\Helpers\Config::put() to save config file
     * @param string $name Table name
     * @param array $fields Field configuration
     * @throws LazerException If table exist
     */
    public static function create($name, array $fields)
    {
        $fields = Helpers\Validate::arrToLower($fields);

        if (Helpers\Data::table($name)->exists() && Helpers\Config::table($name)->exists())
        {
            throw new LazerException('helper\Table "' . $name . '" already exists');
        }

        $types = array_values($fields);

        Helpers\Validate::types($types);

        if (!array_key_exists('id', $fields))
        {
            $fields = array('id' => 'integer') + $fields;
        }

        $data            = new \stdClass();
        $data->last_id   = 0;
        $data->schema    = $fields;
        $data->relations = new \stdClass();

        Helpers\Data::table($name)->put(array());
        Helpers\Config::table($name)->put($data);
    }

    /**
     * Removing table with config
     * @uses \Lazer\Classes\Helpers\Data::remove() to remove data file
     * @uses \Lazer\Classes\Helpers\Config::remove() to remove config file
     * @param string $name Table name
     * @return boolean|LazerException
     */
    public static function remove($name)
    {
        if (Helpers\Data::table($name)->remove() && Helpers\Config::table($name)->remove())
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Grouping results by one field
     * @param string $column
     * @return self
     */
    public function groupBy($column)
    {
        if (Helpers\Validate::table($this->name)->field($column))
        {
            $this->resetKeys             = 0;
            $this->pending[__FUNCTION__] = $column;
        }

        return $this;
    }

    /**
     * Grouping array pending method
     */
    protected function groupByPending()
    {
        $column = $this->pending['groupBy'];

        $grouped = array();
        foreach ($this->data as $object)
        {
            $grouped[$object->{$column}][] = $object;
        }

        $this->data = $grouped;
    }

    /**
     * JOIN other tables
     * @param string $table relations separated by :
     * @return self
     */
    public function with($table)
    {
        $this->pending['with'][] = explode(':', $table);
        return $this;
    }

    /**
     * Pending function for with(), joining other tables to current
     */
    protected function withPending()
    {
        $joins = $this->pending['with'];
        foreach ($joins as $join)
        {
            $local   = (count($join) > 1) ? array_slice($join, -2, 1)[0] : $this->name;
            $foreign = end($join);

            $relation = Relation::table($local)->with($foreign);

            $data = $this->data;

            foreach ($join as $part)
            {
                $data = $relation->build($data, $part);
            }
        }
    }

    /**
     * Sorting data by field
     * @param string $key Field name
     * @param string $direction ASC|DESC
     * @return self
     */
    public function orderBy($key, $direction = 'ASC')
    {
        if (Helpers\Validate::table($this->name)->field($key))
        {
            $directions                        = array(
                'ASC'  => SORT_ASC,
                'DESC' => SORT_DESC
            );
            $this->pending[__FUNCTION__][$key] = isset($directions[$direction]) ? $directions[$direction] : 'ASC';
        }

        return $this;
    }

    /**
     * Sort an array of objects by more than one field.
     * @
     * @link http://blog.amnuts.com/2011/04/08/sorting-an-array-of-objects-by-one-or-more-object-property/ It's not mine algorithm
     */
    protected function orderByPending()
    {
        $properties = $this->pending['orderBy'];
        uasort($this->data, function($a, $b) use ($properties)
        {
            foreach ($properties as $column => $direction)
            {
                if (is_int($column))
                {
                    $column    = $direction;
                    $direction = SORT_ASC;
                }
                $collapse = function($node, $props)
                {
                    if (is_array($props))
                    {
                        foreach ($props as $prop)
                        {
                            $node = (!isset($node->$prop)) ? null : $node->$prop;
                        }
                        return $node;
                    }
                    else
                    {
                        return (!isset($node->$props)) ? null : $node->$props;
                    }
                };
                $aProp = $collapse($a, $column);
                $bProp = $collapse($b, $column);

                if ($aProp != $bProp)
                {
                    return ($direction == SORT_ASC) ? strnatcasecmp($aProp, $bProp) : strnatcasecmp($bProp, $aProp);
                }
            }
            return FALSE;
        });
    }

    /**
     * Where function, like SQL
     *
     * Operators:
     * - Standard operators (=, !=, >, <, >=, <=)
     * - IN (only for array value)
     * - NOT IN (only for array value)
     *
     * @param string $field Field name
     * @param string $op Operator
     * @param mixed $value Field value
     * @return self
     */
    public function where($field, $op, $value)
    {
        $this->pending['where'][] = array(
            'type'  => 'and',
            'field' => $field,
            'op'    => $op,
            'value' => $value,
        );

        return $this;
    }

    /**
     * Alias for where()
     * @param string $field Field name
     * @param string $op Operator
     * @param mixed $value Field value
     * @return self
     */
    public function andWhere($field, $op, $value)
    {
        $this->where($field, $op, $value);

        return $this;
    }

    /**
     * Alias for where(), setting OR for searching
     * @param string $field Field name
     * @param string $op Operator
     * @param mixed $value Field value
     * @return self
     */
    public function orWhere($field, $op, $value)
    {
        $this->pending['where'][] = array(
            'type'  => 'or',
            'field' => $field,
            'op'    => $op,
            'value' => $value,
        );

        return $this;
    }

    /**
     * Filter function for array_filter() in where()
     * @return boolean
     */
    protected function wherePending()
    {
        $operator = array(
            '='   => '==',
            '!='  => '!=',
            '>'   => '>',
            '<'   => '<',
            '>='  => '>=',
            '<='  => '<=',
            'and' => '&&',
            'or'  => '||'
        );

        $this->data = array_filter($this->data, function($row) use ($operator)
        {
            $clause = '';
            $result = true;

            foreach ($this->pending['where'] as $key => $condition)
            {
                $value = $condition['value'];
                $type = $condition['type'];
                $op = $condition['op'];
                $field = $condition['field'];

                if (is_array($value) && $op == 'IN')
                {
                    $value = (in_array($row->{$field}, $value)) ? 1 : 0;
                    $op    = '==';
                    $field = 1;
                }
                elseif (!is_array($value) && in_array($op, array('LIKE', 'like')))
                {
                    $regex = "/^" . str_replace('%', '(.*?)', preg_quote($value)) . "$/si";
                    $value = preg_match($regex, $row->{$field});
                    $op    = '==';
                    $field = 1;
                }
                elseif (!is_array($value) && $op != 'IN')
                {
                    $value = is_string($value) ?
                        '\'' . mb_strtolower($value) . '\'' :
                        $value;

                    $op    = $operator[$op];
                    $field = is_string($row->{$field}) ?
                        'mb_strtolower($row->' . $field .')' :
                        '$row->' . $field;
                }

                $type = (!$key) ?
                    null :
                    $operator[$type];

                $query = array($type, $field, $op, $value);
                $clause .= implode(' ', $query) . ' ';

                eval('$result = ' . $clause . ';');
            }

            return $result;
        });
    }

    /**
     * Returning data as indexed or assoc array.
     * @param string $key Field that will be the key, NULL for Indexed
     * @param string $value Field that will be the value
     * @return array
     */
    public function asArray($key = null, $value = null)
    {
        if (!is_null($key))
        {
            Helpers\Validate::table($this->name)->field($key);
        }
        if (!is_null($value))
        {
            Helpers\Validate::table($this->name)->field($value);
        }

        $datas = array();
        if (!$this->resetKeys)
        {
            if (is_null($key) && is_null($value))
            {
                return $this->data;
            }
            else
            {
                foreach ($this->data as $rowKey => $data)
                {
                    $datas[$rowKey] = array();
                    foreach ($data as $row)
                    {
                        if (is_null($key))
                        {
                            $datas[$rowKey][] = $row->{$value};
                        }
                        elseif (is_null($value))
                        {
                            $datas[$rowKey][$row->{$key}] = $row;
                        }
                        else
                        {
                            $datas[$rowKey][$row->{$key}] = $row->{$value};
                        }
                    }
                }
            }
        }
        else
        {
            if (is_null($key) && is_null($value))
            {
                foreach ($this->data as $data)
                {
                    $datas[] = get_object_vars($data);
                }
            }
            else
            {
                foreach ($this->data as $data)
                {
                    if (is_null($key))
                    {
                        $datas[] = $data->{$value};
                    }
                    elseif (is_null($value))
                    {
                        $datas[$data->{$key}] = $data;
                    }
                    else
                    {
                        $datas[$data->{$key}] = $data->{$value};
                    }
                }
            }
        }

        return $datas;
    }

    /**
     * Limit returned data
     *
     * Should be used at the end of chain, before end method
     * @param integer $number Limit number
     * @param integer $offset Offset number
     * @return self
     */
    public function limit($number, $offset = 0)
    {
        $this->pending['limit'] = array(
            'offset' => $offset,
            'number' => $number
        );

        return $this;
    }

    /**
     * Pending function for limit()
     */
    protected function limitPending()
    {
        $offset     = $this->pending['limit']['offset'];
        $num        = $this->pending['limit']['number'];
        $this->data = array_slice($this->data, $offset, $num);
    }

    /**
     * Add new fields to table, array schema like in create() function
     * @param array $fields Associative array
     */
    public function addFields(array $fields)
    {
        $fields = Helpers\Validate::arrToLower($fields);

        Helpers\Validate::types(array_values($fields));

        $schema = $this->schema();
        $fields = array_diff_assoc($fields, $schema);

        if (!empty($fields))
        {
            $config         = $this->config();
            $config->schema = array_merge($schema, $fields);

            $data = $this->getData();
            foreach ($data as $key => $object)
            {
                foreach ($fields as $name => $type)
                {
                    if (Helpers\Validate::isNumeric($type))
                        $data[$key]->{$name} = 0;
                    else
                        $data[$key]->{$name} = null;
                }
            }

            Helpers\Data::table($this->name)->put($data);
            Helpers\Config::table($this->name)->put($config);
        }
    }

    /**
     * Delete fields from array
     * @param array $fields Indexed array
     */
    public function deleteFields(array $fields)
    {
        $fields = Helpers\Validate::arrToLower($fields);

        Helpers\Validate::table($this->name)->fields($fields);

        $config         = $this->config();
        $config->schema = array_diff_key($this->schema(), array_flip($fields));

        $data = $this->getData();
        foreach ($data as $key => $object)
        {
            foreach ($fields as $name)
            {
                unset($data[$key]->{$name});
            }
        }

        Helpers\Data::table($this->name)->put($data);
        Helpers\Config::table($this->name)->put($config);
    }

    /**
     * Returns table name
     * @return string table name
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Returning object with config for table
     * @return object Config
     */
    public function config()
    {
        return Helpers\Config::table($this->name)->get();
    }

    /**
     * Return array with names of fields
     * @return array Fields
     */
    public function fields()
    {
        return Helpers\Config::table($this->name)->fields();
    }

    /**
     * Returning assoc array with types of fields
     * @return array Fields type
     */
    public function schema()
    {
        return Helpers\Config::table($this->name)->schema();
    }

    /**
     * Returning assoc array with relationed tables
     * @param string|null $tableName
     * @return array Fields type
     */
    public function relations($tableName = null)
    {
        return Helpers\Config::table($this->name)->relations($tableName, true);
    }

    /**
     * Returning last ID from table
     * @return integer Last ID
     */
    public function lastId()
    {
        return Helpers\Config::table($this->name)->lastId();
    }

    /**
     * Insert a row
     *
     * @throws LazerException
     */
    public function insert()
    {
        $this->save(true);
    }

    /**
     * Saving inserted or updated data
     * @param bool $forceInsert
     * @throws LazerException
     */
    public function save($forceInsert = false)
    {
        $data = $this->getData();
        if (!$this->currentId || $forceInsert)
        {
            $config = $this->config();
            $config->last_id++;

            $this->setField('id', $config->last_id);
            array_push($data, $this->set);

            Helpers\Config::table($this->name)->put($config);
        }
        else
        {
            $this->setField('id', $this->currentId);
            $data[$this->currentKey] = $this->set;
        }

        Helpers\Data::table($this->name)->put($data);

        // after save, clear all $set data
        $this->set = new \stdClass();

//         $this->setFields();
    }

    /**
     * Deleting loaded data
     * @return boolean
     */
    public function delete()
    {
        $data = $this->getData();
        if (isset($this->currentId))
        {
            unset($data[$this->currentKey]);
        }
        else
        {
            $this->pending();
            $old  = $data;
            $data = array_diff_key($old, $this->data);
        }
        $this->data = array_values($data);

        return Helpers\Data::table($this->name)->put($this->data) ? true : false;
    }

    /**
     * Return count in integer or array of integers (if grouped)
     * @return mixed
     */
    public function count()
    {
        if (!$this->resetKeys)
        {
            $count = array();
            foreach ($this->data as $group => $data)
            {
                $count[$group] = count($data);
            }
        }
        else
        {
            $count = count($this->data);
        }

        return $count;
    }

    /**
     * Returns one row with specified ID
     * @param integer $id Row ID
     * @return self
     */
    public function find($id = NULL)
    {
        if ($id !== NULL)
        {
            $data             = $this->getData();
            $this->currentId  = $id;
            $this->currentKey = $this->getRowKey($id);
            foreach ($data[$this->currentKey] as $field => $value)
            {
                $this->setField($field, $value);
            }
        }
        else
        {
            $this->limit(1)->findAll();
            $data = $this->data;
            if (count($data))
            {
                foreach ($data[0] as $field => $value)
                {
                    $this->setField($field, $value);
                }

                $this->currentId  = $this->getField('id');
                $this->currentKey = $this->getRowKey($this->currentId);
            }
        }
        return clone $this;
    }

    /**
     * Make data ready to read
     */
    public function findAll()
    {
        $this->pending();
        $this->data = $this->resetKeys ? array_values($this->data) : $this->data;

        return clone $this;
    }

    /**
     * Iterator for Data
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Debug functions, prints whole query with values
     */
    public function debug()
    {
        $print = "Lazer::table(" . $this->name . ")\n";
        foreach ($this->pending as $function => $values)
        {
            if (!empty($values))
            {

                if (is_array($values))
                {
                    if (is_array(reset($values)))
                    {
                        foreach ($values as $value)
                        {
                            if ($function == 'where')
                            {
                                array_shift($value);
                            }
                            if ($function == 'with')
                            {
                                $params = implode(':', $value);
                            }
                            else
                            {
                                $params = implode(', ', $value);
                            }
                            $print .= "\t" . '->' . $function . '(' . $params . ')' . "\n";
                        }
                    }
                    else
                    {
                        $params = implode(', ', $values);
                        $print .= "\t" . '->' . $function . '(' . $params . ')' . "\n";
                    }
                }
                else
                {
                    $print .= "\t" . '->' . $function . '(' . $values . ')' . "\n";
                }
            }
        }
        echo '<pre>' . print_r($print, true) . '</pre>';
        $this->clearQuery();
    }

}
