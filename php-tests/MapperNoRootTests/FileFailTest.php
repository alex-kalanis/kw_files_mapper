<?php

namespace MapperNoRootTests;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_paths\PathsException;


class FileFailTest extends AStorageTest
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

        $lib = $this->getFileFailLib();
        $this->expectException(FilesException::class);
        $lib->readFile(['dummy2.txt']);
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

        $lib = $this->getFileFailLib();
        $this->expectException(FilesException::class);
        $lib->saveFile(['extra.txt'], 'qwertzuiopasdfghjklyxcvbnm0123456789');
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

        $lib = $this->getFileFailLib();
        $this->expectException(FilesException::class);
        $lib->saveFile([], 'qwertzuiopasdfghjklyxcvbnm0123456789');
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testSave3(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileLib();
        $this->expectException(FilesException::class);
        $lib->saveFile(['not existent', 'directory', 'with file'], 'qwertzuiopasdfghjklyxcvbnm0123456789');
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testSave4(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getFileFailRecLib();
        $this->expectException(FilesException::class);
        $lib->saveFile(['possible file'], 'qwertzuiopasdfghjklyxcvbnm0123456789');
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

        $lib = $this->getFileFailLib();
        $this->expectException(FilesException::class);
        $lib->copyFile(['dummy2.txt'], ['extra1.txt']);
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

        $lib = $this->getFileLib();
        $this->assertFalse($lib->copyFile(['dummy2.txt'], ['dummy1.txt']));
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

        $lib = $this->getFileFailLib();
        $this->expectException(FilesException::class);
        $lib->moveFile(['extra1.txt'], ['extra2.txt']);
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

        $lib = $this->getFileLib();
        $this->expectException(FilesException::class);
        $lib->moveFile(['not source.txt'], ['extra2.txt']);
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

        $lib = $this->getFileLib();
        $this->assertFalse($lib->moveFile(['sub', 'dummy3.txt'], ['whatabout', 'other2.txt']));
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

        $lib = $this->getFileLib();
        $this->assertFalse($lib->moveFile(['sub', 'dummy3.txt'], ['other2.txt']));
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

        $lib = $this->getFileFailLib();
        $this->expectException(FilesException::class);
        $lib->deleteFile(['extra2.txt']);
    }
}

