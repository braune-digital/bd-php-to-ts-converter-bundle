<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class PropertyMetaData
{
    private string $name;

    private ?string $type;

    private ?bool $isNullable;

    private ?DocBlock $dockBlock;

    public function __construct(
        string $name,
        ?string $type,
        ?bool $isNullable,
        ?DocBlock $dockBlock
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->isNullable = $isNullable;
        $this->dockBlock = $dockBlock;
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

    public function dockBlock(): ?DocBlock
    {
        return $this->dockBlock;
    }
}