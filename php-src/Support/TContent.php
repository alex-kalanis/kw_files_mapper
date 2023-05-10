<?php

namespace kalanis\kw_files_mapper\Support;


use kalanis\kw_files\FilesException;
use kalanis\kw_files\Traits\TLang;


/**
 * Trait TContent
 * @package kalanis\kw_files_mapper\Support
 * Convert content
 */
trait TContent
{
    use TLang;

    /**
     * @param string $name
     * @param string|resource $content
     * @throws FilesException
     * @return string
     */
    protected function contentToString(string $name, $content): string
    {
        if (is_resource($content)) {
            rewind($content);
            $data = stream_get_contents($content, -1, 0);
            if (false === $data) {
                // @codeCoverageIgnoreStart
                throw new FilesException($this->getLang()->flCannotSaveFile($name));
            }
            // @codeCoverageIgnoreEnd
            return strval($data);
        } else {
            return strval($content);
        }
    }
}
