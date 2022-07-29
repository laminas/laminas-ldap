<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Exception;

use Laminas\Ldap\Exception\LdapException;
use Laminas\Ldap\Ldap;
use PHPUnit\Framework\TestCase;

class LdapExceptionTest extends TestCase
{
    /** @dataProvider constructorArgumentsProvider */
    public function testException(?Ldap $ldap, string $message, int $code, string $expectedMessage, int $expectedCode)
    {
        $e = new LdapException($ldap, $message, $code);

        $this->assertEquals($expectedMessage, $e->getMessage());
        $this->assertEquals($expectedCode, $e->getCode());
    }

    /** @return non-empty-array<string, array{null, '', int, non-empty-string, int}> */
    public function constructorArgumentsProvider(): array
    {
        return [
            // Description => [LDAP object, message, code, expected message, expected code]
            'default'     => [null, '', 0, 'no exception message', 0],
            'hexadecimal' => [null, '', 15, '0xf: no exception message', 15],
        ];
    }
}
