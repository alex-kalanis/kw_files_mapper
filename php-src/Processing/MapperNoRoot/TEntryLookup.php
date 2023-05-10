<?php

namespace kalanis\kw_files_mapper\Processing\MapperNoRoot;


use kalanis\kw_files_mapper\Support\TTranslate;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Search\Search;


/**
 * Trait TEntryLookup
 * @package kalanis\kw_files_mapper\Processing\MapperNoRoot
 * Get entry
 */
trait TEntryLookup
{
    use TTranslate;

    /**
     * @param array<string> $path
     * @param ARecord|null $parentNode
     * @throws MapperException
     * @return ARecord|null
     */
    protected function getEntry(array $path, ?ARecord $parentNode = null): ?ARecord
    {
        if (empty($path)) {
            // no root node
            return null;
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
}
