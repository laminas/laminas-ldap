<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap\Exception;

use Laminas\Ldap\Exception\LdapException;
use Laminas\Ldap\Ldap;

class LdapExceptionTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider constructorArgumentsProvider
     *
     * @param Ldap $ldap
     * @param string $message
     * @param int $code
     * @param string $expectedMessage
     * @param int $expectedCode
     */
    public function testException($ldap, $message, $code, $expectedMessage, $expectedCode)
    {
        $e = new LdapException($ldap, $message, $code);

        $this->assertEquals($expectedMessage, $e->getMessage());
        $this->assertEquals($expectedCode, $e->getCode());
    }

    public function constructorArgumentsProvider()
    {
        return [
            // Description => [LDAP object, message, code, expected message, expected code]
            'default' => [null, '', 0, 'no exception message', 0],
            'hexadecimal' => [null, '', 15, '0xf: no exception message', 15],
        ];
    }
}
