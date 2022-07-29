<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use InvalidArgumentException;
use Laminas\Ldap;
use Laminas\Ldap\Exception\LdapException;
use stdClass;

use function array_merge;

/**
 * @group      Laminas_Ldap
 */
class CrudTest extends AbstractOnlineTestCase
{
    public function testAddAndDelete()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => 'TestCreated',
            'objectClass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->add($dn, $data);
            $this->assertEquals(1, $this->getLDAP()->count('ou=TestCreated'));
            $this->getLDAP()->delete($dn);
            $this->assertEquals(0, $this->getLDAP()->count('ou=TestCreated'));
        } catch (LdapException $e) {
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testUpdate()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => 'TestCreated',
            'l'           => 'mylocation1',
            'objectClass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->add($dn, $data);
            $entry = $this->getLDAP()->getEntry($dn);
            $this->assertEquals('mylocation1', $entry['l'][0]);
            $entry['l'] = 'mylocation2';
            $this->getLDAP()->update($dn, $entry);
            $entry = $this->getLDAP()->getEntry($dn);
            $this->getLDAP()->delete($dn);
            $this->assertEquals('mylocation2', $entry['l'][0]);
        } catch (LdapException $e) {
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testIllegalAdd()
    {
        $dn   = $this->createDn('ou=TestCreated,ou=Node2,');
        $data = [
            'ou'          => 'TestCreated',
            'objectClass' => 'organizationalUnit',
        ];
        $this->expectException(LdapException::class);
        $this->getLDAP()->add($dn, $data);
    }

    public function testIllegalUpdate()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => 'TestCreated',
            'objectclass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->add($dn, $data);
            $entry                  = $this->getLDAP()->getEntry($dn);
            $entry['objectclass'][] = 'inetOrgPerson';

            $exThrown = false;
            try {
                $this->getLDAP()->update($dn, $entry);
            } catch (LdapException $e) {
                $exThrown = true;
            }
            $this->getLDAP()->delete($dn);
            if (! $exThrown) {
                $this->fail('no exception thrown while illegally updating entry');
            }
        } catch (LdapException $e) {
            $this->fail($e->getMessage());
        }

        // This test "manually" handles which exceptions are expected where.
        // So it does make any "assert*" calls, and does not set any expected exception.
        // Because of this, phpunit will flag this as a risky test,
        // so assert something about the LDAP object.
        $this->assertEquals(0, $this->getLDAP()->getLastErrorCode());
    }

    public function testIllegalDelete()
    {
        $dn = $this->createDn('ou=TestCreated,');
        $this->expectException(LdapException::class);
        $this->getLDAP()->delete($dn);
    }

    public function testDeleteRecursively()
    {
        $topDn = $this->createDn('ou=RecursiveTest,');
        $dn    = $topDn;
        $data  = [
            'ou'          => 'RecursiveTest',
            'objectclass' => 'organizationalUnit',
        ];
        $this->getLDAP()->add($dn, $data);
        for ($level = 1; $level <= 5; $level++) {
            $name = 'Level' . $level;
            $dn   = 'ou=' . $name . ',' . $dn;
            $data = [
                'ou'          => $name,
                'objectclass' => 'organizationalUnit',
            ];
            $this->getLDAP()->add($dn, $data);
            for ($item = 1; $item <= 5; $item++) {
                $uid   = 'Item' . $item;
                $idn   = 'ou=' . $uid . ',' . $dn;
                $idata = [
                    'ou'          => $uid,
                    'objectclass' => 'organizationalUnit',
                ];
                $this->getLDAP()->add($idn, $idata);
            }
        }

        $exCaught = false;
        try {
            $this->getLDAP()->delete($topDn, false);
        } catch (LdapException $e) {
            $exCaught = true;
        }
        $this->assertTrue(
            $exCaught,
            'Execption not raised when deleting item with children without specifiying recursive delete'
        );
        $this->getLDAP()->delete($topDn, true);
        $this->assertFalse($this->getLDAP()->exists($topDn));
    }

    public function testSave()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => 'TestCreated',
            'objectclass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->save($dn, $data);
            $this->assertTrue($this->getLDAP()->exists($dn));
            $data['l'] = 'mylocation1';
            $this->getLDAP()->save($dn, $data);
            $this->assertTrue($this->getLDAP()->exists($dn));
            $entry = $this->getLDAP()->getEntry($dn);
            $this->getLDAP()->delete($dn);
            $this->assertEquals('mylocation1', $entry['l'][0]);
        } catch (LdapException $e) {
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testPrepareLDAPEntryArray()
    {
        $data = [
            'a1' => 'TestCreated',
            'a2' => 'account',
            'a3' => null,
            'a4' => '',
            'a5' => ['TestCreated'],
            'a6' => ['account'],
            'a7' => [null],
            'a8' => [''],
            'a9' => ['', null, 'account', '', null, 'TestCreated', '', null],
        ];
        Ldap\Ldap::prepareLDAPEntryArray($data);
        $expected = [
            'a1' => ['TestCreated'],
            'a2' => ['account'],
            'a3' => [],
            'a4' => [],
            'a5' => ['TestCreated'],
            'a6' => ['account'],
            'a7' => [],
            'a8' => [],
            'a9' => ['account', 'TestCreated'],
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * @group Laminas-7888
     */
    public function testZeroValueMakesItThroughSanitationProcess()
    {
        $data = [
            'string'       => '0',
            'integer'      => 0,
            'stringArray'  => ['0'],
            'integerArray' => [0],
            'null'         => null,
            'empty'        => '',
            'nullArray'    => [null],
            'emptyArray'   => [''],
        ];
        Ldap\Ldap::prepareLDAPEntryArray($data);
        $expected = [
            'string'       => ['0'],
            'integer'      => ['0'],
            'stringarray'  => ['0'],
            'integerarray' => ['0'],
            'null'         => [],
            'empty'        => [],
            'nullarray'    => [],
            'emptyarray'   => [],
        ];
        $this->assertEquals($expected, $data);
    }

    public function testPrepareLDAPEntryArrayArrayData()
    {
        $data = [
            'a1' => [['account']],
        ];
        $this->expectException(InvalidArgumentException::class);
        Ldap\Ldap::prepareLDAPEntryArray($data);
    }

    public function testPrepareLDAPEntryArrayObjectData()
    {
        $class    = new stdClass();
        $class->a = 'b';
        $data     = [
            'a1' => [$class],
        ];
        $this->expectException(InvalidArgumentException::class);
        Ldap\Ldap::prepareLDAPEntryArray($data);
    }

    public function testAddWithDnObject()
    {
        $dn   = Ldap\Dn::fromString($this->createDn('ou=TestCreated,'));
        $data = [
            'ou'          => 'TestCreated',
            'objectclass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->add($dn, $data);
            $this->assertEquals(1, $this->getLDAP()->count('ou=TestCreated'));
            $this->getLDAP()->delete($dn);
        } catch (LdapException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testUpdateWithDnObject()
    {
        $dn   = Ldap\Dn::fromString($this->createDn('ou=TestCreated,'));
        $data = [
            'ou'          => 'TestCreated',
            'l'           => 'mylocation1',
            'objectclass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->add($dn, $data);
            $entry = $this->getLDAP()->getEntry($dn);
            $this->assertEquals('mylocation1', $entry['l'][0]);
            $entry['l'] = 'mylocation2';
            $this->getLDAP()->update($dn, $entry);
            $entry = $this->getLDAP()->getEntry($dn);
            $this->getLDAP()->delete($dn);
            $this->assertEquals('mylocation2', $entry['l'][0]);
        } catch (LdapException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSaveWithDnObject()
    {
        $dn   = Ldap\Dn::fromString($this->createDn('ou=TestCreated,'));
        $data = [
            'ou'          => 'TestCreated',
            'objectclass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->save($dn, $data);
            $this->assertTrue($this->getLDAP()->exists($dn));
            $data['l'] = 'mylocation1';
            $this->getLDAP()->save($dn, $data);
            $this->assertTrue($this->getLDAP()->exists($dn));
            $entry = $this->getLDAP()->getEntry($dn);
            $this->getLDAP()->delete($dn);
            $this->assertEquals('mylocation1', $entry['l'][0]);
        } catch (LdapException $e) {
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testAddObjectClass()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => 'TestCreated',
            'l'           => 'mylocation1',
            'objectClass' => 'organizationalUnit',
        ];
        try {
            $this->getLDAP()->add($dn, $data);
            $entry                       = $this->getLDAP()->getEntry($dn);
            $entry['objectclass'][]      = 'domainRelatedObject';
            $entry['associatedDomain'][] = 'domain';
            $this->getLDAP()->update($dn, $entry);
            $entry = $this->getLDAP()->getEntry($dn);
            $this->getLDAP()->delete($dn);

            $this->assertEquals('domain', $entry['associateddomain'][0]);
            $this->assertContains('organizationalUnit', $entry['objectclass']);
            $this->assertContains('domainRelatedObject', $entry['objectclass']);
        } catch (LdapException $e) {
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testRemoveObjectClass()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'associatedDomain' => 'domain',
            'ou'               => 'TestCreated',
            'l'                => 'mylocation1',
            'objectClass'      => ['organizationalUnit', 'domainRelatedObject'],
        ];
        try {
            $this->getLDAP()->add($dn, $data);
            $entry                     = $this->getLDAP()->getEntry($dn);
            $entry['objectclass']      = 'organizationalUnit';
            $entry['associatedDomain'] = null;
            $this->getLDAP()->update($dn, $entry);
            $entry = $this->getLDAP()->getEntry($dn);
            $this->getLDAP()->delete($dn);

            $this->assertArrayNotHasKey('associateddomain', $entry);
            $this->assertContains('organizationalUnit', $entry['objectclass']);
            $this->assertNotContains('domainRelatedObject', $entry['objectclass']);
        } catch (LdapException $e) {
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group Laminas-9564
     */
    public function testAddingEntryWithMissingRdnAttribute()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'objectClass' => ['organizationalUnit'],
        ];
        try {
            $this->getLdap()->add($dn, $data);
            $entry = $this->getLdap()->getEntry($dn);
            $this->getLdap()->delete($dn);
            $this->assertEquals(['TestCreated'], $entry['ou']);
        } catch (LdapException $e) {
            if ($this->getLdap()->exists($dn)) {
                $this->getLdap()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group Laminas-9564
     */
    public function testAddingEntryWithMissingRdnAttributeValue()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => ['SecondOu'],
            'objectClass' => ['organizationalUnit'],
        ];
        try {
            $this->getLdap()->add($dn, $data);
            $entry = $this->getLdap()->getEntry($dn);
            $this->getLdap()->delete($dn);
            $this->assertEquals(['TestCreated', 'SecondOu'], $entry['ou']);
        } catch (LdapException $e) {
            if ($this->getLdap()->exists($dn)) {
                $this->getLdap()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group Laminas-9564
     */
    public function testAddingEntryThatHasMultipleValuesOnRdnAttribute()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => ['TestCreated', 'SecondOu'],
            'objectClass' => ['organizationalUnit'],
        ];
        try {
            $this->getLdap()->add($dn, $data);
            $entry = $this->getLdap()->getEntry($dn);
            $this->getLdap()->delete($dn);
            $this->assertEquals(['TestCreated', 'SecondOu'], $entry['ou']);
        } catch (LdapException $e) {
            if ($this->getLdap()->exists($dn)) {
                $this->getLdap()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group Laminas-9564
     */
    public function testUpdatingEntryWithAttributeThatIsAnRdnAttribute()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => ['TestCreated'],
            'objectClass' => ['organizationalUnit'],
        ];
        try {
            $this->getLdap()->add($dn, $data);
            $entry = $this->getLdap()->getEntry($dn);

            $data = ['ou' => array_merge($entry['ou'], ['SecondOu'])];
            $this->getLdap()->update($dn, $data);
            $entry = $this->getLdap()->getEntry($dn);
            $this->getLdap()->delete($dn);
            $this->assertEquals(['TestCreated', 'SecondOu'], $entry['ou']);
        } catch (LdapException $e) {
            if ($this->getLdap()->exists($dn)) {
                $this->getLdap()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group Laminas-9564
     */
    public function testUpdatingEntryWithRdnAttributeValueMissingInData()
    {
        $dn   = $this->createDn('ou=TestCreated,');
        $data = [
            'ou'          => ['TestCreated'],
            'objectClass' => ['organizationalUnit'],
        ];
        try {
            $this->getLdap()->add($dn, $data);
            $entry = $this->getLdap()->getEntry($dn);

            $data = ['ou' => 'SecondOu'];
            $this->getLdap()->update($dn, $data);
            $entry = $this->getLdap()->getEntry($dn);
            $this->getLdap()->delete($dn);
            $this->assertEquals(['TestCreated', 'SecondOu'], $entry['ou']);
        } catch (LdapException $e) {
            if ($this->getLdap()->exists($dn)) {
                $this->getLdap()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }
}
