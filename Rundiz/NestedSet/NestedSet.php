<?php
/**
 * @package Nested Set
 * @version 0.1
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\NestedSet;

/**
 * Nested Set class for build left, right, level data.
 *
 * @author Vee W.
 */
class NestedSet
{


    /**
     * @var integer Identity column name in the table. This column should be primary key.
     */
    public $id_column_name = 'id';

    /**
     * @var integer Parent ID that refer to the ID column.
     */
    public $parent_id_column_name = 'parent_id';

    /**
     * @var string Left column name in the table.
     */
    public $left_column_name = 'left';

    /**
     * @var string Right column name in the table.
     */
    public $right_column_name = 'right';

    /**
     * @var string Level column name in the table. The root item will be start at level 1, the sub items of the root will be increase their level.
     */
    public $level_column_name = 'level';

    /**
     * @var string Position column name in the table. The position will be start at 1 for each level, it means the different level always start at 1.
     */
    public $position_column_name = 'position';

    /**
     * @var string Table name.
     */
    public $table_name = 'taxonomy';

    /**
     * @var \Rundiz\NestedSet\Database The database class connector.
     */
    public $Database;


    /**
     * NestedSet class constructor.
     * 
     * @param array $config Available config key: [pdoconfig [dsn], [username], [password], [options]] (see more at http://php.net/manual/en/pdo.construct.php), [tablename]
     */
    public function __construct(array $config = [])
    {
        $this->Database = new Database((isset($config['pdoconfig']) ? $config['pdoconfig'] : []));

        if (isset($config['tablename'])) {
            $this->table_name = $config['tablename'];
        }
    }// __construct


