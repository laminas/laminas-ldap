<?php

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
     * @return null
     */
    public function getOid()
    {
        return null;
    }

    /**
     * Gets the attributes that this objectClass must contain
     *
     * @return null
     */
    public function getMustContain()
    {
        return null;
    }

    /**
     * Gets the attributes that this objectClass may contain
     *
     * @return null
     */
    public function getMayContain()
    {
        return null;
    }

    /**
     * Gets the objectClass description
     *
     * @return null
     */
    public function getDescription()
    {
        return null;
    }

    /**
     * Gets the objectClass type
     *
     * @return null
     */
    public function getType()
    {
        return null;
    }

    /**
     * Returns the parent objectClasses of this class.
     * This includes structural, abstract and auxiliary objectClasses
     *
     * @return null
     */
    public function getParentClasses()
    {
        return null;
    }
}
