<?php

namespace Lazer\Test\Classes;

use Lazer\Classes\Database;
use Lazer\Test\VfsHelper\Config as TestHelper;

class DatabaseTest extends \PHPUnit_Framework_TestCase {

    use TestHelper;

    /**
     * @var Database
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->setUpFilesystem();
        $this->object = new Database;
    }

    /**
     * @covers \Lazer\Classes\Database::create
     */
    public function testCreateTable()
    {
        $this->assertFalse($this->root->hasChild('newTable.data.json'));
        $this->assertFalse($this->root->hasChild('newTable.config.json'));
        $this->object->create('newTable', array(
            'myInteger' => 'integer',
            'myString'  => 'string',
            'myBool'    => 'boolean',
            'myDouble'    => 'double'
        ));
        $this->assertTrue($this->root->hasChild('newTable.data.json'));
        $this->assertTrue($this->root->hasChild('newTable.config.json'));
    }

    /**
     * @covers \Lazer\Classes\Database::create
     * @expectedException \Lazer\Classes\LazerException
     * @expectedExceptionMessageRegExp #Table ".*" already exists#
     */
    public function testCreateExistingTable()
    {
        $this->object->create('users', array(
            'myInteger' => 'integer',
            'myString'  => 'string',
            'myBool'    => 'boolean'
        ));
    }

    /**
     * @covers \Lazer\Classes\Database::remove
     */
    public function testRemoveExistingTable()
    {
        $this->assertTrue($this->root->hasChild('users.data.json'));
        $this->assertTrue($this->root->hasChild('users.config.json'));
        $this->object->remove('users');
        $this->assertFalse($this->root->hasChild('users.data.json'));
        $this->assertFalse($this->root->hasChild('users.config.json'));
    }

    /**
     * @covers \Lazer\Classes\Database::remove
     * @expectedException \Lazer\Classes\LazerException
     * @expectedExceptionMessageRegExp #.* File does not exists#
     */
    public function testRemoveNotExistingTable()
    {
        $this->object->remove('someTable');
    }

    /**
     * @covers \Lazer\Classes\Database::table
     * @expectedException \Lazer\Classes\LazerException
     * @expectedExceptionMessageRegExp #Table "[a-zA-Z0-9_-]+" does not exists#
     */
    public function testTableNotExists()
    {
        $this->object->table('notExistingTable');
    }

    /**
     * @covers \Lazer\Classes\Database::table
     */
    public function testTableExists()
    {
        $table = $this->object->table('users');
        $this->assertInstanceOf('Lazer\Classes\Database', $table);
        $this->assertEquals('users', $table->name());
        return $this->object->table('users');
    }

    /**
     * @covers \Lazer\Classes\Database::findAll
     * @depends testTableExists
     */
    public function testFindAll($table)
    {
        $results = $table->findAll();
        $this->assertInstanceOf('Lazer\Classes\Database', $results);
        $this->assertSame(4, count($results));
    }

    /**
     * @covers \Lazer\Classes\Database::find
     * @covers \Lazer\Classes\Database::getRowKey
     * @depends testTableExists
     */
    public function testFind($table)
    {
        $result = array();

        $result[] = $table->find();
        $this->assertInstanceOf('Lazer\Classes\Database', $result[0]);
        $this->assertSame(1, count($result[0]));

        $result[] = $table->find(2);
        $this->assertInstanceOf('Lazer\Classes\Database', $result[1]);
        $this->assertSame(1, count($result[1]));
        $this->assertSame(2, $result[1]->id);
    }

    /**
     * Lazer\Classes\Database::asArray
     * @depends testTableExists
     */
    public function testAsArray($table)
    {
        $results = $table->findAll()->asArray();
        $this->assertInternalType('array', $results);
        $this->assertArrayHasKey(0, $results);

        $resultsKeyField = $table->findAll()->asArray('id');
        $this->assertInternalType('array', $resultsKeyField);
        $this->assertArrayHasKey(3, $resultsKeyField);
        $this->assertArrayNotHasKey(0, $resultsKeyField);

        $resultsValueField = $table->findAll()->asArray(null, 'id');
        $this->assertInternalType('array', $resultsValueField);
        $this->assertArrayNotHasKey(4, $resultsValueField);
        $this->assertArrayHasKey(0, $resultsValueField);

        $resultsKeyValue = $table->findAll()->asArray('id', 'name', 'price');
        $this->assertInternalType('array', $resultsValueField);
        $this->assertArraySubset([2 => 'Kriss'], $resultsKeyValue);

        $resultsGroupBy = $this->object->table('order')->groupBy('category')->findAll()->asArray();
        $this->assertInternalType('array', $resultsGroupBy);
        $this->assertArrayHasKey('a', $resultsGroupBy);
        $this->assertArrayHasKey('b', $resultsGroupBy);
    }

