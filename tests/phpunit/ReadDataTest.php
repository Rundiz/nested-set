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


    /**
     * Test get selected item (or start from selected item but skip it) and look up until root.
     *
     * @return void
     */
    public function testGetTaxonomyWithParents()
    {
        // tests on `test_taxonomy` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy';
        // test filter taxonomy id option.
        $options = [];
        $options['filter_taxonomy_id'] = 13;
        $result = $this->NestedSet->getTaxonomyWithParents($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result);
        $this->assertCount(4, $result);
        $this->assertArraySubset(['Root 2', '2.1', '2.1.1', '2.1.1.2'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);

        // test filter taxonomy id and skip current (selected) item options.
        $options = [];
        $options['filter_taxonomy_id'] = 13;
        $options['skipCurrent'] = true;
        $result = $this->NestedSet->getTaxonomyWithParents($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result);
        $this->assertCount(3, $result);
        $this->assertArraySubset(['Root 2', '2.1', '2.1.1'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);

        // test on search option.
        $options = [];
        $options['search']['columns'] = ['name'];
        $options['search']['searchValue'] = '3.2';
        $result = $this->NestedSet->getTaxonomyWithParents($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result);
        $this->assertCount(5, $result);
        $this->assertArraySubset(['Root 3', '3.2', '3.2.1', '3.2.2'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);

        // tests on `test_taxonomy2` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        // test filter taxonomy id option.
        $options = [];
        $options['filter_taxonomy_id'] = 13;
        $result = $this->NestedSet->getTaxonomyWithParents($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result, 't_name');
        $this->assertCount(4, $result);
        $this->assertArraySubset(['Root 2', '2.1', '2.1.1', '2.1.1.2'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);

        // test where option.
        $options = [];
        $options['where']['whereString'] = '`node`.`t_status` = :t_status AND `node`.`t_type` = :t_type';
        $options['where']['whereValues'] = [':t_status' => 0, ':t_type' => 'category'];
        $result = $this->NestedSet->getTaxonomyWithParents($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result, 't_name');
        $this->assertCount(9, $result);
        $this->assertArraySubset(['Root 2', '2.1', '2.1.1', '2.1.1.3', '2.3', '2.4', 'Root 3', '3.2', '3.2.3'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);
    }// testGetTaxonomyWithParents


    /**
     * Test get selected item and retrieve its children.
     *
     * @return void
     */
    public function testGetTaxonomyWithChildren()
    {
        // tests on `test_taxonomy` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy';
        // test filter taxonomy id option.
        $options = [];
        $options['filter_taxonomy_id'] = 3;
        $result = $this->NestedSet->getTaxonomyWithChildren($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result);
        $this->assertCount(7, $result);
        $this->assertArraySubset(['Root 3', '3.1', '3.2', '3.2.1', '3.2.2', '3.2.3', '3.3'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);

        // test filter taxonomy id with options.
        $options = [];
        $options['filter_taxonomy_id'] = 16;
        $options['unlimited'] = true;
        $result = $this->NestedSet->getTaxonomyWithChildren($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result);
        $this->assertCount(4, $result);
        $this->assertArraySubset(['3.2', '3.2.1', '3.2.2', '3.2.3'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);

        // tests on `test_taxonomy2` table. ----------------------------------------------
        $this->NestedSet->tableName = 'test_taxonomy2';
        $this->NestedSet->idColumnName = 'tid';
        $this->NestedSet->leftColumnName = 't_left';
        $this->NestedSet->rightColumnName = 't_right';
        $this->NestedSet->levelColumnName = 't_level';
        $this->NestedSet->positionColumnName = 't_position';
        // test filter taxonomy id option.
        $options = [];
        $options['filter_taxonomy_id'] = 4;
        $options['where'] = [
            'whereString' => '`child`.`t_type` = :t_type',
            'whereValues' => [':t_type' => 'category'],
        ];
        $result = $this->NestedSet->getTaxonomyWithChildren($options);
        unset($options);
        $resultNames = $this->getNamesAsArray($result, 't_name');
        $this->assertCount(7, $result);
        $this->assertArraySubset(['2.1', '2.1.1', '2.1.1.1', '2.1.1.2', '2.1.1.3', '2.1.2', '2.1.3'], $resultNames);
        $this->assertEquals(count($result), count($resultNames));
        unset($result, $resultNames);
    }// testGetTaxonomyWithChildren


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
