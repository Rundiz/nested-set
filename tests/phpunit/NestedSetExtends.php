<?php


namespace Rundiz\NestedSet\Tests;


class NestedSetExtends extends \Rundiz\NestedSet\NestedSet
{


    /**
     * {@inheritDoc}
     */
    public function getTreeRebuildChildren(array $array): array
    {
        return parent::getTreeRebuildChildren($array);
    }// getTreeRebuildChildren


    /**
     * {@inheritDoc}
     */
    public function getTreeWithChildren(array $where = []): array
    {
        return parent::getTreeWithChildren($where);
    }// getTreeWithChildren


    /**
     * {@inheritDoc}
     */
    public function rebuildGenerateTreeData(array &$array, int $id, int $level, int &$n)
    {
        parent::rebuildGenerateTreeData($array, $id, $level, $n);
    }// ebuildGenerateTreeData


}