<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Laminas\Ldap;

use function array_reverse;
use function getenv;
use function substr;

/**
 * @group      Laminas_Ldap
 */
abstract class AbstractOnlineTestCase extends AbstractTestCase
{
    private static ?Ldap\Ldap $ldap;

    public static function setUpBeforeClass(): void
    {
        $options = [
            'host'     => getenv('TESTS_LAMINAS_LDAP_HOST'),
            'username' => getenv('TESTS_LAMINAS_LDAP_USERNAME'),
            'password' => getenv('TESTS_LAMINAS_LDAP_PASSWORD'),
            'baseDn'   => getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'),
        ];
        if (getenv('TESTS_LAMINAS_LDAP_PORT') && getenv('TESTS_LAMINAS_LDAP_PORT') !== '389') {
            $options['port'] = getenv('TESTS_LAMINAS_LDAP_PORT');
        }
        if (getenv('TESTS_LAMINAS_LDAP_USE_START_TLS')) {
            $options['useStartTls'] = getenv('TESTS_LAMINAS_LDAP_USE_START_TLS');
        }
        if (getenv('TESTS_LAMINAS_LDAP_USE_SSL')) {
            $options['useSsl'] = getenv('TESTS_LAMINAS_LDAP_USE_SSL');
        }
        if (getenv('TESTS_LAMINAS_LDAP_BIND_REQUIRES_DN')) {
            $options['bindRequiresDn'] = getenv('TESTS_LAMINAS_LDAP_BIND_REQUIRES_DN');
        }
        if (getenv('TESTS_LAMINAS_LDAP_ACCOUNT_FILTER_FORMAT')) {
            $options['accountFilterFormat'] = getenv('TESTS_LAMINAS_LDAP_ACCOUNT_FILTER_FORMAT');
        }
        if (getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME')) {
            $options['accountDomainName'] = getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME');
        }
        if (getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME_SHORT')) {
            $options['accountDomainNameShort'] = getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME_SHORT');
        }

        self::$ldap = new Ldap\Ldap($options);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$ldap !== null) {
            self::$ldap->disconnect();
            self::$ldap = null;
        }
    }

    /** @var array<string, array<string, string>> */
    private $nodes;

    /**
     * @return Ldap\Ldap
     */
    protected function getLDAP()
    {
        assert(self::$ldap instanceof Ldap\Ldap);
        return self::$ldap;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! getenv('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            $this->markTestSkipped("Laminas_Ldap online tests are not enabled");
        }

        $this->getLDAP()->bind();
    }

    protected function createDn(string $dn): string
    {
        if (substr($dn, -1) !== ',') {
            $dn .= ',';
        }
        $dn .= getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE');

        return Ldap\Dn::fromString($dn)->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER);
    }

    protected function prepareLDAPServer(): void
    {
        $this->nodes = [
            $this->createDn('ou=Node,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Node",
                "postalCode"  => "1234",
            ],
            $this->createDn('ou=Test1,ou=Node,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Test1",
            ],
            $this->createDn('ou=Test2,ou=Node,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Test2",
            ],
            $this->createDn('ou=Test1,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Test1",
                "l"           => "e",
            ],
            $this->createDn('ou=Test2,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Test2",
                "l"           => "d",
            ],
            $this->createDn('ou=Test3,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Test3",
                "l"           => "c",
            ],
            $this->createDn('ou=Test4,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Test4",
                "l"           => "b",
            ],
            $this->createDn('ou=Test5,')
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Test5",
                "l"           => "a",
            ],
        ];

        $ldap = $this->getLDAP()->getResource();
        foreach ($this->nodes as $dn => $entry) {
            ldap_add($ldap, $dn, $entry);
        }
    }

    protected function cleanupLDAPServer()
    {
        if (! getenv('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            return;
        }
        $ldap = $this->getLDAP()->getResource();
        foreach (array_reverse($this->nodes) as $dn => $entry) {
            ldap_delete($ldap, $dn);
        }
    }
}
