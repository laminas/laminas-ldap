<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Ldap\Node;

/**
 * @group      Laminas_Ldap
 */
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    protected function createTestArrayData()
    {
        $data = array(
            'dn'          => 'cn=name,dc=example,dc=org',
            'cn'          => array('name'),
            'host'        => array('a', 'b', 'c'),
            'empty'       => array(),
            'boolean'     => array('TRUE', 'FALSE'),
            'objectclass' => array('account', 'top'),
        );
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
