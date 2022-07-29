<?php

namespace Laminas\Ldap;

use LDAP\Connection;
use LDAP\Result;
use LDAP\ResultEntry;

use function is_a;
use function is_object;
use function is_resource;
use function version_compare;

use const PHP_VERSION;

/**
 * Laminas\Ldap\Handler is a collection of LDAP handler related functions.
 */
class Handler
{
    /**
     * @param resource|Connection|Result|ResultEntry $handle
     * @param string $handleClassName
     */
    private static function isHandle($handle, $handleClassName): bool
    {
        $useResource = version_compare(PHP_VERSION, '8.1.0') < 0;
        return ($useResource && is_resource($handle))
            || (! $useResource && is_object($handle) && is_a($handle, $handleClassName));
    }

    /**
     * Checks if the given handle is an LDAP connection object or a resource based on the running PHP version.
     *
     * @param resource $handle
     * @return bool
     */
    public static function isLdapHandle($handle)
    {
        return self::isHandle($handle, '\\Ldap\\Connection');
    }

    /**
     * Checks if the given handle is an LDAP result object or a resource based on the running PHP version.
     *
     * @param resource $handle
     * @return bool
     */
    public static function isResultHandle($handle)
    {
        return self::isHandle($handle, '\\LDAP\\Result');
    }

    /**
     * Checks if the given handle is an LDAP result entry object or a resource based on the running PHP version.
     *
     * @param resource $handle
     * @return bool
     */
    public static function isResultEntryHandle($handle)
    {
        return self::isHandle($handle, '\\LDAP\\ResultEntry');
    }
}
