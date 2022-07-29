<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Node;

use Laminas\Ldap;
use LaminasTest\Ldap as TestLdap;

use function array_key_exists;
use function array_merge;
use function count;
use function getenv;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Node
 */
class UpdateTest extends TestLdap\AbstractOnlineTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareLDAPServer();
    }

    protected function tearDown(): void
    {
        if (! getenv('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            return;
        }

        foreach ($this->getLDAP()->getBaseNode()->searchChildren('objectClass=*') as $child) {
            $this->getLDAP()->delete($child->getDn(), true);
        }

        parent::tearDown();
    }

    protected function stripActiveDirectorySystemAttributes(array &$entry): void
    {
        $adAttributes = [
            'distinguishedname',
            'instancetype',
            'name',
            'objectcategory',
            'objectguid',
            'usnchanged',
            'usncreated',
            'whenchanged',
            'whencreated',
        ];
        foreach ($adAttributes as $attr) {
            if (array_key_exists($attr, $entry)) {
                unset($entry[$attr]);
            }
        }

        if (array_key_exists('objectclass', $entry) && count($entry['objectclass']) > 0) {
            if ($entry['objectclass'][0] !== 'top') {
                $entry['objectclass'] = array_merge(['top'], $entry['objectclass']);
            }
        }
    }

    public function testSimpleUpdateOneValue()
    {
        $dn       = $this->createDn('ou=Test1,');
        $node1    = Ldap\Node::fromLDAP($dn, $this->getLDAP());
        $node1->l = 'f';
        $node1->update();

        $this->assertTrue($this->getLDAP()->exists($dn));
        $node2 = $this->getLDAP()->getEntry($dn);
        $this->stripActiveDirectorySystemAttributes($node2);
        unset($node2['dn']);
        $node1 = $node1->getData(false);
        $this->stripActiveDirectorySystemAttributes($node1);
        $this->assertEquals($node2, $node1);
    }

    public function testAddNewNode()
    {
        $dn       = $this->createDn('ou=Test,');
        $node1    = Ldap\Node::create($dn, ['organizationalUnit']);
        $node1->l = 'a';
        $node1->update($this->getLDAP());

        $this->assertTrue($this->getLDAP()->exists($dn));
        $node2 = $this->getLDAP()->getEntry($dn);
        $this->stripActiveDirectorySystemAttributes($node2);
        unset($node2['dn']);
        $node1 = $node1->getData(false);
        $this->stripActiveDirectorySystemAttributes($node1);
        $this->assertEquals($node2, $node1);
    }

    public function testMoveExistingNode()
    {
        $dnOld    = $this->createDn('ou=Test1,');
        $dnNew    = $this->createDn('ou=Test,');
        $node1    = Ldap\Node::fromLDAP($dnOld, $this->getLDAP());
        $node1->l = 'f';
        $node1->setDn($dnNew);
        $node1->update();

        $this->assertFalse($this->getLDAP()->exists($dnOld));
        $this->assertTrue($this->getLDAP()->exists($dnNew));
        $node2 = $this->getLDAP()->getEntry($dnNew);
        $this->stripActiveDirectorySystemAttributes($node2);
        unset($node2['dn']);
        $node1 = $node1->getData(false);
        $this->stripActiveDirectorySystemAttributes($node1);
        $this->assertEquals($node2, $node1);
    }

    public function testMoveNewNode()
    {
        $dnOld    = $this->createDn('ou=Test,');
        $dnNew    = $this->createDn('ou=TestNew,');
        $node1    = Ldap\Node::create($dnOld, ['organizationalUnit']);
        $node1->l = 'a';
        $node1->setDn($dnNew);
        $node1->update($this->getLDAP());

        $this->assertFalse($this->getLDAP()->exists($dnOld));
        $this->assertTrue($this->getLDAP()->exists($dnNew));
        $node2 = $this->getLDAP()->getEntry($dnNew);
        $this->stripActiveDirectorySystemAttributes($node2);
        unset($node2['dn']);
        $node1 = $node1->getData(false);
        $this->stripActiveDirectorySystemAttributes($node1);
        $this->assertEquals($node2, $node1);
    }

    public function testModifyDeletedNode()
    {
        $dn    = $this->createDn('ou=Test1,');
        $node1 = Ldap\Node::create($dn, ['organizationalUnit']);
        $node1->delete();
        $node1->update($this->getLDAP());

        $this->assertFalse($this->getLDAP()->exists($dn));

        $node1->l = 'a';
        $node1->update();

        $this->assertFalse($this->getLDAP()->exists($dn));
    }

    public function testAddDeletedNode()
    {
        $dn    = $this->createDn('ou=Test,');
        $node1 = Ldap\Node::create($dn, ['organizationalUnit']);
        $node1->delete();
        $node1->update($this->getLDAP());

        $this->assertFalse($this->getLDAP()->exists($dn));
    }

    public function testMoveDeletedExistingNode()
    {
        $dnOld = $this->createDn('ou=Test1,');
        $dnNew = $this->createDn('ou=Test,');
        $node1 = Ldap\Node::fromLDAP($dnOld, $this->getLDAP());
        $node1->setDn($dnNew);
        $node1->delete();
        $node1->update();

        $this->assertFalse($this->getLDAP()->exists($dnOld));
        $this->assertFalse($this->getLDAP()->exists($dnNew));
    }

    public function testMoveDeletedNewNode()
    {
        $dnOld = $this->createDn('ou=Test,');
        $dnNew = $this->createDn('ou=TestNew,');
        $node1 = Ldap\Node::create($dnOld, ['organizationalUnit']);
        $node1->setDn($dnNew);
        $node1->delete();
        $node1->update($this->getLDAP());

        $this->assertFalse($this->getLDAP()->exists($dnOld));
        $this->assertFalse($this->getLDAP()->exists($dnNew));
    }

    public function testMoveNode()
    {
        $dnOld = $this->createDn('ou=Test1,');
        $dnNew = $this->createDn('ou=Test,');

        $node = Ldap\Node::fromLDAP($dnOld, $this->getLDAP());
        $node->setDn($dnNew);
        $node->update();
        $this->assertFalse($this->getLDAP()->exists($dnOld));
        $this->assertTrue($this->getLDAP()->exists($dnNew));

        $node = Ldap\Node::fromLDAP($dnNew, $this->getLDAP());
        $node->move($dnOld);
        $node->update();
        $this->assertFalse($this->getLDAP()->exists($dnNew));
        $this->assertTrue($this->getLDAP()->exists($dnOld));

        $node = Ldap\Node::fromLDAP($dnOld, $this->getLDAP());
        $node->rename($dnNew);
        $node->update();
        $this->assertFalse($this->getLDAP()->exists($dnOld));
        $this->assertTrue($this->getLDAP()->exists($dnNew));
    }
}
