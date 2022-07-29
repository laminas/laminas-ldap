<?php

namespace Laminas\Ldap\Node\Schema;

use Laminas\Ldap;
use Laminas\Ldap\Node;

/**
 * Laminas\Ldap\Node\Schema\ActiveDirectory provides a simple data-container for the Schema node of
 * an Active Directory server.
 */
class ActiveDirectory extends Node\Schema
{
    /**
     * The attribute Types
     *
     * @var array
     */
    protected $attributeTypes = [];
    /**
     * The object classes
     *
     * @var array
     */
    protected $objectClasses = [];

    /**
     * Parses the schema
     *
     * @return ActiveDirectory Provides a fluid interface
     */
    protected function parseSchema(Ldap\Dn $dn, Ldap\Ldap $ldap)
    {
        parent::parseSchema($dn, $ldap);
        foreach (
            $ldap->search(
                '(objectClass=classSchema)',
                $dn,
                Ldap\Ldap::SEARCH_SCOPE_ONE
            ) as $node
        ) {
            $val                                  = new ObjectClass\ActiveDirectory($node);
            $this->objectClasses[$val->getName()] = $val;
        }
        foreach (
            $ldap->search(
                '(objectClass=attributeSchema)',
                $dn,
                Ldap\Ldap::SEARCH_SCOPE_ONE
            ) as $node
        ) {
            $val                                   = new AttributeType\ActiveDirectory($node);
            $this->attributeTypes[$val->getName()] = $val;
        }

        return $this;
    }

    /**
     * Gets the attribute Types
     *
     * @return array
     */
    public function getAttributeTypes()
    {
        return $this->attributeTypes;
    }

    /**
     * Gets the object classes
     *
     * @return array
     */
    public function getObjectClasses()
    {
        return $this->objectClasses;
    }
}
