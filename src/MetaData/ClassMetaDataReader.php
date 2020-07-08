<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class ClassMetaDataReader
{
    private DocBlockReader $docBlockReader;

    private Parser $parser;

    public function __construct(DocBlockReader $docBlockReader)
    {
        $this->docBlockReader = $docBlockReader;
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    public function __invoke(string $phpFilePath, ?string $relativeSourcePath): ClassMetaData
    {
        $fileContents = file_get_contents($phpFilePath);

        $ast = $this
            ->parser
            ->parse($fileContents);

        $traverser = new NodeTraverser();
        foreach($traverser->traverse($ast) as $node) {
            if(!$node instanceof Namespace_) {
                continue;
            }

            /** @var Namespace_ $namespace */
            $namespace = $node;

            $namespaceParts = $namespace->name->parts;
            $className = null;
            $useStatements = [];
            $properties = [];

            foreach($namespace->stmts as $stmt) {
                if($stmt instanceof Use_) {
                    foreach($stmt->uses as $useStatement) {
                        $useStatements[] = new UseStatement(
                            $useStatement->name->parts,
                            $useStatement->alias
                                ? $useStatement->alias->name
                                : null
                        );
                    }

                    continue;
                }

                if($stmt instanceof Class_) {
                    $className = $stmt
                        ->name
                        ->name;

                    $extendsClass = null !== $stmt->extends
                        ? $stmt->extends->parts[0]
                        : null;

                    $fqn = sprintf('\\%s\\%s', implode('\\', $namespaceParts ), $className);

                    $reflectionClass = new \ReflectionClass($fqn);
                    $classDocBlockString = $reflectionClass->getDocComment();

                    $classDockBlock = false !== $classDocBlockString
                        ? $this
                            ->docBlockReader
                            ->__invoke($classDocBlockString)
                        : null;

                    foreach($stmt->getProperties() as $property)
                    {
                        $propertyProp = $property
                            ->props[0];

                        $name = $propertyProp
                            ->name
                            ->name;

                        [
                            $type,
                            $isNullable
                        ] = $this->extractTypeAndNullable($property);

                        $reflectionProperty = new \ReflectionProperty($fqn, $name);
                        $propDocBlockString = $reflectionProperty->getDocComment();

                        $propDockBlock = false !== $propDocBlockString
                            ? $this
                                ->docBlockReader
                                ->__invoke($propDocBlockString)
                            : null;

                        $properties[] = new PropertyMetaData(
                            $name,
                            $type,
                            $isNullable,
                            $propDockBlock
                        );
                    }

                    return new ClassMetaData(
                        $namespaceParts,
                        $className,
                        $extendsClass,
                        $classDockBlock,
                        $useStatements,
                        $properties,
                        $relativeSourcePath
                    );
                }
            }
        }

        throw new \RuntimeException(sprintf(
            'Could not find a valid PHP-Class Declaration in "%s"',
            $phpFilePath
        ));
    }

    private function extractTypeAndNullable(Property $property)
    {
        $typeProp = $property->type;

        if (null === $typeProp) {
            return [
                null,
                null
            ];
        } elseif($typeProp instanceof Name) {
            return [
                $typeProp->parts[0],
                false
            ];
        } elseif($typeProp instanceof Identifier) {
            return [
                $typeProp->name,
                false
            ];
        } elseif($typeProp instanceof NullableType) {
            if($typeProp->type instanceof Identifier) {
                return [
                    $typeProp->type->name,
                    true
                ];
            } elseif($typeProp->type instanceof Name) {
                return [
                    $typeProp->type->parts[0],
                    true
                ];
            } else {
                throw new \RuntimeException(sprintf(
                    'Unsupported name type "%s".',
                    get_class($typeProp)
                ));
            }
        } elseif(is_string($typeProp)) {
            return [
                $typeProp,
                false
            ];
        } else {
            throw new \RuntimeException(sprintf(
                'Unsupported name type "%s".',
                get_class($typeProp)
            ));
        }

    }
}