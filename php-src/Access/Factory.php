<?php

namespace kalanis\kw_files_mapper\Access;


use kalanis\kw_files\Access as original_access;
use kalanis\kw_files\FilesException;
use kalanis\kw_files\Processing as original_processing;
use kalanis\kw_files\Traits\TLang;
use kalanis\kw_files_mapper\Processing;
use kalanis\kw_files_mapper\Support\Process;
use kalanis\kw_mapper\Records\ARecord;
use kalanis\kw_paths\PathsException;
use kalanis\kw_storage\Interfaces\IStorage;


/**
 * Class Factory
 * @package kalanis\kw_files_mapper\Access
 * Create Composite access to storage
 */
class Factory extends original_access\Factory
{
    use TLang;

    /**
     * @param string|array<string|int, string|int|float|bool|object>|object $param
     * @throws PathsException
     * @throws FilesException
     * @return original_access\CompositeAdapter
     */
    public function getClass($param): original_access\CompositeAdapter
    {
        if (is_string($param)) {
            return new original_access\CompositeAdapter(
                new original_processing\Volume\ProcessNode($param, $this->lang),
                new original_processing\Volume\ProcessDir($param, $this->lang),
                new original_processing\Volume\ProcessFile($param, $this->lang)
            );

        } elseif (is_array($param)) {
            if (isset($param['path']) && is_string($param['path'])) {
                return new original_access\CompositeAdapter(
                    new original_processing\Volume\ProcessNode($param['path'], $this->lang),
                    new original_processing\Volume\ProcessDir($param['path'], $this->lang),
                    new original_processing\Volume\ProcessFile($param['path'], $this->lang)
                );

            } elseif (isset($param['source']) && is_string($param['source'])) {
                return new original_access\CompositeAdapter(
                    new original_processing\Volume\ProcessNode($param['source'], $this->lang),
                    new original_processing\Volume\ProcessDir($param['source'], $this->lang),
                    new original_processing\Volume\ProcessFile($param['source'], $this->lang)
                );

            } elseif (isset($param['source']) && is_object($param['source']) && ($param['source'] instanceof IStorage)) {
                return new original_access\CompositeAdapter(
                    new original_processing\Storage\ProcessNode($param['source'], $this->lang),
                    new original_processing\Storage\ProcessDir($param['source'], $this->lang),
                    new original_processing\Storage\ProcessFile($param['source'], $this->lang)
                );

            } elseif (isset($param['source']) && is_object($param['source']) && ($param['source'] instanceof ARecord)) {
                $trans = (isset($param['translate']) && is_object($param['translate']) && ($param['translate'] instanceof Process\Translate))
                    ? $param['translate']
                    : null
                ;
                return new original_access\CompositeAdapter(
                    new Processing\Mapper\ProcessNode($param['source'], $trans, $this->lang),
                    new Processing\Mapper\ProcessDir($param['source'], $trans, $this->lang),
                    new Processing\Mapper\ProcessFile($param['source'], $trans, $this->lang)
                );
            }

        } elseif (is_object($param)) {
            if ($param instanceof IStorage) {
                return new original_access\CompositeAdapter(
                    new original_processing\Storage\ProcessNode($param, $this->lang),
                    new original_processing\Storage\ProcessDir($param, $this->lang),
                    new original_processing\Storage\ProcessFile($param, $this->lang)
                );
            } elseif ($param instanceof ARecord) {
                return new original_access\CompositeAdapter(
                    new Processing\Mapper\ProcessNode($param, null, $this->lang),
                    new Processing\Mapper\ProcessDir($param, null, $this->lang),
                    new Processing\Mapper\ProcessFile($param, null, $this->lang)
                );
            }
        }

        throw new FilesException($this->getLang()->flNoAvailableClasses());
    }
}
