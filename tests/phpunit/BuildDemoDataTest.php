<?php


namespace Rundiz\NestedSet\Tests;

class BuildDemoDataTest extends \PHPUnit\Framework\TestCase
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


    /**
     * Drop taxonomy table and then create & insert demo data.
     * 
     * @throws \Exception
     */
    public function testImportDemoData()
    {
        if (!file_exists(dirname(__DIR__) . '/via-http/taxonomy_unbuild_data.sql')) {
            throw new \Exception('demo sql file could not be found. (taxonomy_unbuild_data.sql)');
        }

        $sql = file_get_contents(dirname(__DIR__) . '/via-http/taxonomy_unbuild_data.sql');
        $sql = str_replace('`taxonomy`', '`' . $this->db_config['tablename'] . '`', $sql);

        // empty the table first.
        $this->NestedSet->Database->PDO->query('DROP TABLE IF EXISTS `' . $this->db_config['tablename'] . '`');
        $result = $this->NestedSet->Database->PDO->exec($sql);

        $this->assertTrue(is_int($result));

        unset($result, $sql);
    }// testImportDemoData


    /**
     * Test get tree with children method in nested set class.
     */
    public function testGetTreeWithChildren()
    {
        $result = $this->NestedSet->getTreeWithChildren();

        $this->assertTrue(is_array($result));
        $this->assertCount(21, $result);
    }// testGetTreeWithChildren


    /**
     * Test rebuild and get right value of id = 3 that must be 40.
     */
    public function testRebuild()
    {
        $this->NestedSet->rebuild();

        $sql = 'SELECT `' . $this->NestedSet->id_column_name . '`, `' . $this->NestedSet->right_column_name . '` FROM `' . $this->NestedSet->table_name . '` WHERE `' . $this->NestedSet->id_column_name . '` = :id';
        $stmt = $this->NestedSet->Database->PDO->prepare($sql);
        $stmt->bindValue(':id', 3, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        unset($sql, $stmt);

        $this->assertEquals(40, $row->{$this->NestedSet->right_column_name});
    }// testRebuild


}
