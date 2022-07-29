<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Dn;

use Laminas\Ldap;
use PHPUnit\Framework\TestCase;

use function chr;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Dn
 */
class EscapingTest extends TestCase
{
    public function testEscapeValues()
    {
        $dnval    = '  ' . chr(22) . ' t,e+s"t,\\v<a>l;u#e=!    ';
        $expected = '\20\20\16 t\,e\+s\"t\,\\\\v\<a\>l\;u\#e\=!\20\20\20\20';
        $this->assertEquals($expected, Ldap\Dn::escapeValue($dnval));
        $this->assertEquals($expected, Ldap\Dn::escapeValue([$dnval]));
        $this->assertEquals(
            [$expected, $expected, $expected],
            Ldap\Dn::escapeValue([$dnval, $dnval, $dnval])
        );
    }

    public function testUnescapeValues()
    {
        $dnval    = '\\20\\20\\16\\20t\\,e\\+s \\"t\\,\\\\v\\<a\\>l\\;u\\#e\\=!\\20\\20\\20\\20';
        $expected = '  ' . chr(22) . ' t,e+s "t,\\v<a>l;u#e=!    ';
        $this->assertEquals($expected, Ldap\Dn::unescapeValue($dnval));
        $this->assertEquals($expected, Ldap\Dn::unescapeValue([$dnval]));
        $this->assertEquals(
            [$expected, $expected, $expected],
            Ldap\Dn::unescapeValue([$dnval, $dnval, $dnval])
        );
    }
}
