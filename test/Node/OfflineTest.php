<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap\Node;

use Laminas\Ldap;
use Laminas\Ldap\Exception\LdapException;
use LaminasTest\Ldap as TestLdap;

/**
 * @group      Laminas_Ldap
 * @group      Ldap\Node
 */
class OfflineTest extends TestLdap\AbstractTestCase
{
    protected function assertLocalDateTimeString($timestamp, $value)
    {
        $tsValue = date('YmdHisO', $timestamp);

        if (date('O', strtotime('20120101'))) {
            // Local timezone is +0000 when DST is off. Laminas_Ldap converts
            // +0000 to "Z" (see Laminas\Ldap\Converter\Converter:toLdapDateTime()), so
            // take account of that here
            $tsValue = str_replace('+0000', 'Z', $tsValue);
        }

        $this->assertEquals($tsValue, $value);
    }

    protected function assertUtcDateTimeString($localTimestamp, $value)
    {
        $localOffset  = date('Z', $localTimestamp);
        $utcTimestamp = $localTimestamp - $localOffset;
        $this->assertEquals(date('YmdHis', $utcTimestamp) . 'Z', $value);
    }

    public function testCreateFromArrayStringDn()
    {
        $data = $this->createTestArrayData();
        $node = Ldap\Node::fromArray($data);
        $this->assertInstanceOf('Laminas\Ldap\Node', $node);
        $this->assertFalse($node->isAttached());
        $this->assertFalse($node->willBeDeleted());
        $this->assertFalse($node->willBeMoved());
        $this->assertTrue($node->isNew());
    }

    public function testCreateFromArrayObjectDn()
    {
        $data       = $this->createTestArrayData();
        $data['dn'] = Ldap\Dn::fromString($data['dn']);
        $node       = Ldap\Node::fromArray($data);
        $this->assertInstanceOf('Laminas\Ldap\Node', $node);
        $this->assertFalse($node->isAttached());
    }

    public function testCreateFromArrayMissingDn()
    {
        $this->expectException(LdapException::class);
        $data = $this->createTestArrayData();
        unset($data['dn']);
        $node = Ldap\Node::fromArray($data);
    }

    public function testCreateFromArrayIllegalDn()
    {
        $this->expectException(LdapException::class);
        $data       = $this->createTestArrayData();
        $data['dn'] = 5;
        $node       = Ldap\Node::fromArray($data);
    }

    public function testCreateFromArrayMalformedDn()
    {
        $this->expectException(LdapException::class);
        $data       = $this->createTestArrayData();
        $data['dn'] = 'name1,cn=name2,dc=example,dc=org';
        $node       = Ldap\Node::fromArray($data);
    }

    public function testCreateFromArrayAndEnsureRdnValues()
    {
        $data       = $this->createTestArrayData();
        $data['dn'] = Ldap\Dn::fromString($data['dn']);
        $node       = Ldap\Node::fromArray($data);
        $this->assertInstanceOf('Laminas\Ldap\Node', $node);
        $this->assertFalse($node->isAttached());
        unset($data['dn']);
        $this->assertEquals($data, $node->getData());
    }

    public function testGetDnString()
    {
        $data = $this->createTestArrayData();
        $node = Ldap\Node::fromArray($data);
        $this->assertEquals($data['dn'], $node->getDnString());
    }

    public function testGetDnArray()
    {
        $data = $this->createTestArrayData();
        $node = Ldap\Node::fromArray($data);
        $exA  = Ldap\Dn::explodeDn($data['dn']);
        $this->assertEquals($exA, $node->getDnArray());
    }

    public function testGetDnObject()
    {
        $data      = $this->createTestArrayData();
        $node      = Ldap\Node::fromArray($data);
        $compareDn = Ldap\Dn::fromString('cn=name,dc=example,dc=org');
        $this->assertEquals($compareDn, $node->getDn());
        $this->assertNotSame($node->getDn(), $node->getDn());
    }

    public function testGetRdnString()
    {
        $node = $this->createTestNode();
        $this->assertEquals('cn=name', $node->getRdnString());
    }

    public function testGetRdnArray()
    {
        $node = $this->createTestNode();
        $this->assertEquals(['cn' => 'name'], $node->getRdnArray());
    }

    public function testSerialize()
    {
        $node      = $this->createTestNode();
        $sdata     = serialize($node);
        $newObject = unserialize($sdata);
        $this->assertEquals($node, $newObject);
    }

    public function testToString()
    {
        $node = $this->createTestNode();
        $this->assertEquals('cn=name,dc=example,dc=org', $node->toString());
        $this->assertEquals('cn=name,dc=example,dc=org', (string) $node);
    }

