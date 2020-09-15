<?php
/**
 * Test create or insert data process.
 */


namespace Rundiz\NestedSet\Tests;


class CreateDataTest extends \PHPUnit\Framework\TestCase
{


    /**
     * @var \PDO
     */
    protected $PDO;


    /**
     * @var \Rundiz\NestedSet\Tests\NestedSetExtends
     */
    protected $NestedSet;


    public function setUp()
    {
        $this->PDO = require dirname(__DIR__) . '/common/pdo-connect.php';
        $this->NestedSet = new NestedSetExtends($this->PDO);
    }


    public function tearDown()
    {
        $this->PDO = null;
        $this->NestedSet = null;
    }

    
    public function testImportDemoData()
    {
        if (!file_exists(dirname(__DIR__) . '/common/demo-data.sql')) {
            throw new \Exception('demo sql file could not be found. (common/demo-data.sql)');
        }

        // empty tables.
        $sql = 'TRUNCATE TABLE `test_taxonomy`';
        $this->PDO->exec($sql);
        $sql = 'TRUNCATE TABLE `test_taxonomy2`';
        $this->PDO->exec($sql);
        unset($sql);

        $sqlFileContents = file_get_contents(dirname(__DIR__) . '/common/demo-data.sql');
        $expSql = explode(';', $sqlFileContents);
        unset($sqlFileContents);
        $this->assertTrue(is_array($expSql));
        $this->assertTrue(!empty($expSql));
        if (is_array($expSql)) {
            foreach ($expSql as $sql) {
                if (!empty($sql)) {
                    $result = $this->PDO->exec($sql);
                    $this->assertTrue(is_int($result));
                }
            }
            unset($sql);
        }
        unset($expSql);
    }// testImportDemoData


    /**
     * @depends testImportDemoData
     */
    public function testGetTreeWithChildren()
    {
        $this->NestedSet->tableName = 'test_taxonomy';
        $result = $this->NestedSet->getTreeWithChildren();
        $this->assertTrue(is_array($result));
        $this->assertCount(21, $result);

        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        $result = $this->NestedSet->getTreeWithChildren();
        $this->assertTrue(is_array($result));
        $this->assertCount(33, $result);
        
        $result = $this->NestedSet->getTreeWithChildren([
            'whereString' => 't_type = :t_type',
            'whereValues' => [':t_type' => 'category'],
        ]);
        $this->assertTrue(is_array($result));
        $this->assertCount(21, $result);
        
        $result = $this->NestedSet->getTreeWithChildren([
            'whereString' => '(t_type = :t_type AND t_status = :t_status)',
            'whereValues' => [':t_type' => 'category', ':t_status' => 1],
        ]);
        $this->assertTrue(is_array($result));
        $this->assertCount(17, $result);
    }// testGetTreeWithChildren


    public function testGetTreeRebuildChildren()
    {
        $root = new \stdClass();
        $root->{$this->NestedSet->idColumnName} = 0;
        $root->children = [];
        $array = [$root];
        unset($root);
        // dummy data. ----------------------------
        $array[1] = (object) [
            'id' => 1,
            'parent_id' => 0,
            'name' => 'Root 1',
            'level' => 1,
            'children' => [],
        ];
        $array[2] = (object) [
            'id' => 2,
            'parent_id' => 0,
            'name' => 'Root 2',
            'level' => 1,
            'children' => [],
        ];
        $array[4] = (object) [
            'id' => 4,
            'parent_id' => 2,
            'name' => '2.1',
            'level' => 2,
            'children' => [],
        ];
        $array[6] = (object) [
            'id' => 6,
            'parent_id' => 4,
            'name' => '2.1.1',
            'level' => 3,
            'children' => [],
        ];
        // end dummy data. ------------------------

        $array = $this->NestedSet->getTreeRebuildChildren($array);

        $assert = [
            0 => (object) [
                'id' => 0,
                'children' => [
                    1 => 1,
                    2 => 2,
                ],
            ],
            1 => (object) [
                'id' => 1,
                'parent_id' => 0,
                'name' => 'Root 1',
                'level' => 1,
                'children' => [],
            ],
            2 => (object) [
                'id' => 2,
                'parent_id' => 0,
                'name' => 'Root 2',
                'level' => 1,
                'children' => [
                    4 => 4,
                ],
            ],
            4 => (object) [
                'id' => 4,
                'parent_id' => 2,
                'name' => '2.1',
                'level' => 2,
                'children' => [
                    6 => 6,
                ],
            ],
            6 => (object) [
                'id' => 6,
                'parent_id' => 4,
                'name' => '2.1.1',
                'level' => 3,
                'children' => [],
            ],
        ];
        $this->assertArraySubset($assert, $array);
    }// testGetTreeRebuildChildren


