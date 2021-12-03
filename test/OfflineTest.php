<?php

namespace LaminasTest\Ldap;

use Laminas\Config;
use Laminas\Ldap;
use Laminas\Ldap\Exception\LdapException;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Ldap
 * @requires extension ldap
 */
class OfflineTest extends TestCase
{
    use PHPMock;

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
    protected function setUp(): void
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
        } catch (LdapException $e) {
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
                                 'reconnectAttempts'      => 0,
                                 'networkTimeout'         => null,
                                 'saslOpts'               => null,
                            ], $ldap->getOptions());
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
                                 'reconnectAttempts'      => 0,
                                 'networkTimeout'         => null,
                                 'saslOpts'               => null,
                            ], $ldap->getOptions());
    }

    /**
     * @dataProvider removingAttributesProvider
     */
    public function testRemovingAttributes(
        $dn,
        $attributes,
        $allowEmptyAttributes,
        $expectedDn,
        $expectedAttributesToRemove
    ) {
        $ldap_mod_del = $this->getFunctionMock('Laminas\\Ldap', "ldap_mod_del");
        $ldap_mod_del->expects($this->once())
                     ->with(
                         $this->isNull(),
                         $this->equalTo($expectedDn),
                         $this->equalTo($expectedAttributesToRemove)
                     )
                     ->willReturn(true);

        $ldap = new \Laminas\Ldap\Ldap();
        $this->assertSame($ldap, $ldap->deleteAttributes($dn, $attributes, $allowEmptyAttributes));
    }

    public function removingAttributesProvider()
    {
        return [
            // Description => [dn, attributes, allow empty attributes, expected dn, expected attributes to remove]
            'every attribute is used' => [
                'foo',
                ['foo' => 'bar'],
                false,
                'foo',
                ['foo' => 'bar']
            ],
            'Empty baz is removed' => [
                'foo',
                ['foo' => 'bar', 'baz' => []],
                false,
                'foo',
                ['foo' => 'bar']
            ],
            'Empty baz is kept due to set $emptyAll-parameter' => [
                'foo',
                ['foo' => 'bar', 'baz' => []],
                true,
                'foo',
                ['foo' => 'bar', 'baz' => []]
            ],
            'DN is provided as DN-Object, not string' => [
                \Laminas\Ldap\Dn::fromString('dc=foo'),
                ['foo' => 'bar', 'baz' => []],
                true,
                'dc=foo',
                ['foo' => 'bar', 'baz' => []]
            ],
        ];
    }

    public function testRemovingAttributesFails()
    {
        $ldap_mod_del = $this->getFunctionMock('Laminas\\Ldap', 'ldap_mod_del');
        $ldap_mod_del->expects($this->once())
                     ->willReturn(false);

        $ldap = new \Laminas\Ldap\Ldap();
        $this->expectException(LdapException::class);
        $ldap->deleteAttributes('foo', ['bar']);
    }

    /**
     * @dataProvider removingAttributesProvider
     */
    public function testAddingAttributes(
        $dn,
        $attributes,
        $allowEmptyAttributes,
        $expectedDn,
        $expectedAttributesToRemove
    ) {
        $ldap_mod_add = $this->getFunctionMock('Laminas\\Ldap', "ldap_mod_add");
        $ldap_mod_add->expects($this->once())
                     ->with(
                         $this->isNull(),
                         $this->equalTo($expectedDn),
                         $this->equalTo($expectedAttributesToRemove)
                     )
                     ->willReturn(true);

        $ldap = new \Laminas\Ldap\Ldap();
        $this->assertSame($ldap, $ldap->addAttributes($dn, $attributes, $allowEmptyAttributes));
    }

    public function testAddingAttributesFails()
    {
        $ldap_mod_del = $this->getFunctionMock('Laminas\\Ldap', 'ldap_mod_add');
        $ldap_mod_del->expects($this->once())
                     ->willReturn(false);

        $ldap = new \Laminas\Ldap\Ldap();
        $this->expectException(LdapException::class);
        $ldap->addAttributes('foo', ['bar']);
    }

    /**
     * @dataProvider removingAttributesProvider
     */
    public function testUpdatingAttributes(
        $dn,
        $attributes,
        $allowEmptyAttributes,
        $expectedDn,
        $expectedAttributesToRemove
    ) {
        $ldap_mod_upd = $this->getFunctionMock('Laminas\\Ldap', "ldap_mod_replace");
        $ldap_mod_upd->expects($this->once())
                     ->with(
                         $this->isNull(),
                         $this->equalTo($expectedDn),
                         $this->equalTo($expectedAttributesToRemove)
                     )
                     ->willReturn(true);

        $ldap = new \Laminas\Ldap\Ldap();
        $this->assertSame($ldap, $ldap->updateAttributes($dn, $attributes, $allowEmptyAttributes));
    }

    public function testUpdatingAttributesFails()
    {
        $ldap_mod_upd = $this->getFunctionMock('Laminas\\Ldap', 'ldap_mod_replace');
        $ldap_mod_upd->expects($this->once())
                     ->willReturn(false);

        $ldap = new \Laminas\Ldap\Ldap();
        $this->expectException(LdapException::class);
        $ldap->updateAttributes('foo', ['bar']);
    }
}
