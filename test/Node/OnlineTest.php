<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Node;

use Laminas\Ldap;
use Laminas\Ldap\Exception\ExceptionInterface;
use Laminas\Ldap\Node;
use Laminas\Ldap\Node\Collection;
use LaminasTest\Ldap as TestLdap;

use function getenv;
use function key;
use function serialize;
use function unserialize;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Node
 */
class OnlineTest extends TestLdap\AbstractOnlineTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareLDAPServer();
    }

    protected function tearDown(): void
    {
        $this->cleanupLDAPServer();
        parent::tearDown();
    }

    public function testLoadFromLDAP()
    {
        $dn   = $this->createDn('ou=Test1,');
        $node = Ldap\Node::fromLDAP($dn, $this->getLDAP());
        $this->assertInstanceOf(Node::class, $node);
        $this->assertTrue($node->isAttached());
    }

    public function testChangeReadOnlySystemAttributes()
    {
        $node = $this->getLDAP()->getBaseNode();

        try {
            $node->setAttribute('createTimestamp', false);
            $this->fail('Expected exception for modification of read-only attribute createTimestamp');
        } catch (ExceptionInterface $e) {
            $this->assertEquals('Cannot change attribute because it\'s read-only', $e->getMessage());
        }
        try {
            $node->createTimestamp = false;
            $this->fail('Expected exception for modification of read-only attribute createTimestamp');
        } catch (ExceptionInterface $e) {
            $this->assertEquals('Cannot change attribute because it\'s read-only', $e->getMessage());
        }
        try {
            $node['createTimestamp'] = false;
            $this->fail('Expected exception for modification of read-only attribute createTimestamp');
        } catch (ExceptionInterface $e) {
            $this->assertEquals('Cannot change attribute because it\'s read-only', $e->getMessage());
        }
        try {
            $node->appendToAttribute('createTimestamp', 'value');
            $this->fail('Expected exception for modification of read-only attribute createTimestamp');
        } catch (ExceptionInterface $e) {
            $this->assertEquals('Cannot change attribute because it\'s read-only', $e->getMessage());
        }
        try {
            $rdn  = $node->getRdnArray(Ldap\Dn::ATTR_CASEFOLD_LOWER);
            $attr = key($rdn);
            $node->deleteAttribute($attr);
            $this->fail('Expected exception for modification of read-only attribute ' . $attr);
        } catch (ExceptionInterface $e) {
            $this->assertEquals('Cannot change attribute because it\'s part of the RDN', $e->getMessage());
        }
    }

    public function testLoadFromLDAPIllegalEntry()
    {
        $dn = $this->createDn('ou=Test99,');
        $this->expectException(ExceptionInterface::class);
        $node = Ldap\Node::fromLDAP($dn, $this->getLDAP());
    }

    public function testDetachAndReattach()
    {
        $dn   = $this->createDn('ou=Test1,');
        $node = Ldap\Node::fromLDAP($dn, $this->getLDAP());
        $this->assertInstanceOf(Node::class, $node);
        $this->assertTrue($node->isAttached());
        $node->detachLDAP();
        $this->assertFalse($node->isAttached());
        $node->attachLDAP($this->getLDAP());
        $this->assertTrue($node->isAttached());
    }

    public function testSerialize()
    {
        $dn        = $this->createDn('ou=Test1,');
        $node      = Ldap\Node::fromLDAP($dn, $this->getLDAP());
        $sdata     = serialize($node);
        $newObject = unserialize($sdata);
        $this->assertFalse($newObject->isAttached());
        $this->assertTrue($node->isAttached());
        $this->assertEquals($sdata, serialize($newObject));
    }

    public function testAttachToInvalidLDAP()
    {
        $data = [
            'dn'          => 'ou=name,dc=example,dc=org',
            'ou'          => ['name'],
            'l'           => ['a', 'b', 'c'],
            'objectClass' => ['organizationalUnit', 'top'],
        ];
        $node = Ldap\Node::fromArray($data);
        $this->assertFalse($node->isAttached());
        $this->expectException(ExceptionInterface::class);
        $node->attachLDAP($this->getLDAP());
    }

    public function testAttachToValidLDAP()
    {
        $data = [
            'dn'          => $this->createDn('ou=name,'),
            'ou'          => ['name'],
            'l'           => ['a', 'b', 'c'],
            'objectClass' => ['organizationalUnit', 'top'],
        ];
        $node = Ldap\Node::fromArray($data);
        $this->assertFalse($node->isAttached());
        $node->attachLDAP($this->getLDAP());
        $this->assertTrue($node->isAttached());
    }

    public function testExistsDn()
    {
        $data  = [
            'dn'          => $this->createDn('ou=name,'),
            'ou'          => ['name'],
            'l'           => ['a', 'b', 'c'],
            'objectClass' => ['organizationalUnit', 'top'],
        ];
        $node1 = Ldap\Node::fromArray($data);
        $node1->attachLDAP($this->getLDAP());
        $this->assertFalse($node1->exists());
        $dn    = $this->createDn('ou=Test1,');
        $node2 = Ldap\Node::fromLDAP($dn, $this->getLDAP());
        $this->assertTrue($node2->exists());
    }

    public function testReload()
    {
        $dn   = $this->createDn('ou=Test1,');
        $node = Ldap\Node::fromLDAP($dn, $this->getLDAP());
        $node->reload();
        $this->assertEquals($dn, $node->getDn()->toString());
        $this->assertEquals('ou=Test1', $node->getRdnString());
    }

    public function testGetNode()
    {
        $dn   = $this->createDn('ou=Test1,');
        $node = $this->getLDAP()->getNode($dn);
        $this->assertEquals($dn, $node->getDn()->toString());
        $this->assertEquals("Test1", $node->getAttribute('ou', 0));
    }

    public function testGetIllegalNode()
    {
        $dn = $this->createDn('ou=Test99,');
        $this->expectException(ExceptionInterface::class);
        $node = $this->getLDAP()->getNode($dn);
    }

    public function testGetBaseNode()
    {
        $node = $this->getLDAP()->getBaseNode();
        $this->assertEquals(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'), $node->getDnString());

        $dn = Ldap\Dn::fromString(
            getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'),
            Ldap\Dn::ATTR_CASEFOLD_LOWER
        );
        $this->assertEquals($dn[0]['ou'], $node->getAttribute('ou', 0));
    }

    public function testSearchSubtree()
    {
        $node  = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $items = $node->searchSubtree(
            '(objectClass=organizationalUnit)',
            Ldap\Ldap::SEARCH_SCOPE_SUB,
            [],
            'ou'
        );
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertEquals(3, $items->count());

        $i   = 0;
        $dns = [
            $this->createDn('ou=Node,'),
            $this->createDn('ou=Test1,ou=Node,'),
            $this->createDn('ou=Test2,ou=Node,'),
        ];
        foreach ($items as $key => $node) {
            $key = Ldap\Dn::fromString($key)->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER);
            $this->assertEquals($dns[$i], $key);
            if ($i === 0) {
                $this->assertEquals('Node', $node->ou[0]);
            } else {
                $this->assertEquals('Test' . $i, $node->ou[0]);
            }
            $this->assertEquals($key, $node->getDnString(Ldap\Dn::ATTR_CASEFOLD_LOWER));
            $i++;
        }
        $this->assertEquals(3, $i);
    }

    public function testCountSubtree()
    {
        $node = $this->getLDAP()->getNode(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'));
        $this->assertEquals(9, $node->countSubtree(
            '(objectClass=organizationalUnit)',
            Ldap\Ldap::SEARCH_SCOPE_SUB
        ));
    }

    public function testCountChildren()
    {
        $node = $this->getLDAP()->getNode(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'));
        $this->assertEquals(6, $node->countChildren());
        $node = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $this->assertEquals(2, $node->countChildren());
    }

    public function testSearchChildren()
    {
        $node = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $this->assertEquals(2, $node->searchChildren('(objectClass=*)', [], 'ou')->count());
        $node = $this->getLDAP()->getNode(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'));
        $this->assertEquals(6, $node->searchChildren('(objectClass=*)', [], 'ou')->count());
    }

    public function testGetParent()
    {
        $node  = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $pnode = $node->getParent();
        $this->assertEquals(
            Ldap\Dn::fromString(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'))
                ->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER),
            $pnode->getDnString(Ldap\Dn::ATTR_CASEFOLD_LOWER)
        );
    }

    public function testGetNonexistentParent()
    {
        $node = $this->getLDAP()->getNode(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'));
        $this->expectException(ExceptionInterface::class);
        $pnode = $node->getParent();
    }

    public function testLoadFromLDAPWithDnObject()
    {
        $dn   = Ldap\Dn::fromString($this->createDn('ou=Test1,'));
        $node = Ldap\Node::fromLDAP($dn, $this->getLDAP());
        $this->assertInstanceOf(Node::class, $node);
        $this->assertTrue($node->isAttached());
    }
}
