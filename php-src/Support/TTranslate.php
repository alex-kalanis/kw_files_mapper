<?php

namespace kalanis\kw_files_mapper\Support;


/**
 * Trait TTranslate
 * @package kalanis\kw_files_mapper\Support
 * Convert content
 */
trait TTranslate
{
    protected ?Process\Translate $translation = null;

    public function setTranslation(?Process\Translate $translation = null): void
    {
        $this->translation = $translation;
    }

    public function getTranslation(): Process\Translate
    {
        if (empty($this->translation)) {
            $this->translation = new Process\Translate();
        }
        return $this->translation;
    }
}
