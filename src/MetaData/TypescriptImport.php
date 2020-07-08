<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class TypescriptImport
{
    private string $name;
    private string $path;

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

}