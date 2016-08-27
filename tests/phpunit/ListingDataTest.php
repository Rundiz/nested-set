<?php


namespace Rundiz\NestedSet\Tests;

class ListingDataTest extends \PHPUnit_Framework_TestCase
{


    protected $db_config;

    /**
     * @var \Rundiz\NestedSet\NestedSet
     */
    protected $NestedSet;


    protected function setUp()
    {
        $this->db_config = require dirname(__DIR__) . '/via-http/db-config.php';
        $this->NestedSet = new \Rundiz\NestedSet\Tests\NestedSetExtended(['pdoconfig' => $this->db_config, 'tablename' => $this->db_config['tablename']]);
    }// setUp


    protected function tearDown()
    {
        $this->db_config = null;
        $this->NestedSet = null;
    }// tearDown


    public function testListTaxonomy()
    {
        $options = [];
        $options['unlimited'] = true;
        $list_txn = $this->NestedSet->listTaxonomy($options);
        unset($options);

        // assert
        $this->assertArrayHasKey('total', $list_txn);
        $this->assertArrayHasKey('items', $list_txn);
        $this->assertEquals(20, $list_txn['total']);
        $this->assertCount(3, $list_txn['items']);// due to this is nested list (tree list), it is not flatten list then it will be count only root items which there are just 3. (Root 1, Root 2, Root 3.)
    }// testListTaxonomy


    public function testListTaxonomyFlatten()
    {
        $options = [];
        $options['unlimited'] = true;
        $list_txn = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);

        // assert
        $this->assertArrayHasKey('total', $list_txn);
        $this->assertArrayHasKey('items', $list_txn);
        $this->assertEquals(20, $list_txn['total']);
        $this->assertCount(20, $list_txn['items']);// due to this is flatten list, it will be count all items that were fetched which there are 20 items.
    }// testListTaxonomyFlatten


}
