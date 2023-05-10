<?php

namespace MapperNoRootTests;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_paths\PathsException;


class NodeFailTest extends AStorageTest
{
    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testExists(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getNodeFailLib();
        $this->expectException(FilesException::class);
        $lib->exists(['unknown']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testDir(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getNodeFailLib();
        $this->expectException(FilesException::class);
        $lib->isDir(['unknown']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testFile(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getNodeFailLib();
        $this->expectException(FilesException::class);
        $lib->isFile(['unknown']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testSize(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getNodeFailLib();
        $this->expectException(FilesException::class);
        $lib->size(['unknown']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCreated(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getNodeFailLib();
        $this->expectException(FilesException::class);
        $lib->created(['unknown']);
    }
}
