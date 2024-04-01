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

    /**
     * @param resource $content
     * @return string
     */
    protected function streamToString($content): string
    {
        rewind($content);
        return strval(stream_get_contents($content, -1, 0));
    }

    /**
     * @param string $content
     * @return resource
     */
    protected function stringToStream(string $content)
    {
        $handle = fopen('php://memory', 'rb+');
        fwrite($handle, $content);
        rewind($handle);
        return $handle;
    }
}
