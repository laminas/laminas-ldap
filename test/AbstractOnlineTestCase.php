<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Ldap;

/**
 * @category   Laminas
 * @package    Laminas_Ldap
 * @subpackage UnitTests
 * @group      Laminas_Ldap
 */
abstract class AbstractOnlineTestCase extends AbstractTestCase
{
    /**
     * @var Ldap\Ldap
     */
    private $ldap;

    /**
     * @var array
     */
    private $nodes;

    /**
     * @return Ldap\Ldap
     */
    protected function getLDAP()
    {
        return $this->ldap;
    }

    protected function setUp()
    {
        if (!constant('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            $this->markTestSkipped("Laminas_Ldap online tests are not enabled");
        }

        $options = array(
            'host'     => TESTS_LAMINAS_LDAP_HOST,
            'username' => TESTS_LAMINAS_LDAP_USERNAME,
            'password' => TESTS_LAMINAS_LDAP_PASSWORD,
            'baseDn'   => TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE,
        );
        if (defined('TESTS_LAMINAS_LDAP_PORT') && TESTS_LAMINAS_LDAP_PORT != 389) {
            $options['port'] = TESTS_LAMINAS_LDAP_PORT;
        }
        if (defined('TESTS_LAMINAS_LDAP_USE_START_TLS')) {
            $options['useStartTls'] = TESTS_LAMINAS_LDAP_USE_START_TLS;
        }
        if (defined('TESTS_LAMINAS_LDAP_USE_SSL')) {
            $options['useSsl'] = TESTS_LAMINAS_LDAP_USE_SSL;
        }
        if (defined('TESTS_LAMINAS_LDAP_BIND_REQUIRES_DN')) {
            $options['bindRequiresDn'] = TESTS_LAMINAS_LDAP_BIND_REQUIRES_DN;
        }
        if (defined('TESTS_LAMINAS_LDAP_ACCOUNT_FILTER_FORMAT')) {
            $options['accountFilterFormat'] = TESTS_LAMINAS_LDAP_ACCOUNT_FILTER_FORMAT;
        }
        if (defined('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME')) {
            $options['accountDomainName'] = TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME;
        }
        if (defined('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME_SHORT')) {
            $options['accountDomainNameShort'] = TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME_SHORT;
        }

        $this->ldap = new Ldap\Ldap($options);
        $this->ldap->bind();
    }

    protected function tearDown()
    {
        if ($this->ldap !== null) {
            $this->ldap->disconnect();
            $this->ldap = null;
        }
    }

    protected function createDn($dn)
    {
        if (substr($dn, -1) !== ',') {
            $dn .= ',';
        }
        $dn = $dn . TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE;

        return Ldap\Dn::fromString($dn)->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER);
    }

    protected function prepareLDAPServer()
    {
        $this->nodes = array(
            $this->createDn('ou=Node,')          =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Node",
                  "postalCode"  => "1234"),
            $this->createDn('ou=Test1,ou=Node,') =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Test1"),
            $this->createDn('ou=Test2,ou=Node,') =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Test2"),
            $this->createDn('ou=Test1,')         =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Test1",
                  "l"           => "e"),
            $this->createDn('ou=Test2,')         =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Test2",
                  "l"           => "d"),
            $this->createDn('ou=Test3,')         =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Test3",
                  "l"           => "c"),
            $this->createDn('ou=Test4,')         =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Test4",
                  "l"           => "b"),
            $this->createDn('ou=Test5,')         =>
            array("objectClass" => "organizationalUnit",
                  "ou"          => "Test5",
                  "l"           => "a"),
        );

        $ldap = $this->ldap->getResource();
        foreach ($this->nodes as $dn => $entry) {
            ldap_add($ldap, $dn, $entry);
        }
    }

    protected function cleanupLDAPServer()
    {
        if (!constant('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            return;
        }
        $ldap = $this->ldap->getResource();
        foreach (array_reverse($this->nodes) as $dn => $entry) {
            ldap_delete($ldap, $dn);
        }
    }
}
