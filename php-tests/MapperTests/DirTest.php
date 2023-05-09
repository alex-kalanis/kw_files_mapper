<?php

namespace MapperTests;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IProcessDirs;
use kalanis\kw_files\Interfaces\ITypes;
use kalanis\kw_files\Node;
use kalanis\kw_files_mapper\Processing\Mapper;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_paths\PathsException;


class DirTest extends AStorageTest
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

        $lib = $this->getLib();
        $this->assertTrue($lib->createDir(['another'], false), 'not created on root');
        $this->assertTrue($lib->createDir(['sub', 'added'], false), 'exists with subdir'); // not exists in sub dir
        $this->assertFalse($lib->createDir(['sub', 'added'], true), 'not exists yet in subdir'); // already exists in sub dir
        $this->assertFalse($lib->createDir(['more', 'added'], false), 'should not create subdir'); // not exists both dirs, cannot deep
        $this->assertTrue($lib->createDir(['more', 'added'], true), 'cannot create subdir'); // not exists both dirs, can deep
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testRead1(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLib();
        $subList = $lib->readDir([], false, true);
        usort($subList, [$this, 'sortingPaths']);

        $entry = reset($subList);
        /** @var Node $entry */
        $this->assertEquals([], $entry->getPath());
        $this->assertEquals(ITypes::TYPE_DIR, $entry->getType());
        $this->assertTrue($entry->isDir());
        $this->assertFalse($entry->isFile());

        $entry = next($subList);
        $this->assertEquals(['dummy1.txt'], $entry->getPath());
        $this->assertEquals(36, $entry->getSize());
        $this->assertEquals(ITypes::TYPE_FILE, $entry->getType());
        $this->assertFalse($entry->isDir());
        $this->assertTrue($entry->isFile());

        $entry = next($subList);
        $this->assertEquals(['dummy2.txt'], $entry->getPath());
        $this->assertEquals(36, $entry->getSize());
        $this->assertEquals(ITypes::TYPE_FILE, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['last_one'], $entry->getPath());
        $this->assertEquals(ITypes::TYPE_DIR, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['next_one'], $entry->getPath());
        $this->assertEquals(ITypes::TYPE_DIR, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['other1.txt'], $entry->getPath());
        $this->assertEquals(36, $entry->getSize());
        $this->assertEquals(ITypes::TYPE_FILE, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['other2.txt'], $entry->getPath());
        $this->assertEquals(36, $entry->getSize());
        $this->assertEquals(ITypes::TYPE_FILE, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['sub'], $entry->getPath());
        $this->assertEquals(ITypes::TYPE_DIR, $entry->getType());

        $this->assertFalse(next($subList));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testRead2(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLib();
        $subList = $lib->readDir(['next_one'], true);
        usort($subList, [$this, 'sortingPaths']);

        $entry = reset($subList);
        /** @var Node $entry */
        $this->assertEquals([], $entry->getPath());
        $this->assertEquals(ITypes::TYPE_DIR, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['sub_one'], $entry->getPath());
        $this->assertEquals(ITypes::TYPE_DIR, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['sub_one', '.gitkeep'], $entry->getPath());
        $this->assertEquals(0, $entry->getSize());
        $this->assertEquals(ITypes::TYPE_FILE, $entry->getType());

        $this->assertFalse(next($subList));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testRead3(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLib();
        $subList = $lib->readDir(['last_one'], false);
        $entry = reset($subList);
        /** @var Node $entry */
        $this->assertEquals([], $entry->getPath());
        $this->assertEquals(ITypes::TYPE_DIR, $entry->getType());

        $entry = next($subList);
        $this->assertEquals(['.gitkeep'], $entry->getPath());
        $this->assertEquals(0, $entry->getSize());
        $this->assertEquals(ITypes::TYPE_FILE, $entry->getType());

        $this->assertFalse(next($subList));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testReadFail(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLib();
        $this->expectException(FilesException::class);
        $lib->readDir(['dummy2.txt']);
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

        $lib = $this->getLib();
        $this->assertTrue($lib->copyDir(['next_one'], ['more']));
        $this->assertTrue($lib->moveDir(['more'], ['another']));
        $this->assertTrue($lib->deleteDir(['another'], true));
        $this->assertFalse($lib->deleteDir(['another']));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testDeleteShallow(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLib();
        $this->assertTrue($lib->createDir(['extra']));
        $this->assertTrue($lib->deleteDir(['extra']));
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testDeleteDeep(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLib();
        $this->assertTrue($lib->deleteDir(['next_one'], true));
    }

    /**
     * @throws PathsException
     * @throws MapperException
     * @return IProcessDirs
     */
    protected function getLib(): IProcessDirs
    {
        return new Mapper\ProcessDir(new SQLiteTestRecord(), new Mapper\Process\Translate());
    }
}
