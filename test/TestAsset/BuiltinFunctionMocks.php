<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\TestAsset;

use phpmock\Mock;

use function fopen;

class BuiltinFunctionMocks
{
    /** @var Mock|null */
    public static $ldap_connect_mock;
    /** @var Mock|null */
    public static $ldap_bind_mock;
    /** @var Mock|null */
    public static $ldap_set_option_mock;

    public static function createMocks()
    {
        $ldap_connect_mock = new Mock(
            'Laminas\\Ldap',
            'ldap_connect',
            static function () {
                static $resource = null;
                if ($resource === null) {
                    $resource = fopen(__FILE__, 'r');
                }
                return $resource;
            }
        );

        $ldap_bind_mock = new Mock(
            'Laminas\\Ldap',
            'ldap_bind',
            static fn(): bool => true
        );

        $ldap_set_option_mock = new Mock(
            'Laminas\\Ldap',
            'ldap_set_option',
            static fn(): bool => true
        );

        $ldap_connect_mock->define();
        $ldap_bind_mock->define();
        $ldap_set_option_mock->define();

        static::$ldap_connect_mock    = $ldap_connect_mock;
        static::$ldap_bind_mock       = $ldap_bind_mock;
        static::$ldap_set_option_mock = $ldap_set_option_mock;
    }
}
