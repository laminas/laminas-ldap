<?php

namespace Laminas\Ldap\Node\Schema\AttributeType;

use Laminas\Ldap\Node\Schema;

/**
 * Laminas\Ldap\Node\Schema\AttributeType\ActiveDirectory provides access to the attribute type
 * schema information on an Active Directory server.
 */
class ActiveDirectory extends Schema\AbstractItem implements AttributeTypeInterface
{
    /**
     * Gets the attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->ldapdisplayname[0];
    }

    /**
     * Gets the attribute OID
     *
     * @return null
     */
    public function getOid()
    {
        return null;
    }

    /**
     * Gets the attribute syntax
     *
     * @return null
     */
    public function getSyntax()
    {
        return null;
    }

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        return null;
    }

    /**
     * Returns if the attribute is single-valued.
     *
     * @return null
     */
    public function isSingleValued()
    {
        return null;
    }

    /**
     * Gets the attribute description
     *
     * @return null
     */
    public function getDescription()
    {
        return null;
    }
}
