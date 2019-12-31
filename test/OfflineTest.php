<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Config;
use Laminas\Ldap;
use Laminas\Ldap\Exception;

/**
 * @group      Laminas_Ldap
 * @requires extension ldap
 */
class OfflineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Laminas\Ldap\Ldap instance
     *
     * @var Ldap\Ldap
     */
    protected $ldap = null;

    /**
     * Setup operations run prior to each test method:
     *
     * * Creates an instance of Laminas\Ldap\Ldap
     *
     * @return void
     */
    public function setUp()
    {
        $this->ldap = new Ldap\Ldap();
    }

    /**
     * @return void
     */
    public function testInvalidOptionResultsInException()
    {
        $optionName = 'invalid';
        try {
            $this->ldap->setOptions([$optionName => 'irrelevant']);
            $this->fail('Expected Laminas\Ldap\Exception\LdapException not thrown');
        } catch (Exception\LdapException $e) {
            $this->assertEquals("Unknown Laminas\Ldap\Ldap option: $optionName", $e->getMessage());
        }
    }

    public function testOptionsGetter()
    {
        $options = [
            'host'     => getenv('TESTS_LAMINAS_LDAP_HOST'),
            'username' => getenv('TESTS_LAMINAS_LDAP_USERNAME'),
            'password' => getenv('TESTS_LAMINAS_LDAP_PASSWORD'),
            'baseDn'   => getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
        ];
        $ldap    = new Ldap\Ldap($options);
        $this->assertEquals([
                                 'host'                   => getenv('TESTS_LAMINAS_LDAP_HOST'),
                                 'port'                   => 0,
                                 'useSsl'                 => false,
                                 'username'               => getenv('TESTS_LAMINAS_LDAP_USERNAME'),
                                 'password'               => getenv('TESTS_LAMINAS_LDAP_PASSWORD'),
                                 'bindRequiresDn'         => false,
                                 'baseDn'                 => getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
                                 'accountCanonicalForm'   => null,
                                 'accountDomainName'      => null,
                                 'accountDomainNameShort' => null,
                                 'accountFilterFormat'    => null,
                                 'allowEmptyPassword'     => false,
                                 'useStartTls'            => false,
                                 'optReferrals'           => false,
                                 'tryUsernameSplit'       => true,
                                 'networkTimeout'         => null,
                            ], $ldap->getOptions()
        );
    }

    public function testConfigObject()
    {
        $config = new Config\Config([
                                         'host'     => getenv('TESTS_LAMINAS_LDAP_HOST'),
                                         'username' => getenv('TESTS_LAMINAS_LDAP_USERNAME'),
                                         'password' => getenv('TESTS_LAMINAS_LDAP_PASSWORD'),
                                         'baseDn'   => getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
                                    ]);
        $ldap   = new Ldap\Ldap($config);
        $this->assertEquals([
                                 'host'                   => getenv('TESTS_LAMINAS_LDAP_HOST'),
                                 'port'                   => 0,
                                 'useSsl'                 => false,
                                 'username'               => getenv('TESTS_LAMINAS_LDAP_USERNAME'),
                                 'password'               => getenv('TESTS_LAMINAS_LDAP_PASSWORD'),
                                 'bindRequiresDn'         => false,
                                 'baseDn'                 => getenv('TESTS_LAMINAS_LDAP_BASE_DN'),
                                 'accountCanonicalForm'   => null,
                                 'accountDomainName'      => null,
                                 'accountDomainNameShort' => null,
                                 'accountFilterFormat'    => null,
                                 'allowEmptyPassword'     => false,
                                 'useStartTls'            => false,
                                 'optReferrals'           => false,
                                 'tryUsernameSplit'       => true,
                                 'networkTimeout'         => null,
                            ], $ldap->getOptions()
        );
    }
}
