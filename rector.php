<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/tools',
    ]);

    // Keep the Rector caches inside the build directory rather than the repository root.
    $rectorCacheDirectory          = __DIR__ . '/.build/cache/.rector.cache';
    $rectorContainerCacheDirectory = __DIR__ . '/.build/cache/.rector.container.cache';

    foreach ([$rectorCacheDirectory, $rectorContainerCacheDirectory] as $cacheDirectory) {
        if (
            !is_dir($cacheDirectory)
            && !mkdir($cacheDirectory, 0o775, true)
            && !is_dir($cacheDirectory)
        ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created.', $cacheDirectory));
        }
    }

    $rectorConfig->cacheDirectory($rectorCacheDirectory);
    $rectorConfig->containerCacheDirectory($rectorContainerCacheDirectory);
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

    // The shared rule sets and skips; 80300 is this package's PHP floor.
    (require __DIR__ . '/.build/vendor/magicsunday/coding-standard/rector/base.php')($rectorConfig, 80300);

    // Package-local skips on top of the shared ones.
    $rectorConfig->skip([
        __DIR__ . '/.build',
        ClassPropertyAssignToConstructorPromotionRector::class,
        LocallyCalledStaticMethodToNonStaticRector::class,
        ParamTypeByMethodCallTypeRector::class,
        ReadOnlyPropertyRector::class,
        RemoveUselessVarTagRector::class,
    ]);
};
