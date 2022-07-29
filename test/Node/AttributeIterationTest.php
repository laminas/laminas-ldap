<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Node;

use LaminasTest\Ldap as TestLdap;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Node
 */
class AttributeIterationTest extends TestLdap\AbstractTestCase
{
    public function testSimpleIteration()
    {
        $node = $this->createTestNode();
        $i    = 0;
        $data = [];
        foreach ($node->getAttributes() as $k => $v) {
            $this->assertNotNull($k);
            $this->assertNotNull($v);
            $this->assertEquals($node->$k, $v);
            $data[$k] = $v;
            $i++;
        }
        $this->assertEquals(5, $i);
        $this->assertCount($i, $node);
        $this->assertEquals([
            'boolean'     => [true, false],
            'cn'          => ['name'],
            'empty'       => [],
            'host'        => ['a', 'b', 'c'],
            'objectclass' => ['account', 'top'],
        ], $data);
    }
}
