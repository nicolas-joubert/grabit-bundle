<?php

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withPaths([
        __DIR__.'/src',
    ])
    // here we can define, what prepared sets of rules will be applied
    ->withPreparedSets(deadCode: true, codeQuality: true, doctrineCodeQuality: true, symfonyCodeQuality: true)
    ->withAttributesSets(symfony: true, doctrine: true)
    ->withSets([
        LevelSetList::UP_TO_PHP_83,
        SymfonySetList::SYMFONY_72,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ])
    ->withSkip([
        StringClassNameToClassConstantRector::class => [
            __DIR__.'/src/DependencyInjection/Configuration.php',
        ],
    ])
;
