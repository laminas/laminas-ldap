<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Dn;

use Laminas\Ldap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group("Laminas_Ldap_Dn")]
#[Group("Laminas_Ldap")]
class MiscTest extends TestCase
{
    public function testIsChildOfIllegalDn1(): void
    {
        $dn1 = 'name1,cn=name2,dc=example,dc=org';
        $dn2 = 'dc=example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfIllegalDn2(): void
    {
        $dn1 = 'cn=name1,cn=name2,dc=example,dc=org';
        $dn2 = 'example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfIllegalBothDn(): void
    {
        $dn1 = 'name1,cn=name2,dc=example,dc=org';
        $dn2 = 'example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOf(): void
    {
        $dn1 = 'cb=name1,cn=name2,dc=example,dc=org';
        $dn2 = 'dc=example,dc=org';
        $this->assertTrue(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfWithDnObjects(): void
    {
        $dn1 = Ldap\Dn::fromString('cb=name1,cn=name2,dc=example,dc=org');
        $dn2 = Ldap\Dn::fromString('dc=example,dc=org');
        $this->assertTrue(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfOtherSubtree(): void
    {
        $dn1 = 'cb=name1,cn=name2,dc=example,dc=org';
        $dn2 = 'dc=example,dc=de';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfParentDnLonger(): void
    {
        $dn1 = 'dc=example,dc=de';
        $dn2 = 'cb=name1,cn=name2,dc=example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }
}
