<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Node;

use Laminas\Ldap\Node;
use Laminas\Ldap\Node\ChildrenIterator;
use LaminasTest\Ldap as TestLdap;

use function getenv;
use function serialize;
use function unserialize;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Node
 */
class ChildrenTest extends TestLdap\AbstractOnlineTestCase
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

    public function testGetChildrenOnAttachedNode()
    {
        $node     = $this->getLDAP()->getBaseNode();
        $children = $node->getChildren();
        $this->assertInstanceOf(ChildrenIterator::class, $children);
        $this->assertCount(6, $children);
        $this->assertInstanceOf(Node::class, $children['ou=Node']);
    }

    public function testGetChildrenOnDetachedNode()
    {
        $node = $this->getLDAP()->getBaseNode();
        $node->detachLDAP();
        $children = $node->getChildren();
        $this->assertInstanceOf(ChildrenIterator::class, $children);
        $this->assertCount(0, $children);

        $node->attachLDAP($this->getLDAP());
        $node->reload();
        $children = $node->getChildren();

        $this->assertInstanceOf(ChildrenIterator::class, $children);
        $this->assertCount(6, $children);
        $this->assertInstanceOf(Node::class, $children['ou=Node']);
    }

    public function testHasChildrenOnAttachedNode()
    {
        $node = $this->getLDAP()->getNode(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'));
        $this->assertTrue($node->hasChildren());
        $this->assertTrue($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $this->assertTrue($node->hasChildren());
        $this->assertTrue($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Test1,'));
        $this->assertFalse($node->hasChildren());
        $this->assertFalse($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Test1,ou=Node,'));
        $this->assertFalse($node->hasChildren());
        $this->assertFalse($node->hasChildren());
    }

    public function testHasChildrenOnDetachedNodeWithoutPriorGetChildren()
    {
        $node = $this->getLDAP()->getNode(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'));
        $node->detachLDAP();
        $this->assertFalse($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $node->detachLDAP();
        $this->assertFalse($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Test1,'));
        $node->detachLDAP();
        $this->assertFalse($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Test1,ou=Node,'));

        $node->detachLDAP();
        $this->assertFalse($node->hasChildren());
    }

    public function testHasChildrenOnDetachedNodeWithPriorGetChildren()
    {
        $node = $this->getLDAP()->getNode(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'));
        $node->getChildren();
        $node->detachLDAP();
        $this->assertTrue($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $node->getChildren();
        $node->detachLDAP();
        $this->assertTrue($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Test1,'));
        $node->getChildren();
        $node->detachLDAP();
        $this->assertFalse($node->hasChildren());

        $node = $this->getLDAP()->getNode($this->createDn('ou=Test1,ou=Node,'));
        $node->getChildren();
        $node->detachLDAP();
        $this->assertFalse($node->hasChildren());
    }

    public function testChildrenCollectionSerialization()
    {
        $node = $this->getLDAP()->getNode($this->createDn('ou=Node,'));

        $children = $node->getChildren();
        $this->assertTrue($node->hasChildren());
        $this->assertCount(2, $children);

        $string = serialize($node);
        $node2  = unserialize($string);

        $children2 = $node2->getChildren();
        $this->assertTrue($node2->hasChildren());
        $this->assertCount(2, $children2);

        $node2->attachLDAP($this->getLDAP());

        $children2 = $node2->getChildren();
        $this->assertTrue($node2->hasChildren());
        $this->assertCount(2, $children2);

        $node = $this->getLDAP()->getNode($this->createDn('ou=Node,'));
        $this->assertTrue($node->hasChildren());
        $string = serialize($node);
        $node2  = unserialize($string);
        $this->assertFalse($node2->hasChildren());
        $node2->attachLDAP($this->getLDAP());
        $this->assertTrue($node2->hasChildren());
    }

    public function testCascadingAttachAndDetach()
    {
        $node         = $this->getLDAP()->getBaseNode();
        $baseChildren = $node->getChildren();
        $nodeChildren = $baseChildren['ou=Node']->getChildren();

        $this->assertTrue($node->isAttached());
        foreach ($baseChildren as $bc) {
            $this->assertTrue($bc->isAttached());
        }
        foreach ($nodeChildren as $nc) {
            $this->assertTrue($nc->isAttached());
        }

        $node->detachLDAP();
        $this->assertFalse($node->isAttached());
        foreach ($baseChildren as $bc) {
            $this->assertFalse($bc->isAttached());
        }
        foreach ($nodeChildren as $nc) {
            $this->assertFalse($nc->isAttached());
        }

        $node->attachLDAP($this->getLDAP());
        $this->assertTrue($node->isAttached());
        $this->assertSame($this->getLDAP(), $node->getLDAP());
        foreach ($baseChildren as $bc) {
            $this->assertTrue($bc->isAttached());
            $this->assertSame($this->getLDAP(), $bc->getLDAP());
        }
        foreach ($nodeChildren as $nc) {
            $this->assertTrue($nc->isAttached());
            $this->assertSame($this->getLDAP(), $nc->getLDAP());
        }
    }
}