    public function testToArray()
    {
        $node = $this->createTestNode();
        $this->assertEquals([
                                 'dn'          => 'cn=name,dc=example,dc=org',
                                 'cn'          => ['name'],
                                 'host'        => ['a', 'b', 'c'],
                                 'empty'       => [],
                                 'boolean'     => [true, false],
                                 'objectclass' => ['account', 'top'],
                            ], $node->toArray());
    }

    public function testToJson()
    {
        $node = $this->createTestNode();
        $this->assertEquals('{"dn":"cn=name,dc=example,dc=org",' .
                            '"boolean":[true,false],' .
                            '"cn":["name"],' .
                            '"empty":[],' .
                            '"host":["a","b","c"],' .
                            '"objectclass":["account","top"]}', $node->toJson());
    }

    public function testGetData()
    {
        $data = $this->createTestArrayData();
        $node = Ldap\Node::fromArray($data);
        ksort($data, SORT_STRING);
        unset($data['dn']);
        $this->assertEquals($data, $node->getData());
    }

    public function testGetObjectClass()
    {
        $node = $this->createTestNode();
        $this->assertEquals(['account', 'top'], $node->getObjectClass());
    }

    public function testModifyObjectClass()
    {
        $node = $this->createTestNode();
        $this->assertEquals(['account', 'top'], $node->getObjectClass());

        $node->setObjectClass('domain');
        $this->assertEquals(['domain'], $node->getObjectClass());

        $node->setObjectClass(['account', 'top']);
        $this->assertEquals(['account', 'top'], $node->getObjectClass());

        $node->appendObjectClass('domain');
        $this->assertEquals(['account', 'top', 'domain'], $node->getObjectClass());

        $node->setObjectClass('domain');
        $node->appendObjectClass(['account', 'top']);
        $this->assertEquals(['domain', 'account', 'top'], $node->getObjectClass());
    }

    public function testGetAttributes()
    {
        $node     = $this->createTestNode();
        $expected = [
            'boolean'     => [true, false],
            'cn'          => ['name'],
            'empty'       => [],
            'host'        => ['a', 'b', 'c'],
            'objectclass' => ['account', 'top'],
        ];
        $this->assertEquals($expected, $node->getAttributes());
        $this->assertFalse($node->willBeDeleted());
        $this->assertFalse($node->willBeMoved());
        $this->assertFalse($node->isNew());

        $node->delete();
        $this->assertTrue($node->willBeDeleted());
    }

    public function testAppendToAttributeFirstTime()
    {
        $node = $this->createTestNode();
        $node->appendToAttribute('host', 'newHost');
        $ts = mktime(12, 30, 30, 6, 25, 2008);
        $node->appendToDateTimeAttribute('objectClass', $ts);
        $this->assertEquals('newHost', $node->host[3]);
        $this->assertEquals($ts, $node->getDateTimeAttribute('objectClass', 2));
    }

    public function testExistsAttribute()
    {
        $node = $this->createTestNode();
        $this->assertFalse($node->existsAttribute('nonExistant'));
        $this->assertFalse($node->existsAttribute('empty', false));
        $this->assertTrue($node->existsAttribute('empty', true));

        $node->newEmpty = null;
        $this->assertFalse($node->existsAttribute('newEmpty', false));
        $this->assertTrue($node->existsAttribute('newEmpty', true));

        $node->empty = 'string';
        $this->assertTrue($node->existsAttribute('empty', false));
        $this->assertTrue($node->existsAttribute('empty', true));

        $node->deleteAttribute('empty');
        $this->assertFalse($node->existsAttribute('empty', false));
        $this->assertTrue($node->existsAttribute('empty', true));
    }

