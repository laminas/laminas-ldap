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
     * @return string
     */
    public function getOid()
    {
    }

    /**
     * Gets the attribute syntax
     *
     * @return string
     */
    public function getSyntax()
    {
    }

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength()
    {
    }

    /**
     * Returns if the attribute is single-valued.
     *
     * @return bool
     */
    public function isSingleValued()
    {
    }

    /**
     * Gets the attribute description
     *
     * @return string
     */
    public function getDescription()
    {
    }
}
