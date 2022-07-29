<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Converter;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Laminas\Ldap\Converter\Converter;
use PHPUnit\Framework\TestCase;
use stdClass;
use UnexpectedValueException;

use function chr;
use function date_timestamp_set;
use function fopen;
use function serialize;
use function stream_get_contents;

/**
 * @group      Laminas_Ldap
 */
class ConverterTest extends TestCase
{
    public function testAsc2hex32(): void
    {
        $expected = '\00\01\02\03\04\05\06\07\08\09\0a\0b\0c\0d\0e\0f\10\11\12\13\14\15\16\17\18\19'
                    . '\1a\1b\1c\1d\1e\1f !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`'
                    . 'abcdefghijklmnopqrstuvwxyz{|}~';
        $str      = '';
        for ($i = 0; $i < 127; $i++) {
            $str .= chr($i);
        }
        $this->assertEquals($expected, Converter::ascToHex32($str));
    }

    public function testHex2asc(): void
    {
        $expected = '';
        for ($i = 0; $i < 127; $i++) {
            $expected .= chr($i);
        }

        $str = '\00\01\02\03\04\05\06\07\08\09\0a\0b\0c\0d\0e\0f\10\11\12\13\14\15\16\17\18\19\1a\1b'
               . '\1c\1d\1e\1f !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefg'
               . 'hijklmnopqrstuvwxyz{|}~';
        $this->assertEquals($expected, Converter::hex32ToAsc($str));
    }

    /**
     * @dataProvider toLdapDateTimeProvider
     * @param array{date: DateTime|int|string|bool, utc: bool} $convert
     */
    public function testToLdapDateTime(array $convert, string $expect): void
    {
        $result = Converter::toLdapDatetime($convert['date'], $convert['utc']);
        $this->assertEquals($expect, $result);
    }

    /** @return non-empty-list<array{array{date: DateTime|int|string|bool, utc: bool}, string}> */
    public function toLdapDateTimeProvider(): array
    {
        $tz = new DateTimeZone('UTC');
        return [
            [
                [
                    'date' => 0,
                    'utc'  => true,
                ],
                '19700101000000Z',
            ],
            [
                [
                    'date' => new DateTime('2010-05-12 13:14:45+0300', $tz),
                    'utc'  => false,
                ],
                '20100512131445+0300',
            ],
            [
                [
                    'date' => new DateTime('2010-05-12 13:14:45+0300', $tz),
                    'utc'  => true,
                ],
                '20100512101445Z',
            ],
            [
                [
                    'date' => '2010-05-12 13:14:45+0300',
                    'utc'  => false,
                ],
                '20100512131445+0300',
            ],
            [
                [
                    'date' => '2010-05-12 13:14:45+0300',
                    'utc'  => true,
                ],
                '20100512101445Z',
            ],
            [
                [
                    'date' => DateTime::createFromFormat(DateTime::ISO8601, '2010-05-12T13:14:45+0300'),
                    'utc'  => true,
                ],
                '20100512101445Z',
            ],
            [
                [
                    'date' => DateTime::createFromFormat(DateTime::ISO8601, '2010-05-12T13:14:45+0300'),
                    'utc'  => false,
                ],
                '20100512131445+0300',
            ],
            [
                [
                    'date' => date_timestamp_set(new DateTime(), 0),
                    'utc'  => true,
                ],
                '19700101000000Z',
            ],
        ];
    }

    /**
     * @dataProvider toLdapBooleanProvider
     * @param  'TRUE'|'FALSE' $expect
     * @param mixed           $convert
     */
    public function testToLdapBoolean(string $expect, $convert): void
    {
        $this->assertEquals($expect, Converter::toLdapBoolean($convert));
    }

    /** @return non-empty-list<array{'TRUE'|'FALSE', mixed}> */
    public function toLdapBooleanProvider(): array
    {
        return [
            ['TRUE', true],
            ['TRUE', 1],
            ['TRUE', 'true'],
            ['FALSE', 'false'],
            ['FALSE', false],
            ['FALSE', ['true']],
            ['FALSE', ['false']],
        ];
    }

    /**
     * @dataProvider toLdapSerializeProvider
     * @param mixed $convert
     */
    public function testToLdapSerialize(string $expect, $convert): void
    {
        $this->assertEquals($expect, Converter::toLdapSerialize($convert));
    }