    public function testGetSetAndDeleteMethods()
    {
        $node = $this->createTestNode();

        $node->setAttribute('key', 'value1');
        $this->assertEquals('value1', $node->getAttribute('key', 0));
        $node->appendToAttribute('key', 'value2');
        $this->assertEquals('value1', $node->getAttribute('key', 0));
        $this->assertEquals('value2', $node->getAttribute('key', 1));
        $this->assertTrue($node->existsAttribute('key', true));
        $this->assertTrue($node->existsAttribute('key', false));
        $node->deleteAttribute('key');
        $this->assertCount(0, $node->getAttribute('key'));
        $this->assertTrue($node->existsAttribute('key', true));
        $this->assertFalse($node->existsAttribute('key', false));

        $ts = mktime(12, 30, 30, 6, 25, 2008);
        $node->setDateTimeAttribute('key', $ts, false);
        $this->assertLocalDateTimeString($ts, $node->getAttribute('key', 0));
        $this->assertEquals($ts, $node->getDateTimeAttribute('key', 0));
        $node->appendToDateTimeAttribute('key', $ts, true);
        $this->assertLocalDateTimeString($ts, $node->getAttribute('key', 0));
        $this->assertEquals($ts, $node->getDateTimeAttribute('key', 0));
        $this->assertUtcDateTimeString($ts, $node->getAttribute('key', 1));
        $this->assertEquals($ts, $node->getDateTimeAttribute('key', 1));
        $this->assertTrue($node->existsAttribute('key', true));
        $this->assertTrue($node->existsAttribute('key', false));
        $node->deleteAttribute('key');
        $this->assertCount(0, $node->getAttribute('key'));
        $this->assertTrue($node->existsAttribute('key', true));
        $this->assertFalse($node->existsAttribute('key', false));

        $node->setPasswordAttribute('pa$$w0rd', Ldap\Attribute::PASSWORD_HASH_MD5);
        $this->assertEquals('{MD5}bJuLJ96h3bhF+WqiVnxnVA==', $node->getAttribute('userPassword', 0));
        $this->assertTrue($node->existsAttribute('userPassword', true));
        $this->assertTrue($node->existsAttribute('userPassword', false));
        $node->deleteAttribute('userPassword');
        $this->assertCount(0, $node->getAttribute('userPassword'));
        $this->assertTrue($node->existsAttribute('userPassword', true));
        $this->assertFalse($node->existsAttribute('userPassword', false));
    }

    public function testOverloading()
    {
        $node = $this->createTestNode();

        $node->key = 'value1';
        $this->assertEquals('value1', $node->key[0]);
        $this->assertTrue(isset($node->key));
        unset($node->key);
        $this->assertCount(0, $node->key);
        $this->assertFalse(isset($node->key));
    }

    public function testIllegalAttributeAccessRdnAttributeSet()
    {
        $this->expectException(LdapException::class);
        $node     = $this->createTestNode();
        $node->cn = 'test';
    }

    public function testIllegalAttributeAccessDnSet()
    {
        $this->expectException(LdapException::class);
        $node     = $this->createTestNode();
        $node->dn = 'test';
    }

    public function testAttributeAccessDnGet()
    {
        $node = $this->createTestNode();
        $this->assertIsString($node->dn);
        $this->assertEquals($node->getDn()->toString(), $node->dn);
    }

    public function testArrayAccess()
    {
        $node = $this->createTestNode();

        $node['key'] = 'value1';
        $this->assertEquals('value1', $node['key'][0]);
        $this->assertTrue(isset($node['key']));
        unset($node['key']);
        $this->assertCount(0, $node['key']);
        $this->assertFalse(isset($node['key']));
    }

    public function testCreateEmptyNode()
    {
        $dn          = 'cn=name,dc=example,dc=org';
        $objectClass = ['account', 'test', 'inetOrgPerson'];
        $node        = Ldap\Node::create($dn, $objectClass);
        $this->assertEquals($dn, $node->getDnString());
        $this->assertEquals('cn=name', $node->getRdnString());
        $this->assertEquals('name', $node->cn[0]);
        $this->assertEquals($objectClass, $node->objectClass);
        $this->assertFalse($node->willBeDeleted());
        $this->assertFalse($node->willBeMoved());
        $this->assertTrue($node->isNew());

        $node->delete();
        $this->assertTrue($node->willBeDeleted());
    }

    public function testGetChangedData()
    {
        $node        = $this->createTestNode();
        $node->host  = ['d'];
        $node->empty = 'not Empty';
        unset($node->objectClass);
        $changedData = $node->getChangedData();
        $this->assertEquals(['d'], $changedData['host']);
        $this->assertEquals(['not Empty'], $changedData['empty']);
        $this->assertEquals([], $changedData['objectclass']);
    }

    public function testDeleteUnusedAttribute()
    {
        $node = $this->createTestNode();
        $node->deleteAttribute('nonexistent');
        $changedData = $node->getChangedData();
        $this->assertArrayNotHasKey('nonexistent', $changedData);
    }

    public function testRenameNodeString()
    {
        $data = $this->createTestArrayData();
        $node = Ldap\Node::fromArray($data);

        $newDnString = 'cn=test+ou=Lab+uid=tester,cn=name,dc=example,dc=org';
        $node->setDn($newDnString);
        $this->assertEquals($data['dn'], $node->getCurrentDn()->toString());
        $this->assertEquals($newDnString, $node->getDn()->toString());
        $this->assertEquals(['test'], $node->cn);
        $this->assertEquals(['tester'], $node->uid);
        $this->assertEquals(['Lab'], $node->ou);

        $this->assertFalse($node->willBeDeleted());
        $this->assertFalse($node->willBeMoved());
        $this->assertTrue($node->isNew());
    }

