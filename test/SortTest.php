<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Ldap\Collection\DefaultIterator;

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

        $iterator = new DefaultIterator($this->getLdap(), $search);
        $sortFunction = function ($a, $b) {
            return 1;
        };

        $this->assertEquals('strnatcasecmp', $iterator->getSortFunction());
        $iterator->setSortFunction($sortFunction);
        $this->assertEquals($sortFunction, $iterator->getSortFunction());
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

        $this->assertEquals('strnatcasecmp', $iterator->getSortFunction());
        $reflectionObject = new \ReflectionObject($iterator);
        $reflectionProperty = $reflectionObject->getProperty('entries');
        $reflectionProperty->setAccessible(true);

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
        $lSorted = ['d', 'e', 'a', 'b', 'c'];

        $search = ldap_search(
            $this->getLDAP()->getResource(),
            getenv('TESTS_LAMINAS_LDAP_WRITEABLE_SUBTREE'),
            '(l=*)',
            ['l']
        );

        $iterator = new DefaultIterator($this->getLdap(), $search);
        $sortFunction = function ($a, $b) use ($lSorted) {
            // Sort values by the number of "1" in their binary representation
            // and when that is equals by their position in the alphabet.
            $f = strlen(str_replace('0', '', decbin(bin2hex($a)))) -
                 strlen(str_replace('0', '', decbin(bin2hex($b))));
            if ($f < 0) {
                return -1;
            } elseif ($f > 0) {
                return 1;
            }
            return strnatcasecmp($a, $b);
        };
        $iterator->setSortFunction($sortFunction);

        $this->assertEquals($sortFunction, $iterator->getSortFunction());
        $reflectionObject = new \ReflectionObject($iterator);
        $reflectionProperty = $reflectionObject->getProperty('entries');
        $reflectionProperty->setAccessible(true);

        $iterator->sort('l');

        $reflectionEntries = $reflectionProperty->getValue($iterator);
        foreach ($lSorted as $index => $value) {
            $this->assertEquals($value, $reflectionEntries[$index]["sortValue"]);
        }
    }
}
