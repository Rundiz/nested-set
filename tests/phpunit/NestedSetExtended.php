<?php


namespace Rundiz\NestedSet\Tests;

class NestedSetExtended extends \Rundiz\NestedSet\NestedSet
{


    /**
     * {@inheritDoc}
     */
    public function getTreeWithChildren()
    {
        return parent::rebuildGetTreeWithChildren();
    }// getTreeWithChildren


}
