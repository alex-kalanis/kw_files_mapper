<?php

namespace kalanis\kw_files_mapper\Processing\Mapper;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class ProcessNode
 * @package kalanis\kw_files_mapper\Processing\Mapper
 * Process nodes in basic ways
 */
class ProcessNode implements IProcessNodes
{
    use TLang;
    use TEntryLookup;

    /** @var ARecord */
    protected $record = null;
    /** @var Process\Translate */
    protected $translate = null;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setLang($lang);
        $this->record = $record;
        $this->translate = $translate ?: new Process\Translate();
    }

    public function exists(array $entry): bool
    {
        if (empty($entry)) {
            return true;
        }
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
        if (empty($entry)) {
            return true;
        }
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
        if (empty($entry)) {
            return false;
        }
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

    protected function getTranslation(): Process\Translate
    {
        return $this->translate;
    }
}
