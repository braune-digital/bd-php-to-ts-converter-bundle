<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class TypescriptInterfaceDeclarationFactory
{
    public function __invoke(ClassMetaData $metaData): TypescriptInterfaceDeclaration
    {
        $name = $metaData->className();
        $targetFileName = implode(
            \DIRECTORY_SEPARATOR,
            array_filter([
                $metaData->relativeSourcePath(),
                sprintf('%s.ts', $this->generateFileName($name))
            ])
        );
        $extendsClass = $metaData->extendsClass();

        /** @var TypescriptImport[] $imports */
        $imports =
            array_values(
                array_filter(
                    array_map(
                        fn(UseStatement $useStatement) => $this
                            ->convertUseStatementToImport($useStatement, $metaData),
                        $metaData->useStatements()
                    )
                )
            );

        $properties = array_map(
            function(PropertyMetaData $propertyMetaData) use (&$imports) {
                return $this
                    ->convertPropertyMetaData(
                        $propertyMetaData,
                        $imports
                    );
            },
            $metaData->properties()
        );

        if (null !== $extendsClass) {
            $this->ensureExtendedClassIsImported($extendsClass, $imports);
        }

        return new TypescriptInterfaceDeclaration(
            $targetFileName,
            $name,
            $extendsClass,
            $imports,
            $properties
        );
    }

    private function convertUseStatementToImport(UseStatement $useStatement, ClassMetaData $metaData): ?TypescriptImport
    {
        $useParts = $useStatement->parts();
        $name = array_pop($useParts);
        $path = $this->generateImportPathForUseStatement($useStatement, $metaData);

        if (null === $path) {
            return null;
        }

        $fullPath = $this->convertPathAndNameToTsImportPath($path, $name);

        return new TypescriptImport(
            $name,
            $fullPath
        );
    }

    private function generateImportPathForUseStatement(UseStatement $useStatement, ClassMetaData $metaData): ?string
    {
        $useParts = $useStatement->parts();
        $contextNamespaceParts = $metaData->namespaceParts();

        if (null !== $metaData->relativeSourcePath())
        {
            $relativeParts = array_reverse(explode(\DIRECTORY_SEPARATOR, $metaData->relativeSourcePath()));

            foreach($relativeParts as $relativePart)
            {
                if ($relativePart === end($contextNamespaceParts)) {
                    array_pop($contextNamespaceParts);
                    continue;
                }

                break;
            }
        }

        $contextNamespace = implode('\\', $contextNamespaceParts);
        $useStatementNamespace = implode('\\', $useParts);

        if(0 !== strpos($useStatementNamespace, $contextNamespace)) {
            return null;
        }

        $useStatementParts = $useStatement->parts();
        // Remove last element, as this is the file-name and we only need the directory path
        array_pop($useStatementParts);

        $metaDataParts = $metaData->namespaceParts();

        foreach($contextNamespaceParts as $contextNamespacePart) {
            array_shift($useStatementParts);
            array_shift($metaDataParts);
        }

        $parts = [
            '.'
        ];

        foreach($metaDataParts as $mPart) {
            $parts[] = '..';
        }

        foreach($useStatementParts as $uPart) {
            $parts[] = $uPart;
        }

        return implode(\DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * @param PropertyMetaData $propertyMetaData
     * @param TypescriptImport[] $imports
     * @return TypescriptProperty
     */
    private function convertPropertyMetaData(PropertyMetaData $propertyMetaData, array &$imports): TypescriptProperty
    {
        $name = $propertyMetaData->name();
        $type = $this->convertPropertyTypeToTypescriptType($propertyMetaData, $imports);

        return new TypescriptProperty(
            $name,
            $type,
            $propertyMetaData->isNullable()
        );
    }

    /**
     * @param PropertyMetaData $propertyMetaData
     * @param TypescriptImport[] $imports
     * @return string
     */
    private function convertPropertyTypeToTypescriptType(PropertyMetaData $propertyMetaData, array &$imports): string
    {
        $phpType = null !== $propertyMetaData->dockBlock() && $propertyMetaData->dockBlock()->containsPart('var')
            ? $propertyMetaData->dockBlock()->part('var')
            : $propertyMetaData->type();

        if (null === $phpType) {
            return 'any';
        }

        if (array_key_exists($phpType, self::BASIC_TYPE_BINDINGS)) {
            return self::BASIC_TYPE_BINDINGS[$phpType];
        }

        // If First letter is upercase, we have a class-name
        if (preg_match('~^\p{Lu}~u', $phpType)) {
            $basePHPType = '[]' === substr($phpType, -2)
                ? substr($phpType, 0, -2)
                : $phpType;

            $relatedImport = array_filter(
                $imports,
                fn(TypescriptImport $import) => $basePHPType === $import->name()
            );

            if (0 === count($relatedImport))
            {
                $tsPath = $this->convertPathAndNameToTsImportPath('.', $basePHPType);
                array_push($imports, new TypescriptImport(
                    $basePHPType,
                    $tsPath
                ));
            }

            return $phpType;
        }


        return 'any';
    }

    const BASIC_TYPE_BINDINGS = [
        'int' => 'number',
        'float' => 'number',
        'decimal' => 'number',
        'boolean' => 'number',
        'string' => 'string',
    ];

    private function convertPathAndNameToTsImportPath(string $path, string $name): string
    {
        return implode('/', [
            $path,
            $this->generateFileName($name)
        ]);
    }

    public function generateFileName(string $name): String
    {
        $matches = [];
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $name, $matches);
        $nameParts = $matches[0];
        $lastPart = array_pop($nameParts);

        return 0 === count($nameParts)
            ? strtolower($lastPart)
            : sprintf(
                '%s.%s',
                lcfirst(implode('', $nameParts)),
                strtolower($lastPart)
            );
    }

    /**
     * @param string $extendsClass
     * @param TypescriptImport[] $imports
     */
    private function ensureExtendedClassIsImported(string $extendsClass, array &$imports): void
    {
        foreach ($imports as $import)
        {
            if($import->name() === $extendsClass) {
                return;
            }
        }

        $tsPath = $this->convertPathAndNameToTsImportPath('.', $extendsClass);

        array_unshift(
            $imports,
            new TypescriptImport(
                $extendsClass,
                $tsPath
            )
        );
    }
}