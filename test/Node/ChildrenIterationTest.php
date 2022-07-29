<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Node;

use Laminas\Ldap;
use Laminas\Ldap\Node;
use LaminasTest\Ldap as TestLdap;
use RecursiveIteratorIterator;

use function getenv;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Node
 */
class ChildrenIterationTest extends TestLdap\AbstractOnlineTestCase
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

    public function testSimpleIteration()
    {
        $node     = $this->getLDAP()->getBaseNode();
        $children = $node->getChildren();

        $i = 1;
        foreach ($children as $rdn => $n) {
            $dn  = $n->getDn()->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER);
            $rdn = Ldap\Dn::implodeRdn($n->getRdnArray(), Ldap\Dn::ATTR_CASEFOLD_LOWER);
            if ($i === 1) {
                $this->assertEquals('ou=Node', $rdn);
                $this->assertEquals($this->createDn('ou=Node,'), $dn);
            } else {
                $this->assertEquals('ou=Test' . ($i - 1), $rdn);
                $this->assertEquals($this->createDn('ou=Test' . ($i - 1) . ','), $dn);
            }
            $i++;
        }
        $this->assertEquals(6, $i - 1);
    }

    public function testSimpleRecursiveIteration()
    {
        $node = $this->getLDAP()->getBaseNode();
        $ri   = new RecursiveIteratorIterator($node, RecursiveIteratorIterator::SELF_FIRST);
        $i    = 0;
        foreach ($ri as $rdn => $n) {
            $dn  = $n->getDn()->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER);
            $rdn = Ldap\Dn::implodeRdn($n->getRdnArray(), Ldap\Dn::ATTR_CASEFOLD_LOWER);
            if ($i === 0) {
                $this->assertEquals(Ldap\Dn::fromString(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'))
                        ->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER), $dn);
            } elseif ($i === 1) {
                $this->assertEquals('ou=Node', $rdn);
                $this->assertEquals($this->createDn('ou=Node,'), $dn);
            } else {
                if ($i < 4) {
                    $j    = $i - 1;
                    $base = $this->createDn('ou=Node,');
                } else {
                    $j    = $i - 3;
                    $base = Ldap\Dn::fromString(getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'))
                        ->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER);
                }
                $this->assertEquals('ou=Test' . $j, $rdn);
                $this->assertEquals('ou=Test' . $j . ',' . $base, $dn);
            }
            $i++;
        }
        $this->assertEquals(9, $i);
    }

    /**
     * Test issue reported by Lance Hendrix on
     * https://getlaminas.org/wiki/display/LaminasPROP/Laminas_Ldap+-+Extended+support+-+Stefan+Gehrig?
     *      focusedCommentId=13107431#comment-13107431
     */
    public function testCallingNextAfterIterationShouldNotThrowException()
    {
        $node  = $this->getLDAP()->getBaseNode();
        $nodes = $node->searchChildren('(objectClass=*)');
        foreach ($nodes as $rdn => $n) { // phpcs:ignore
            // do nothing - just iterate
        }
        $nodes->next();
        $this->assertInstanceOf(Node::class, $nodes->current());
    }
}
