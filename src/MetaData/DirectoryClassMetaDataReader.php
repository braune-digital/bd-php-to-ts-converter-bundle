<?php

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DirectoryClassMetaDataReader
{
    private ClassMetaDataReader $classMetaDataReader;

    public function __construct(ClassMetaDataReader $classMetaDataReader)
    {
        $this->classMetaDataReader = $classMetaDataReader;
    }

    public function __invoke(string $directory): array
    {
        // Finder for all php files in the directory (and sub directories)
        $finder = (new Finder())
            ->in($directory)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->name('*.php')
            ->files();

        return array_map(
            function (SplFileInfo $object) use ($directory) {
                $relativePath = trim(str_replace(realpath($directory), '', realpath($object->getPath())), \DIRECTORY_SEPARATOR);

                return $this
                    ->classMetaDataReader
                    ->__invoke(
                        $object->getRealPath(),
                        strlen($relativePath) > 0
                            ? $relativePath
                            : null
                    );
            },
            iterator_to_array($finder)
        );
    }
}