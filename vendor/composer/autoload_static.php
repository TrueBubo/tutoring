<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit128f5f54d48eb8cd2b4223b2c0f672eb
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Ds\\' => 3,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ds\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-ds/php-ds/src',
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
            $loader->classMap = ComposerStaticInit128f5f54d48eb8cd2b4223b2c0f672eb::$classMap;

        }, null, ClassLoader::class);
    }
}
