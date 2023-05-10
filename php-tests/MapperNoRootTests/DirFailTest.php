<?php

namespace MapperNoRootTests;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_paths\PathsException;


class DirFailTest extends AStorageTest
{
    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCreate(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirFailLib();
        $this->expectException(FilesException::class);
        $lib->createDir(['another'], false);
    }

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

        $lib = $this->getDirFailLib();
        $this->expectException(FilesException::class);
        $lib->readDir([''], false, true);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCopy(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirFailLib();
        $this->expectException(FilesException::class);
        $lib->copyDir(['next_one'], ['more']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCopyUnknown(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->copyDir(['not source'], ['extra2']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCopyToUnknownDir(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->copyDir(['next_one', 'sub_one'], ['unknown', 'last_one']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCopyToExisting(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->copyDir(['next_one', 'sub_one'], ['last_one']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testCopyToSub(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->copyDir(['next_one', 'sub_one'], ['next_one', 'sub_one', 'deeper']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testMove(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirFailLib();
        $this->expectException(FilesException::class);
        $lib->moveDir(['more'], ['another']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testMoveUnknown(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->moveDir(['not source'], ['extra2']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testMoveToUnknownDir(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->moveDir(['next_one', 'sub_one'], ['unknown', 'last_one']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testMoveToExisting(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->moveDir(['next_one', 'sub_one'], ['last_one']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testMoveToSub(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->expectException(FilesException::class);
        $lib->moveDir(['next_one', 'sub_one'], ['next_one', 'sub_one', 'deeper']);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testDelete(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirFailLib();
        $this->expectException(FilesException::class);
        $lib->deleteDir(['another'], true);
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testDeleteFile(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->assertFalse($lib->deleteDir(['sub', 'dummy3.txt']));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testDeleteNonEmpty(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getDirLib();
        $this->assertFalse($lib->deleteDir(['next_one']));
    }
}
