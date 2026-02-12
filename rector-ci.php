<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\CallLike\CreateStubOverCreateMockArgRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\PropertyCreateMockToCreateStubRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;

return RectorConfig::configure()
    ->withPhpSets(php84: true)
    ->withAttributesSets(phpunit: true)
    ->withComposerBased(phpunit: true)
    ->withRules([
        FinalizeTestCaseClassRector::class,
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        __DIR__ . '/rector-ci.php',
        AddOverrideAttributeToOverriddenMethodsRector::class,
        CreateStubOverCreateMockArgRector::class    => [
            __DIR__ . '/src/TestCase/CacheCleaner/AbstractCacheCleanerTestCase.php',
        ],
        PropertyCreateMockToCreateStubRector::class => [
            __DIR__ . '/tests/Provider/SimpleCachedProviderTestTraitTest.php',
        ],
    ])
    ->withImportNames();
