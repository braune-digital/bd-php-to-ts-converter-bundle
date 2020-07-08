<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class DocBlock
{
    private ?string $description;

    /**
     * @var array[]
     */
    private array $parts;

    public function __construct(?string $description, array $parts)
    {
        $this->description = $description;
        $this->parts = $parts;
    }

    public function part(string $name): string
    {
        return $this->parts[$name][0];
    }

    public function containsPart(string $name): bool
    {
        return array_key_exists($name, $this->parts);
    }
}