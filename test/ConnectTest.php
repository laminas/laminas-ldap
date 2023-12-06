<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Laminas\Ldap;
use Laminas\Ldap\Exception;
use PHPUnit\Framework\TestCase;

use function array_key_exists;
use function array_merge;
use function getenv;
use function rand;
use function strpos;

/* Note: The ldap_connect function does not actually try to connect. This
 * is why many tests attempt to bind with invalid credentials. If the
 * bind returns 'Invalid credentials' we know the transport related work
 * was successful.
 */

/**
 * @group      Laminas_Ldap
 */
class ConnectTest extends TestCase
{
    /**
     * @var array{
     *     host: string,
     *     username: string,
     *     password: string,
     *     baseDn: string,
     *     port?: numeric-string,
     *     useSsl?: string|bool,
     * }
     */
    protected $options;

    protected function setUp(): void
    {
        if (! getenv('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            $this->markTestSkipped("Laminas_Ldap online tests are not enabled");
        }

        $this->options = [
            'host'     => getenv('TESTS_LAMINAS_LDAP_HOST'),
            'username' => getenv('TESTS_LAMINAS_LDAP_USERNAME'),
            'password' => getenv('TESTS_LAMINAS_LDAP_PASSWORD'),
            'baseDn'   => getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
        ];
        if (getenv('TESTS_LAMINAS_LDAP_PORT') && getenv('TESTS_LAMINAS_LDAP_PORT') !== '389') {
            $this->options['port'] = getenv('TESTS_LAMINAS_LDAP_PORT');
        }
        if (getenv('TESTS_LAMINAS_LDAP_USE_SSL')) {
            $this->options['useSsl'] = getenv('TESTS_LAMINAS_LDAP_USE_SSL');
        }
    }

    public function testEmptyOptionsConnect(): void
    {
        $ldap = new Ldap\Ldap([]);
        try {
            $ldap->connect();
            $this->fail('Expected exception for empty options');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('host parameter is required', $zle->getMessage());
        }
    }

    public function testUnknownHostConnect(): void
    {
        $ldap = new Ldap\Ldap(['host' => 'bogus.example.com']);
        try {
            // connect doesn't actually try to connect until bind is called
            $ldap->connect()->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for unknown host');
        } catch (Exception\LdapException $zle) {
            $alternatives = [
                'Can\'t contact LDAP server',
                'Failed to connect to LDAP server',
            ];
            $message      = $zle->getMessage();

            foreach ($alternatives as $alternative) {
                if (strpos($message, $alternative) !== false) {
                    $this->assertTrue(true, 'Found one of the expected failure messages');
                    return;
                }
            }

            $this->fail('Didn\'t find an expected failure message');
        }
    }

    public function testPlainConnect(): void
    {
        $ldap = new Ldap\Ldap($this->options);
        try {
            // Connect doesn't actually try to connect until bind is called
            // but if we get 'Invalid credentials' then we know the connect
            // succeeded.
            $ldap->connect()->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for invalid username');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
        }
    }

    public function testNetworkTimeoutConnect(): void
    {
        $networkTimeout = 1;
        $ldap           = new Ldap\Ldap(array_merge($this->options, ['networkTimeout' => $networkTimeout]));

        $ldap->connect();
        ldap_get_option($ldap->getResource(), LDAP_OPT_NETWORK_TIMEOUT, $actual);
        $this->assertEquals($networkTimeout, $actual);
    }

    public function testExplicitParamsConnect(): void
    {
        $host = getenv('TESTS_LAMINAS_LDAP_HOST');
        $port = 0;
        if (getenv('TESTS_LAMINAS_LDAP_PORT') && getenv('TESTS_LAMINAS_LDAP_PORT') !== '389') {
            $port = getenv('TESTS_LAMINAS_LDAP_PORT');
        }
        $useSsl = false;
        if (getenv('TESTS_LAMINAS_LDAP_USE_SSL')) {
            $useSsl = getenv('TESTS_LAMINAS_LDAP_USE_SSL');
        }

        $ldap = new Ldap\Ldap();
        try {
            $ldap->connect($host, $port, $useSsl)
                ->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for invalid username');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
        }
    }

    public function testExplicitPortConnect(): void
    {
        $port = 389;
        if (getenv('TESTS_LAMINAS_LDAP_PORT')) {
            $port = getenv('TESTS_LAMINAS_LDAP_PORT');
        }
        if (getenv('TESTS_LAMINAS_LDAP_USE_SSL')) {
            $port = 636;
            if (getenv('TESTS_LAMINAS_LDAPS_PORT')) {
                $port = getenv('TESTS_LAMINAS_LDAPS_PORT');
            }
        }

        $ldap = new Ldap\Ldap($this->options);
        try {
            $ldap->connect(null, $port)
                ->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for invalid username');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
        }
    }

    public function testExplicitNetworkTimeoutConnect(): void
    {
        $networkTimeout = rand(1, 100);
        if (array_key_exists('networkTimeout', $this->options)) {
            unset($this->options['networkTimeout']);
        }

        $ldap = new Ldap\Ldap($this->options);
        $ldap->connect(null, null, null, null, $networkTimeout);

        ldap_get_option($ldap->getResource(), LDAP_OPT_NETWORK_TIMEOUT, $actual);
        $this->assertEquals($networkTimeout, $actual);
    }

    public function testBadPortConnect(): void
    {
        $options         = $this->options;
        $options['port'] = 10;

        $ldap = new Ldap\Ldap($options);
        try {
            $ldap->connect()->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for unknown username');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Can\'t contact LDAP server', $zle->getMessage());
        }
    }

