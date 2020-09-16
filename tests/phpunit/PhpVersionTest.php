<?php


namespace Rundiz\NestedSet\Tests;

class PhpVersionTest extends \PHPUnit\Framework\TestCase
{


    /**
     * Test minimum of PHP version is met.
     *
     * @return void
     */
    public function testPhpVersion()
    {
        $this->assertTrue(version_compare(PHP_VERSION, '7.0.0', '>='));
    }// testPhpVersion


}