    /**
     * Lazer\Classes\Database::count
     * @depends testTableExists
     */
    public function testIsCountable($table)
    {
        $results = $table->findAll();
        $this->assertSame(4, count($results));
        $this->assertSame(4, $results->count());

        $table1 = $this->object->table('order');
        $query  = $table1->groupBy('category')->findAll()->count();
        $this->assertInternalType('array', $query);
    }

    /**
     * @covers \Lazer\Classes\Database::limit
     * @covers \Lazer\Classes\Database::limitPending
     * @depends testTableExists
     */
    public function testLimit($table)
    {
        $results = $table->limit(1)->findAll();
        $this->assertInstanceOf('Lazer\Classes\Database', $results);
        $this->assertSame(1, count($results));
    }

    /**
     * @covers \Lazer\Classes\Database::orderBy
     * @covers \Lazer\Classes\Database::orderByPending
     */
    public function testOrderBy()
    {
        $table   = $this->object->table('order');
        $query   = array();
        $query[] = $table->orderBy('id')->findAll()->asArray();
        $query[] = $table->orderBy('id', 'DESC')->findAll()->asArray();
        $query[] = $table->orderBy('name')->findAll()->asArray();
        $query[] = $table->orderBy('name', 'DESC')->findAll()->asArray();
        $query[] = $table->orderBy('price', 'DESC')->findAll()->asArray();
        $query[] = $table->orderBy('category')->orderBy('name')->findAll()->asArray();
        $query[] = $table->orderBy('category')->orderBy('name', 'DESC')->findAll()->asArray();
        $query[] = $table->orderBy('category')->orderBy('name')->orderBy('number')->findAll()->asArray();

        $this->assertSame(1, reset($query[0])['id']);
        $this->assertSame(9, end($query[0])['id']);

        $this->assertSame(9, reset($query[1])['id']);
        $this->assertSame(1, end($query[1])['id']);

        $this->assertSame(6, reset($query[2])['id']);
        $this->assertSame(4, end($query[2])['id']);

        $this->assertSame(4, reset($query[3])['id']);
        $this->assertSame(6, end($query[3])['id']);

//        $this->assertSame(1, reset($query[4])['id']);
//        $this->assertSame(7, end($query[4])['id']);

        //$this->assertSame(1, reset($query[5])['id']);
        $this->assertSame(7, end($query[5])['id']);

        $this->assertSame(4, reset($query[6])['id']);
        $this->assertSame(6, end($query[6])['id']);
    }

    /**
     * @covers \Lazer\Classes\Database::where
     * @covers \Lazer\Classes\Database::orWhere
     * @covers \Lazer\Classes\Database::andWhere
     * @covers \Lazer\Classes\Database::wherePending
     */
    public function testWhere()
    {
        $table   = $this->object->table('users');
        $query   = array();
        $query[] = $table->where('id', '=', 1)->findAll();
        $query[] = $table->where('id', '!=', 4)->findAll();
        $query[] = $table->where('name', '=', 'Kriss')->findAll();
        $query[] = $table->where('name', '!=', 'Kriss')->findAll();
        $query[] = $table->where('id', '>', 2)->findAll();
        $query[] = $table->where('id', '<', 3)->findAll();
        $query[] = $table->where('id', '>=', 2)->findAll();
        $query[] = $table->where('id', '<=', 3)->findAll();
        $query[] = $table->where('id', '<=', 3)->andWhere('id', '>', 1)->findAll();
        $query[] = $table->where('id', 'IN', [1, 2])->findAll();
        $query[] = $table->where('name', '=', 'Larry')->orWhere('name', '=', 'Kriss')->findAll();
        $query[] = $table->where('name', 'LIKE', 'La%')->findAll();
        $query[] = $table->where('name', 'LIKE', '%ss')->findAll();
        $query[] = $table->where('name', 'LIKE', '%a%')->findAll();

        foreach ($query[0] as $row)
        {
            $this->assertEquals(1, $row->id);
        }

        foreach ($query[1] as $row)
        {
            $this->assertNotEquals(4, $row->id);
        }

        foreach ($query[2] as $row)
        {
            $this->assertEquals('Kriss', $row->name);
        }

        foreach ($query[3] as $row)
        {
            $this->assertNotEquals('Kriss', $row->name);
        }

        foreach ($query[4] as $row)
        {
            $this->assertGreaterThan(2, $row->id);
        }

        foreach ($query[5] as $row)
        {
            $this->assertLessThan(3, $row->id);
        }

        foreach ($query[6] as $row)
        {
            $this->assertGreaterThanOrEqual(2, $row->id);
        }

        foreach ($query[7] as $row)
        {
            $this->assertLessThanOrEqual(3, $row->id);
        }

        foreach ($query[8] as $row)
        {
            $this->assertContains($row->id, [2, 3]);
        }

        foreach ($query[9] as $row)
        {
            $this->assertContains($row->id, [1, 2]);
        }

        foreach ($query[10] as $row)
        {
            $this->assertContains($row->name, ['Larry', 'Kriss']);
        }

        foreach ($query[11] as $row)
        {
            $this->assertSame('Larry', $row->name);
        }

        foreach ($query[12] as $row)
        {
            $this->assertSame('Kriss', $row->name);
        }

        foreach ($query[13] as $row)
        {
            $this->assertContains($row->name, ['Larry', 'Paul']);
        }
    }

