<?php

namespace Laminas\Ldap;

/**
 * Laminas\Ldap\Handler is a collection of LDAP handler related functions.
 */
class Handler
{

    /**
     * @param object|resource $handle
     * @return bool
     */
    private static function isHandle($handle, $handleClassName)
    {
        $useResource = version_compare(PHP_VERSION, '8.1.0') < 0;
        return ($useResource && is_resource($handle)) || (! $useResource && is_object($handle) && is_a($handle, $handleClassName, true));
    }

    /**
     * Checks if the given handle is an LDAP connection object or a resource based on the running PHP version.
     *
     * @param object|resource $handle
     * @return bool
     */
    public static function isLdapHandle($handle)
    {
        return self::isHandle($handle, '\\Ldap\\Connection');
    }

    /**
     * Checks if the given handle is an LDAP result object or a resource based on the running PHP version.
     *
     * @param object|resource $handle
     * @return bool
     */
    public static function isResultHandle($handle)
    {
        return self::isHandle($handle, '\\LDAP\\Result');
    }

    /**
     * Checks if the given handle is an LDAP result entry object or a resource based on the running PHP version.
     *
     * @param object|resource $handle
     * @return bool
     */
    public static function isResultEntryHandle($handle)
    {
        return self::isHandle($handle, '\\LDAP\\ResultEntry');
    }
}
