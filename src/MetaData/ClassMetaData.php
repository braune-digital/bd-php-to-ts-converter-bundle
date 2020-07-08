<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class ClassMetaData
{
    /**
     * @var string[]
     */
    private array $namespaceParts;

    private string $className;

    private ?string $extendsClass;

    private ?DocBlock $docBlock;

    /**
     * @var UseStatement[]
     */
    private array $useStatements;

    /**
     * @var PropertyMetaData[]
     */
    private array $properties;

    private ?string $relativeSourcePath;

    /**
     * @param string[] $namespaceParts
     * @param string $className
     * @param string|null $extendsClass
     * @param DocBlock|null $docBlock
     * @param UseStatement[] $useStatements
     * @param PropertyMetaData[] $properties
     * @param string|null $relativeSourcePath
     */
    public function __construct(
        array $namespaceParts,
        string $className,
        ?string $extendsClass,
        ?DocBlock $docBlock,
        array $useStatements,
        array $properties,
        ?string $relativeSourcePath
    )
    {
        $this->namespaceParts = $namespaceParts;
        $this->className = $className;
        $this->extendsClass = $extendsClass;
        $this->docBlock = $docBlock;
        $this->useStatements = $useStatements;
        $this->properties = $properties;
        $this->relativeSourcePath = $relativeSourcePath;
    }

    /**
     * @return string[]
     */
    public function namespaceParts(): array
    {
        return $this->namespaceParts;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function extendsClass(): ?string
    {
        return $this->extendsClass;
    }

    public function hasDocBlock(): bool
    {
        return null !== $this->docBlock;
    }

    public function docBlock(): ?DocBlock
    {
        return $this->docBlock;
    }

    /**
     * @return UseStatement[]
     */
    public function useStatements(): array
    {
        return $this->useStatements;
    }

    /**
     * @return PropertyMetaData[]
     */
    public function properties(): array
    {
        return $this->properties;
    }

    public function relativeSourcePath(): ?string
    {
        return $this->relativeSourcePath;
    }
}