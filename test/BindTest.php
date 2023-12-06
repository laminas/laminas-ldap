<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Laminas\Ldap;
use Laminas\Ldap\Exception;
use Laminas\Ldap\Exception\LdapException;
use LDAP\Connection;
use PHPUnit\Framework\TestCase;

use function getenv;
use function putenv;
use function strstr;

/* Note: The ldap_connect function does not actually try to connect. This
 * is why many tests attempt to bind with invalid credentials. If the
 * bind returns 'Invalid credentials' we know the transport related work
 * was successful.
 */

/**
 * @group      Laminas_Ldap
 */
class BindTest extends TestCase
{
    /**
     * @var array{
     *     host: string,
     *     username: string,
     *     password: string,
     *     baseDn: string,
     *     port?: numeric-string,
     *     useStartTls?: bool|string,
     *     bindRequiresDn?: bool|string,
     *     accountFilterFormat?: string,
     *     accountDomainNameShort?: string,
     * }
     */
    protected $options;
    /** @var string */
    protected $altPrincipalName;
    /** @var string */
    protected $altUsername;
    /** @var bool|string */
    protected $bindRequiresDn = false;

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
        if (getenv('TESTS_LAMINAS_LDAP_PORT')) {
            $this->options['port'] = getenv('TESTS_LAMINAS_LDAP_PORT');
        }
        if (getenv('TESTS_LAMINAS_LDAP_USE_START_TLS')) {
            $this->options['useStartTls'] = getenv('TESTS_LAMINAS_LDAP_USE_START_TLS');
        }
        if (getenv('TESTS_LAMINAS_LDAP_USE_SSL')) {
            $this->options['useSsl'] = getenv('TESTS_LAMINAS_LDAP_USE_SSL');
        }
        if (getenv('TESTS_LAMINAS_LDAP_BIND_REQUIRES_DN')) {
            $this->options['bindRequiresDn'] = getenv('TESTS_LAMINAS_LDAP_BIND_REQUIRES_DN');
        }
        if (getenv('TESTS_LAMINAS_LDAP_ACCOUNT_FILTER_FORMAT')) {
            $this->options['accountFilterFormat'] = getenv('TESTS_LAMINAS_LDAP_ACCOUNT_FILTER_FORMAT');
        }
        if (getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME')) {
            $this->options['accountDomainName'] = getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME');
        }
        if (getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME_SHORT')) {
            $this->options['accountDomainNameShort'] = getenv('TESTS_LAMINAS_LDAP_ACCOUNT_DOMAIN_NAME_SHORT');
        }
        if (getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME')) {
            $this->altUsername = getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME');
        }
        $this->altPrincipalName = getenv('TESTS_LAMINAS_LDAP_ALT_PRINCIPAL_NAME');

