<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class TypescriptInterfaceDeclaration
{
    private string $targetFileName;

    private string $name;

    private ?string $extendsClass;

    /**
     * @var TypescriptImport[]
     */
    private array $imports;

    /**
     * @var TypescriptProperty[]
     */
    private array $properties;

    public function __construct(
        string $targetFileName,
        string $name,
        ?string $extendsClass,
        array $imports,
        array $properties
    ) {
        $this->targetFileName = $targetFileName;
        $this->name = $name;
        $this->extendsClass = $extendsClass;
        $this->imports = $imports;
        $this->properties = $properties;
    }

    public function targetFileName(): ?string
    {
        return $this->targetFileName;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function extendsClass(): ?string
    {
        return $this->extendsClass;
    }

    /**
     * @return TypescriptImport[]
     */
    public function imports(): array
    {
        return $this->imports;
    }

    /**
     * @return TypescriptImport[]
     */
    public function usedImports(): array
    {
        return array_filter(
            array_map(
                function(TypescriptImport $import) {
                    foreach($this->properties() as $property) {
                        $propType = $property->type();

                        if (null === $propType) {
                            continue;
                        }

                        $baseType = str_replace('[]', '', $propType);

                        if ($import->name() === $baseType) {
                            return $import;
                        }
                    }

                    if(null !== $this->extendsClass() && $this->extendsClass() === $import->name()) {
                        return $import;
                    }

                    return null;
                },
                $this->imports()
            )
        );
    }

    /**
     * @return TypescriptProperty[]
     */
    public function properties(): array
    {
        return $this->properties;
    }

    public function generateTypescriptInterface(): string
    {
        ob_start();
        print '// Generated Class, do not change manually!'.PHP_EOL;

        foreach($this->usedImports() as $import)
        {
            print sprintf(
                "import %s from '%s';".PHP_EOL,
                $import->name(),
                $import->path()
            );
        }

        print PHP_EOL;
        print sprintf(
            'interface %s {'.PHP_EOL,
            null !== $this->extendsClass()
                ? sprintf(
                    '%s extends %s',
                    $this->name(),
                    $this->extendsClass()
                )
                : $this->name(),
        );

        foreach($this->properties() as $property) {
            print sprintf(
                '    %s%s: %s;'.PHP_EOL,
                $property->name(),
                true === $property->isNullable() ? '?' : '',
                $property->type()
            );
        }

        print '}'.PHP_EOL;
        print PHP_EOL;
        print sprintf(
            'export default %s;'.PHP_EOL,
            $this->name()
        );

        $contents = ob_get_contents();
        ob_clean();

        return $contents;
    }
}