    public function testRenameNodeArray()
    {
        $data = $this->createTestArrayData();
        $node = Ldap\Node::fromArray($data);

        $newDnArray = [
            ['uid' => 'tester'],
            ['dc' => 'example'],
            ['dc' => 'org']];

        $node->setDn($newDnArray);
        $this->assertEquals($data['dn'], $node->getCurrentDn()->toString());
        $this->assertEquals($newDnArray, $node->getDn()->toArray());
        $this->assertEquals(['name'], $node->cn);
    }

    public function testRenameNodeDnObject()
    {
        $data = $this->createTestArrayData();
        $node = Ldap\Node::fromArray($data);

        $newDn = Ldap\Dn::fromString('cn=test+ou=Lab+uid=tester,cn=name,dc=example,dc=org');
        $node->setDn($newDn);
        $this->assertEquals($data['dn'], $node->getCurrentDn()->toString());
        $this->assertEquals($newDn, $node->getDn());
        $this->assertEquals(['test'], $node->cn);
        $this->assertEquals(['tester'], $node->uid);
        $this->assertEquals(['Lab'], $node->ou);
    }

    public function testRenameNodeFromDataSource()
    {
        $node        = $this->createTestNode();
        $newDnString = 'cn=test+ou=Lab+uid=tester,cn=name,dc=example,dc=org';
        $node->rename($newDnString);

        $this->assertFalse($node->willBeDeleted());
        $this->assertTrue($node->willBeMoved());
        $this->assertFalse($node->isNew());
    }

    public function testDnObjectCloning()
    {
        $node1 = $this->createTestNode();
        $dn1   = Ldap\Dn::fromString('cn=name2,dc=example,dc=org');
        $node1->setDn($dn1);
        $dn1->prepend(['cn' => 'name']);
        $this->assertNotEquals($dn1->toString(), $node1->getDn()->toString());

        $dn2   = Ldap\Dn::fromString('cn=name2,dc=example,dc=org');
        $node2 = Ldap\Node::create($dn2);
        $dn2->prepend(['cn' => 'name']);
        $this->assertNotEquals($dn2->toString(), $node2->getDn()->toString());

        $dn3   = Ldap\Dn::fromString('cn=name2,dc=example,dc=org');
        $node3 = Ldap\Node::fromArray([
                                           'dn' => $dn3,
                                           'ou' => 'Test'], false);
        $dn3->prepend(['cn' => 'name']);
        $this->assertNotEquals($dn3->toString(), $node3->getDn()->toString());
    }

    public function testGetChanges()
    {
        $node        = $this->createTestNode();
        $node->host  = ['d'];
        $node->empty = 'not Empty';
        unset($node->boolean);
        $changes = $node->getChanges();
        $this->assertEquals([
                                 'add'     => [
                                     'empty' => ['not Empty']
                                 ],
                                 'delete'  => [
                                     'boolean' => []
                                 ],
                                 'replace' => [
                                     'host' => ['d']
                                 ]
                            ], $changes);

        $node       = Ldap\Node::create('uid=test,dc=example,dc=org', ['account']);
        $node->host = 'host';
        unset($node->cn);
        unset($node['sn']);
        $node['givenName'] = 'givenName';
        $node->appendToAttribute('objectClass', 'domain');
        $this->assertEquals([
                                 'uid'         => ['test'],
                                 'objectclass' => ['account', 'domain'],
                                 'host'        => ['host'],
                                 'givenname'   => ['givenName']
                            ], $node->getChangedData());
        $this->assertEquals([
                                 'add'     => [
                                     'uid'         => ['test'],
                                     'objectclass' => ['account', 'domain'],
                                     'host'        => ['host'],
                                     'givenname'   => ['givenName'],
                                 ],
                                 'delete'  => [],
                                 'replace' => []
                            ], $node->getChanges());
    }

    public function testHasValue()
    {
        $node = $this->createTestNode();

        $this->assertTrue($node->attributeHasValue('cn', 'name'));
        $this->assertFalse($node->attributeHasValue('cn', 'noname'));
        $this->assertTrue($node->attributeHasValue('boolean', true));
        $this->assertTrue($node->attributeHasValue('boolean', false));

        $this->assertTrue($node->attributeHasValue('host', ['a', 'b']));
        $this->assertTrue($node->attributeHasValue('host', ['a', 'b', 'c']));
        $this->assertFalse($node->attributeHasValue('host', ['a', 'b', 'c', 'd']));
        $this->assertTrue($node->attributeHasValue('boolean', [true, false]));
    }