    /**
     * @covers \Lazer\Classes\Database::groupBy
     * @covers \Lazer\Classes\Database::groupByPending
     */
    public function testGroupBy()
    {
        $table   = $this->object->table('order');
        $query   = array();
        $query[] = $table->groupBy('category')->findAll();

        foreach ($query[0] as $category => $group)
        {
            $this->assertInternalType('string', $category);
            $this->assertInternalType('array', $group);
            foreach ($group as $row)
            {
                $this->assertInstanceOf('StdClass', $row);
            }
        }
    }

    /**
     * @covers \Lazer\Classes\Database::addFields
     */
    public function testAddFields()
    {
        $table        = $this->object->table('users');
        $fieldsBefore = $table->fields();
        $table->addFields(array('new' => 'string', 'fields' => 'integer'));
        $fieldsAfter  = $table->fields();

        $this->assertArraySubset(['id', 'name', 'email'], $fieldsBefore, true);
        $this->assertArraySubset(['id', 'name', 'email', 'new', 'fields'], $fieldsAfter, true);        
    }

    /**
     * @covers \Lazer\Classes\Database::addFields
     */
    public function testAddDoubleFieldToOrder()
    {
        $table        = $this->object->table('order');
        $fieldsBefore = $table->fields();
        $table->addFields(array('cost' => 'double'));
        $fieldsAfter  = $table->fields();

        $this->assertArraySubset(['id', 'price', 'name', 'number', 'category'], $fieldsBefore, true);
        $this->assertArraySubset(['id', 'price', 'name', 'number', 'category', 'cost'], $fieldsAfter, true);
    }

    /**
     * @covers \Lazer\Classes\Database::deleteFields
     */
    public function testDeleteFields()
    {
        $table        = $this->object->table('users');
        $fieldsBefore = $table->fields();
        $table->deleteFields(array('name'));
        $fieldsAfter  = $table->fields();

        $this->assertArraySubset(['id', 'name', 'email'], $fieldsBefore, true);
        $this->assertNotContains('name', $fieldsAfter);
    }

