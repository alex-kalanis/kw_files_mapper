<?php

use kalanis\kw_files\Node;
use PHPUnit\Framework\TestCase;


/**
 * Class CommonTestClass
 * The structure for mocking and configuration seems so complicated, but it's necessary to let it be totally idiot-proof
 */
class CommonTestClass extends TestCase
{
    public function sortingPaths(Node $a, Node $b): int
    {
        return $this->fullPath($a) <=> $this->fullPath($b);
    }

    protected function fullPath(Node $node): string
    {
        return implode(DIRECTORY_SEPARATOR, $node->getPath());
    }
}
