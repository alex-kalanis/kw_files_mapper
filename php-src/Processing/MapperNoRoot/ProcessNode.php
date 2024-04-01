<?php

namespace kalanis\kw_files_mapper\Processing\MapperNoRoot;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class ProcessNode
 * @package kalanis\kw_files_mapper\Processing\MapperNoRoot
 * Process nodes in basic ways
 */
class ProcessNode implements IProcessNodes
{
    use TEntryLookup;
    use TLang;

    protected ARecord $record;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setFlLang($lang);
        $this->record = $record;
        $this->setTranslation($translate);
    }

    public function exists(array $entry): bool
    {
        try {
            return !is_null($this->getEntry($entry));
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function isReadable(array $entry): bool
    {
        return true;
    }

    public function isWritable(array $entry): bool
    {
        return true;
    }

    public function isDir(array $entry): bool
    {
        try {
            $entry = $this->getEntry($entry);
            if (is_null($entry)) {
                return false;
            }
            return static::STORAGE_NODE_KEY === strval($entry->__get($this->getTranslation()->getContentKey()));
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function isFile(array $entry): bool
    {
        try {
            $entry = $this->getEntry($entry);
            if (is_null($entry)) {
                return false;
            }
            return static::STORAGE_NODE_KEY !== strval($entry->__get($this->getTranslation()->getContentKey()));
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function size(array $entry): ?int
    {
        if (empty($entry)) {
            return null;
        }
        try {
            $entry = $this->getEntry($entry);
            if (is_null($entry)) {
                return null;
            }
            return strlen(strval($entry->__get($this->getTranslation()->getContentKey())));
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function created(array $entry): ?int
    {
        if (empty($entry) || is_null($this->getTranslation()->getCreatedKey())) {
            return null;
        }
        try {
            $entry = $this->getEntry($entry);
            if (is_null($entry)) {
                return null;
            }
            return intval(strval($entry->__get($this->getTranslation()->getCreatedKey())));
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    protected function getLookupRecord(): ARecord
    {
        return clone $this->record;
    }
}
