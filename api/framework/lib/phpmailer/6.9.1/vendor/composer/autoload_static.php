<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd59f6be467d7fbe150ac151ec8497873
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd59f6be467d7fbe150ac151ec8497873::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd59f6be467d7fbe150ac151ec8497873::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd59f6be467d7fbe150ac151ec8497873::$classMap;

        }, null, ClassLoader::class);
    }
}
