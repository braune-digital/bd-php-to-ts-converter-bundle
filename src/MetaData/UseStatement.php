<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class UseStatement
{
    /**
     * @var string[]
     */
    private array $parts;

    /**
     * @var string|null
     */
    private ?string $alias;

    public function __construct(array $parts, ?string $alias)
    {
        $this->parts = $parts;
        $this->alias = $alias;
    }

    /**
     * @return string[]
     */
    public function parts(): array
    {
        return $this->parts;
    }

    public function alias(): ?string
    {
        return $this->alias;
    }

    public function hasAlias(): bool
    {
        return null !== $this->alias();
    }
}