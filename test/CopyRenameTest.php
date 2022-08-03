<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Laminas\Ldap;
use Laminas\Ldap\Exception\LdapException;

use function getenv;

/**
 * @group      Laminas_Ldap
 */
class CopyRenameTest extends AbstractOnlineTestCase
{
    private string $orgDn;
    private string $newDn;
    private string $orgSubTreeDn;
    private string $newSubTreeDn;
    private string $targetSubTreeDn;

    /** @var array */
    private $nodes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareLDAPServer();

        $this->orgDn           = $this->createDn('ou=OrgTest,');
        $this->newDn           = $this->createDn('ou=NewTest,');
        $this->orgSubTreeDn    = $this->createDn('ou=OrgSubtree,');
        $this->newSubTreeDn    = $this->createDn('ou=NewSubtree,');
        $this->targetSubTreeDn = $this->createDn('ou=Target,');

        $this->nodes = [
            $this->orgDn        => [
                "objectClass" => "organizationalUnit",
                "ou"          => "OrgTest",
            ],
            $this->orgSubTreeDn => [
                "objectClass" => "organizationalUnit",
                "ou"          => "OrgSubtree",
            ],
            'ou=Subtree1,' . $this->orgSubTreeDn
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Subtree1",
            ],
            'ou=Subtree11,ou=Subtree1,' . $this->orgSubTreeDn
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Subtree11",
            ],
            'ou=Subtree12,ou=Subtree1,' . $this->orgSubTreeDn
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Subtree12",
            ],
            'ou=Subtree13,ou=Subtree1,' . $this->orgSubTreeDn
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Subtree13",
            ],
            'ou=Subtree2,' . $this->orgSubTreeDn
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Subtree2",
            ],
            'ou=Subtree3,' . $this->orgSubTreeDn
            => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Subtree3",
            ],
            $this->targetSubTreeDn => [
                "objectClass" => "organizationalUnit",
                "ou"          => "Target",
            ],
        ];

        $ldap = $this->getLDAP()->getResource();
        foreach ($this->nodes as $dn => $entry) {
            ldap_add($ldap, $dn, $entry);
        }
    }

    protected function tearDown(): void
    {
        if (! getenv('TESTS_LAMINAS_LDAP_ONLINE_ENABLED')) {
            return;
        }
        if ($this->getLDAP()->exists($this->newDn)) {
            $this->getLDAP()->delete($this->newDn, false);
        }
        if ($this->getLDAP()->exists($this->orgDn)) {
            $this->getLDAP()->delete($this->orgDn, false);
        }
        if ($this->getLDAP()->exists($this->orgSubTreeDn)) {
            $this->getLDAP()->delete($this->orgSubTreeDn, true);
        }
        if ($this->getLDAP()->exists($this->newSubTreeDn)) {
            $this->getLDAP()->delete($this->newSubTreeDn, true);
        }
        if ($this->getLDAP()->exists($this->targetSubTreeDn)) {
            $this->getLDAP()->delete($this->targetSubTreeDn, true);
        }

        $this->cleanupLDAPServer();
        parent::tearDown();
    }

    public function testSimpleLeafRename()
    {
        $org = $this->getLDAP()->getEntry($this->orgDn, [], true);
        $this->getLDAP()->rename($this->orgDn, $this->newDn, false);
        $this->assertFalse($this->getLDAP()->exists($this->orgDn));
        $this->assertTrue($this->getLDAP()->exists($this->newDn));
        $new = $this->getLDAP()->getEntry($this->newDn);
        $this->assertEquals($org['objectclass'], $new['objectclass']);
        $this->assertEquals(['NewTest'], $new['ou']);
    }

    public function testSimpleLeafMoveAlias()
    {
        $this->getLDAP()->move($this->orgDn, $this->newDn, false);
        $this->assertFalse($this->getLDAP()->exists($this->orgDn));
        $this->assertTrue($this->getLDAP()->exists($this->newDn));
    }

    public function testSimpleLeafMoveToSubtree()
    {
        $this->getLDAP()->moveToSubtree($this->orgDn, $this->orgSubTreeDn, false);
        $this->assertFalse($this->getLDAP()->exists($this->orgDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgTest,' . $this->orgSubTreeDn));
    }

    public function testRenameSourceNotExists()
    {
        $this->expectException(LdapException::class);
        $this->getLDAP()->rename($this->createDn('ou=DoesNotExist,'), $this->newDn, false);
    }

    public function testRenameTargetExists()
    {
        $this->expectException(LdapException::class);
        $this->getLDAP()->rename($this->orgDn, $this->createDn('ou=Test1,'), false);
    }

    public function testRenameTargetParentNotExists()
    {
        $this->expectException(LdapException::class);
        $this->getLDAP()->rename($this->orgDn, $this->createDn('ou=Test1,ou=ParentDoesNotExist,'), false);
    }

    public function testRenameEmulationSourceNotExists()
    {
        $this->expectException(LdapException::class);
        $this->getLDAP()->rename($this->createDn('ou=DoesNotExist,'), $this->newDn, false, true);
    }

    public function testRenameEmulationTargetExists()
    {
        $this->expectException(LdapException::class);
        $this->getLDAP()->rename($this->orgDn, $this->createDn('ou=Test1,'), false, true);
    }

    public function testRenameEmulationTargetParentNotExists()
    {
        $this->expectException(LdapException::class);
        $this->getLDAP()->rename(
            $this->orgDn,
            $this->createDn('ou=Test1,ou=ParentDoesNotExist,'),
            false,
            true
        );
    }

    public function testSimpleLeafRenameEmulation()
    {
        $this->getLDAP()->rename($this->orgDn, $this->newDn, false, true);
        $this->assertFalse($this->getLDAP()->exists($this->orgDn));
        $this->assertTrue($this->getLDAP()->exists($this->newDn));
    }

    public function testSimpleLeafCopyToSubtree()
    {
        $this->getLDAP()->copyToSubtree($this->orgDn, $this->orgSubTreeDn, false);
        $this->assertTrue($this->getLDAP()->exists($this->orgDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgTest,' . $this->orgSubTreeDn));
    }

    public function testSimpleLeafCopy()
    {
        $this->getLDAP()->copy($this->orgDn, $this->newDn, false);
        $this->assertTrue($this->getLDAP()->exists($this->orgDn));
        $this->assertTrue($this->getLDAP()->exists($this->newDn));
    }

    public function testRecursiveRename()
    {
        $this->getLDAP()->rename($this->orgSubTreeDn, $this->newSubTreeDn, true);
        $this->assertFalse($this->getLDAP()->exists($this->orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists($this->newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren($this->newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $this->newSubTreeDn));
    }

    public function testRecursiveMoveToSubtree()
    {
        $this->getLDAP()->moveToSubtree($this->orgSubTreeDn, $this->targetSubTreeDn, true);
        $this->assertFalse($this->getLDAP()->exists($this->orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgSubtree,' . $this->targetSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=OrgSubtree,' . $this->targetSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,ou=OrgSubtree,' . $this->targetSubTreeDn));
    }

    public function testRecursiveCopyToSubtree()
    {
        $this->getLDAP()->copyToSubtree($this->orgSubTreeDn, $this->targetSubTreeDn, true);
        $this->assertTrue($this->getLDAP()->exists($this->orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgSubtree,' . $this->targetSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren($this->orgSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $this->orgSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=OrgSubtree,' . $this->targetSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,ou=OrgSubtree,' . $this->targetSubTreeDn));
    }

    public function testRecursiveCopy()
    {
        $this->getLDAP()->copy($this->orgSubTreeDn, $this->newSubTreeDn, true);
        $this->assertTrue($this->getLDAP()->exists($this->orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists($this->newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren($this->orgSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $this->orgSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren($this->newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $this->newSubTreeDn));
    }

    public function testSimpleLeafRenameWithDnObjects()
    {
        $orgDn = Ldap\Dn::fromString($this->orgDn);
        $newDn = Ldap\Dn::fromString($this->newDn);

        $this->getLDAP()->rename($orgDn, $newDn, false);
        $this->assertFalse($this->getLDAP()->exists($orgDn));
        $this->assertTrue($this->getLDAP()->exists($newDn));

        $this->getLDAP()->move($newDn, $orgDn, false);
        $this->assertTrue($this->getLDAP()->exists($orgDn));
        $this->assertFalse($this->getLDAP()->exists($newDn));
    }

    public function testSimpleLeafMoveToSubtreeWithDnObjects()
    {
        $orgDn        = Ldap\Dn::fromString($this->orgDn);
        $orgSubTreeDn = Ldap\Dn::fromString($this->orgSubTreeDn);

        $this->getLDAP()->moveToSubtree($orgDn, $orgSubTreeDn, false);
        $this->assertFalse($this->getLDAP()->exists($orgDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgTest,' . $orgSubTreeDn->toString()));
    }

    public function testSimpleLeafRenameEmulationWithDnObjects()
    {
        $orgDn = Ldap\Dn::fromString($this->orgDn);
        $newDn = Ldap\Dn::fromString($this->newDn);

        $this->getLDAP()->rename($orgDn, $newDn, false, true);
        $this->assertFalse($this->getLDAP()->exists($orgDn));
        $this->assertTrue($this->getLDAP()->exists($newDn));
    }

    public function testSimpleLeafCopyToSubtreeWithDnObjects()
    {
        $orgDn        = Ldap\Dn::fromString($this->orgDn);
        $orgSubTreeDn = Ldap\Dn::fromString($this->orgSubTreeDn);

        $this->getLDAP()->copyToSubtree($orgDn, $orgSubTreeDn, false);
        $this->assertTrue($this->getLDAP()->exists($orgDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgTest,' . $orgSubTreeDn->toString()));
    }

    public function testSimpleLeafCopyWithDnObjects()
    {
        $orgDn = Ldap\Dn::fromString($this->orgDn);
        $newDn = Ldap\Dn::fromString($this->newDn);

        $this->getLDAP()->copy($orgDn, $newDn, false);
        $this->assertTrue($this->getLDAP()->exists($orgDn));
        $this->assertTrue($this->getLDAP()->exists($newDn));
    }

    public function testRecursiveRenameWithDnObjects()
    {
        $orgSubTreeDn = Ldap\Dn::fromString($this->orgSubTreeDn);
        $newSubTreeDn = Ldap\Dn::fromString($this->newSubTreeDn);

        $this->getLDAP()->rename($orgSubTreeDn, $newSubTreeDn, true);
        $this->assertFalse($this->getLDAP()->exists($orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists($newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren($newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $newSubTreeDn->toString()));
    }

    public function testRecursiveMoveToSubtreeWithDnObjects()
    {
        $orgSubTreeDn    = Ldap\Dn::fromString($this->orgSubTreeDn);
        $targetSubTreeDn = Ldap\Dn::fromString($this->targetSubTreeDn);

        $this->getLDAP()->moveToSubtree($orgSubTreeDn, $targetSubTreeDn, true);
        $this->assertFalse($this->getLDAP()->exists($orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgSubtree,' . $targetSubTreeDn->toString()));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=OrgSubtree,' . $targetSubTreeDn->toString()));
        $this->assertEquals(
            3,
            $this->getLDAP()->countChildren('ou=Subtree1,ou=OrgSubtree,' . $targetSubTreeDn->toString())
        );
    }

    public function testRecursiveCopyToSubtreeWithDnObjects()
    {
        $orgSubTreeDn    = Ldap\Dn::fromString($this->orgSubTreeDn);
        $targetSubTreeDn = Ldap\Dn::fromString($this->targetSubTreeDn);

        $this->getLDAP()->copyToSubtree($orgSubTreeDn, $targetSubTreeDn, true);
        $this->assertTrue($this->getLDAP()->exists($orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists('ou=OrgSubtree,' . $targetSubTreeDn->toString()));
        $this->assertEquals(3, $this->getLDAP()->countChildren($orgSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $orgSubTreeDn->toString()));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=OrgSubtree,' . $targetSubTreeDn->toString()));
        $this->assertEquals(
            3,
            $this->getLDAP()->countChildren('ou=Subtree1,ou=OrgSubtree,' . $targetSubTreeDn->toString())
        );
    }

    public function testRecursiveCopyWithDnObjects()
    {
        $orgSubTreeDn = Ldap\Dn::fromString($this->orgSubTreeDn);
        $newSubTreeDn = Ldap\Dn::fromString($this->newSubTreeDn);

        $this->getLDAP()->copy($orgSubTreeDn, $newSubTreeDn, true);
        $this->assertTrue($this->getLDAP()->exists($orgSubTreeDn));
        $this->assertTrue($this->getLDAP()->exists($newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren($orgSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $orgSubTreeDn->toString()));
        $this->assertEquals(3, $this->getLDAP()->countChildren($newSubTreeDn));
        $this->assertEquals(3, $this->getLDAP()->countChildren('ou=Subtree1,' . $newSubTreeDn->toString()));
    }
}
