<?php

namespace LaminasTest\Ldap\Exception;

use Laminas\Ldap\Exception\LdapException;
use Laminas\Ldap\Ldap;
use PHPUnit\Framework\TestCase;

class LdapExceptionTest extends TestCase
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