    public function testRemoveDuplicates()
    {
        $node           = $this->createTestNode();
        $node->strings1 = ['value1', 'value2', 'value2', 'value3'];
        $node->strings2 = ['value1', 'value2', 'value3', 'value4'];
        $node->boolean1 = [true, true, true, true];
        $node->boolean2 = [true, false, true, false];

        $expected = [
            'cn'          => ['name'],
            'host'        => ['a', 'b', 'c'],
            'empty'       => [],
            'boolean'     => ['TRUE', 'FALSE'],
            'objectclass' => ['account', 'top'],
            'strings1'    => ['value1', 'value2', 'value3'],
            'strings2'    => ['value1', 'value2', 'value3', 'value4'],
            'boolean1'    => ['TRUE'],
            'boolean2'    => ['TRUE', 'FALSE'],
        ];

        $node->removeDuplicatesFromAttribute('strings1');
        $node->removeDuplicatesFromAttribute('strings2');
        $node->removeDuplicatesFromAttribute('boolean1');
        $node->removeDuplicatesFromAttribute('boolean2');
        $this->assertEquals($expected, $node->getData(false));
    }

    public function testRemoveFromAttributeSimple()
    {
        $node       = $this->createTestNode();
        $node->test = ['value1', 'value2', 'value3', 'value3'];
        $node->removeFromAttribute('test', 'value2');
        $this->assertEquals(['value1', 'value3', 'value3'], $node->test);
    }

    public function testRemoveFromAttributeArray()
    {
        $node       = $this->createTestNode();
        $node->test = ['value1', 'value2', 'value3', 'value3'];
        $node->removeFromAttribute('test', ['value1', 'value2']);
        $this->assertEquals(['value3', 'value3'], $node->test);
    }

    public function testRemoveFromAttributeMultipleSimple()
    {
        $node       = $this->createTestNode();
        $node->test = ['value1', 'value2', 'value3', 'value3'];
        $node->removeFromAttribute('test', 'value3');
        $this->assertEquals(['value1', 'value2'], $node->test);
    }

    public function testRemoveFromAttributeMultipleArray()
    {
        $node       = $this->createTestNode();
        $node->test = ['value1', 'value2', 'value3', 'value3'];
        $node->removeFromAttribute('test', ['value1', 'value3']);
        $this->assertEquals(['value2'], $node->test);
    }

    /**
     * Laminas-11611
     */
    public function testRdnAttributesHandleMultiValuedAttribute()
    {
        $data = [
            'dn'          => 'cn=funkygroup,ou=Groupes,dc=domain,dc=local',
            'objectClass' => [
                'groupOfNames',
                'top',
            ],
            'cn'          => [
                'The Funkygroup',
                'funkygroup',
            ],
            'member'      => 'uid=john-doe,ou=Users,dc=domain,dc=local',
        ];

        $node        = Ldap\Node::fromArray($data, true);
        $changedData = $node->getChangedData();
        $this->assertEmpty($changedData);
    }

    /**
     * Laminas-11611
     */
    public function testRdnAttributesHandleMultiValuedAttribute2()
    {
        $data = [
            'dn'          => 'cn=funkygroup,ou=Groupes,dc=domain,dc=local',
            'objectClass' => [
                'groupOfNames',
                'top',
            ],
            'member'      => 'uid=john-doe,ou=Users,dc=domain,dc=local',
        ];

        $node = Ldap\Node::fromArray($data, true);
        $cn   = $node->getAttribute('cn');
        $this->assertEquals([
                                 0 => 'funkygroup'
                            ], $cn);
    }

    /**
     * Laminas-11611
     */
    public function testRdnAttributesHandleMultiValuedAttribute3()
    {
        $data = [
            'dn'          => 'cn=funkygroup,ou=Groupes,dc=domain,dc=local',
            'objectClass' => [
                'groupOfNames',
                'top',
            ],
            'cn'          => [
                0 => 'The Funkygroup'
            ],
            'member'      => 'uid=john-doe,ou=Users,dc=domain,dc=local',
        ];

        $node = Ldap\Node::fromArray($data, true);
        $cn   = $node->getAttribute('cn');
        $this->assertEquals([
                                 0 => 'The Funkygroup',
                                 1 => 'funkygroup',
                            ], $cn);
    }
}
