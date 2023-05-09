<?php

namespace kalanis\kw_files_mapper\Processing\Mapper;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Search\Search;


/**
 * Trait TEntryLookup
 * @package kalanis\kw_files_mapper\Processing\Mapper
 * Get entry
 */
trait TEntryLookup
{
    /**
     * @param array<string> $path
     * @param ARecord|null $parentNode
     * @throws MapperException
     * @return ARecord|null
     */
    protected function getEntry(array $path, ?ARecord $parentNode = null): ?ARecord
    {
        if (empty($path)) {
            $search = new Search($this->getLookupRecord());
            $search->null($this->getTranslation()->getParentKey());
            $all = $search->getResults();

            if (empty($all)) {
                // no root node?!?!
                // @codeCoverageIgnoreStart
                return null;
            }
            // @codeCoverageIgnoreEnd

            return reset($all);
        }

        foreach ($path as $levelKey) {
            $search = new Search($this->getLookupRecord());
            $search->exact($this->getTranslation()->getCurrentKey(), strval($levelKey));
            if ($parentNode) {
                $search->exact(
                    $this->getTranslation()->getParentKey(),
                    strval($parentNode->__get($this->getTranslation()->getPrimaryKey()))
                );
            }
            $all = $search->getResults();
            if (empty($all)) {
                return null;
            }

            $parentNode = reset($all);
        }

        return $parentNode;
    }

    abstract protected function getLookupRecord(): ARecord;

    abstract protected function getTranslation(): Process\Translate;
}
