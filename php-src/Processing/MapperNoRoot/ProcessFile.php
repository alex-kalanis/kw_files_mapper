<?php

namespace kalanis\kw_files_mapper\Processing\MapperNoRoot;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_files_mapper\Support\TContent;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_paths\ArrayPath;
use kalanis\kw_paths\Stuff;


/**
 * Class ProcessFile
 * @package kalanis\kw_files_mapper\Processing\MapperNoRoot
 * Process files in many ways
 */
class ProcessFile implements IProcessFiles
{
    use TContent;
    use TEntryLookup;

    /** @var ARecord */
    protected $record = null;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setLang($lang);
        $this->record = $record;
        $this->setTranslation($translate);
    }

    public function readFile(array $entry, ?int $offset = null, ?int $length = null)
    {
        try {
            $record = $this->getEntry($entry);
            if (is_null($record)) {
                throw new FilesException($this->getLang()->flCannotLoadFile(Stuff::arrayToPath($entry)));
            }

            $content = $record->__get($this->getTranslation()->getContentKey());
            // shit with substr... that needed undefined params was from some java dude?!
            if (!is_null($length)) {
                return mb_substr(strval($content), intval($offset), $length);
            }
            if (!is_null($offset)) {
                return mb_substr(strval($content), $offset);
            }
            return strval($content);
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function saveFile(array $entry, $content): bool
    {
        try {
            $path = Stuff::arrayToPath($entry);

            if (1 > count($entry)) {
                throw new FilesException($this->getLang()->flCannotSaveFile(''));
            } elseif (2 > count($entry)) {
                $name = strval(end($entry));
                $current = $this->getEntry($entry);
                $parent = null;
            } else {
                $parentPath = array_slice($entry, 0, -1);

                $name = strval(end($entry));
                $parent = $this->getEntry($parentPath);
                if (is_null($parent)) {
                    throw new FilesException($this->getLang()->flCannotSaveFile($path));
                }

                $current = $this->getEntry([$name], $parent);
            }

            if (is_null($current)) {
                $current = $this->getLookupRecord();
                $current->__set($this->getTranslation()->getParentKey(), $parent ? strval($parent->__get($this->getTranslation()->getPrimaryKey())) : null);
                $current->__set($this->getTranslation()->getCurrentKey(), $name);
            }

            $current->__set($this->getTranslation()->getContentKey(), $this->contentToString($name, $content));

            if (false === $current->save()) {
                throw new FilesException($this->getLang()->flCannotSaveFile($path));
            }
            return true;
        } catch (MapperException $ex) {
            throw new FilesException($this->getLang()->flCannotSaveFile($path), $ex->getCode(), $ex);
        }
    }

    public function copyFile(array $source, array $dest): bool
    {
        // simplified run - no moving nodes, just use existing ones
        $src = Stuff::arrayToPath($source);
        $dst = Stuff::arrayToPath($dest);
        try {
            $dstRec = $this->getEntry($dest);
            if ($dstRec) {
                throw new FilesException($this->getLang()->flCannotCopyFile($src, $dst));
            }

            return $this->saveFile($dest, $this->readFile($source));
        } catch (MapperException $ex) {
            throw new FilesException($this->getLang()->flCannotCopyFile($src, $dst), $ex->getCode(), $ex);
        }
    }

    public function moveFile(array $source, array $dest): bool
    {
        try {
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
            $src->__set(
                $this->getTranslation()->getParentKey(),
                $dst ? $dst->__get($this->getTranslation()->getPrimaryKey()) : null
            );
            return $src->save();

        } catch (MapperException $ex) {
            throw new FilesException($this->getLang()->flCannotMoveFile(
                Stuff::arrayToPath($source),
                Stuff::arrayToPath($dest)
            ), $ex->getCode(), $ex);
        }
    }

    public function deleteFile(array $entry): bool
    {
        try {
            $record = $this->getEntry($entry);
            if (is_null($record)) {
                return true;
            }
            return $record->delete();
        } catch (MapperException $ex) {
            throw new FilesException($this->getLang()->flCannotRemoveFile(Stuff::arrayToPath($entry)), $ex->getCode(), $ex);
        }
    }

    protected function getLookupRecord(): ARecord
    {
        return clone $this->record;
    }
}