    /**
     * Delete the selected taxonomy ID and pull children's parent ID to the same as selected one.<br>
     * Example: selected taxonomy ID is 4, its parent ID is 2. This method will be pull all children that has parent ID = 4 to 2 and delete the taxonomy ID 4.<br>
     * Always run <code>$NestedSet->rebuild()</code> after insert, update, delete to rebuild the correctly level, left, right data.
     * 
     * @param integer $taxonomy_id The selected taxonomy ID.
     * @return boolean Return true on success, false for otherwise.
     */
    public function deletePullUpChildren($taxonomy_id)
    {
        // get this taxonomy parent id
        $sql = 'SELECT `' . $this->id_column_name . '`, `' . $this->parent_id_column_name . '` FROM `' . $this->table_name . '`';
        $sql .= ' WHERE `' . $this->id_column_name . '` = :taxonomy_id';
        $stmt = $this->Database->PDO->prepare($sql);
        unset($sql);
        $stmt->bindValue(':taxonomy_id', $taxonomy_id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $parent_id = $row->{$this->parent_id_column_name};
        unset($row, $stmt);

        if ($parent_id == null) {
            $parent_id = 0;
        }

        // update this children first level.
        $sql = 'UPDATE `' . $this->table_name . '`';
        $sql .= ' SET `' . $this->parent_id_column_name . '` = :parent_id';
        $sql .= ' WHERE `' . $this->parent_id_column_name . '` = :taxonomy_id';
        $stmt = $this->Database->PDO->prepare($sql);
        unset($sql);
        $stmt->bindValue(':parent_id', $parent_id, \PDO::PARAM_INT);
        $stmt->bindValue(':taxonomy_id', $taxonomy_id, \PDO::PARAM_INT);
        $stmt->execute();
        unset($stmt);

        // delete the selected taxonomy ID
        $sql = 'DELETE FROM `' . $this->table_name . '` WHERE `' . $this->id_column_name . '` = :taxonomy_id';
        $stmt = $this->Database->PDO->prepare($sql);
        $stmt->bindValue(':taxonomy_id', $taxonomy_id, \PDO::PARAM_INT);
        $result = $stmt->execute();
        unset($sql, $stmt);

        return $result;
    }// deletePullUpChildren


    /**
     * Delete the selected taxonomy ID with its ALL children.<br>
     * Always run <code>$NestedSet->rebuild()</code> after insert, update, delete to rebuild the correctly level, left, right data.
     * 
     * @param integer $taxonomy_id The taxonomy ID to delete.
     * @return mixed Return number on success, return false for otherwise.
     */
    public function deleteWithChildren($taxonomy_id)
    {
        $options = [];
        $options['filter_taxonomy_id'] = $taxonomy_id;
        $options['unlimited'] = true;
        $result = $this->getTaxonomyWithChildren($options);
        $i_count = 0;
        unset($options);

        if (is_array($result)) {
            foreach ($result as $row) {
                $sql = 'DELETE FROM `' . $this->table_name . '` WHERE `' . $this->id_column_name . '` = :taxonomy_id';
                $stmt = $this->Database->PDO->prepare($sql);
                $stmt->bindValue(':taxonomy_id', $row->{$this->id_column_name}, \PDO::PARAM_INT);
                $execute = $stmt->execute();
                unset($sql, $stmt);

                if ($execute === true) {
                    $i_count++;
                }
            }// endforeach;
            unset($row);
        }

        unset($execute, $result);

        if ($i_count <= 0) {
            return false;
        } else {
            return $i_count;
        }
    }// deleteWithChildren


    /**
     * Get new position for taxonomy in the selected parent.
     * 
     * @param integer $parent_id The parent ID. If root, set this to 0.
     * @return integer Return the new position in the same parent.
     */
    public function getNewPosition($parent_id)
    {
        $sql = 'SELECT `' . $this->id_column_name . '`, `' . $this->parent_id_column_name . '`, `' . $this->position_column_name . '` FROM `' . $this->table_name . '`';
        $sql .= ' WHERE `' . $this->parent_id_column_name . '` = :parent_id';
        $sql .= ' ORDER BY `' . $this->position_column_name . '` DESC';
        $stmt = $this->Database->PDO->prepare($sql);
        $stmt->bindValue(':parent_id', $parent_id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        unset($sql, $stmt);

        if ($row != null) {
            return ($row->{$this->position_column_name} + 1);
        } else {
            unset($row);
            return 1;
        }
    }// getNewPosition


    /**
     * Get taxonomy from selected item and fetch its ALL children.<br>
     * Example: There are taxonomy tree like this. Root 1 > 1.1 > 1.1.1, Root 2, Root 3 > 3.1, Root 3 > 3.2 > 3.2.1, Root 3 > 3.2 > 3.2.2, Root 3 > 3.3<br>
     * Assume that selected item is Root 3. So, the result will be Root 3 > 3.1, Root 3 > 3.2 > 3.2.1, Root 3 > 3.2 > 3.2.2, Root 3 > 3.3<br>
     * 
     * @param array $options Available options: filter_taxonomy_id, [search [columns], [search_value]], unlimited, offset, limit
     * @return mixed Return array object of taxonomy data if found, return null if not found.
     */
    public function getTaxonomyWithChildren(array $options = [])
    {
        $options['list_flatten'] = true;
        $result = $this->listTaxonomy($options);

        if (is_array($result) && array_key_exists('items', $result) && $result['items'] != null) {
            return $result['items'];
        } else {
            return null;
        }
    }// getTaxonomyWithChildren


    /**
     * Get taxonomy from selected item and fetch its parent in a line until root item.<br>
     * Example: There are taxonomy tree like this. Root1 > 1.1 > 1.1.1 > 1.1.1.1<br>
     * Assume that selected at 1.1.1. So, the result will be Root1 > 1.1 > 1.1.1<br>
     * But if you set 'skip_current' to true the result will be Root1 > 1.1
     * 
     * @link http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/ Original source.
     * @param array $options Available options: taxonomy_id, [search [columns], [search_value]], skip_current
     * @return mixed Return array object of taxonomy data if found, return null if not found.
     */
    public function getTaxonomyWithParents(array $options = [])
    {
        $sql = 'SELECT `parent`.*';
        $sql .= ' FROM `' . $this->table_name . '` AS `node`,';
        $sql .= ' `' . $this->table_name . '` AS `parent`';
        $sql .= ' WHERE';
        $sql .= ' `node`.`' . $this->left_column_name . '` BETWEEN `parent`.`' . $this->left_column_name . '` AND `parent`.`' . $this->right_column_name . '`';
        if (isset($options['taxonomy_id'])) {
            $sql .= ' AND `node`.`' . $this->id_column_name . '` = :taxonomy_id';
        }
        if (
            isset($options['search']) && 
            is_array($options['search']) && 
            array_key_exists('columns', $options['search']) && 
            is_array($options['search']['columns']) && 
            array_key_exists('search_value', $options['search'])
        ) {
            $sql .= ' AND (';
            $array_keys = array_keys($options['search']['columns']);
            $last_array_key = array_pop($array_keys);
            foreach ($options['search']['columns'] as $key => $column) {
                $sql .= '`node`.`' . $column . '` LIKE :search';
                if ($key !== $last_array_key) {
                    $sql .= ' OR ';
                }
            }// endforeach;
            unset($array_keys, $column, $key, $last_array_key);
            $sql .= ')';
        }
        $sql .= ' ORDER BY `parent`.`' . $this->left_column_name . '`';

        $stmt = $this->Database->PDO->prepare($sql);
        if (isset($options['taxonomy_id'])) {
            $stmt->bindValue(':taxonomy_id', $options['taxonomy_id'], \PDO::PARAM_INT);
        }
        if (isset($options['search']) && is_array($options['search']) && array_key_exists('search_value', $options['search'])) {
            $stmt->bindValue(':search', '%'.$options['search']['search_value'].'%', \PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetchAll();

        if (isset($options['skip_current']) && $options['skip_current'] === true) {
            unset($result[count($result)-1]);
        }
        unset($sql, $stmt);

        if ($result !== false && $result !== null) {
            return $result;
        } else {
            return null;
        }
    }// getTaxonomyWithParents


    /**
     * Detect that is this taxonomy's parent setting to be under this taxonomy's children or not.<br>
     * For example: Root 1 > 1.1 > 1.1.1 > 1.1.1.1 > 1.1.1.1.1<br>
     * Assume that you editing 1.1.1 and its parent is 1.1. Now you change its parent to 1.1.1.1.1 which is under its children.<br>
     * The parent of 1.1.1 must be root, Root 1, 1.1 and never go under that.
     * 
     * @param integer $taxonomy_id The taxonomy ID that is chaging the parent.
     * @param integer $parent_id The selected parent ID to check.
     * @return boolean Return true if its parent is under its children (incorrect changes). Return false if its parent is NOT under its children (correct changes).
     */
    public function isParentUnderMyChildren($taxonomy_id, $parent_id)
    {
        if ($parent_id == '0') {
            // if parent is root, always return false because it is correctly!
            return false;
        }

        // check for selected parent that must not under this taxonomy.
        $taxonomy_parents = $this->getTaxonomyWithParents(['taxonomy_id' => $parent_id]);

        if (is_array($taxonomy_parents) && !empty($taxonomy_parents)) {
            foreach ($taxonomy_parents as $row) {
                if ($row->{$this->parent_id_column_name} == $taxonomy_id) {
                    unset($row, $taxonomy_parents);
                    return true;
                }
            }// endforeach;
            unset($row, $taxonomy_parents);
            return false;
        }

        unset($taxonomy_parents);
        return true;
    }// isParentUnderMyChildren


    /**
     * List taxonomy.
     * 
     * @param array $options Available options: taxonomy_id_in, filter_taxonomy_id, filter_parent_id, [search [columns], [search_value]], no_sort_orders, unlimited, offset, limit, list_flatten
     * @return array Return array with 'total' and 'items' as keys.
     */
    public function listTaxonomy(array $options = [])
    {
        // create query SQL statement ------------------------------------------------------
        $sql = 'SELECT * FROM `' . $this->table_name . '` AS `parent`';

        if (isset($options['filter_taxonomy_id']) || isset($options['filter_parent_id']) || isset($options['search'])) {
            // if there is filter or search, there must be inner join to select all of filtered children.
            $sql .= ' INNER JOIN `' . $this->table_name . '` AS `child`';
            $sql .= ' ON `child`.`' . $this->left_column_name . '` BETWEEN `parent`.`' . $this->left_column_name . '` AND `parent`.`' . $this->right_column_name . '`';
        }

        $sql .= ' WHERE 1';

        if (isset($options['taxonomy_id_in']) && is_array($options['taxonomy_id_in']) && !empty($options['taxonomy_id_in'])) {
            // Due to IN() and NOT IN() cannot using bindValue so easily.
            // read more at http://stackoverflow.com/questions/17746667/php-pdo-for-not-in-query-in-mysql
            // and http://stackoverflow.com/questions/920353/can-i-bind-an-array-to-an-in-condition
            if (is_array($options['taxonomy_id_in'])) {
                foreach ($options['taxonomy_id_in'] as $key => $each_tax_id) {
                    if (!is_numeric($each_tax_id) || $each_tax_id != intval($each_tax_id)) {
                        unset($options['taxonomy_id_in'][$key]);
                    }
                }// endforeach;
                unset($each_tax_id, $key);
                $taxonomy_id_in = implode(',', $options['taxonomy_id_in']);
            }
            $sql .= ' AND `' . $this->id_column_name . '` IN (' . $taxonomy_id_in . ')';
        }

        if (isset($options['filter_taxonomy_id'])) {
            $sql .= ' AND `parent`.`' . $this->id_column_name . '` = :filter_taxonomy_id';
        }
        if (isset($options['filter_parent_id'])) {
            $sql .= ' AND `parent`.`' . $this->parent_id_column_name . '` = :filter_parent_id';
        }

        if (
            isset($options['search']) && 
            is_array($options['search']) && 
            array_key_exists('columns', $options['search']) && 
            is_array($options['search']['columns']) && 
            array_key_exists('search_value', $options['search'])
        ) {
            $sql .= ' AND (';
            $array_keys = array_keys($options['search']['columns']);
            $last_array_key = array_pop($array_keys);
            foreach ($options['search']['columns'] as $key => $column) {
                $sql .= '`parent`.`' . $column . '` LIKE :search';
                if ($key !== $last_array_key) {
                    $sql .= ' OR ';
                }
            }// endforeach;
            unset($array_keys, $column, $key, $last_array_key);
            $sql .= ')';
        }

        // group, sort and order
        if (!isset($options['no_sort_orders']) || (isset($options['no_sort_orders']) && $options['no_sort_orders'] == false)) {
            if (isset($options['filter_taxonomy_id']) || isset($options['filter_parent_id']) || isset($options['search'])) {
                $sql .= ' GROUP BY `child`.`' . $this->id_column_name . '`';
                $order_by = '`child`.`' . $this->left_column_name . '` ASC';
            } elseif (isset($taxonomy_id_in) && $taxonomy_id_in != null) {
                $order_by = 'FIELD(`' . $this->id_column_name . '`,' . $taxonomy_id_in . ')';
            } else {
                $order_by = '`parent`.`' . $this->left_column_name . '` ASC';
            }
            if (isset($order_by)) {
                $sql .= ' ORDER BY ' . $order_by;
                unset($order_by);
            }
        }
        unset($taxonomy_id_in);
        // end create query SQL statement -------------------------------------------------

        // prepare and get 'total' count.
        $stmt = $this->Database->PDO->prepare($sql);
        $this->listTaxonomyBindValues($stmt, $options);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $output['total'] = count($result);
        unset($stmt);

        // re-create query and prepare. second step is for set limit and fetch all items.
        if (!isset($options['unlimited']) || (isset($options['unlimited']) && $options['unlimited'] == false)) {
            if (!isset($options['offset']) || (isset($options['offset']) && !is_numeric($options['offset']))) {
                $options['offset'] = 0;
            }
            if (!isset($options['limit']) || isset($options['limit']) && (!is_numeric($options['limit']) || $options['limit'] > '100')) {
                $options['limit'] = 20;
            }

            $sql .= ' LIMIT '.$options['offset'].', '.$options['limit'];

            $stmt = $this->Database->PDO->prepare($sql);
            $this->listTaxonomyBindValues($stmt, $options);
            $stmt->execute();
            $result = $stmt->fetchAll();
            unset($stmt);
        }

        unset($sql);

        // populate tree with children.
        if (
            $output['total'] > 0 && 
            is_array($result) && 
            (
                !isset($options['list_flatten']) || 
                (isset($options['list_flatten']) && $options['list_flatten'] !== true)
            )
        ) {
            $result = $this->listTaxonomyBuildTreeWithChildren($result, $options);
        }// endif; populate tree with children.

        // set 'items' result
        $output['items'] = $result;

        unset($result);
        return $output;
    }// listTaxonomy


    /**
     * Bind taxonomy values for listTaxonomy() method.
     * 
     * @param \PDOStatement $stmt PDO statement class object.
     * @param array $options Available options: taxonomy_id_in, filter_taxonomy_id, filter_parent_id, search, no_sort_orders, unlimited, offset, limit
     */
    protected function listTaxonomyBindValues(\PDOStatement $stmt, array $options = [])
    {
        if (isset($options['filter_taxonomy_id'])) {
            $stmt->bindValue(':filter_taxonomy_id', $options['filter_taxonomy_id'], \PDO::PARAM_INT);
        }
        if (isset($options['filter_parent_id'])) {
            $stmt->bindValue(':filter_parent_id', $options['filter_parent_id'], \PDO::PARAM_INT);
        }
        if (isset($options['search']) && is_array($options['search']) && array_key_exists('search_value', $options['search'])) {
            $stmt->bindValue(':search', '%'.$options['search']['search_value'].'%', \PDO::PARAM_STR);
        }
    }// listTaxonomyBindValues


    /**
     * Build tree data with children.
     * 
     * @param array $result The array item get from fetchAll() method using the PDO.
     * @param array $options Available options: taxonomy_id_in, parent_id, search, no_sort_orders, unlimited, offset, limit, list_flatten
     * @return array Return array data of formatted values.
     */
    protected function listTaxonomyBuildTreeWithChildren(array $result, array $options = [])
    {
        if (isset($options['list_flatten']) && $options['list_flatten'] === true) {
            return $result;
        }

        $items = [];
        foreach ($result as $row) {
            $items[$row->{$this->parent_id_column_name}][] = $row;
        }// endforeach;

        if (!isset($options['taxonomy_id_in'])) {
            // without taxonomy_id_in option exists, this result can format to be heirarchical.
            foreach ($result as $row) {
                if (isset($items[$row->{$this->id_column_name}])) {
                    $row->children = $items[$row->{$this->id_column_name}];
                }
            }// endforeach;

            $result = (isset($items[0]) ? $items[0] : array_shift($items));// this is important ([0]) for prevent duplicate items
        }

        unset($items, $row);
        return $result;
    }// listTaxonomyBuildTreeWithChildren


    /**
     * List taxonomy as flatten not tree.<br>
     * All parameters or arguments are same as listTaxonomy() method.
     * 
     * @param array $options Available options: taxonomy_id_in, filter_taxonomy_id, filter_parent_id, [search [columns], [search_value]], no_sort_orders, unlimited, offset, limit
     * @return array Return array with 'total' and 'items' as keys.
     */
    public function listTaxonomyFlatten(array $options = [])
    {
        $options['list_flatten'] = true;
        $result = $this->listTaxonomy($options);

        if (!is_array($result)) {
            return $result;
        } elseif (is_array($result) && (!array_key_exists('total', $result) || !array_key_exists('items', $result))) {
            return $result;
        }

        if ($result['total'] <= 0) {
            return $result;
        }

        $output = [];
        $output['total'] = $result['total'];
        $flat_count = 0;
        $output['items'] = $result['items'];

        unset($flat_count, $result);
        return $output;
    }// listTaxonomyFlatten


    /**
     * Rebuilds the tree data and save it to the database.<br>
     * This will be rebuild the level, left, right values.
     */
    public function rebuild()
    {
        // get taxonomy tree data in the array format that suit for loop/nest loop verify level.
        $data = $this->rebuildGetTreeWithChildren();

        $n = 0; // need a variable to hold the running n tally
        $level = 0; // need a variable to hold the running level tally

        // verify the level data. this method will be alter the $data value. 
        // so, it doesn't need to use $data = $this->rebuildGenerateTreeData();
        $this->rebuildGenerateTreeData($data, 0, 0, $n);

        foreach ($data as $id => $row) {
            if ($id == '0') {
                continue;
            }

            $sql = 'UPDATE `' . $this->table_name . '`';
            $sql .= ' SET';
            $sql .= ' `' . $this->level_column_name . '` = :level,';
            $sql .= ' `' . $this->left_column_name . '` = :left,';
            $sql .= ' `' . $this->right_column_name . '` = :right';
            $sql .= ' WHERE `' . $this->id_column_name . '` = :id';

            $stmt = $this->Database->PDO->prepare($sql);
            $stmt->bindValue(':level', $row->{$this->level_column_name}, \PDO::PARAM_INT);
            $stmt->bindValue(':left', $row->{$this->left_column_name}, \PDO::PARAM_INT);
            $stmt->bindValue(':right', $row->{$this->right_column_name}, \PDO::PARAM_INT);
            $stmt->bindValue(':id', $row->{$this->id_column_name}, \PDO::PARAM_INT);
            $stmt->execute();
            unset($sql, $stmt);
        }// endforeach;

        unset($data, $id, $row);
    }// rebuild


    /**
     * Rebuild taxonomy level, left, right for tree data.<br>
     * This method will be alter the $arr value. It will verify that level is set correctly.
     * 
     * @param array $arr
     * @param integer $id
     * @param integer $level
     * @param integer $n
     */
    protected function rebuildGenerateTreeData(&$arr, $id, $level, &$n)
    {
        $arr[$id]->{$this->level_column_name} = $level;
        $arr[$id]->{$this->left_column_name} = $n++;

        // loop over the node's children and process their data
        // before assigning the right value
        foreach ($arr[$id]->children as $child_id) {
            $this->rebuildGenerateTreeData($arr, $child_id, $level + 1, $n);
        }

        $arr[$id]->{$this->right_column_name} = $n++;
    }// rebuildGenerateTreeData


    /**
     * Get the data nest tree with children.<br>
     * Its result will be look like this...<pre>
     * Array(
     *     [0] => stdClass Object
     *         (
     *             [id] => 0
     *             [children] => Array
     *                 (
     *                     [1] => 1
     *                     [2] => 2
     *                     [3] => 3
     *                 )
     *         )
     *     [1] => stdClass Object
     *         (
     *             [id] => 1
     *             [parent_id] => 0
     *             [level] => 1
     *             [children] => Array
     *                 (
     *                 )
     *         )
     *     [2] => stdClass Object
     *         (
     *             [id] => 2
     *             [parent_id] => 0
     *             [level] => 1
     *             [children] => Array
     *                 (
     *                     [4] => 4
     *                     [5] => 5
     *                 )
     *         )
     *     [3] => stdClass Object
     *         (
     *             [id] => 3
     *             [parent_id] => 0
     *             [level] => 1
     *             [children] => Array
     *                 (
     *                 )
     *         )
     *     [4] => stdClass Object
     *         (
     *             [id] => 4
     *             [parent_id] => 2
     *             [level] => 2
     *             [children] => Array
     *                 (
     *                 )
     *         )
     *     [5] => stdClass Object
     *         (
     *             [id] => 5
     *             [parent_id] => 2
     *             [level] => 2
     *             [children] => Array
     *                 (
     *                 )
     *         )
     * )</pre>
     * 
     * @return array
     */
    protected function rebuildGetTreeWithChildren()
    {
        $sql = 'SELECT `' . $this->id_column_name . '`, `' . $this->parent_id_column_name . '`, `' . $this->position_column_name . '`, `' . $this->level_column_name . '`, `' . $this->left_column_name . '`, `' . $this->right_column_name . '`';
        $sql .= ' FROM `' . $this->table_name . '`';
        $sql .= ' ORDER BY `' . $this->position_column_name . '` ASC';
        $stmt = $this->Database->PDO->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        unset($stmt);

        // create a root node to hold child data about first level items
        $root = new \stdClass;
        $root->{$this->id_column_name} = 0;
        $root->children = array();

        $arr = array($root);
        unset($root);

        // populate the array and create an empty children array
        foreach ($result as $row) {
            $arr[$row->{$this->id_column_name}] = $row;
            $arr[$row->{$this->id_column_name}]->children = array();
        }

        // now process the array and build the child data
        foreach ($arr as $id => $row) {
            if (isset($row->{$this->parent_id_column_name})) {
                $arr[$row->{$this->parent_id_column_name}]->children[$id] = $id;
            }
        }

        return $arr;
    }// rebuildGetTreeWithChildren


    /**
     * Reformat data from tree to be flatten.
     * 
     * @deprecated since v.0.1
     * @param array $items The result get from db via listTaxonomy() method that were called via listTaxonomyFlatten() method.
     * @param integer $flat_count Flat key number count.
     * @return array Return formatted flatten from tree data.
     */
    protected function reformatDataFlatten($items, &$flat_count)
    {
        if (!is_array($items) && !is_object($items)) {
            return $items;
        }

        foreach ($items as $row) {
            $output[$flat_count] = new \stdClass();

            foreach ($row as $key => $val) {
                if ($key != 'children') {
                    $output[$flat_count]->$key = $val;
                }
            }// endforeach;
            unset($key, $val);

            $flat_count++;

            if (isset($row->children)) {
                $output = array_merge($output, $this->reformatDataFlatten($row->children, $flat_count));
            }
        }// endforeach;

        unset($row);
        return $output;
    }// reformatDataFlatten


}
