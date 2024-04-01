<?php

namespace MapperNoRootTests;


use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_files_mapper\Processing\MapperNoRoot\TEntryLookup;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


class EntryLookupTest extends AStorageTest
{
    /**
     * @throws MapperException
     */
    public function testSimple(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLookupLib();
        $this->assertEmpty($lib->look([]));
        $this->assertNotEmpty($lib->look(['sub']));
        $this->assertNotEmpty($lib->look(['next_one', 'sub_one']));
        $this->assertEmpty($lib->look(['unknown']));
        $this->assertEmpty($lib->look(['sub', 'unknown']));
    }

    /**
     * @throws MapperException
     */
    public function testDeeper(): void
    {
        if ($this->skipIt) {
            $this->markTestSkipped('Skipped by config');
            return;
        }

        $this->dataRefill();

        $lib = $this->getLookupLib();
        $rec = $lib->look(['sub']);
        $this->assertNotEmpty($rec);
        $this->assertNotEmpty($lib->look(['dummy3.txt'], $rec));
        $this->assertEmpty($lib->look(['unknown'], $rec));
    }

    protected function getLookupLib(): XLookup
    {
        return new XLookup(new SQLiteTestRecord());
    }
}


class XLookup
{
    use TEntryLookup;
    use TLang;

    protected ARecord $record;
    protected Process\Translate $translate;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setFlLang($lang);
        $this->record = $record;
        $this->translate = $translate ?: new Process\Translate();
    }

    /**
     * @param string[] $path
     * @param ARecord|null $parentNode
     * @throws MapperException
     * @return ARecord|null
     */
    public function look(array $path, ?ARecord $parentNode = null): ?ARecord
    {
        return $this->getEntry($path, $parentNode);
    }

    protected function getLookupRecord(): ARecord
    {
        return clone $this->record;
    }

    protected function getTranslation(): Process\Translate
    {
        return $this->translate;
    }
}

