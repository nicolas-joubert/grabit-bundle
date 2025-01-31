<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    // ignore 'method_chaining_indentation' rule
    ->notPath('DependencyInjection/Configuration.php')
    // ignore 'fully_qualified_strict_types, single_line_after_imports' rules
    ->notPath('config/bundles.php')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PhpCsFixer' => true,
        '@DoctrineAnnotation' => true,
        '@PHP83Migration' => true,
    ])
    ->setFinder($finder)
;
