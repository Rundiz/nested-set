<?php


namespace Rundiz\NestedSet\Tests;

class DBTest extends \PHPUnit_Framework_TestCase
{


    protected $db_config;


    protected function setUp()
    {
        $this->db_config = require dirname(__DIR__) . '/via-http/db-config.php';
    }// setUp


    protected function tearDown()
    {
        $this->db_config = null;
    }// tearDown


    /**
     * Test db configuration
     */
    public function testDbConfig()
    {
        $NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $this->db_config, 'tablename' => $this->db_config['tablename']]);

        // assert Database class from NestedSet class (class chaining).
        $this->assertTrue(is_object($NestedSet->Database));
    }// testDbConfig


    /**
     * Test that table name configuration correctly.
     */
    public function testTableNameConfig()
    {
        $NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $this->db_config, 'tablename' => $this->db_config['tablename']]);
        $result = $NestedSet->Database->PDO->query('SELECT * FROM `' . $this->db_config['tablename'] . '`');

        // assert result not false
        $this->assertTrue($result !== false);
    }// testTableNameConfig


}
