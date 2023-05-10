<?php

namespace kalanis\kw_files_mapper\Processing\MapperNoRoot;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessDirs;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_files\Interfaces\ITypes;
use kalanis\kw_files\Node;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_files_mapper\Support\TDir;
use kalanis\kw_files_mapper\Support\TSubPart;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_mapper\Search\Search;
use kalanis\kw_paths\ArrayPath;
use kalanis\kw_paths\Stuff;


/**
 * Class ProcessDir
 * @package kalanis\kw_files_mapper\Processing\MapperNoRoot
 * Process dirs in basic ways
 */
class ProcessDir implements IProcessDirs
{
    use TDir;
    use TLang;
    use TEntryLookup;
    use TSubPart;

    /** @var ARecord */
    protected $record = null;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setLang($lang);
        $this->record = $record;
        $this->setTranslation($translate);
    }

    public function createDir(array $entry, bool $deep = false): bool
    {
        try {
            /** @var ARecord|null $parentNode */
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

            return $this->isDir($parentNode);
        } catch (MapperException $ex) {
            throw new FilesException($this->getLang()->flCannotCreateDir(Stuff::arrayToPath($entry)), $ex->getCode(), $ex);
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
            // no root node?
            $search->null($this->getTranslation()->getParentKey());
        }
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
        try {
            $entryPath = Stuff::arrayToPath($entry);
            $node = $this->getEntry($entry);

            if (!is_null($node) && !$this->isDir($node)) {
                throw new FilesException($this->getLang()->flCannotReadDir($entryPath));
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
            throw new FilesException($this->getLang()->flCannotReadDir($entryPath), $ex->getCode(), $ex);
        }
    }

    /**
     * @param ARecord|null $node
     * @param bool $loadRecursive
     * @param bool $wantSize
     * @param string[] $upper
     * @throws MapperException
     * @return Node[]
     */
    protected function subNodes(?ARecord $node, bool $loadRecursive = false, bool $wantSize = false, array $upper = []): array
    {
        $search = new Search($this->getLookupRecord());
        if ($node) {
            $search->exact(
                $this->getTranslation()->getParentKey(),
                strval($node->__get($this->getTranslation()->getPrimaryKey()))
            );
        } else {
            $search->null($this->getTranslation()->getParentKey());
        }

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
                throw new FilesException($this->getLang()->flCannotCopyDir(
                    Stuff::arrayToPath($source),
                    Stuff::arrayToPath($dest)
                ));
            }

            $ptDst = new ArrayPath();
            $ptDst->setArray($dest);

            $src = $this->getEntry($source);
            if (!$src) {
                throw new FilesException($this->getLang()->flCannotProcessNode(Stuff::arrayToPath($source)));
            }

            $dst = $this->getEntry($ptDst->getArrayDirectory());

            $tgt = $this->getEntry([$ptDst->getFileName()], $dst);
            if ($tgt) {
                throw new FilesException($this->getLang()->flCannotProcessNode(Stuff::arrayToPath($dest)));
            }

            $new = $this->getLookupRecord();
            $new->__set($this->getTranslation()->getCurrentKey(), $ptDst->getFileName());
            $new->__set($this->getTranslation()->getParentKey(), $dst ? $dst->__get($this->getTranslation()->getPrimaryKey()) : null);
            $new->__set($this->getTranslation()->getContentKey(), IProcessNodes::STORAGE_NODE_KEY);
            $new->save();

            return $this->copyCycle($src, $new);

        } catch (MapperException $ex) {
            throw new FilesException($this->getLang()->flCannotCopyDir(
                Stuff::arrayToPath($source),
                Stuff::arrayToPath($dest)
            ), $ex->getCode(), $ex);
        }
    }

    public function moveDir(array $source, array $dest): bool
    {
        try {
            if ($this->isSubPart($dest, $source)) {
                throw new FilesException($this->getLang()->flCannotMoveDir(
                    Stuff::arrayToPath($source),
                    Stuff::arrayToPath($dest)
                ));
            }

            $ptDst = new ArrayPath();
            $ptDst->setArray($dest);

            $src = $this->getEntry($source);
            if (!$src) {
                throw new FilesException($this->getLang()->flCannotProcessNode(Stuff::arrayToPath($source)));
            }

            $dst = $this->getEntry($ptDst->getArrayDirectory());

            $tgt = $this->getEntry([$ptDst->getFileName()], $dst);
            if ($tgt) {
                throw new FilesException($this->getLang()->flCannotProcessNode(Stuff::arrayToPath($dest)));
            }

            $src->__set($this->getTranslation()->getCurrentKey(), $ptDst->getFileName());
            $src->__set($this->getTranslation()->getParentKey(), $dst ? $dst->__get($this->getTranslation()->getPrimaryKey()) : null);
            return $src->save();

        } catch (MapperException $ex) {
            throw new FilesException($this->getLang()->flCannotMoveDir(
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
            throw new FilesException($this->getLang()->flCannotRemoveDir(Stuff::arrayToPath($entry)), $ex->getCode(), $ex);
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
        $stat = true;

        $search = new Search($this->getLookupRecord());
        $search->exact($this->getTranslation()->getParentKey(), $source->__get($this->getTranslation()->getPrimaryKey()));
        $src = $search->getResults();

        foreach ($src as $item) {
            $newNode = $this->getLookupRecord();
            $newNode->__set($this->getTranslation()->getCurrentKey(), $item->__get($this->getTranslation()->getCurrentKey()));
            $newNode->__set($this->getTranslation()->getParentKey(), $dest->__get($this->getTranslation()->getPrimaryKey()));
            $newNode->__set($this->getTranslation()->getContentKey(), $item->__get($this->getTranslation()->getContentKey()));
            $stat &= $newNode->save();

            if ($this->isDir($newNode)) {
                $stat &= $this->copyCycle($item, $newNode);
            }
        }

        return $stat;
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
