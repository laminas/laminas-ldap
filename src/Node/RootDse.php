<?php

namespace Laminas\Ldap\Node;

use Laminas\Ldap;
use Laminas\Ldap\Dn;

/**
 * Laminas\Ldap\Node\RootDse provides a simple data-container for the RootDse node.
 */
class RootDse extends AbstractNode
{
    public const SERVER_TYPE_GENERIC         = 1;
    public const SERVER_TYPE_OPENLDAP        = 2;
    public const SERVER_TYPE_ACTIVEDIRECTORY = 3;
    public const SERVER_TYPE_EDIRECTORY      = 4;

    /**
     * Factory method to create the RootDse.
     *
     * @return RootDse
     */
    public static function create(Ldap\Ldap $ldap)
    {
        $dn   = Ldap\Dn::fromString('');
        $data = $ldap->getEntry($dn, ['*', '+'], true);
        if (isset($data['domainfunctionality'])) {
            return new RootDse\ActiveDirectory($dn, $data);
        } elseif (isset($data['dsaname'])) {
            return new RootDse\eDirectory($dn, $data);
        } elseif (
            isset($data['structuralobjectclass'])
            && $data['structuralobjectclass'][0] === 'OpenLDAProotDSE'
        ) {
            return new RootDse\OpenLdap($dn, $data);
        }

        return new static($dn, $data);
    }

    /**
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param array         $data
     */
    protected function __construct(Ldap\Dn $dn, array $data)
    {
        parent::__construct($dn, $data, true);
    }

    /**
     * Gets the namingContexts.
     *
     * @return array
     */
    public function getNamingContexts()
    {
        return $this->getAttribute('namingContexts', null);
    }

    /**
     * Gets the subschemaSubentry.
     *
     * @return string|null
     */
    public function getSubschemaSubentry()
    {
        return $this->getAttribute('subschemaSubentry', 0);
    }

    /**
     * Determines if the version is supported
     *
     * @param  string|int|array $versions version(s) to check
     * @return bool
     */
    public function supportsVersion($versions)
    {
        return $this->attributeHasValue('supportedLDAPVersion', $versions);
    }

    /**
     * Determines if the sasl mechanism is supported
     *
     * @param  string|array $mechlist SASL mechanisms to check
     * @return bool
     */
    public function supportsSaslMechanism($mechlist)
    {
        return $this->attributeHasValue('supportedSASLMechanisms', $mechlist);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_GENERIC;
    }

    /**
     * Returns the schema DN
     *
     * @return Dn
     */
    public function getSchemaDn()
    {
        $schemaDn = $this->getSubschemaSubentry();
        return Ldap\Dn::fromString($schemaDn);
    }
}
