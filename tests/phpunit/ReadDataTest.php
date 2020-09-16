<?php
/**
 * Test getting data for a single row. (Data with its parent or its children).
 */


namespace Rundiz\NestedSet\Tests;


class ReadDataTest extends \PHPUnit\Framework\TestCase
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


    public function testGetParents()
    {
        // @todo working on this.
        $this->assertTrue(true);
    }// testGetParents

    
}