    /**
     * @covers \Lazer\Classes\Database::save
     * @covers \Lazer\Classes\Database::__set
     * @covers \Lazer\Classes\Database::__get
     */
    public function testSave()
    {
        $table        = $this->object->table('users');
        $table->name  = 'Greg';
        $table->email = 'greg@example.com';
        $table->save();

        $id     = $table->lastId();
        $result = $table->find($id);

        $this->assertSame($id, $result->id);
        $this->assertSame('Greg', $result->name);
        $this->assertSame('greg@example.com', $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::save
     * @covers \Lazer\Classes\Database::__set
     * @covers \Lazer\Classes\Database::__get
     */
    public function testSaveInOtherCharEncoding()
    {
        $table        = $this->object->table('users');
        $table->name  = mb_convert_encoding('áéóú', 'ISO-8859-1');
        $table->email = 'greg@example.com';
        $table->save();

        $id     = $table->lastId();
        $result = $table->find($id);

        $this->assertSame($id, $result->id);
        $this->assertSame('áéóú', $result->name);
        $this->assertSame('greg@example.com', $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::set
     * @covers \Lazer\Classes\Database::save
     * @covers \Lazer\Classes\Database::__get
     */
    public function testSetAndSave()
    {
        $table        = $this->object->table('users');
        $table->set([
            'name'  => 'Ananth',
            'email' => 'ananth@example.com'
        ]);
        $table->save();

        $id     = $table->lastId();
        $result = $table->find($id);

        $this->assertSame($id, $result->id);
        $this->assertSame('Ananth', $result->name);
        $this->assertSame('ananth@example.com', $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::set
     * @covers \Lazer\Classes\Database::save
     * @covers \Lazer\Classes\Database::__get
     */
    public function testSetAndSaveInOtherCharEncoding()
    {
        $table        = $this->object->table('users');
        $table->set([
            'name'  => mb_convert_encoding('áéóú', 'ISO-8859-1'),
            'email' => 'ananth@example.com'
        ]);
        $table->save();

        $id     = $table->lastId();
        $result = $table->find($id);

        $this->assertSame($id, $result->id);
        $this->assertSame('áéóú', $result->name);
        $this->assertSame('ananth@example.com', $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::set
     * @covers \Lazer\Classes\Database::save
     * @covers \Lazer\Classes\Database::__get
     */
    public function testSetAndSaveNullFieldData()
    {
        $table        = $this->object->table('users');
        $table->set([
            'name'  => mb_convert_encoding('áéóú', 'ISO-8859-1'),
            'email' => null
        ]);
        $table->save();

        $id     = $table->lastId();
        $result = $table->find($id);

        $this->assertSame($id, $result->id);
        $this->assertSame('áéóú', $result->name);
        $this->assertSame(null, $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::set
     * @covers \Lazer\Classes\Database::save
     * @covers \Lazer\Classes\Database::__get
     */
    public function testSetAndSaveRightEncoding()
    {
        $table        = $this->object->table('users');
        $table->set([
            'name'  => 'áéóú',
            'email' => 'ananth@example.com'
        ]);
        $table->save();

        $id     = $table->lastId();
        $result = $table->find($id);

        $this->assertSame($id, $result->id);
        $this->assertSame('áéóú', $result->name);
        $this->assertSame('ananth@example.com', $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::insert
     */
    public function testInsert()
    {
        $table        = $this->object->table('users');
        $table->name  = 'Greg';
        $table->email = 'greg@example.com';
        $table->insert();

        $id         = $table->lastId();
        $user       = $table->find($id);
        $user->name = 'Gregory';
        $user->insert();

        $result = $table->find($table->lastId());
        $this->assertSame('Gregory', $result->name);
        $this->assertSame('greg@example.com', $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::save
     */
    public function testUpdate()
    {
        $table        = $this->object->table('users');
        $table->name  = 'Greg';
        $table->email = 'greg@example.com';
        $table->save();

        $id         = $table->lastId();
        $user       = $table->find($id);
        $user->name = 'Gregory';
        $user->save();

        $result = $table->find($table->lastId());
        $this->assertSame('Gregory', $result->name);
        $this->assertSame('greg@example.com', $result->email);
    }

    /**
     * @covers \Lazer\Classes\Database::delete
     */
    public function testDelete()
    {
        $table = $this->object->table('order');
        $table->find(1)->delete();
        $this->assertSame(0, $table->where('id', '=', 1)->findAll()->count());

        $table->where('category', '=', 'b')->delete();
        $this->assertSame(0, $table->where('category', '=', 'b')->findAll()->count());

        $table->delete();
        $this->assertSame(0, $table->findAll()->count());
    }

    /**
     * @covers \Lazer\Classes\Database::getField
     * @covers \Lazer\Classes\Database::setField
     */
    public function testSetAndGet()
    {
        $table = $this->object->table('users');
        $table->setField('name', 'Johń');
        $this->assertEquals('Johń', $table->getField('name'));
    }

    /**
     * @covers \Lazer\Classes\Database::__get
     * @expectedException \Lazer\Classes\LazerException
     * @expectedExceptionMessage There is no data
     */
    public function testMagicGet()
    {
        $table = $this->object->table('users');
        $table->someField;
    }
    /**
     * @covers \Lazer\Classes\Database::getField
     * @expectedException \Lazer\Classes\LazerException
     * @expectedExceptionMessage There is no data
     */
    public function testGet()
    {
        $table = $this->object->table('users');
        $table->getField('someField');
    }

    /**
     * @covers \Lazer\Classes\Database::__isset
     */
    public function testMagicIsset()
    {
        $table = $this->object->table('users')->find(1);
        $this->assertTrue(isset($table->name));
        $this->assertFalse(isset($table->someField));
    }

    /**
     * @covers \Lazer\Classes\Database::issetField
     */
    public function testIsset()
    {
        $table = $this->object->table('users')->find(1);
        $this->assertTrue($table->issetField('name'));
        $this->assertFalse($table->issetField('someField'));
    }

}
