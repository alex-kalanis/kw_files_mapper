<?php

namespace MapperTests;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_paths\PathsException;


class FileTest extends AStorageTest
{
    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testRead(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileLib();
        $this->assertEquals('qwertzuiopasdfghjklyxcvbnm0123456789', $lib->readFile(['dummy2.txt']));
        $this->assertEquals('asdfghjklyxcvbnm0123456789', $lib->readFile(['dummy2.txt'], 10));
        $this->assertEquals('asdfghjkly', $lib->readFile(['dummy2.txt'], 10, 10));
        $this->assertEquals('qwertzuiop', $lib->readFile(['dummy2.txt'], null, 10));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testReadNonExist(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileLib();
        $this->expectException(FilesException::class);
        $lib->readFile(['unknown']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testReadFalse(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileLib();
        $this->expectException(FilesException::class);
        $lib->readFile(['sub', 'dummy_nope.txt']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testSave(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileLib();
        $this->assertTrue($lib->saveFile(['extra.txt'], 'qwertzuiopasdfghjklyxcvbnm0123456789'));
        $this->assertEquals('qwertzuiopasdfghjklyxcvbnm0123456789', $lib->readFile(['extra.txt']));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testSave2(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileLib();
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, 'qwertzuiopasdfghjklyxcvbnm0123456789');
        $this->assertTrue($lib->saveFile(['sub', 'foul.txt'], $handle));
        $this->assertEquals('qwertzuiopasdfghjklyxcvbnm0123456789', $lib->readFile(['sub', 'foul.txt']));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCopyMoveDelete(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileLib();
        $this->assertTrue($lib->copyFile(['dummy2.txt'], ['extra1.txt']));
        $this->assertTrue($lib->moveFile(['extra1.txt'], ['extra2.txt']));
        $this->assertTrue($lib->deleteFile(['extra2.txt']));
        $this->assertTrue($lib->deleteFile(['extra3.txt']));
    }
}
