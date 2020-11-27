<?php
/**
 * Test the processes that should work on create or insert the data.
 */


namespace Rundiz\NestedSet\Tests\PHPB71;


class UpdateDataTest extends \PHPUnit\Framework\TestCase
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
        $this->PDO = require dirname(dirname(__DIR__)) . '/common/pdo-connect.php';
        $this->NestedSet = new \Rundiz\NestedSet\Tests\NestedSetExtends($this->PDO);
    }


    public function tearDown()
    {
        $this->PDO = null;
        $this->NestedSet = null;
    }


    /**
     * Test check that selected parent is same level or under its children.
     * 
     * This will be use to check before update the data.
     *
     * @return void
     */
    public function testIsParentUnderMyChildren()
    {
        // tests on `test_taxonomy` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy';
        $this->assertTrue($this->NestedSet->isParentUnderMyChildren(9, 12));
        $this->assertTrue($this->NestedSet->isParentUnderMyChildren(9, 14));
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(9, 4));
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(9, 7));
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(9, 20));

        // tests on `test_taxonomy2` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        $this->assertTrue($this->NestedSet->isParentUnderMyChildren(
            9, // 2.1.1 (9)
            12, // shouldn't under 2.1.1.1 (12)
            [
                'whereString' => '`node`.`t_type` = :t_type',
                'whereValues' => [':t_type' => 'category'],
            ]
        ));
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(
            9, // 2.1.1 (9)
            20, // is okay to be under 3.2.3 (20 - will be new parent)
            [
                'whereString' => '`node`.`t_type` = :t_type',
                'whereValues' => [':t_type' => 'category'],
            ]
        ));

        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(
            19, // 3.2.2
            16, // is under 3.2
            [
                'whereString' => '`node`.`t_type` = :t_type',
                'whereValues' => [':t_type' => 'category'],
            ]
        ));
        // test search not found because incorrect `t_type` (must be return `true`).
        $this->assertTrue($this->NestedSet->isParentUnderMyChildren(
            19, 
            16, 
            [
                'whereString' => '`node`.`t_type` = :t_type',
                'whereValues' => [':t_type' => 'product-category'],
            ]
        ));

        $this->assertTrue($this->NestedSet->isParentUnderMyChildren(
            21, // camera (21)
            25, // shouldn't under nikon (25)
            [
                'whereString' => '`node`.`t_type` = :t_type',
                'whereValues' => [':t_type' => 'product-category'],
            ]
        ));
        // test multiple level children.
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(
            30, // dell
            22, // is under desktop (28) > and desktop is under computer (22)
            [
                'whereString' => '`node`.`t_type` = :t_type',
                'whereValues' => [':t_type' => 'product-category'],
            ]
        ));
    }// testIsParentUnderMyChildren


}