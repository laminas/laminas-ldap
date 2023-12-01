<?php

namespace Laminas\Ldap;

use LDAP\Connection;
use LDAP\Result;
use LDAP\ResultEntry;

/**
 * Laminas\Ldap\Handler is a collection of LDAP handler related functions.
 */
class Handler
{
    /**
     * Checks if the given handle is an LDAP connection object or a resource based on the running PHP version.
     *
     * @param mixed $handle
     * @return bool
     * @psalm-assert-if-true Connection $handle
     */
    public static function isLdapHandle($handle)
    {
        return $handle instanceof Connection;
    }

    /**
     * Checks if the given handle is an LDAP result object or a resource based on the running PHP version.
     *
     * @param mixed $handle
     * @return bool
     * @psalm-assert-if-true Result $handle
     */
    public static function isResultHandle($handle)
    {
        return $handle instanceof Result;
    }

    /**
     * Checks if the given handle is an LDAP result entry object or a resource based on the running PHP version.
     *
     * @param mixed $handle
     * @return bool
     * @psalm-assert-if-true ResultEntry $handle
     */
    public static function isResultEntryHandle($handle)
    {
        return $handle instanceof ResultEntry;
    }
}