        if (isset($this->options['bindRequiresDn'])) {
            $this->bindRequiresDn = $this->options['bindRequiresDn'];
        }
    }

    public function testEmptyOptionsBind()
    {
        $ldap = new Ldap\Ldap([]);
        try {
            $ldap->bind();
            $this->fail('Expected exception for empty options');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('A host parameter is required', $zle->getMessage());
        }
    }

    public function testAnonymousBind()
    {
        $options = $this->options;
        unset($options['password']);

        $ldap = new Ldap\Ldap($options);
        try {
            $ldap->bind();
        } catch (Exception\LdapException $zle) {
            // or I guess the server doesn't allow unauthenticated binds
            $this->assertStringContainsString('unauthenticated bind', $zle->getMessage());
        }
    }

    public function testNoBaseDnBind()
    {
        $options = $this->options;
        unset($options['baseDn']);
        $options['bindRequiresDn'] = true;

        $ldap = new Ldap\Ldap($options);
        try {
            $ldap->bind('invalid', 'ignored');
            $this->fail('Expected exception for baseDn missing');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Base DN not set', $zle->getMessage());
        }
    }

    public function testNoDomainNameBind()
    {
        $options = $this->options;
        unset($options['accountDomainName']);
        $options['bindRequiresDn']       = false;
        $options['accountCanonicalForm'] = Ldap\Ldap::ACCTNAME_FORM_PRINCIPAL;

        $ldap = new Ldap\Ldap($options);
        try {
            $ldap->bind('invalid', 'ignored');
            $this->fail('Expected exception for missing accountDomainName');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Option required: accountDomainName', $zle->getMessage());
        }
    }

    public function testPlainBind()
    {
        $ldap = new Ldap\Ldap($this->options);
        $ldap->bind();
        $this->assertNotNull($ldap->getResource());
    }

    public function testConnectBind()
    {
        $ldap = new Ldap\Ldap($this->options);
        $ldap->connect()->bind();
        $this->assertNotNull($ldap->getResource());
    }

    public function testExplicitParamsBind()
    {
        $options  = $this->options;
        $username = $options['username'];
        $password = $options['password'];

        unset($options['username']);
        unset($options['password']);

        $ldap = new Ldap\Ldap($options);
        $ldap->bind($username, $password);
        $this->assertNotNull($ldap->getResource());
    }

    public function testRequiresDnBind()
    {
        $options = $this->options;

        $options['bindRequiresDn'] = true;

        $ldap = new Ldap\Ldap($options);
        try {
            $ldap->bind($this->altUsername, 'invalid');
            $this->fail('Expected exception not thrown');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString('Invalid credentials', $zle->getMessage());
        }
    }

    public function testRequiresDnWithoutDnBind()
    {
        $options = $this->options;

        $options['bindRequiresDn'] = true;

        unset($options['username']);

        $ldap = new Ldap\Ldap($options);
        try {
            $ldap->bind($this->altPrincipalName);
            $this->fail('Expected exception not thrown');
        } catch (Exception\LdapException $zle) {
            /* Note that if your server actually allows anonymous binds this test will fail.
             */
            if (getenv('TESTS_LAMINAS_LDAP_ANONYMOUS_BIND_ALLOWED')) {
                $this->markTestSkipped('Anonymous bind needs to be disallowed for this test');
            }

            $this->assertStringContainsString('Failed to retrieve DN', $zle->getMessage());
        }
    }

    public function testBindWithEmptyPassword()
    {
        $options                       = $this->options;
        $options['allowEmptyPassword'] = false;
        $ldap                          = new Ldap\Ldap($options);
        try {
            $ldap->bind($this->altUsername, '');
            $this->fail('Expected exception for empty password');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString(
                'Empty password not allowed - see allowEmptyPassword option.',
                $zle->getMessage()
            );
        }

        $options['allowEmptyPassword'] = true;
        $ldap                          = new Ldap\Ldap($options);
        try {
            $ldap->bind($this->altUsername, '');
        } catch (Exception\LdapException $zle) {
            if (
                $zle->getMessage() ===
                'Empty password not allowed - see allowEmptyPassword option.'
            ) {
                $this->fail('Exception for empty password');
            } else {
                $message = $zle->getMessage();
                $this->assertTrue(strstr($message, 'Invalid credentials')
                        || strstr($message, 'Server is unwilling to perform'));
                return;
            }
        }
        $this->assertNotNull($ldap->getResource());
    }

    public function testBindWithoutDnUsernameAndDnRequired()
    {
        $options                   = $this->options;
        $options['username']       = getenv('TESTS_LAMINAS_LDAP_ALT_USERNAME');
        $options['bindRequiresDn'] = true;
        $ldap                      = new Ldap\Ldap($options);
        try {
            $ldap->bind();
            $this->fail('Expected exception for empty password');
        } catch (Exception\LdapException $zle) {
            $this->assertStringContainsString(
                'Binding requires username in DN form',
                $zle->getMessage()
            );
        }
    }

    /**
     * @group Laminas-8259
     */
    public function testBoundUserIsFalseIfNotBoundToLDAP()
    {
        $ldap = new Ldap\Ldap($this->options);
        $this->assertFalse($ldap->getBoundUser());
    }

    /**
     * @group Laminas-8259
     */
    public function testBoundUserIsReturnedAfterBinding()
    {
        $ldap = new Ldap\Ldap($this->options);
        $ldap->bind();
        $this->assertEquals(getenv('TESTS_LAMINAS_LDAP_USERNAME'), $ldap->getBoundUser());
    }

    /**
     * @group Laminas-8259
     */
    public function testResourceIsAlwaysReturned()
    {
        $ldap = new Ldap\Ldap($this->options);
        $this->assertInstanceOf(Connection::class, $ldap->getResource());
        $this->assertEquals(getenv('TESTS_LAMINAS_LDAP_USERNAME'), $ldap->getBoundUser());
    }

    protected function getSslLdap(array $options): Ldap\Ldap
    {
        $port = '636';
        if (getenv('TESTS_LAMINAS_LDAPS_PORT')) {
            $port = getenv('TESTS_LAMINAS_LDAPS_PORT');
        }
        $options['useSsl'] = true;
        $options['port']   = $port;

        return new Ldap\Ldap($options);
    }

    /** @runInSeparateProcess */
    public function testSaslBind()
    {
        // The certificate seems not to be "good enough" for SASL-bind
        putenv('LDAPTLS_REQCERT=never');

        $options             = $this->options;
        $options['saslOpts'] = [
            'sasl_mech' => 'EXTERNAL',
        ];
        $ldap                = $this->getSslLdap($options);
        $ldap->bind();

        $this->assertEquals(
            getenv('TESTS_LAMINAS_LDAP_USERNAME'),
            $ldap->getBoundUser()
        );
    }

    /** @runInSeparateProcess */
    public function testSaslBindNoExplicitUsername()
    {
        // The certificate seems not to be "good enough" for SASL-bind
        putenv('LDAPTLS_REQCERT=never');

        // Username should not be required, as it can be derived from the
        // client certificate.
        $options = $this->options;
        unset($options['username']);
        unset($options['password']);
        $options['saslOpts'] = [
            'sasl_mech' => 'EXTERNAL',
        ];
        $ldap                = $this->getSslLdap($options);
        $ldap->bind();
        // The "pass" expectation here is just that no exception was thrown.
        // Getting to this point in the code is the "definition of pass".
        // phpunit will flag this as a risky test if we do not assert anything,
        // so assert something about the LDAP object.
        $this->assertEquals(0, $ldap->getLastErrorCode());
    }

    /**
     * @see https://net.educause.edu/ir/library/pdf/csd4875.pdf
     */
    public function testBindWithNullPassword()
    {
        $ldap = new Ldap\Ldap($this->options);
        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Invalid credentials');
        $ldap->bind($this->altUsername, "\0invalidpassword");
    }
}
