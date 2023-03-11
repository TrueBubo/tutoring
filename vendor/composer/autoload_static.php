<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit128f5f54d48eb8cd2b4223b2c0f672eb
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'D' => 
        array (
            'Ds\\' => 3,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/src',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Ds\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-ds/php-ds/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Tatocaster\\Monolog\\Formatter\\' => 
            array (
                0 => __DIR__ . '/..' . '/tatocaster/monolog-json-unicode-pretty-formatter/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit128f5f54d48eb8cd2b4223b2c0f672eb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit128f5f54d48eb8cd2b4223b2c0f672eb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit128f5f54d48eb8cd2b4223b2c0f672eb::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit128f5f54d48eb8cd2b4223b2c0f672eb::$classMap;

        }, null, ClassLoader::class);
    }
}
