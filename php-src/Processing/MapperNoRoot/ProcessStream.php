<?php

namespace kalanis\kw_files_mapper\Processing\MapperNoRoot;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessFileStreams;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_files\Traits\TToStream;
use kalanis\kw_files\Traits\TToString;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_paths\ArrayPath;
use kalanis\kw_paths\Stuff;


/**
 * Class ProcessStream
 * @package kalanis\kw_files_mapper\Processing\MapperNoRoot
 * Process files in many ways
 */
class ProcessStream implements IProcessFileStreams
{
    use TEntryLookup;
    use TLang;
    use TToStream;
    use TToString;

    protected ARecord $record;

    public function __construct(ARecord $record, ?Process\Translate $translate = null, ?IFLTranslations $lang = null)
    {
        $this->setFlLang($lang);
        $this->record = $record;
        $this->setTranslation($translate);
    }

    public function readFileStream(array $entry)
    {
        try {
            $record = $this->getEntry($entry);
            $path = Stuff::arrayToPath($entry);
            if (is_null($record)) {
                throw new FilesException($this->getFlLang()->flCannotLoadFile($path));
            }

            return $this->toStream($path, $record->__get($this->getTranslation()->getContentKey()));
        } catch (MapperException $ex) {
            throw new FilesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function saveFileStream(array $entry, $content, int $mode = 0): bool
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

            $current->__set($this->getTranslation()->getContentKey(), $prepend . $this->toString($path, $content));

            if (false === $current->save()) {
                throw new FilesException($this->getFlLang()->flCannotSaveFile($path));
            }
            return true;
        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotSaveFile($path), $ex->getCode(), $ex);
        }
    }

    public function copyFileStream(array $source, array $dest): bool
    {
        // simplified run - no moving nodes, just use existing ones
        $src = Stuff::arrayToPath($source);
        $dst = Stuff::arrayToPath($dest);
        try {
            $dstRec = $this->getEntry($dest);
            if ($dstRec) {
                return false;
            }

            $sourceStream = $this->readFileStream($source);
            rewind($sourceStream);
            return $this->saveFileStream($dest, $sourceStream);
        } catch (MapperException $ex) {
            throw new FilesException($this->getFlLang()->flCannotCopyFile($src, $dst), $ex->getCode(), $ex);
        }
    }

    public function moveFileStream(array $source, array $dest): bool
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

    protected function getLookupRecord(): ARecord
    {
        return clone $this->record;
    }
}
