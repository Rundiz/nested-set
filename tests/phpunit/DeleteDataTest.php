<?php
/**
 * Test the processes that should work on create or insert the data.
 */


namespace Rundiz\NestedSet\Tests;


class DeleteDataTest extends \PHPUnit\Framework\TestCase
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

        if (!file_exists(dirname(__DIR__) . '/common/demo-data.sql')) {
            throw new \Exception('demo sql file could not be found. (common/demo-data.sql)');
        }

        // empty tables.
        $sql = 'TRUNCATE TABLE `test_taxonomy`';
        $this->PDO->exec($sql);
        $sql = 'TRUNCATE TABLE `test_taxonomy2`';
        $this->PDO->exec($sql);
        unset($sql);

        $sqlFileContents = file_get_contents(dirname(__DIR__) . '/common/demo-data.sql');
        $expSql = explode(';', $sqlFileContents);
        unset($sqlFileContents);
        if (is_array($expSql)) {
            foreach ($expSql as $sql) {
                if (!empty($sql)) {
                    $this->PDO->exec($sql);
                }
            }
            unset($sql);
        }
        unset($expSql);

        // build the left, right, data.
        $this->NestedSet->tableName = 'test_taxonomy';
        $this->NestedSet->rebuild();
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        // rebuild where t_type = category.
        $this->NestedSet->rebuild([
            'whereString' => 't_type = :t_type',
            'whereValues' => [':t_type' => 'category'],
        ]);
        $this->NestedSet->restoreColumnsName();
    }// setUp


    public function tearDown()
    {
        $this->PDO = null;
        $this->NestedSet = null;
    }


    /**
     * Test delete selected item with its children.
     *
     * @return void
     */
    public function testDeleteWithChildren()
    {$this->assertTrue(true);
        // tests on `test_taxonomy` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy';
        // test to make sure that the data has been built correctly.
        $options['filter_taxonomy_id'] = 16;
        $options['unlimited'] = true;
        $result = $this->NestedSet->getTaxonomyWithChildren($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result);
        $this->assertCount(4, $result);
        $this->assertArraySubset(['3.2', '3.2.1', '3.2.2', '3.2.3'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);

        $options = [];
        $options['unlimited'] =true;
        $resultBeforeDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $deleteResult = $this->NestedSet->deleteWithChildren(16);
        $this->NestedSet->rebuild();
        $resultAfterDelete = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);
        $this->assertCount(20, $resultBeforeDelete['items']);
        $this->assertSame(4, $deleteResult);
        $this->assertCount(16, $resultAfterDelete['items']);

        // tests on `test_taxonomy2` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        
        // test delete with where condition
        $filter_taxonomy_id = 16;
        $options = [];
        $options['unlimited'] =true;
        $resultBeforeDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $getWithChildrenOptions = [];
        $getWithChildrenOptions['filter_taxonomy_id'] = $filter_taxonomy_id;
        $getWithChildrenOptions['where'] = [
            'whereString' => '`parent`.`t_type` = :t_type',
            'whereValues' => [':t_type' => 'category'],
        ];
        $resultTargetBeforeDelete = $this->getNamesAsArray(
            $this->NestedSet->getTaxonomyWithChildren($getWithChildrenOptions), 
            't_name'
        );
        $deleteResult = $this->NestedSet->deleteWithChildren(
            $filter_taxonomy_id, 
            [
                'whereString' => '`parent`.`t_type` = :t_type', 
                'whereValues' => [':t_type' => 'category'],
            ]
        );
        $this->NestedSet->rebuild();
        $resultAfterDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $resultTargetAfterDelete = $this->NestedSet->getTaxonomyWithChildren($getWithChildrenOptions);
        unset($getWithChildrenOptions, $options);
        $this->assertArraySubset(['3.2', '3.2.1', '3.2.2', '3.2.3'], $resultTargetBeforeDelete);
        $this->assertCount(32, $resultBeforeDelete['items']);
        $this->assertSame(4, $deleteResult);
        $this->assertNull($resultTargetAfterDelete);
        $this->assertCount(28, $resultAfterDelete['items']);

        // test with incorrect where condition
        $filter_taxonomy_id = 28;
        $options = [];
        $options['unlimited'] =true;
        $options['where'] = [
            'whereString' => '`parent`.`t_type` = :t_type',
            'whereValues' => [':t_type' => 'product-category'],
        ];
        $resultBeforeDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $getWithChildrenOptions = [];
        $getWithChildrenOptions['filter_taxonomy_id'] = $filter_taxonomy_id;
        $getWithChildrenOptions['where'] = [
            'whereString' => '`parent`.`t_type` = :t_type',
            'whereValues' => [':t_type' => 'category'],// incorrect, the id 28 should be product-category type.
        ];
        $resultTargetBeforeDelete = $this->NestedSet->getTaxonomyWithChildren($getWithChildrenOptions);
        $deleteResult = $this->NestedSet->deleteWithChildren(
            $filter_taxonomy_id, 
            [
                'whereString' => '`parent`.`t_type` = :t_type', 
                'whereValues' => [':t_type' => 'category'],
            ]
        );
        $this->NestedSet->rebuild();
        $resultAfterDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $resultTargetAfterDelete = $this->NestedSet->getTaxonomyWithChildren($getWithChildrenOptions);
        unset($getWithChildrenOptions, $options);
        $this->assertNull($resultTargetBeforeDelete);
        $this->assertCount(12, $resultBeforeDelete['items']);
        $this->assertFalse($deleteResult);// delete nothing due to incorrect where condition.
        $this->assertNull($resultTargetAfterDelete);
        $this->assertCount(12, $resultAfterDelete['items']);
    }// testDeleteWithChildren


    public function testDeletePullUpChildren()
    {
        // tests on `test_taxonomy` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy';
        $options = [];
        $options['unlimited'] =true;
        $resultBeforeDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $deleteResult = $this->NestedSet->deletePullUpChildren(9);
        $this->NestedSet->rebuild();
        $resultAfterDelete = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);
        $this->assertCount(20, $resultBeforeDelete['items']);
        $this->assertTrue($deleteResult);
        $this->assertCount(19, $resultAfterDelete['items']);

        // tests on `test_taxonomy2` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        // test the same as first table.
        $options = [];
        $options['unlimited'] =true;
        $options['where'] = [
            'whereString' => '`parent`.`t_type` = :t_type',
            'whereValues' => [':t_type' => 'category'],
        ];
        $resultBeforeDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $deleteResult = $this->NestedSet->deletePullUpChildren(9);
        $this->NestedSet->rebuild();
        $resultAfterDelete = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);
        $this->assertCount(20, $resultBeforeDelete['items']);
        $this->assertTrue($deleteResult);
        $this->assertCount(19, $resultAfterDelete['items']);

        // test delete on product-category
        $options = [];
        $options['unlimited'] =true;
        $options['where'] = [
            'whereString' => '`parent`.`t_type` = :t_type',
            'whereValues' => [':t_type' => 'product-category'],
        ];
        $resultBeforeDelete = $this->NestedSet->listTaxonomyFlatten($options);
        $deleteResult = $this->NestedSet->deletePullUpChildren(28);// delete desktop (28). parent of desktop is computer (22).
        $this->NestedSet->rebuild();
        $resultAfterDelete = $this->NestedSet->listTaxonomyFlatten($options);
        unset($options);
        $this->assertCount(12, $resultBeforeDelete['items']);
        $this->assertTrue($deleteResult);
        $this->assertCount(11, $resultAfterDelete['items']);
        // test by get some child of deleted item.
        $sql = 'SELECT * FROM `' . $this->NestedSet->tableName . '` WHERE `' . $this->NestedSet->idColumnName . '` = :tid';
        $Sth = $this->PDO->prepare($sql);
        $Sth->bindValue(':tid', 30);// get dell (30)
        $Sth->execute();
        $row = $Sth->fetch();
        $Sth->closeCursor();
        $this->assertEquals(22, $row->{$this->NestedSet->parentIdColumnName});// test that dell's (30) parent is computer (22) now. before delete desktop (28), dell's (30) parent is desktop (28).
    }// testDeletePullUpChildren


    /**
     * Rebuild result array by get the names as 2D array.
     *
     * @param array $result
     * @param string $name
     * @return array
     */
    private function getNamesAsArray(array $result, string $nameColumn = 'name'): array
    {
        $resultNames = [];
        if (is_array($result)) {
            foreach ($result as $row) {
                $resultNames[] = $row->{$nameColumn};
            }// endforeach;
            unset($row);
        }
        return $resultNames;
    }// getNamesAsArray


}