<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Ldap\Node;

use Laminas\Ldap;

/**
 * Laminas\Ldap\Node\Schema provides a simple data-container for the Schema node.
 */
class Schema extends AbstractNode
{
    const OBJECTCLASS_TYPE_UNKNOWN    = 0;
    const OBJECTCLASS_TYPE_STRUCTURAL = 1;
    const OBJECTCLASS_TYPE_ABSTRACT   = 3;
    const OBJECTCLASS_TYPE_AUXILIARY  = 4;

    /**
     * Factory method to create the Schema node.
     *
     * @param  \Laminas\Ldap\Ldap $ldap
     * @return Schema
     */
    public static function create(Ldap\Ldap $ldap)
    {
        $dn   = $ldap->getRootDse()->getSchemaDn();
        $data = $ldap->getEntry($dn, ['*', '+'], true);
        switch ($ldap->getRootDse()->getServerType()) {
            case RootDse::SERVER_TYPE_ACTIVEDIRECTORY:
                return new Schema\ActiveDirectory($dn, $data, $ldap);
            case RootDse::SERVER_TYPE_OPENLDAP:
                return new Schema\OpenLdap($dn, $data, $ldap);
            case RootDse::SERVER_TYPE_EDIRECTORY:
            default:
                return new static($dn, $data, $ldap);
        }
    }

    /**
     * Constructor.
     *
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param  \Laminas\Ldap\Dn   $dn
     * @param  array           $data
     * @param  \Laminas\Ldap\Ldap $ldap
     */
    protected function __construct(Ldap\Dn $dn, array $data, Ldap\Ldap $ldap)
    {
        parent::__construct($dn, $data, true);
        $this->parseSchema($dn, $ldap);
    }

    /**
     * Parses the schema
     *
     * @param  \Laminas\Ldap\Dn   $dn
     * @param  \Laminas\Ldap\Ldap $ldap
     * @return Schema Provides a fluid interface
     */
    protected function parseSchema(Ldap\Dn $dn, Ldap\Ldap $ldap)
    {
        return $this;
    }

    /**
     * Gets the attribute Types
     *
     * @return array
     */
    public function getAttributeTypes()
    {
        return [];
    }

    /**
     * Gets the object classes
     *
     * @return array
     */
    public function getObjectClasses()
    {
        return [];
    }
}
