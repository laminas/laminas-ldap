<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Laminas\Ldap\Exception\LdapException;

use function array_merge;
use function file_get_contents;
use function function_exists;
use function getenv;
use function sprintf;

class ReconnectTest extends AbstractOnlineTestCase
{
    /** @return non-empty-array<string, string> */
    protected static function getStandardOptions(): array
    {
        // Options array setup copied verbatim from
        // AbstractOnlineTestCase::setUpBeforeClass(), where it is unfortunately
        // not readily accessible without refactoring AbstractOnlineTestCase.
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
        return $options;
    }

    protected function setUp(): void
    {
        self::markTestIncomplete('Reconnect test setup is not available');

        parent::setUp();

        if (! getenv('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            $this->markTestSkipped("Laminas_Ldap online tests are not enabled");
        }

        $this->getLDAP()->setOptions(static::getStandardOptions());
    }

    protected function tearDown(): void
    {
        // Make sure we're using a non-expired connection with known settings
        // for each test.
        $this->getLDAP()->disconnect();

        parent::tearDown();
    }

    protected function triggerReconnection(): void
    {
        $entry = $this->getLDAP()->getEntry(
            'uid=' . getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
            ['uid']
        );
        $this->assertEquals(
            getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME'),
            $entry['uid'][0]
        );
        $this->assertEquals(
            0,
            $this->getLDAP()->getReconnectsAttempted()
        );

        $this->causeLdapConnectionFailure();

        $entry = $this->getLDAP()->getEntry(
            'uid=' . getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
            ['uid']
        );
        $this->assertEquals(
            getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME'),
            $entry['uid'][0]
        );

        $this->assertGreaterThan(
            0,
            $this->getLDAP()->getReconnectsAttempted()
        );
    }

    protected function causeLdapConnectionFailure(): void
    {
        $url = sprintf(
            'http://%s:%s/drop_3890.php',
            getenv('TESTS_LAMINAS_LDAP_HOST'),
            getenv('TESTS_LAMINAS_LDAP_SCRIPTS_PORT')
        );
        file_get_contents($url);
    }

    public function testNoReconnectWhenNotRequested(): void
    {
        $this->getLDAP()->setOptions(
            array_merge(
                $this->getLDAP()->getOptions(),
                ['reconnectAttempts' => 0]
            )
        );

        $this->getLDAP()->bind();
        $entry = $this->getLDAP()->getEntry(
            'uid=' . getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
            ['uid']
        );
        $this->assertEquals(
            getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME'),
            $entry['uid'][0]
        );

        $this->causeLdapConnectionFailure();

        $this->assertNull(
            $this->getLDAP()->getEntry(
                'uid=' . getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
                ['uid']
            ),
            'A query on a connection that should have been timed out was honored by the server.'
        );
    }

    public function testReconnectWhenRequested(): void
    {
        $this->getLDAP()->setOptions(
            array_merge(
                $this->getLDAP()->getOptions(),
                ['reconnectAttempts' => 1]
            )
        );

        $this->getLDAP()->bind();
        $this->triggerReconnection();
    }

    public function testMultipleReconnectAttempts(): void
    {
        $this->getLDAP()->setOptions(
            array_merge(
                $this->getLDAP()->getOptions(),
                [
                    'reconnectAttempts' => 2,
                    'port'              => 3899,
                ]
            )
        );

        try {
            $this->getLDAP()->bind();
            $this->assertTrue(false, 'Server listening on unexpected port?');
        } catch (LdapException $e) {
            $this->assertEquals(
                2,
                $this->getLDAP()->getReconnectsAttempted()
            );
        }
    }

    public function testConnectParameterPreservation(): void
    {
        $options = $this->getLDAP()->getOptions();
        unset($options['host']);
        unset($options['port']);
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->connect(
            getenv('TESTS_LAMINAS_LDAP_HOST'),
            getenv('TESTS_LAMINAS_LDAP_PORT')
        );

        $this->triggerReconnection();
    }

    public function testParametersOverridePropertiesDuringReconnect(): void
    {
        $options                      = $this->getLDAP()->getOptions();
        $options['port']             += 9;
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->connect(null, getenv('TESTS_LAMINAS_LDAP_PORT'));
        $this->triggerReconnection();
    }

    /**
     * TODO: Add this test once merged with PR#64, which has SSL support in CI.
     * public function testReconnectionWithSsl()
     * {
     * $options = $this->getLDAP()->getOptions();
     * $options['port'] = getenv('TESTS_LAMINAS_LDAPS_PORT');
     * $options['reconnectAttempts'] = 1;
     * $this->getLDAP()->setOptions($options);
     *
     * $this->getLDAP()->connect(null, null, true);
     * $this->triggerReconnection();
     * }
     */
    public function testAddReconnect(): void
    {
        $options                      = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn   = $this->createDn('ou=TestCreatedOnReconnect,');
        $data = [
            'ou'          => 'TestCreatedOnReconnect',
            'objectClass' => 'organizationalUnit',
        ];

        if ($this->getLDAP()->exists($dn)) {
            $this->getLDAP()->delete($dn);
        }

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());

        $this->causeLdapConnectionFailure();

        $this->getLDAP()->add($dn, $data);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());
        $this->assertEquals(1, $this->getLDAP()->count('ou=TestCreatedOnReconnect'));
        $this->getLDAP()->delete($dn);
        $this->assertEquals(0, $this->getLDAP()->count('ou=TestCreatedOnReconnect'));
    }

    public function testUpdateReconnect(): void
    {
        $options                      = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn   = $this->createDn('ou=TestModifiedOnReconnect,');
        $data = [
            'ou'          => 'TestModifiedOnReconnect',
            'l'           => 'mylocation1',
            'objectClass' => 'organizationalUnit',
        ];

        if ($this->getLDAP()->exists($dn)) {
            $this->getLDAP()->delete($dn);
        }
        $this->getLDAP()->add($dn, $data);
        $entry = $this->getLDAP()->getEntry($dn);

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        $this->causeLdapConnectionFailure();

        $entry['l'] = 'mylocation2';
        $this->getLDAP()->update($dn, $entry);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());
        $entry = $this->getLDAP()->getEntry($dn);
        $this->getLDAP()->delete($dn);
        $this->assertEquals('mylocation2', $entry['l'][0]);
    }

    public function testDeleteReconnect(): void
    {
        $options                      = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn   = $this->createDn('ou=TestDeletedOnReconnect,');
        $data = [
            'ou'          => 'TestDeletedOnReconnect',
            'objectClass' => 'organizationalUnit',
        ];

        if (! $this->getLDAP()->exists($dn)) {
            $this->getLDAP()->add($dn, $data);
        }

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        $this->causeLdapConnectionFailure();

        $this->getLDAP()->delete($dn);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());
    }

    public function testRenameReconnect(): void
    {
        if (! function_exists('ldap_rename')) {
            $this->markTestSkipped("Test would provide no useful coverage
            because the php installation lacks ldap_rename().");
        }
        $options                      = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn   = $this->createDn('ou=TestRenameOnReconnect,');
        $data = [
            'ou'          => 'TestRenameOnReconnect',
            'objectClass' => 'organizationalUnit',
        ];

        if (! $this->getLDAP()->exists($dn)) {
            $this->getLDAP()->add($dn, $data);
        }

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        $this->causeLdapConnectionFailure();

        $newDn = $this->createDn('ou=TestRenamedOnReconnect');
        $this->getLDAP()->rename($dn, $newDn);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());

        $this->getLDAP()->delete($newDn, true);
    }

    public function testErroneousModificationDoesNotTriggerReconnect(): void
    {
        $options                      = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn   = $this->createDn('ou=DoesNotExistReconnect,');
        $data = [
            'ou'          => 'DoesNotExistReconnect',
            'objectClass' => 'organizationalUnit',
        ];

        try {
            $this->getLDAP()->update($dn, $data);
            $this->assertFalse(true, 'Update of nonexistent DN succeeded?');
        } catch (LdapException $e) {
            $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        }
    }
}
