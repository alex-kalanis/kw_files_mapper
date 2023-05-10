<?php

namespace AccessTests;


use CommonTestClass;
use kalanis\kw_files\Access\CompositeAdapter;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_files_mapper\Processing;
use kalanis\kw_files_mapper\Access\Factory;
use kalanis\kw_files\FilesException;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers;
use kalanis\kw_mapper\Records\ASimpleRecord;
use kalanis\kw_paths\PathsException;
use kalanis\kw_storage\Storage\Key\DefaultKey;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target\Memory;


class FactoryTest extends CommonTestClass
{
    /**
     * @param $param
     * @throws FilesException
     * @throws PathsException
     * @dataProvider passProvider
     */
    public function testPass($param): void
    {
        $lib = new Factory();
        $this->assertInstanceOf(CompositeAdapter::class, $lib->getClass($param));
    }

    public function passProvider(): array
    {
        $storage = new Storage(new DefaultKey(), new Memory());
        return [
            ['somewhere'],
            [__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tree'],
            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tree']],
            [['source' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tree']],
            [$storage],
            [new XSrc()],
            [['source' => $storage]],
            [['source' => $storage, 'translate' => new XTr()]],
        ];
    }

    /**
     * @throws FilesException
     * @throws MapperException
     * @throws PathsException
     */
    public function testPassExtra(): void
    {
        $lib = new Factory();
        $adapt = $lib->getClass(['source' => new XSrc(), 'translate' => new XTr(), ]);
        $this->assertInstanceOf(Processing\Mapper\ProcessDir::class, $adapt->getDir());
        $this->assertInstanceOf(Processing\Mapper\ProcessFile::class, $adapt->getFile());
        $this->assertInstanceOf(Processing\Mapper\ProcessNode::class, $adapt->getNode());
    }

    /**
     * @param mixed $param
     * @throws PathsException
     * @throws FilesException
     * @dataProvider failProvider
     */
    public function testFail($param): void
    {
        $lib = new Factory();
        $this->expectException(FilesException::class);
        $lib->getClass($param);
    }

    public function failProvider(): array
    {
        return [
            [true],
            [false],
            [null],
            [new \stdClass()],
            [['what' => 'irrelevant']],
            [['path' => []]],
            [['path' => null]],
            [['path' => new \stdClass()]],
            [['source' => []]],
            [['source' => null]],
            [['source' => new \stdClass()]],
        ];
    }
}


class XSrc extends ASimpleRecord
{
    protected function addEntries(): void
    {
        $this->addEntry('name', IEntryType::TYPE_STRING, 256);
        $this->addEntry('content', IEntryType::TYPE_STRING, 65536);
        $this->addEntry('parentName', IEntryType::TYPE_STRING, 256);
        $this->setMapper(XSrcMapper::class);
    }
}


class XSrcMapper extends Mappers\APreset
{
    protected function setMap(): void
    {
        $this->setSource('preset');
        $this->setRelation('name', 0);
        $this->setRelation('parentName', 1);
        $this->setRelation('content', 2);
        $this->addPrimaryKey('id');
    }

    protected function loadFromStorage(): array
    {
        return [
            ['', null, IProcessNodes::STORAGE_NODE_KEY],
            ['dum', '', IProcessNodes::STORAGE_NODE_KEY],
            ['cegf', 'dum', IProcessNodes::STORAGE_NODE_KEY],
            ['vdrvda', 'cegf', 'vfddv1234567uhbzgv'],
            ['wstgs', 'dum', 'bgdsdfsdv54321dbxb'],
        ];
    }
}


class XTr extends Process\Translate
{
    /** @var string */
    protected $current = 'name';
    /** @var string */
    protected $parent = 'parentName';
    /** @var string */
    protected $primary = 'name';
    /** @var string */
    protected $content = 'content';
    /** @var string|null */
    protected $created = null;
}
