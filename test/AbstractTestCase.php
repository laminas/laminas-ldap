<?php

namespace LaminasTest\Ldap;

use Laminas\Ldap\Node;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Ldap
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @return array
     */
    protected function createTestArrayData()
    {
        $data = [
            'dn'          => 'cn=name,dc=example,dc=org',
            'cn'          => ['name'],
            'host'        => ['a', 'b', 'c'],
            'empty'       => [],
            'boolean'     => ['TRUE', 'FALSE'],
            'objectclass' => ['account', 'top'],
        ];
        return $data;
    }

    /**
     * @return Node
     */
    protected function createTestNode()
    {
        return Node::fromArray($this->createTestArrayData(), true);
    }
}
