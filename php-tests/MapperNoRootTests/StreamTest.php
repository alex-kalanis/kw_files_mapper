<?php

namespace MapperNoRootTests;


use kalanis\kw_files\FilesException;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_paths\PathsException;


class StreamTest extends AStorageTest
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

        $lib = $this->getStreamLib();
        $this->assertEquals('qwertzuiopasdfghjklyxcvbnm0123456789', $this->streamToString($lib->readFileStream(['dummy2.txt'])));
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

        $lib = $this->getStreamLib();
        $this->expectException(FilesException::class);
        $lib->readFileStream(['unknown']);
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

        $lib = $this->getStreamLib();
        $this->expectException(FilesException::class);
        $lib->readFileStream(['sub', 'dummy_nope.txt']);
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

        $lib = $this->getStreamLib();
        $this->assertTrue($lib->saveFileStream(['extra.txt'], $this->stringToStream('qwertzuiopasdfghjklyxcvbnm0123456789')));
        $this->assertEquals('qwertzuiopasdfghjklyxcvbnm0123456789', $this->streamToString($lib->readFileStream(['extra.txt'])));
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

        $lib = $this->getStreamLib();
        $this->assertTrue($lib->saveFileStream(['sub', 'foul.txt'], $this->stringToStream('qwertzuiopasdfghjklyxcvbnm0123456789')));
        $this->assertEquals('qwertzuiopasdfghjklyxcvbnm0123456789', $this->streamToString($lib->readFileStream(['sub', 'foul.txt'])));
        $this->assertTrue($lib->saveFileStream(['sub', 'foul.txt'], $this->stringToStream('qwertzuiopasdfghjklyxcvbnm0123456789'), FILE_APPEND));
        $this->assertEquals('qwertzuiopasdfghjklyxcvbnm0123456789qwertzuiopasdfghjklyxcvbnm0123456789', $this->streamToString($lib->readFileStream(['sub', 'foul.txt'])));
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

        $lib = $this->getStreamLib();
        $this->assertTrue($lib->copyFileStream(['dummy2.txt'], ['extra1.txt']));
        $this->assertTrue($lib->moveFileStream(['extra1.txt'], ['extra2.txt']));
        $lib2 = $this->getFileLib();
        $this->assertTrue($lib2->deleteFile(['extra2.txt']));
        $this->assertTrue($lib2->deleteFile(['extra3.txt']));
    }
}
