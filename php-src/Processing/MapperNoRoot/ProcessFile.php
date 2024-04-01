<?php

namespace kalanis\kw_files_mapper\Processing\MapperNoRoot;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_files_mapper\Support\Process;
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
    use TEntryLookup;
    use TLang;

    protected ARecord $record;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setFlLang($lang);
        $this->record = $record;
        $this->setTranslation($translate);
    }

    public function readFile(array $entry, ?int $offset = null, ?int $length = null): string
    {
        try {
            $record = $this->getEntry($entry);
            if (is_null($record)) {
                throw new FilesException($this->getFlLang()->flCannotLoadFile(Stuff::arrayToPath($entry)));
            }

            $content = $record->__get($this->getTranslation()->getContentKey());
            // shit with substr... that needed undefined params was from some java dude?!
            if (!is_null($length)) {
                return strval(substr(strval($content), intval($offset), $length));
            }
            if (!is_null($offset)) {
                return strval(substr(strval($content), $offset));
            }
            return strval($content);
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function saveFile(array $entry, string $content, ?int $offset = null, int $mode = 0): bool
    {
        $path = Stuff::arrayToPath($entry);
        try {

            if (1 > count($entry)) {
                throw new FilesException($this->getFlLang()->flCannotSaveFile(''));
            } elseif (2 > count($entry)) {
                $name = strval(end($entry));
                $current = $this->getEntry($entry);
                $parent = null;
            } else {
                $parentPath = array_slice($entry, 0, -1);

                $name = strval(end($entry));
                $parent = $this->getEntry($parentPath);
                if (is_null($parent)) {
                    throw new FilesException($this->getFlLang()->flCannotSaveFile($path));
                }

                $current = $this->getEntry([$name], $parent);
            }

            $prepend = '';
            if (is_null($current)) {
                $current = $this->getLookupRecord();
                $current->__set($this->getTranslation()->getParentKey(), $parent ? strval($parent->__get($this->getTranslation()->getPrimaryKey())) : null);
                $current->__set($this->getTranslation()->getCurrentKey(), $name);
            } else {
                if (FILE_APPEND == $mode) {
                    $prepend = strval($current->__get($this->getTranslation()->getContentKey()));
                }
            }

            if (!is_null($offset)) {
                $prepend = str_pad(strval(substr($prepend, 0, $offset)), $offset, chr(0));
            }

            $current->__set($this->getTranslation()->getContentKey(), $prepend . $content);

            if (false === $current->save()) {
                throw new FilesException($this->getFlLang()->flCannotSaveFile($path));
            }
            return true;
        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotSaveFile($path), $ex->getCode(), $ex);
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
                return false;
            }

            return $this->saveFile($dest, $this->readFile($source));
        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotCopyFile($src, $dst), $ex->getCode(), $ex);
        }
    }

    public function moveFile(array $source, array $dest): bool
    {
        try {
            $ptDst = new ArrayPath();
            $ptDst->setArray($dest);

            $src = $this->getEntry($source);
            if (!$src) {
                throw new FilesException($this->getFlLang()->flCannotProcessNode(Stuff::arrayToPath($source)));
            }

            $dst = $this->getEntry($ptDst->getArrayDirectory());

            $tgt = $this->getEntry([$ptDst->getFileName()], $dst);
            if ($tgt) {
                return false;
            }

            $src->__set($this->getTranslation()->getCurrentKey(), $ptDst->getFileName());
            $src->__set(
                $this->getTranslation()->getParentKey(),
                $dst ? $dst->__get($this->getTranslation()->getPrimaryKey()) : null
            );
            return $src->save();

        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotMoveFile(
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
            throw new FilesException($this->getFlLang()->flCannotRemoveFile(Stuff::arrayToPath($entry)), $ex->getCode(), $ex);
        }
    }

    protected function getLookupRecord(): ARecord
    {
        return clone $this->record;
    }
}
