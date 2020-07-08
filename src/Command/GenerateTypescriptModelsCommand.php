<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\Command;

use BrauneDigital\PhpToTypescriptConverterBundle\MetaData\ClassMetaData;
use BrauneDigital\PhpToTypescriptConverterBundle\MetaData\DirectoryClassMetaDataReader;
use BrauneDigital\PhpToTypescriptConverterBundle\MetaData\TypescriptInterfaceDeclaration;
use BrauneDigital\PhpToTypescriptConverterBundle\MetaData\TypescriptInterfaceDeclarationFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateTypescriptModelsCommand extends Command
{
    const SOURCE_DIR_ARG_NAME = 'source-dir';
    const TARGET_DIR_ARG_NAME = 'target-dir';
    const FORCE_OPTION_NAME = 'force';

    protected static $defaultName = 'bd:php-to-typescript-models-converter:convert';

    private string $projectDir;

    private DirectoryClassMetaDataReader $directoryClassMetaDataReader;

    private TypescriptInterfaceDeclarationFactory $declarationFactory;

    private Filesystem $fileSystem;

    public function __construct(
        string $projectDir,
        DirectoryClassMetaDataReader $directoryClassMetaDataReader,
        TypescriptInterfaceDeclarationFactory $declarationFactory
    ) {
        parent::__construct(null);

        $this->projectDir = $projectDir;
        $this->directoryClassMetaDataReader = $directoryClassMetaDataReader;
        $this->declarationFactory = $declarationFactory;

        $this->fileSystem = new Filesystem();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                self::SOURCE_DIR_ARG_NAME,
                InputArgument::REQUIRED,
                'Directory-Path for Source-files'
            )
            ->addArgument(
                self::TARGET_DIR_ARG_NAME,
                InputArgument::REQUIRED,
                'Director-Path for Target-files'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceDir = $this->handlePathArgument(
            $input->getArgument(self::SOURCE_DIR_ARG_NAME)
        );

        $targetDir = $this->handlePathArgument(
            $input->getArgument(self::TARGET_DIR_ARG_NAME)
        );

        $data = $this
            ->directoryClassMetaDataReader
            ->__invoke($sourceDir);

        $modelClasses = array_filter(
            $data,
            fn(ClassMetaData $metaData) => $metaData->hasDocBlock() && $metaData
                ->docBlock()
                ->containsPart('ConvertToTypescriptModel')
        );

        $tsClasses = array_map(
            fn(ClassMetaData $metaData) => $this
                ->declarationFactory
                ->__invoke($metaData),
            $modelClasses
        );

        array_map(
            function (TypescriptInterfaceDeclaration $declaration) use ($targetDir) {
                $targetPathName = implode(
                    \DIRECTORY_SEPARATOR,
                    [
                        $targetDir,
                        $declaration->targetFileName()
                    ]);

                $this
                    ->fileSystem
                    ->dumpFile(
                        $targetPathName,
                        $declaration->generateTypescriptInterface()
                    );
            },
            $tsClasses
        );

        return 0;
    }

    /**
     * @param string $pathArgument
     * @return string
     *
     * FIXME: Make it work in Windows as well, if needed :P
     */
    private function handlePathArgument(string $pathArgument): string
    {
        $fullPath = 0 === strpos($pathArgument, \DIRECTORY_SEPARATOR)
            ? $pathArgument
            : implode(\DIRECTORY_SEPARATOR, [
                $this->projectDir,
                $pathArgument
            ]);

        if (!$this->fileSystem->exists($fullPath)) {
            $this->fileSystem->mkdir($fullPath);
        }

        return realpath($fullPath);
    }
}