<?php

namespace Lazer\Classes;

use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\Helpers\Config;

/**
 * Relation class of LAZER project.
 *
 * @category Core
 * @author Grzegorz Kuźnik
 * @copyright (c) 2013, Grzegorz Kuźnik
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link https://github.com/Greg0/Lazer-Database GitHub Repository
 */
class Relation {

    /**
     * Tables names
     * @var array tables
     */
    protected $tables = array(
        'local'   => null,
        'foreign' => null
    );

    /**
     * Relation keys names
     * @var array keys
     */
    protected $keys = array(
        'local'   => null,
        'foreign' => null
    );

    /**
     * Current relation type
     * @var string
     */
    protected $relationType;

    /**
     * All relations types
     * @var array
     */
    protected static $relations = array('belongsTo', 'hasMany', 'hasAndBelongsToMany');

    /**
     * Factory method
     * @param string $name Name of table
     * @return self
     */
    public static function table($name)
    {
        Validate::table($name)->exists();

        $self                  = new self();
        $self->tables['local'] = $name;

        return $self;
    }

    /**
     * Getter of junction table name in many2many relation
     * @return boolean|string Name of junction table or false
     */
    public function getJunction()
    {
        if ($this->relationType == 'hasAndBelongsToMany')
        {
            $tables = $this->tables;
            sort($tables);
            return implode('_', $tables);
        }
        return false;
    }

    /**
     * Set relation type to field
     * @param string $relation Name of relation
     */
    protected function setRelationType($relation)
    {
        Validate::relationType($relation);
        $this->relationType = $relation;
    }

    /**
     * Set table name
     * @param string $type local or foreign
     * @param string $name table name
     */
    protected function setTable($type, $name)
    {
        Validate::table($name)->exists();
        $this->tables[$type] = $name;
    }

    /**
     * Set key name
     * @param string $type local or foreign
     * @param string $key key name
     * @return self
     * @throws LazerException First you must define tables name
     */
    protected function setKey($type, $key)
    {
        if (!in_array(null, $this->tables))
        {
            Validate::table($this->tables[$type])->field($key);

            $this->keys[$type] = $key;
            return $this;
        }

        throw new LazerException('First you must define tables name');
    }

    /**
     * Set local key name
     * @param string $key key name
     * @return self
     * @throws LazerException First you must define tables name
     */
    public function localKey($key)
    {
        return $this->setKey('local', $key);
    }

    /**
     * Set foreign key name
     * @param string $key key name
     * @return self
     * @throws LazerException First you must define tables name
     */
    public function foreignKey($key)
    {
        return $this->setKey('foreign', $key);
    }

    /**
     * Set relation one2many to table
     * @param string $table Table name
     * @return self
     */
    public function belongsTo($table)
    {
        $this->setTable('foreign', $table);
        $this->setRelationType(__FUNCTION__);

        return $this;
    }

    /**
     * Set relation many2one to table
     * @param string $table Table name
     * @return self
     */
    public function hasMany($table)
    {
        $this->setTable('foreign', $table);
        $this->setRelationType(__FUNCTION__);

        return $this;
    }

    /**
     * Set relation many2many to table
     * @param string $table Table name
     * @return self
     */
    public function hasAndBelongsToMany($table)
    {
        $this->setTable('foreign', $table);
        $this->setRelationType(__FUNCTION__);

        return $this;
    }

    /**
     * Use relation to table
     * @param string $table Table name
     * @return self
     */
    public function with($table)
    {
        Validate::relation($this->tables['local'], $table);
        $this->setTable('foreign', $table);
        $this->setRelationType(Config::table($this->tables['local'])->relations($this->tables['foreign'])->type);
        $this->setKey('local', Config::table($this->tables['local'])->relations($this->tables['foreign'])->keys->local);
        $this->setKey('foreign', Config::table($this->tables['local'])->relations($this->tables['foreign'])->keys->foreign);

        return $this;
    }

    /**
     * Set specified relation
     * @throws LazerException Tables names or keys missing
     */
    public function setRelation()
    {
        if (!in_array(null, $this->tables) && !in_array(null, $this->keys))
        {
            $this->addRelation();
            return true;
        }
        else
        {
            throw new LazerException('Tables names or keys missing');
        }
    }

