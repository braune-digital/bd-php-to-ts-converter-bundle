<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class TypescriptProperty
{
    private string $name;
    private ?string $type;
    private ?bool $isNullable;

    public function __construct(string $name, ?string $type, ?bool $isNullable)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isNullable = $isNullable;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function isNullable(): ?bool
    {
        return $this->isNullable;
    }
}