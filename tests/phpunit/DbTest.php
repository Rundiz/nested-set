<?php


namespace Rundiz\NestedSet\Tests;


use PHPUnit\Framework\Attributes\Depends;


class DBTest extends \PHPUnit\Framework\TestCase
{


    /**
     * @var \PDO
     */
    protected $PDO;


    public function setup(): void
    {
        $this->PDO = require dirname(__DIR__) . '/common/pdo-connect.php';
    }// setup


    public function teardown(): void
    {
        $this->PDO = null;
    }


    /**
     * Test that DB configurations are configured correctly.
     */
    public function testDbConfig()
    {
        $this->assertTrue(is_object($this->PDO));
        $this->assertTrue($this->PDO instanceof \PDO);
    }// testDbConfig


    /**
     * Test that the tables (DB structure) are created or imported correctly.
     */
    #[Depends('testDbConfig')]
    public function testTablesInstalled()
    {
        $sql = 'SHOW TABLES LIKE \'test_taxonomy\'';
        $Sth = $this->PDO->prepare($sql);
        $Sth->execute();
        $result = $Sth->fetchAll();
        $Sth->closeCursor();
        $this->assertNotEmpty($result);

        $sql = 'SHOW TABLES LIKE \'test_taxonomy2\'';
        $Sth = $this->PDO->prepare($sql);
        $Sth->execute();
        $result = $Sth->fetchAll();
        $Sth->closeCursor();
        $this->assertNotEmpty($result);
    }// testTablesInstalled


}