    public function testSetOptionsConnect(): void
    {
        $ldap = new Ldap\Ldap();
        $ldap->setOptions($this->options);
        try {
            $ldap->connect()->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for invalid username');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
        }
    }

    public function testMultiConnect(): void
    {
        $ldap = new Ldap\Ldap($this->options);
        for ($i = 0; $i < 3; $i++) {
            try {
                $ldap->connect()->bind('CN=ignored,DC=example,DC=com', 'ignored');
                $this->fail('Expected exception for unknown username');
            } catch (Exception\LdapException $zle) {
                $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
            }
        }
    }

    public function testDisconnect(): void
    {
        $ldap = new Ldap\Ldap($this->options);
        for ($i = 0; $i < 3; $i++) {
            $ldap->disconnect();
            try {
                $ldap->connect()->bind('CN=ignored,DC=example,DC=com', 'ignored');
                $this->fail('Expected exception for unknown username');
            } catch (Exception\LdapException $zle) {
                $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
            }
        }
    }

    public function testGetErrorCode(): void
    {
        $ldap = new Ldap\Ldap($this->options);
        try {
            // Connect doesn't actually try to connect until bind is called
            // but if we get 'Invalid credentials' then we know the connect
            // succeeded.
            $ldap->connect()->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for invalid username');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Invalid credentials', $zle->getMessage());

            $this->assertEquals(0x31, $zle->getCode());
            $this->assertEquals(0x0, $ldap->getLastErrorCode());
        }
    }

    /**
     * @group Laminas-8274
     */
    public function testConnectWithUri(): void
    {
        $host = getenv('TESTS_LAMINAS_LDAP_HOST');
        $port = 0;
        if (getenv('TESTS_LAMINAS_LDAP_PORT') && getenv('TESTS_LAMINAS_LDAP_PORT') !== '389') {
            $port = getenv('TESTS_LAMINAS_LDAP_PORT');
        }
        $useSsl = false;
        if (getenv('TESTS_LAMINAS_LDAP_USE_SSL')) {
            $useSsl = getenv('TESTS_LAMINAS_LDAP_USE_SSL');
        }
        if ($useSsl) {
            $host = 'ldaps://' . $host;
        } else {
            $host = 'ldap://' . $host;
        }
        if ($port) {
            $host .= ':' . $port;
        }

        $ldap = new Ldap\Ldap();
        try {
            $ldap->connect($host)
                ->bind('CN=ignored,DC=example,DC=com', 'ignored');
            $this->fail('Expected exception for invalid username');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
        }
    }

    /**
     * @see https://github.com/zendframework/zend-ldap/issues/19
     *
     * @dataProvider connectionWithoutPortInOptionsArrayProvider
     */
    public function testConnectionWithoutPortInOptionsArray(string $host, bool $ssl, string $connectUri): void
    {
        $options = [
            'host'   => $host,
            'useSsl' => $ssl,
        ];

        $ldap = new Ldap\Ldap($options);
        $ldap->connect();
        // bind should throw the expected exception
        // The purpose of the test is to see that the $connectURI string is found
        // in the exception message.
        $this->expectException(Exception\LdapException::class);
        $this->expectExceptionMessage($connectUri);
        $ldap->bind();
    }

    /** @return non-empty-list<array{string, bool, string}> */
    public function connectionWithoutPortInOptionsArrayProvider(): array
    {
        // purposely set the host to something invalid so that the test will throw an exception
        $host = 'unknown';
        return [
            // ['host', 'boolean whether to use LDAPS or not', 'connectionURI'],
            [$host, false, 'ldap://' . $host . ':389'],
            [$host, true, 'ldaps://' . $host . ':636'],
        ];
    }
}