    /** @return non-empty-list<array{string, mixed}> */
    public function toLdapSerializeProvider(): array
    {
        return [
            ['N;', null],
            ['i:1;', 1],
            [serialize(new DateTime('@0')), new DateTime('@0')],
            [
                'a:3:{i:0;s:4:"test";i:1;i:1;s:3:"foo";s:3:"bar";}',
                [
                    'test',
                    1,
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider toLdapProvider
     * @param array{value: mixed, type: int} $expect
     */
    public function testToLdap($expect, array $convert): void
    {
        $this->assertEquals($expect, Converter::toLdap($convert['value'], $convert['type']));
    }

    /** @return non-empty-list<array{mixed, array{value: mixed, type: int}}> */
    public function toLdapProvider(): array
    {
        return [
            [
                null,
                [
                    'value' => null,
                    'type'  => 0,
                ],
            ],
            [
                '19700101000000Z',
                [
                    'value' => 0,
                    'type'  => 2,
                ],
            ],
            [
                '0',
                [
                    'value' => 0,
                    'type'  => 0,
                ],
            ],
            [
                'FALSE',
                [
                    'value' => 0,
                    'type'  => 1,
                ],
            ],
            [
                '19700101000000Z',
                [
                    'value' => DateTime::createFromFormat(DateTime::ISO8601, '1970-01-01T00:00:00+0000'),
                    'type'  => 0,
                ],
            ],
            [
                Converter::toLdapBoolean(true),
                [
                    'value' => (bool) true,
                    'type'  => 0,
                ],
            ],
            [
                Converter::toLdapSerialize(new stdClass()),
                [
                    'value' => new stdClass(),
                    'type'  => 0,
                ],
            ],
            [
                Converter::toLdapSerialize(['foo']),
                [
                    'value' => ['foo'],
                    'type'  => 0,
                ],
            ],
            [
                stream_get_contents(fopen(__FILE__, 'r')),
                [
                    'value' => fopen(__FILE__, 'r'),
                    'type'  => 0,
                ],
            ],
        ];
    }

    /**
     * @dataProvider fromLdapUnserializeProvider
     * @param mixed $expect
     */
    public function testFromLdapUnserialize($expect, string $convert): void
    {
        $this->assertEquals($expect, Converter::fromLdapUnserialize($convert));
    }

    public function testFromLdapUnserializeThrowsException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Converter::fromLdapUnserialize('--');
    }

    /** @return non-empty-list<array{mixed, non-empty-string}> */
    public function fromLdapUnserializeProvider(): array
    {
        return [
            [null, 'N;'],
            [1, 'i:1;'],
            [false, 'b:0;'],
        ];
    }

    public function testFromLdapBoolean(): void
    {
        $this->assertTrue(Converter::fromLdapBoolean('TRUE'));
        $this->assertFalse(Converter::fromLdapBoolean('FALSE'));
        $this->expectException(InvalidArgumentException::class);
        Converter::fromLdapBoolean('test');
    }

    /** @dataProvider fromLdapDateTimeProvider */
    public function testFromLdapDateTime(DateTime $expected, string $convert, bool $utc): void
    {
        if (true === $utc) {
            $expected->setTimezone(new DateTimeZone('UTC'));
        }
        $this->assertEquals($expected, Converter::fromLdapDatetime($convert, $utc));
    }

    /** @return non-empty-list<array{DateTime, string, bool}> */
    public function fromLdapDateTimeProvider(): array
    {
        return [
            [new DateTime('2010-12-24 08:00:23+0300'), '20101224080023+0300', false],
            [new DateTime('2010-12-24 08:00:23+0300'), '20101224080023+03\'00\'', false],
            [new DateTime('2010-12-24 08:00:23+0000'), '20101224080023', false],
            [new DateTime('2010-12-24 08:00:00+0000'), '201012240800', false],
            [new DateTime('2010-12-24 08:00:00+0000'), '2010122408', false],
            [new DateTime('2010-12-24 00:00:00+0000'), '20101224', false],
            [new DateTime('2010-12-01 00:00:00+0000'), '201012', false],
            [new DateTime('2010-01-01 00:00:00+0000'), '2010', false],
            [new DateTime('2010-04-03 12:23:34+0000'), '20100403122334', true],
        ];
    }

    /**
     * @dataProvider         fromLdapDateTimeException
     * @param mixed $value
     */
    public function testFromLdapDateTimeThrowsException($value)
    {
        $this->expectException(InvalidArgumentException::class);
        Converter::fromLdapDatetime($value);
    }

    /** @return non-empty-list<array{non-empty-string}> */
    public static function fromLdapDateTimeException(): array
    {
        return [
            ['foobar'],
            ['201'],
            ['201013'],
            ['20101232'],
            ['2010123124'],
            ['201012312360'],
            ['20101231235960'],
            ['20101231235959+13'],
            ['20101231235959+1160'],
        ];
    }

    /**
     * @dataProvider fromLdapProvider
     * @param mixed $expect
     * @param mixed $value
     */
    public function testFromLdap($expect, $value, int $type, bool $dateTimeAsUtc): void
    {
        $this->assertSame($expect, Converter::fromLdap($value, $type, $dateTimeAsUtc));
    }

    /** @return non-empty-list<array{mixed, mixed, int, true}> */
    public function fromLdapProvider(): array
    {
        return [
            ['1', '1', 0, true],
            ['0', '0', 0, true],
            [true, 'TRUE', 0, true],
            [false, 'FALSE', 0, true],
            ['123456789', '123456789', 0, true],
            // Laminas-11639
            ['+123456789', '+123456789', 0, true],
        ];
    }
}