    /**
     * @depends testImportDemoData
     */
    public function testRebuildGenerateTreeData()
    {
        $root = new \stdClass();
        $root->{$this->NestedSet->idColumnName} = 0;
        $root->children = [];
        $array = [$root];
        unset($root);
        // dummy data. ----------------------------
        $array[1] = (object) [
            'id' => 1,
            'parent_id' => 0,
            'name' => 'Root 1',
            'level' => 1,
            'children' => [],
        ];
        $array[2] = (object) [
            'id' => 2,
            'parent_id' => 0,
            'name' => 'Root 2',
            'level' => 1,
            'children' => [],
        ];
        $array[3] = (object) [
            'id' => 3,
            'parent_id' => 0,
            'name' => 'Root 3',
            'level' => 1,
            'children' => [],
        ];
        $array[4] = (object) [
            'id' => 4,
            'parent_id' => 2,
            'name' => '2.1',
            'level' => 2,
            'children' => [],
        ];
        $array[5] = (object) [
            'id' => 5,
            'parent_id' => 2,
            'name' => '2.2',
            'level' => 2,
            'children' => [],
        ];
        $array[6] = (object) [
            'id' => 6,
            'parent_id' => 4,
            'name' => '2.1.1',
            'level' => 3,
            'children' => [],
        ];
        $array[7] = (object) [
            'id' => 7,
            'parent_id' => 4,
            'name' => '2.1.2',
            'level' => 3,
            'children' => [],
        ];
        $array[8] = (object) [
            'id' => 8,
            'parent_id' => 4,
            'name' => '2.1.3',
            'level' => 3,
            'children' => [],
        ];
        $array = $this->NestedSet->getTreeRebuildChildren($array);
        // end dummy data. ------------------------

        $n = 0;
        $level = 0;
        $this->NestedSet->rebuildGenerateTreeData($array, 0, 0, $n);

        $assert = [
            0 => (object) [
                'id' => 0,
                'children' =>
                [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                ],
                'level' => 0,
                'left' => 0,
                'right' => 17,
            ],
            1 => (object) [
                'id' => 1,
                'parent_id' => 0,
                'name' => 'Root 1',
                'level' => 1,
                'children' =>
                [],
                'left' => 1,
                'right' => 2,
            ],
            2 => (object) [
                'id' => 2,
                'parent_id' => 0,
                'name' => 'Root 2',
                'level' => 1,
                'children' =>
                [
                    4 => 4,
                    5 => 5,
                ],
                'left' => 3,
                'right' => 14,
            ],
            3 => (object) [
                'id' => 3,
                'parent_id' => 0,
                'name' => 'Root 3',
                'level' => 1,
                'children' =>
                [],
                'left' => 15,
                'right' => 16,
            ],
            4 => (object) [
                'id' => 4,
                'parent_id' => 2,
                'name' => '2.1',
                'level' => 2,
                'children' =>
                [
                    6 => 6,
                    7 => 7,
                    8 => 8,
                ],
                'left' => 4,
                'right' => 11,
            ],
            5 => (object) [
                'id' => 5,
                'parent_id' => 2,
                'name' => '2.2',
                'level' => 2,
                'children' =>
                [],
                'left' => 12,
                'right' => 13,
            ],
            6 => (object) [
                'id' => 6,
                'parent_id' => 4,
                'name' => '2.1.1',
                'level' => 3,
                'children' =>
                [],
                'left' => 5,
                'right' => 6,
            ],
            7 => (object) [
                'id' => 7,
                'parent_id' => 4,
                'name' => '2.1.2',
                'level' => 3,
                'children' =>
                [],
                'left' => 7,
                'right' => 8,
            ],
            8 => (object) [
                'id' => 8,
                'parent_id' => 4,
                'name' => '2.1.3',
                'level' => 3,
                'children' =>
                [],
                'left' => 9,
                'right' => 10,
            ],
        ];
        $this->assertArraySubset($assert, $array);
    }// testRebuildGenerateTreeData


