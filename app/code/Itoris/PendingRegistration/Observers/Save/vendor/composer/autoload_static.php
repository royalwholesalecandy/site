<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd6def3795c58d7eef440b53e2d1c4806
{
    public static $classMap = array (
        'Insightly' => __DIR__ . '/../..' . '/insightly.php',
        'InsightlyRequest' => __DIR__ . '/../..' . '/insightly.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitd6def3795c58d7eef440b53e2d1c4806::$classMap;

        }, null, ClassLoader::class);
    }
}
