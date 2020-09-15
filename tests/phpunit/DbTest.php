<?php


namespace Rundiz\NestedSet\Tests;

class DBTest extends \PHPUnit\Framework\TestCase
{


    public function testDbConfig()
    {
        $PDO = require dirname(__DIR__) . '/common/pdo-connect.php';

        $this->assertTrue(is_object($PDO));
        $this->assertTrue($PDO instanceof \PDO);

        return $PDO;
    }// testDbConfig


    /**
     * @depends testDbConfig
     */
    public function testTablesInstalled(\PDO $PDO)
    {
        $sql = 'SHOW TABLES LIKE \'test_taxonomy\'';
        $Sth = $PDO->prepare($sql);
        $Sth->execute();
        $result = $Sth->fetchAll();
        $Sth->closeCursor();
        $this->assertNotEmpty($result);

        $sql = 'SHOW TABLES LIKE \'test_taxonomy2\'';
        $Sth = $PDO->prepare($sql);
        $Sth->execute();
        $result = $Sth->fetchAll();
        $Sth->closeCursor();
        $this->assertNotEmpty($result);
    }// testTablesInstalled


}
