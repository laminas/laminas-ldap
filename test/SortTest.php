<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Laminas\Ldap\Collection\DefaultIterator;
use ReflectionObject;

use function bin2hex;
use function decbin;
use function getenv;
use function hexdec;
use function str_replace;
use function strlen;
use function strnatcasecmp;

class SortTest extends AbstractOnlineTestCase
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

    /**
     * Test whether a callable is set correctly
     */
    public function testSettingCallable()
    {
        $search = ldap_search(
            $this->getLDAP()->getResource(),
            getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'),
            '(l=*)',
            ['l']
        );

        $iterator     = new DefaultIterator($this->getLdap(), $search);
        $sortFunction = static fn($a, $b): int => 1;

        $reflectionObject   = new ReflectionObject($iterator);
        $reflectionProperty = $reflectionObject->getProperty('sortFunction');
        $this->assertEquals('strnatcasecmp', $reflectionProperty->getValue($iterator));
        $iterator->setSortFunction($sortFunction);
        $this->assertEquals($sortFunction, $reflectionProperty->getValue($iterator));
    }

    /**
     * Test whether sorting works as expected out of the box
     */
    public function testSorting()
    {
        $lSorted = ['a', 'b', 'c', 'd', 'e'];

        $search = ldap_search(
            $this->getLDAP()->getResource(),
            getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'),
            '(l=*)',
            ['l']
        );

        $iterator = new DefaultIterator($this->getLdap(), $search);

        $reflectionObject   = new ReflectionObject($iterator);
        $reflectionProperty = $reflectionObject->getProperty('sortFunction');
        $this->assertEquals('strnatcasecmp', $reflectionProperty->getValue($iterator));

        $reflectionProperty = $reflectionObject->getProperty('entries');

        $iterator->sort('l');

        $reflectionEntries = $reflectionProperty->getValue($iterator);
        foreach ($lSorted as $index => $value) {
            $this->assertEquals($value, $reflectionEntries[$index]["sortValue"]);
        }
    }

    /**
     * Test sorting with custom sort-function
     */
    public function testCustomSorting()
    {
        $lSorted = ['a', 'b', 'd', 'c', 'e'];

        $search = ldap_search(
            $this->getLDAP()->getResource(),
            getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'),
            '(l=*)',
            ['l']
        );

        $iterator     = new DefaultIterator($this->getLdap(), $search);
        $sortFunction = static function ($a, $b): int {
            // Sort values by the number of "1" in their binary representation
            // and when that is equals by their position in the alphabet.
            $f = strlen(str_replace('0', '', decbin(hexdec(bin2hex($a))))) -
                 strlen(str_replace('0', '', decbin(hexdec(bin2hex($b)))));
            if ($f < 0) {
                return -1;
            } elseif ($f > 0) {
                return 1;
            }
            return strnatcasecmp($a, $b);
        };
        $iterator->setSortFunction($sortFunction);

        $reflectionObject   = new ReflectionObject($iterator);
        $reflectionProperty = $reflectionObject->getProperty('sortFunction');
        $this->assertEquals($sortFunction, $reflectionProperty->getValue($iterator));

        $reflectionProperty = $reflectionObject->getProperty('entries');

        $iterator->sort('l');

        $reflectionEntries = $reflectionProperty->getValue($iterator);

        $actual = [];
        foreach ($reflectionEntries as $value) {
            $actual[] = $value['sortValue'];
        }
        self::assertSame($lSorted, $actual);
    }
}
