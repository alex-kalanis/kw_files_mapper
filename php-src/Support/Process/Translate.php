<?php

namespace kalanis\kw_files_mapper\Support\Process;


/**
 * Class Translate
 * @package kalanis\kw_files_mapper\Support\Process
 * Translate keys which represents the column names
 * When it contains null then the column does not exists and cannot be used
 */
class Translate
{
    protected string $current = 'name';
    protected string $parent = 'parentId';
    protected string $primary = 'id';
    protected string $content = 'content';
    protected ?string $created = 'created';

    public function getCurrentKey(): string
    {
        return $this->current;
    }

    public function getParentKey(): string
    {
        return $this->parent;
    }

    public function getPrimaryKey(): string
    {
        return $this->primary;
    }

    public function getContentKey(): string
    {
        return $this->content;
    }

    public function getCreatedKey(): ?string
    {
        return $this->created;
    }
}
