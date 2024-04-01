<?php

namespace MapperNoRootTests;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_paths\PathsException;


class StreamFailTest extends AStorageTest
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

        $lib = $this->getStreamFailLib();
        $this->expectException(FilesException::class);
        $lib->readFileStream(['dummy2.txt']);
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

        $lib = $this->getStreamFailLib();
        $this->expectException(FilesException::class);
        $lib->saveFileStream(['extra.txt'], $this->stringToStream('qwertzuiopasdfghjklyxcvbnm0123456789'));
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

        $lib = $this->getStreamFailLib();
        $this->expectException(FilesException::class);
        $lib->saveFileStream([], $this->stringToStream('qwertzuiopasdfghjklyxcvbnm0123456789'));
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

        $lib = $this->getStreamLib();
        $this->expectException(FilesException::class);
        $lib->saveFileStream(['not existent', 'directory', 'with file'], $this->stringToStream('qwertzuiopasdfghjklyxcvbnm0123456789'));
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

        $lib = $this->getStreamFailRecLib();
        $this->expectException(FilesException::class);
        $lib->saveFileStream(['possible file'], $this->stringToStream('qwertzuiopasdfghjklyxcvbnm0123456789'));
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

        $lib = $this->getStreamFailLib();
        $this->expectException(FilesException::class);
        $lib->copyFileStream(['dummy2.txt'], ['extra1.txt']);
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

        $lib = $this->getStreamLib();
        $this->assertFalse($lib->copyFileStream(['dummy2.txt'], ['dummy1.txt']));
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

        $lib = $this->getStreamFailLib();
        $this->expectException(FilesException::class);
        $lib->moveFileStream(['extra1.txt'], ['extra2.txt']);
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

        $lib = $this->getStreamLib();
        $this->expectException(FilesException::class);
        $lib->moveFileStream(['not source.txt'], ['extra2.txt']);
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

        $lib = $this->getStreamLib();
        $this->assertFalse($lib->moveFileStream(['sub', 'dummy3.txt'], ['whatabout', 'other2.txt']));
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

        $lib = $this->getStreamLib();
        $this->assertFalse($lib->moveFileStream(['sub', 'dummy3.txt'], ['other2.txt']));
    }
}

