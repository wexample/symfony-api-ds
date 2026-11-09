<?php

namespace Wexample\SymfonyApiDs\Traits;

use Wexample\SymfonyApiDs\WexampleSymfonyApiDsBundle;
use Wexample\SymfonyHelpers\Traits\BundleClassTrait;

trait SymfonyApiDsBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyApiDsBundle::class;
    }
}