    /**
     * Get relation information
     * @return array relation information
     */
    public function getRelation()
    {
        return array(
            'tables' => $this->tables,
            'keys'   => $this->keys,
            'type'   => $this->relationType
        );
    }

    /**
     * Remove relation
     */
    public function removeRelation()
    {
        if ($this->relationType == 'hasAndBelongsToMany')
        {
            $junction = $this->getJunction();

            $this->deleteRelationData($junction, $this->tables['local']);
            $this->deleteRelationData($junction, $this->tables['foreign']);
        }
        $this->deleteRelationData($this->tables['local'], $this->tables['foreign']);
    }

    /**
     * Add data to configs and create all necessary files
     */
    protected function addRelation()
    {
        if ($this->relationType == 'hasAndBelongsToMany')
        {
            $junction = $this->getJunction();

            try
            {
                Validate::table($junction)->exists();
            }
            catch (LazerException $e)
            {
                Database::create($junction, array(
                    $this->tables['local'] . '_id'   => 'integer',
                    $this->tables['foreign'] . '_id' => 'integer',
                ));

                $this->insertRelationData($junction, $this->tables['local'], 'hasMany', array(
                    'local'   => $this->tables['local'] . '_id',
                    'foreign' => $this->keys['local']
                ));

                $this->insertRelationData($junction, $this->tables['foreign'], 'hasMany', array(
                    'local'   => $this->tables['foreign'] . '_id',
                    'foreign' => $this->keys['foreign']
                ));
            }
        }
        $this->insertRelationData($this->tables['local'], $this->tables['foreign'], $this->relationType, $this->keys);
    }

    /**
     * Inserts relation data to config file
     * @param string $from Local table
     * @param string $to Related table
     * @param string $type Relation type
     * @param array $keys Relationed keys
     */
    protected function insertRelationData($from, $to, $type, array $keys)
    {
        $config                    = Config::table($from);
        $content                   = $config->get();
        $content->relations->{$to} = array(
            'type' => $type,
            'keys' => $keys,
        );
        $config->put($content);
    }

    /**
     * Inserts relation data to config file
     * @param string $from Local table
     * @param string $to Related table
     */
    protected function deleteRelationData($from, $to)
    {
        $config  = Config::table($from);
        $content = $config->get();
        unset($content->relations->{$to});
        $config->put($content);
    }

    /**
     * Process query with joined data
     * @param object $row One row of data
     * @return Database
     */
    protected function join($row)
    {
        $keys['local']   = $this->keys['local'];
        $keys['foreign'] = $this->keys['foreign'];

        if ($this->relationType == 'hasAndBelongsToMany')
        {
            $join = Database::table($this->getJunction())
                ->groupBy($this->tables['local'] . '_id')
                ->where($this->tables['local'] . '_id', '=', $row->{$keys['local']})
                ->findAll()
                ->asArray(null, $this->tables['foreign'] . '_id');


            if (empty($join))
                return array();

            return Database::table($this->tables['foreign'])
                ->where($keys['foreign'], 'IN', $join[$row->{$keys['local']}]);
        }

        return Database::table($this->tables['foreign'])
            ->where($keys['foreign'], '=', $row->{$keys['local']});
    }

    /**
     *
     * @param array $array
     * @param string $part
     * @return array
     */
    public function build(array $array, $part)
    {
        $return = array();
        foreach ($array as $key => $row)
        {
            if (is_object($row))
            {
                if ($row instanceof \stdClass)
                {
                    $part = ucfirst($part);

                    if (!isset($row->{$part}))
                    {
                        $query = $this->join($row);

                        if ($this->relationType == 'belongsTo')
                        {
                            $query = $query->findAll();
                            $query = reset($query)[0];
                        }

                        $row->{$part} = $query;
                    }

                    $array[$key] = $row->{$part};
                    $return[]    = $row->{$part};
                }
                else
                {
                    $row->with($part);
                }
            }
            else
            {
                $return = array_merge($return, $this->build($row, $part));
            }
        }
        return $return;
    }

    /**
     * Get relations types
     * @return array
     */
    public static function relations()
    {
        return self::$relations;
    }

}