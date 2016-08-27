<?php


namespace Rundiz\NestedSet\Tests;

class DeleteDataTest extends \PHPUnit_Framework_TestCase
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

        // reset db -----------------------------------------------------------------------------
        if (!file_exists(dirname(__DIR__) . '/via-http/taxonomy_unbuild_data.sql')) {
            throw new \Exception('demo sql file could not be found. (taxonomy_unbuild_data.sql)');
        }
        $sql = file_get_contents(dirname(__DIR__) . '/via-http/taxonomy_unbuild_data.sql');
        $sql = str_replace('`taxonomy`', '`' . $this->db_config['tablename'] . '`', $sql);
        $this->NestedSet->Database->PDO->query('DROP TABLE IF EXISTS `' . $this->db_config['tablename'] . '`');
        $this->NestedSet->Database->PDO->exec($sql);
        unset($sql);
        $this->NestedSet->rebuild();
        // end reset db ------------------------------------------------------------------------
    }// setUp


    protected function tearDown()
    {
        $this->db_config = null;
        $this->NestedSet = null;
    }// tearDown


    public function testDeleteWithChildren()
    {
        $list_txn = $this->NestedSet->listTaxonomyFlatten(['unlimited' => true]);
        // delete
        $this->NestedSet->deleteWithChildren(16);
        $this->NestedSet->rebuild();
        $list_txn_after_delete = $this->NestedSet->listTaxonomyFlatten(['unlimited' => true]);

        // assert
        $this->assertCount(20, $list_txn['items']);
        $this->assertCount(16, $list_txn_after_delete['items']);
    }// testDeleteWithChildren


    public function testDeletePullUpChildren()
    {
        $list_txn = $this->NestedSet->listTaxonomyFlatten(['unlimited' => true]);
        // delete
        $this->NestedSet->deletePullUpChildren(9);
        $this->NestedSet->rebuild();
        $list_txn_after_delete = $this->NestedSet->listTaxonomyFlatten(['unlimited' => true]);

        // assert
        $this->assertCount(20, $list_txn['items']);
        $this->assertCount(19, $list_txn_after_delete['items']);
    }// testDeletePullUpChildren


    public static function tearDownAfterClass()
    {
        $ThisClass = new static();

        $ThisClass->db_config = require dirname(__DIR__) . '/via-http/db-config.php';
        $ThisClass->NestedSet = new \Rundiz\NestedSet\Tests\NestedSetExtended(['pdoconfig' => $ThisClass->db_config, 'tablename' => $ThisClass->db_config['tablename']]);

        // reset db -----------------------------------------------------------------------------
        if (!file_exists(dirname(__DIR__) . '/via-http/taxonomy_unbuild_data.sql')) {
            throw new \Exception('demo sql file could not be found. (taxonomy_unbuild_data.sql)');
        }
        $sql = file_get_contents(dirname(__DIR__) . '/via-http/taxonomy_unbuild_data.sql');
        $sql = str_replace('`taxonomy`', '`' . $ThisClass->db_config['tablename'] . '`', $sql);
        $ThisClass->NestedSet->Database->PDO->query('DROP TABLE IF EXISTS `' . $ThisClass->db_config['tablename'] . '`');
        $ThisClass->NestedSet->Database->PDO->exec($sql);
        unset($sql);
        $ThisClass->NestedSet->rebuild();
        // end reset db ------------------------------------------------------------------------
    }// tearDownAfterClass


}
