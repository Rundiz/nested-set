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

        
        // @todo tests on `test_taxonomy` table. ----------------------------------------------
    }// testListTaxonomy


}