<?php


namespace Rundiz\NestedSet\Tests;

class ReadDataTest extends \PHPUnit\Framework\TestCase
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


    public function testGetNewPosition()
    {
        $new_position = $this->NestedSet->getNewPosition(4);

        $this->assertEquals(4, $new_position);
    }// testGetNewPosition


    public function testGetParents()
    {
        $options = [];
        $options['taxonomy_id'] = 13;
        $test_1 = $this->NestedSet->getTaxonomyWithParents($options);
        unset($options);

        $options = [];
        $options['search']['columns'] = ['name'];
        $options['search']['search_value'] = '3.2.1';
        $options['skip_current'] = true;
        $test_2 = $this->NestedSet->getTaxonomyWithParents($options);
        unset($options);

        // assert
        $this->assertCount(4, $test_1);
        $test_1_row = $test_1[0];
        $this->assertEquals(2, $test_1_row->{$this->NestedSet->id_column_name});
        unset($test_1, $test_1_row);
        $this->assertCount(2, $test_2);
        $test_2_row = $test_2[0];
        $this->assertEquals(3, $test_2_row->{$this->NestedSet->id_column_name});
        unset($test_2, $test_2_row);
    }// testGetParents


    public function testGetChildren()
    {
        $options = [];
        $options['filter_taxonomy_id'] = 3;
        $test_1 = $this->NestedSet->getTaxonomyWithChildren($options);
        unset($options);

        $options = [];
        $options['filter_taxonomy_id'] = 9;
        $test_2 = $this->NestedSet->getTaxonomyWithChildren($options);
        unset($options);

        // assert
        $this->assertCount(7, $test_1);
        $this->assertCount(4, $test_2);

        unset($test_1, $test_2);
    }// testGetChildren


    public function testIsParentUnderMyChildren()
    {
        $this->assertTrue($this->NestedSet->isParentUnderMyChildren(9, 12));
        $this->assertTrue($this->NestedSet->isParentUnderMyChildren(9, 14));
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(9, 4));
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(9, 7));
        $this->assertFalse($this->NestedSet->isParentUnderMyChildren(9, 20));
    }// isParentUnderMyChildren


}
