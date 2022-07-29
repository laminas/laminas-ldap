<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\TestAsset;

use function strrev;
use function strtolower;

class CustomNaming
{
    public static function name1(string $attrib): string
    {
        return strtolower(strrev($attrib));
    }

    public function name2(string $attrib): string
    {
        return strrev($attrib);
    }
}
