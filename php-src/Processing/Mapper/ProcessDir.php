<?php

namespace kalanis\kw_files_mapper\Processing\Mapper;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessDirs;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_files\Interfaces\ITypes;
use kalanis\kw_files\Node;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_files\Traits\TSubPart;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_files_mapper\Support\TDir;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_paths\ArrayPath;
use kalanis\kw_paths\Stuff;


/**
 * Class ProcessDir
 * @package kalanis\kw_files_mapper\Processing\Mapper
 * Process dirs in basic ways
 */
class ProcessDir implements IProcessDirs
{
    use TDir;
    use TEntryLookup;
    use TLang;
    use TSubPart;

    protected ARecord $record;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setFlLang($lang);
        $this->record = $record;
        $this->setTranslation($translate);
    }

    public function createDir(array $entry, bool $deep = false): bool
    {
        try {
            $parentNode = $this->getEntry([]);

            $maxPos = count($entry) - 1;
            foreach (array_values($entry) as $pos => $levelKey) {

                $all = $this->getChildrenRecords(strval($levelKey), $parentNode);
                if (!empty($all)) {
                    if ($pos == $maxPos) {
                        // wanted one already exists
                        return false;
                    }
                    $parentNode = reset($all);
                } elseif ($pos == $maxPos) {
                    // create wanted one
                    $parentNode = $this->createRecord(strval($levelKey), $parentNode);
                } elseif ($deep) {
                    // create on lower level
                    $parentNode = $this->createRecord(strval($levelKey), $parentNode);
                } else {
                    return false;
                }
            }

            return $parentNode ? $this->isDir($parentNode) : false;
        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotCreateDir(Stuff::arrayToPath($entry)), $ex->getCode(), $ex);
        }
    }

    /**
     * @param string $currentName
     * @param ARecord|null $parentNode
     * @throws MapperException
     * @return ARecord[]
     */
    protected function getChildrenRecords(string $currentName, ?ARecord $parentNode): array
    {
        $search = new Search($this->getLookupRecord());
        $search->exact($this->getTranslation()->getCurrentKey(), $currentName);
        if ($parentNode) {
            $search->exact(
                $this->getTranslation()->getParentKey(),
                strval($parentNode->__get($this->getTranslation()->getPrimaryKey()))
            );
        } else {
            // no root node?!
            // @codeCoverageIgnoreStart
            $search->null($this->getTranslation()->getParentKey());
        }
        // @codeCoverageIgnoreEnd
        return $search->getResults();
    }

    /**
     * @param string $name
     * @param ARecord|null $parentNode
     * @throws MapperException
     * @return ARecord
     */
    protected function createRecord(string $name, ?ARecord $parentNode): ARecord
    {
        $record = $this->getLookupRecord();
        $record->__set(
            $this->getTranslation()->getParentKey(),
            $parentNode ? strval($parentNode->__get($this->getTranslation()->getPrimaryKey())) : null
        );
        $record->__set(
            $this->getTranslation()->getCurrentKey(),
            $name
        );
        $record->__set(
            $this->getTranslation()->getContentKey(),
            IProcessNodes::STORAGE_NODE_KEY
        );
        $record->save();
        $record->load();

        return $record;
    }

    public function readDir(array $entry, bool $loadRecursive = false, bool $wantSize = false): array
    {
        $entryPath = Stuff::arrayToPath($entry);
        try {
            $node = $this->getEntry($entry);
            if (is_null($node)) {
                // no root node?!
                // @codeCoverageIgnoreStart
                throw new FilesException($this->getFlLang()->flCannotReadDir($entryPath));
            }
            // @codeCoverageIgnoreEnd

            if (!$this->isDir($node)) {
                throw new FilesException($this->getFlLang()->flCannotReadDir($entryPath));
            }
            /** @var array<string, Node> */
            $files = [];
            $startSub = new Node();
            $startSub->setData(
                [],
                0,
                ITypes::TYPE_DIR
            );
            $files[] = $startSub;

            return array_merge($files, $this->subNodes($node, $loadRecursive, $wantSize));
        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotReadDir($entryPath), $ex->getCode(), $ex);
        }
    }

    /**
     * @param ARecord $node
     * @param bool $loadRecursive
     * @param bool $wantSize
     * @param string[] $upper
     * @throws MapperException
     * @return Node[]
     */
    protected function subNodes(ARecord $node, bool $loadRecursive = false, bool $wantSize = false, array $upper = []): array
    {
        $search = new Search($this->getLookupRecord());
        $search->exact(
            $this->getTranslation()->getParentKey(),
            strval($node->__get($this->getTranslation()->getPrimaryKey()))
        );

        /** @var Node[] $files */
        $files = [];
        foreach ($search->getResults() as $item) {
            $path = array_merge($upper, [strval($item->__get($this->getTranslation()->getCurrentKey()))]);
            if ($this->isDir($item)) {
                $sub = new Node();
                $files[] = $sub->setData(
                    $path,
                    0,
                    ITypes::TYPE_DIR
                );
                if ($loadRecursive) {
                    $files = array_merge($files, $this->subNodes($item, $loadRecursive, $wantSize, $path));
                }
            } else {
                // normal node - file
                $sub = new Node();
                $files[] = $sub->setData(
                    $path,
                    $wantSize ? $this->getSize($item) : 0,
                    ITypes::TYPE_FILE
                );
            }
        }
        return $files;
    }

    public function copyDir(array $source, array $dest): bool
    {
        try {
            if ($this->isSubPart($dest, $source)) {
                return false;
            }

            $ptDst = new ArrayPath();
            $ptDst->setArray($dest);

            $src = $this->getEntry($source);
            if (!$src) {
                return false;
            }

            $dst = $this->getEntry($ptDst->getArrayDirectory());
            if (!$dst) {
                return false;
            }

            $tgt = $this->getEntry([$ptDst->getFileName()], $dst);
            if ($tgt) {
                return false;
            }

            $new = $this->getLookupRecord();
            $new->__set($this->getTranslation()->getCurrentKey(), $ptDst->getFileName());
            $new->__set($this->getTranslation()->getParentKey(), $dst->__get($this->getTranslation()->getPrimaryKey()));
            $new->__set($this->getTranslation()->getContentKey(), $dst->__get($this->getTranslation()->getContentKey()));
            $new->save();

            return $this->copyCycle($src, $new);

        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotCopyDir(
                Stuff::arrayToPath($source),
                Stuff::arrayToPath($dest)
            ), $ex->getCode(), $ex);
        }
    }

    public function moveDir(array $source, array $dest): bool
    {
        try {
            if ($this->isSubPart($dest, $source)) {
                return false;
            }

            $ptDst = new ArrayPath();
            $ptDst->setArray($dest);

            $src = $this->getEntry($source);
            if (!$src) {
                return false;
            }

            $dst = $this->getEntry($ptDst->getArrayDirectory());
            if (!$dst) {
                return false;
            }

            $tgt = $this->getEntry([$ptDst->getFileName()], $dst);
            if ($tgt) {
                return false;
            }

            $src->__set($this->getTranslation()->getCurrentKey(), $ptDst->getFileName());
            $src->__set($this->getTranslation()->getParentKey(), $dst->__get($this->getTranslation()->getPrimaryKey()));
            return $src->save();

        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotMoveDir(
                Stuff::arrayToPath($source),
                Stuff::arrayToPath($dest)
            ), $ex->getCode(), $ex);
        }
    }

    public function deleteDir(array $entry, bool $deep = false): bool
    {
        try {
            $start = $this->getEntry($entry);
            if (is_null($start)) {
                return false;
            }
            if ($deep) {
                return $this->removeCycle($start);
            } elseif ($this->isDir($start)) {
                return $this->removeDir($start);
            } else {
                return false;
            }
        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotRemoveDir(Stuff::arrayToPath($entry)), $ex->getCode(), $ex);
        }
    }

    /**
     * Copy a file, or recursively copy a folder and its contents - version for db storage
     * @param ARecord $source Source node
     * @param ARecord $dest Destination node
     * @throws MapperException
     * @return bool
     */
    protected function copyCycle(ARecord $source, ARecord $dest): bool
    {
        $stat = 0;

        $search = new Search($this->getLookupRecord());
        $search->exact($this->getTranslation()->getParentKey(), strval($source->__get($this->getTranslation()->getPrimaryKey())));
        $src = $search->getResults();

        foreach ($src as $item) {
            $newNode = $this->getLookupRecord();
            $newNode->__set($this->getTranslation()->getCurrentKey(), $item->__get($this->getTranslation()->getCurrentKey()));
            $newNode->__set($this->getTranslation()->getParentKey(), $dest->__get($this->getTranslation()->getPrimaryKey()));
            $newNode->__set($this->getTranslation()->getContentKey(), $item->__get($this->getTranslation()->getContentKey()));
            $stat += intval(!$newNode->save());

            if ($this->isDir($newNode)) {
                $stat += intval(!$this->copyCycle($item, $newNode));
            }
        }

        return !boolval($stat);
    }

    /**
     * @param ARecord $entry
     * @throws MapperException
     * @return bool
     */
    protected function removeCycle(ARecord $entry): bool
    {
        if ($this->isDir($entry)) {
            $search = new Search($this->getLookupRecord());
            $search->exact($this->getTranslation()->getParentKey(), strval($entry->__get($this->getTranslation()->getPrimaryKey())));
            $fileListing = $search->getResults();
            foreach ($fileListing as $fileRecord) {
                $this->removeCycle($fileRecord);
            }
        }
        return $entry->delete();
    }

    /**
     * @param ARecord $entry
     * @throws MapperException
     * @return bool
     */
    protected function removeDir(ARecord $entry): bool
    {
        $subs = new Search($this->getLookupRecord());
        $subs->exact($this->getTranslation()->getParentKey(), strval($entry->__get($this->getTranslation()->getPrimaryKey())));
        if (1 > $subs->getCount()) {
            return $entry->delete();
        } else {
            return false;
        }
    }

    protected function getLookupRecord(): ARecord
    {
        return clone $this->record;
    }
}
