<?php
/**
 * Test listing data (multiple items).
 */


namespace Rundiz\NestedSet\Tests;


class ListingDataTest extends \PHPUnit\Framework\TestCase
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


    public function testListTaxonomy()
    {
        // tests on `test_taxonomy` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy';
        // full result test.
        $options = [];
        $options['unlimited'] = true;
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals(20, $result['total']);
        $this->assertCount(3, $result['items']);
        // due to this is nested list (tree list), 
        // it is not flatten list then it will be count only root items which there are just 3. 
        // (Root 1, Root 2, Root 3.)

        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['filter_taxonomy_id'] = 1;
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertEquals(1, $result['total']);// with children.
        $this->assertCount(1, $result['items']);// only root items because not flatten.

        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['filter_parent_id'] = 2;
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertEquals(11, $result['total']);// with children.
        $this->assertCount(5, $result['items']);// only root items because not flatten.

        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['search'] = [
            'columns' => ['name'],
            'searchValue' => '3.',
        ];
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertEquals(6, $result['total']);// with children.
        $this->assertCount(3, $result['items']);// only root items because not flatten.

        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['taxonomy_id_in'] = [1, 5, 6, 15, 99];
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertEquals(4, $result['total']);// with children.
        $this->assertCount(4, $result['items']);// was flatten by the class functional.
        
        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['no_sort_orders'] = true;
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertEquals(20, $result['total']);// with children.
        $this->assertCount(3, $result['items']);// only root items because not flatten.

        
        // tests on `test_taxonomy2` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        // full result test.
        $options = [];
        $options['unlimited'] = true;
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals(32, $result['total']);// with children.
        $this->assertCount(6, $result['items']);// only root items because not flatten.

        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['where'] = [
            'whereString' => '`t_type` = :t_type',
            'whereValues' => [':t_type' => 'category'],
        ];
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertEquals(20, $result['total']);// with children.
        $this->assertCount(3, $result['items']);// only root items because not flatten.

        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['where'] = [
            'whereString' => '`t_type` = :t_type',
            'whereValues' => [':t_type' => 'product-category'],
        ];
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        // assert
        $this->assertEquals(12, $result['total']);// with children.
        $this->assertCount(3, $result['items']);// only root items because not flatten.
    }// testListTaxonomy


    /**
     * Sub test of `testListTaxonomy()` but expect (assert) the children that will be generated from `NestedSet->listTaxonomyBuildTreeWithChildren()`.
     * 
     * @depends testListTaxonomy
     */
    public function testListTaxonomyExpectChildren()
    {
        $this->NestedSet->tableName = 'test_taxonomy';
        $options = [];
        $options['unlimited'] = true;
        $result = $this->NestedSet->listTaxonomy($options);
        unset($options);
        $this->assertTrue(isset($result['items'][1]) && is_object($result['items'][1]));
        $this->assertObjectHasAttribute('children', $result['items'][1]);
        $this->assertEquals(2, $result['items'][1]->id);
        $this->assertTrue(isset($result['items'][1]->children[0]) && is_object($result['items'][1]->children[0]));
        $this->assertObjectHasAttribute('children', $result['items'][1]->children[0]);
        $this->assertEquals(4, $result['items'][1]->children[0]->id);
    }// testListTaxonomyExpectChildren


    public function testListTaxonomyFlatten()
    {
        // tests on `test_taxonomy` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy';
        $options = [];
        $options['unlimited'] = true;
        $list_txn = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);
        // assert
        $this->assertArrayHasKey('total', $list_txn);
        $this->assertArrayHasKey('items', $list_txn);
        $this->assertEquals(20, $list_txn['total']);
        $this->assertCount(20, $list_txn['items']);// due to this is flatten list, it will be count all items that were fetched which there are 20 items.
        
        // tests on `test_taxonomy2` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        // full result test.
        $options = [];
        $options['unlimited'] = true;
        $result = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);
        // assert
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals(32, $result['total']);// with children.
        $this->assertCount(32, $result['items']);// was flatten.

        // tests with options.
        $options = [];
        $options['unlimited'] = true;
        $options['where'] = [
            'whereString' => '`t_type` = :t_type',
            'whereValues' => [':t_type' => 'product-category'],
        ];
        $result = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);
        // assert
        $this->assertEquals(12, $result['total']);// with children.
        $this->assertCount(12, $result['items']);// was flatten.
    }// testListTaxonomyFlatten


}