    public function testRebuild()
    {
        $this->NestedSet->tableName = 'test_taxonomy';
        $this->NestedSet->rebuild();
        // get the result of 3
        $sql = 'SELECT * FROM `' . $this->NestedSet->tableName . '` WHERE `' . $this->NestedSet->idColumnName . '` = :id';
        $Sth = $this->PDO->prepare($sql);
        $Sth->bindValue(':id', 3, \PDO::PARAM_INT);
        $Sth->execute();
        $row = $Sth->fetch();
        $Sth->closeCursor();
        unset($sql, $Sth);
        // assert value must be matched.
        $this->assertEquals(40, $row->{$this->NestedSet->rightColumnName});
        $this->assertEquals(1, $row->{$this->NestedSet->levelColumnName});

        // get the result of 10
        $sql = 'SELECT * FROM `' . $this->NestedSet->tableName . '` WHERE `' . $this->NestedSet->idColumnName . '` = :id';
        $Sth = $this->PDO->prepare($sql);
        $Sth->bindValue(':id', 10, \PDO::PARAM_INT);
        $Sth->execute();
        $row = $Sth->fetch();
        $Sth->closeCursor();
        unset($sql, $Sth);
        // assert value must be matched.
        $this->assertEquals(13, $row->{$this->NestedSet->leftColumnName});
        $this->assertEquals(14, $row->{$this->NestedSet->rightColumnName});
        $this->assertEquals(3, $row->{$this->NestedSet->levelColumnName});

        // test on `test_taxonomy2` table. ------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        // rebuild where t_type = category.
        $this->NestedSet->rebuild([
            'whereString' => 't_type = :t_type',
            'whereValues' => [':t_type' => 'category'],
        ]);
        // get the result of 3
        $sql = 'SELECT * FROM `' . $this->NestedSet->tableName . '` WHERE `' . $this->NestedSet->idColumnName . '` = :id';
        $Sth = $this->PDO->prepare($sql);
        $Sth->bindValue(':id', 3, \PDO::PARAM_INT);
        $Sth->execute();
        $row = $Sth->fetch();
        $Sth->closeCursor();
        unset($sql, $Sth);
        // assert value must be matched.
        $this->assertEquals(40, $row->{$this->NestedSet->rightColumnName});
        $this->assertEquals(1, $row->{$this->NestedSet->levelColumnName});

        // get the result of 10
        $sql = 'SELECT * FROM `' . $this->NestedSet->tableName . '` WHERE `' . $this->NestedSet->idColumnName . '` = :id';
        $Sth = $this->PDO->prepare($sql);
        $Sth->bindValue(':id', 10, \PDO::PARAM_INT);
        $Sth->execute();
        $row = $Sth->fetch();
        $Sth->closeCursor();
        unset($sql, $Sth);
        // assert value must be matched.
        $this->assertEquals(13, $row->{$this->NestedSet->leftColumnName});
        $this->assertEquals(14, $row->{$this->NestedSet->rightColumnName});
        $this->assertEquals(3, $row->{$this->NestedSet->levelColumnName});

        // get the result of 29 (t_type = product_category and it did not yet rebuilt).
        $sql = 'SELECT * FROM `' . $this->NestedSet->tableName . '` WHERE `' . $this->NestedSet->idColumnName . '` = :id';
        $Sth = $this->PDO->prepare($sql);
        $Sth->bindValue(':id', 29, \PDO::PARAM_INT);
        $Sth->execute();
        $row = $Sth->fetch();
        $Sth->closeCursor();
        unset($sql, $Sth);
        // assert value must be matched.
        $this->assertEquals(0, $row->{$this->NestedSet->leftColumnName});
        $this->assertEquals(0, $row->{$this->NestedSet->rightColumnName});
        $this->assertEquals(0, $row->{$this->NestedSet->levelColumnName});
    }// testRebuild


}