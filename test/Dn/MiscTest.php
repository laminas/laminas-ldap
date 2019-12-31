<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap\Dn;

use Laminas\Ldap;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Dn
 */
class MiscTest extends \PHPUnit_Framework_TestCase
{
    public function testIsChildOfIllegalDn1()
    {
        $dn1 = 'name1,cn=name2,dc=example,dc=org';
        $dn2 = 'dc=example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfIllegalDn2()
    {
        $dn1 = 'cn=name1,cn=name2,dc=example,dc=org';
        $dn2 = 'example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfIllegalBothDn()
    {
        $dn1 = 'name1,cn=name2,dc=example,dc=org';
        $dn2 = 'example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOf()
    {
        $dn1 = 'cb=name1,cn=name2,dc=example,dc=org';
        $dn2 = 'dc=example,dc=org';
        $this->assertTrue(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfWithDnObjects()
    {
        $dn1 = Ldap\Dn::fromString('cb=name1,cn=name2,dc=example,dc=org');
        $dn2 = Ldap\Dn::fromString('dc=example,dc=org');
        $this->assertTrue(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfOtherSubtree()
    {
        $dn1 = 'cb=name1,cn=name2,dc=example,dc=org';
        $dn2 = 'dc=example,dc=de';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }

    public function testIsChildOfParentDnLonger()
    {
        $dn1 = 'dc=example,dc=de';
        $dn2 = 'cb=name1,cn=name2,dc=example,dc=org';
        $this->assertFalse(Ldap\Dn::isChildOf($dn1, $dn2));
    }
}
