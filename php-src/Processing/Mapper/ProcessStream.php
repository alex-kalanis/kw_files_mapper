<?php

namespace kalanis\kw_files_mapper\Processing\Mapper;


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
 * @package kalanis\kw_files_mapper\Processing\Mapper
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
                throw new FilesException($this->getFlLang()->flCannotSaveFile($path));
            }

            $tgtArr = new ArrayPath();
            $tgtArr->setArray($entry);

            $current = $this->getEntry($entry);
            $parent = $this->getEntry($tgtArr->getArrayDirectory());

            if (!empty($tgtArr->getArrayDirectory()) && empty($parent)) {
                throw new FilesException($this->getFlLang()->flCannotSaveFile($path));
            }

            $prepend = '';
            if (is_null($current)) {
                $current = $this->getLookupRecord();
                $current->__set($this->getTranslation()->getParentKey(), $parent ? strval($parent->__get($this->getTranslation()->getPrimaryKey())) : null);
                $current->__set($this->getTranslation()->getCurrentKey(), $tgtArr->getFileName());
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
