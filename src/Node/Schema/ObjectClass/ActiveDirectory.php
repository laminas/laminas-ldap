<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Ldap\Node\Schema\ObjectClass;

use Laminas\Ldap\Node\Schema;

/**
 * Laminas\Ldap\Node\Schema\ObjectClass\ActiveDirectory provides access to the objectClass
 * schema information on an Active Directory server.
 */
class ActiveDirectory extends Schema\AbstractItem implements ObjectClassInterface
{
    /**
     * Gets the objectClass name
     *
     * @return string
     */
    public function getName()
    {
        return $this->ldapdisplayname[0];
    }

    /**
     * Gets the objectClass OID
     *
     * @return string
     */
    public function getOid()
    {
    }

    /**
     * Gets the attributes that this objectClass must contain
     *
     * @return array
     */
    public function getMustContain()
    {
    }

    /**
     * Gets the attributes that this objectClass may contain
     *
     * @return array
     */
    public function getMayContain()
    {
    }

    /**
     * Gets the objectClass description
     *
     * @return string
     */
    public function getDescription()
    {
    }

    /**
     * Gets the objectClass type
     *
     * @return int
     */
    public function getType()
    {
    }

    /**
     * Returns the parent objectClasses of this class.
     * This includes structural, abstract and auxiliary objectClasses
     *
     * @return array
     */
    public function getParentClasses()
    {
    }
}
