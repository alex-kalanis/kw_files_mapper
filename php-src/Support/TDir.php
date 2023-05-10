<?php

namespace kalanis\kw_files_mapper\Support;


use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Trait TDir
 * @package kalanis\kw_files_mapper\Support
 * Convert content
 */
trait TDir
{
    use TTranslate;

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function isDir(ARecord $record): bool
    {
        return IProcessNodes::STORAGE_NODE_KEY === strval($record->__get($this->getTranslation()->getContentKey()));
    }

    /**
     * @param ARecord $entry
     * @throws MapperException
     * @return int
     */
    protected function getSize(ARecord $entry): int
    {
        return strlen(strval($entry->__get($this->getTranslation()->getContentKey())));
    }
}
