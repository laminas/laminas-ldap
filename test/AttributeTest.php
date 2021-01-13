<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Ldap\Attribute;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Ldap
 */
class AttributeTest extends TestCase
{
    protected function assertLocalDateTimeString($timestamp, $value)
    {
        $tsValue = date('YmdHisO', $timestamp);

        if (date('O', strtotime('20120101'))) {
            // Local timezone is +0000 when DST is off. Laminas_Ldap converts
            // +0000 to "Z" (see Laminas\Ldap\Converter\Converter:toLdapDateTime()), so
            // take account of that here
            $tsValue = str_replace('+0000', 'Z', $tsValue);
        }

        $this->assertEquals($tsValue, $value);
    }

    protected function assertUtcDateTimeString($localTimestamp, $value)
    {
        $localOffset  = date('Z', $localTimestamp);
        $utcTimestamp = $localTimestamp - $localOffset;
        $this->assertEquals(date('YmdHis', $utcTimestamp) . 'Z', $value);
    }

    public function testGetAttributeValue()
    {
        $data  = ['uid' => ['value']];
        $value = Attribute::getAttribute($data, 'uid', 0);
        $this->assertEquals('value', $value);
    }

    public function testGetNonExistentAttributeValue()
    {
        $data  = ['uid' => ['value']];
        $value = Attribute::getAttribute($data, 'uid', 1);
        $this->assertNull($value);
    }

    public function testInvalidValue()
    {
        $data  = ['uid' => ['45678+']];
        $value = Attribute::getAttribute($data, 'uid', 0);

        $this->assertEquals('45678+', $value);
    }

    public function testGetNonExistentAttribute()
    {
        $data  = ['uid' => ['value']];
        $value = Attribute::getAttribute($data, 'uid2', 0);
        $this->assertNull($value);
        $array = Attribute::getAttribute($data, 'uid2');
        $this->assertInternalType('array', $array);
        $this->assertCount(0, $array);
    }

    public function testGetAttributeWithWrongIndexType()
    {
        $data  = ['uid' => ['value']];
        $value = Attribute::getAttribute($data, 'uid', 'index');
        $this->assertNull($value);
        $value = Attribute::getAttribute($data, 'uid', 3.1415);
        $this->assertNull($value);
    }

    public function testGetAttributeArray()
    {
        $data  = ['uid' => ['value']];
        $value = Attribute::getAttribute($data, 'uid');
        $this->assertInternalType('array', $value);
        $this->assertCount(1, $value);
        $this->assertContains('value', $value);
    }

    public function testSimpleSetAttribute()
    {
        $data = [];
        Attribute::setAttribute($data, 'uid', 'new', false);
        $this->assertArrayHasKey('uid', $data);
        $this->assertInternalType('array', $data['uid']);
        $this->assertCount(1, $data['uid']);
        $this->assertContains('new', $data['uid']);
    }

    public function testSimpleOverwriteAttribute()
    {
        $data = ['uid' => ['old']];
        Attribute::setAttribute($data, 'uid', 'new', false);
        $this->assertArrayHasKey('uid', $data);
        $this->assertInternalType('array', $data['uid']);
        $this->assertCount(1, $data['uid']);
        $this->assertContains('new', $data['uid']);
    }

    public function testSimpleAppendAttribute()
    {
        $data = ['uid' => ['old']];
        Attribute::setAttribute($data, 'uid', 'new', true);
        $this->assertArrayHasKey('uid', $data);
        $this->assertInternalType('array', $data['uid']);
        $this->assertCount(2, $data['uid']);
        $this->assertContains('old', $data['uid']);
        $this->assertContains('new', $data['uid']);
        $this->assertEquals('old', $data['uid'][0]);
        $this->assertEquals('new', $data['uid'][1]);
    }

    public function testBooleanAttributeHandling()
    {
        $data = [
            'p1_true'  => ['TRUE'],
            'p1_false' => ['FALSE']
        ];
        Attribute::setAttribute($data, 'p2_true', true);
        Attribute::setAttribute($data, 'p2_false', false);
        $this->assertEquals('TRUE', $data['p2_true'][0]);
        $this->assertEquals('FALSE', $data['p2_false'][0]);
        $this->assertEquals(true, Attribute::getAttribute($data, 'p1_true', 0));
        $this->assertEquals(false, Attribute::getAttribute($data, 'p1_false', 0));
    }

    public function testArraySetAttribute()
    {
        $data = [];
        Attribute::setAttribute($data, 'uid', ['new1', 'new2'], false);
        $this->assertArrayHasKey('uid', $data);
        $this->assertInternalType('array', $data['uid']);
        $this->assertCount(2, $data['uid']);
        $this->assertContains('new1', $data['uid']);
        $this->assertContains('new2', $data['uid']);
        $this->assertEquals('new1', $data['uid'][0]);
        $this->assertEquals('new2', $data['uid'][1]);
    }

    public function testArrayOverwriteAttribute()
    {
        $data = ['uid' => ['old']];
        Attribute::setAttribute($data, 'uid', ['new1', 'new2'], false);
        $this->assertArrayHasKey('uid', $data);
        $this->assertInternalType('array', $data['uid']);
        $this->assertCount(2, $data['uid']);
        $this->assertContains('new1', $data['uid']);
        $this->assertContains('new2', $data['uid']);
        $this->assertEquals('new1', $data['uid'][0]);
        $this->assertEquals('new2', $data['uid'][1]);
    }

    public function testArrayAppendAttribute()
    {
        $data = ['uid' => ['old']];
        Attribute::setAttribute($data, 'uid', ['new1', 'new2'], true);
        $this->assertArrayHasKey('uid', $data);
        $this->assertInternalType('array', $data['uid']);
        $this->assertCount(3, $data['uid']);
        $this->assertContains('old', $data['uid']);
        $this->assertContains('new1', $data['uid']);
        $this->assertContains('new2', $data['uid']);
        $this->assertEquals('old', $data['uid'][0]);
        $this->assertEquals('new1', $data['uid'][1]);
        $this->assertEquals('new2', $data['uid'][2]);
    }

    public function testPasswordSettingSHA()
    {
        $data = [];
        Attribute::setPassword($data, 'pa$$w0rd', Attribute::PASSWORD_HASH_SHA);
        $password = Attribute::getAttribute($data, 'userPassword', 0);
        $this->assertEquals('{SHA}vi3X+3ptD4ulrdErXo+3W72mRyE=', $password);
    }

    public function testPasswordSettingMD5()
    {
        $data = [];
        Attribute::setPassword($data, 'pa$$w0rd', Attribute::PASSWORD_HASH_MD5);
        $password = Attribute::getAttribute($data, 'userPassword', 0);
        $this->assertEquals('{MD5}bJuLJ96h3bhF+WqiVnxnVA==', $password);
    }

    public function testPasswordSettingUnicodePwd()
    {
        $data = [];
        Attribute::setPassword($data, 'new', Attribute::PASSWORD_UNICODEPWD);
        $password = Attribute::getAttribute($data, 'unicodePwd', 0);
        $this->assertEquals("\x22\x00\x6E\x00\x65\x00\x77\x00\x22\x00", $password);
    }

    public function testPasswordSettingCustomAttribute()
    {
        $data = [];
        Attribute::setPassword(
            $data,
            'pa$$w0rd',
            Attribute::PASSWORD_HASH_SHA,
            'myAttribute'
        );
        $password = Attribute::getAttribute($data, 'myAttribute', 0);
        $this->assertNotNull($password);
    }

    public function testSetAttributeWithObject()
    {
        $data      = [];
        $object    = new \stdClass();
        $object->a = 1;
        $object->b = 1.23;
        $object->c = 'string';
        Attribute::setAttribute($data, 'object', $object);
        $this->assertEquals(serialize($object), $data['object'][0]);
    }

    public function testSetAttributeWithFilestream()
    {
        $data   = [];
        $stream = fopen(__DIR__ . '/_files/AttributeTest.input.txt', 'r');
        Attribute::setAttribute($data, 'file', $stream);
        fclose($stream);
        $this->assertEquals('String from file', $data['file'][0]);
    }

    public function testSetDateTimeValueLocal()
    {
        $ts   = mktime(12, 30, 30, 6, 25, 2008);
        $data = [];
        Attribute::setDateTimeAttribute($data, 'ts', $ts, false);
        $this->assertLocalDateTimeString($ts, $data['ts'][0]);
    }

    public function testSetDateTimeValueUtc()
    {
        $ts   = mktime(12, 30, 30, 6, 25, 2008);
        $data = [];
        Attribute::setDateTimeAttribute($data, 'ts', $ts, true);
        $this->assertUtcDateTimeString($ts, $data['ts'][0]);
    }

    public function testSetDateTimeValueLocalArray()
    {
        $ts   = [];
        $ts[] = mktime(12, 30, 30, 6, 25, 2008);
        $ts[] = mktime(1, 25, 30, 1, 2, 2008);
        $data = [];
        Attribute::setDateTimeAttribute($data, 'ts', $ts, false);
        $this->assertLocalDateTimeString($ts[0], $data['ts'][0]);
        $this->assertLocalDateTimeString($ts[1], $data['ts'][1]);
    }

    public function testSetDateTimeValueIllegal()
    {
        $ts   = 'dummy';
        $data = [];
        Attribute::setDateTimeAttribute($data, 'ts', $ts, false);
        $this->assertCount(0, $data['ts']);
    }

    public function testGetDateTimeValueFromLocal()
    {
        $ts   = mktime(12, 30, 30, 6, 25, 2008);
        $data = [];
        Attribute::setDateTimeAttribute($data, 'ts', $ts, false);
        $this->assertLocalDateTimeString($ts, $data['ts'][0]);
        $retTs = Attribute::getDateTimeAttribute($data, 'ts', 0);
        $this->assertEquals($ts, $retTs);
    }

    public function testGetDateTimeValueFromUtc()
    {
        $ts   = mktime(12, 30, 30, 6, 25, 2008);
        $data = [];
        Attribute::setDateTimeAttribute($data, 'ts', $ts, true);
        $this->assertUtcDateTimeString($ts, $data['ts'][0]);
        $retTs = Attribute::getDateTimeAttribute($data, 'ts', 0);
        $this->assertEquals($ts, $retTs);
    }

    public function testGetDateTimeValueFromArray()
    {
        $ts   = [];
        $ts[] = mktime(12, 30, 30, 6, 25, 2008);
        $ts[] = mktime(1, 25, 30, 1, 2, 2008);
        $data = [];
        Attribute::setDateTimeAttribute($data, 'ts', $ts, false);
        $this->assertLocalDateTimeString($ts[0], $data['ts'][0]);
        $this->assertLocalDateTimeString($ts[1], $data['ts'][1]);
        $retTs = Attribute::getDateTimeAttribute($data, 'ts');
        $this->assertEquals($ts[0], $retTs[0]);
        $this->assertEquals($ts[1], $retTs[1]);
    }

    public function testGetDateTimeValueIllegal()
    {
        $data  = ['ts' => ['dummy']];
        $retTs = Attribute::getDateTimeAttribute($data, 'ts', 0);
        $this->assertEquals('dummy', $retTs);
    }

    public function testGetDateTimeValueNegativeOffset()
    {
        $data      = ['ts' => ['20080612143045-0700']];
        $retTs     = Attribute::getDateTimeAttribute($data, 'ts', 0);
        $tsCompare = gmmktime(21, 30, 45, 6, 12, 2008);
        $this->assertEquals($tsCompare, $retTs);
    }

    public function testGetDateTimeValueNegativeOffset2()
    {
        $data      = ['ts' => ['20080612143045-0715']];
        $retTs     = Attribute::getDateTimeAttribute($data, 'ts', 0);
        $tsCompare = gmmktime(21, 45, 45, 6, 12, 2008);
        $this->assertEquals($tsCompare, $retTs);
    }

    public function testRemoveAttributeValueSimple()
    {
        $data = ['test' => ['value1', 'value2', 'value3', 'value3']];
        Attribute::removeFromAttribute($data, 'test', 'value2');
        $this->assertArrayHasKey('test', $data);
        $this->assertInternalType('array', $data['test']);
        $this->assertCount(3, $data['test']);
        $this->assertContains('value1', $data['test']);
        $this->assertContains('value3', $data['test']);
        $this->assertNotContains('value2', $data['test']);
    }

    public function testRemoveAttributeValueArray()
    {
        $data = ['test' => ['value1', 'value2', 'value3', 'value3']];
        Attribute::removeFromAttribute($data, 'test', ['value1', 'value2']);
        $this->assertArrayHasKey('test', $data);
        $this->assertInternalType('array', $data['test']);
        $this->assertCount(2, $data['test']);
        $this->assertContains('value3', $data['test']);
        $this->assertNotContains('value1', $data['test']);
        $this->assertNotContains('value2', $data['test']);
    }

    public function testRemoveAttributeMultipleValueSimple()
    {
        $data = ['test' => ['value1', 'value2', 'value3', 'value3']];
        Attribute::removeFromAttribute($data, 'test', 'value3');
        $this->assertArrayHasKey('test', $data);
        $this->assertInternalType('array', $data['test']);
        $this->assertCount(2, $data['test']);
        $this->assertContains('value1', $data['test']);
        $this->assertContains('value2', $data['test']);
        $this->assertNotContains('value3', $data['test']);
    }

    public function testRemoveAttributeMultipleValueArray()
    {
        $data = ['test' => ['value1', 'value2', 'value3', 'value3']];
        Attribute::removeFromAttribute($data, 'test', ['value1', 'value3']);
        $this->assertArrayHasKey('test', $data);
        $this->assertInternalType('array', $data['test']);
        $this->assertCount(1, $data['test']);
        $this->assertContains('value2', $data['test']);
        $this->assertNotContains('value1', $data['test']);
        $this->assertNotContains('value3', $data['test']);
    }

    public function testRemoveAttributeValueBoolean()
    {
        $data = ['test' => ['TRUE', 'FALSE', 'TRUE', 'FALSE']];
        Attribute::removeFromAttribute($data, 'test', false);
        $this->assertArrayHasKey('test', $data);
        $this->assertInternalType('array', $data['test']);
        $this->assertCount(2, $data['test']);
        $this->assertContains('TRUE', $data['test']);
        $this->assertNotContains('FALSE', $data['test']);
    }

    public function testRemoveAttributeValueInteger()
    {
        $data = ['test' => ['1', '2', '3', '4']];
        Attribute::removeFromAttribute($data, 'test', [2, 4]);
        $this->assertArrayHasKey('test', $data);
        $this->assertInternalType('array', $data['test']);
        $this->assertCount(2, $data['test']);
        $this->assertContains('1', $data['test']);
        $this->assertContains('3', $data['test']);
        $this->assertNotContains('2', $data['test']);
        $this->assertNotContains('4', $data['test']);
    }

    public function testRemoveDuplicates()
    {
        $data     = [
            'strings1' => ['value1', 'value2', 'value2', 'value3'],
            'strings2' => ['value1', 'value2', 'value3', 'value4'],
            'boolean1' => ['TRUE', 'TRUE', 'TRUE', 'TRUE'],
            'boolean2' => ['TRUE', 'FALSE', 'TRUE', 'FALSE'],
        ];
        $expected = [
            'strings1' => ['value1', 'value2', 'value3'],
            'strings2' => ['value1', 'value2', 'value3', 'value4'],
            'boolean1' => ['TRUE'],
            'boolean2' => ['TRUE', 'FALSE'],
        ];
        Attribute::removeDuplicatesFromAttribute($data, 'strings1');
        Attribute::removeDuplicatesFromAttribute($data, 'strings2');
        Attribute::removeDuplicatesFromAttribute($data, 'boolean1');
        Attribute::removeDuplicatesFromAttribute($data, 'boolean2');
        $this->assertEquals($expected, $data);
    }

    public function testHasValue()
    {
        $data = [
            'strings1' => ['value1', 'value2', 'value2', 'value3'],
            'strings2' => ['value1', 'value2', 'value3', 'value4'],
            'boolean1' => ['TRUE', 'TRUE', 'TRUE', 'TRUE'],
            'boolean2' => ['TRUE', 'FALSE', 'TRUE', 'FALSE'],
        ];

        $this->assertTrue(Attribute::attributeHasValue($data, 'strings1', 'value1'));
        $this->assertFalse(Attribute::attributeHasValue($data, 'strings1', 'value4'));
        $this->assertTrue(Attribute::attributeHasValue($data, 'boolean1', true));
        $this->assertFalse(Attribute::attributeHasValue($data, 'boolean1', false));

        $this->assertTrue(Attribute::attributeHasValue(
            $data,
            'strings1',
            ['value1', 'value2']
        ));
        $this->assertTrue(Attribute::attributeHasValue(
            $data,
            'strings1',
            ['value1', 'value2', 'value3']
        ));
        $this->assertFalse(Attribute::attributeHasValue(
            $data,
            'strings1',
            ['value1', 'value2', 'value3', 'value4']
        ));
        $this->assertTrue(Attribute::attributeHasValue(
            $data,
            'strings2',
            ['value1', 'value2', 'value3', 'value4']
        ));

        $this->assertTrue(Attribute::attributeHasValue(
            $data,
            'boolean2',
            [true, false]
        ));
        $this->assertFalse(Attribute::attributeHasValue(
            $data,
            'boolean1',
            [true, false]
        ));
    }

    public function testPasswordGenerationSSHA()
    {
        $password = 'pa$$w0rd';
        $ssha     = Attribute::createPassword($password, Attribute::PASSWORD_HASH_SSHA);
        $encoded  = substr($ssha, strpos($ssha, '}'));
        $binary   = base64_decode($encoded);
        $this->assertEquals(24, strlen($binary));
        $hash = substr($binary, 0, 20);
        $salt = substr($binary, 20);
        $this->assertEquals(4, strlen($salt));
        $this->assertEquals(sha1($password . $salt, true), $hash);
    }

    public function testPasswordGenerationSHA()
    {
        $password = 'pa$$w0rd';
        $sha      = Attribute::createPassword($password, Attribute::PASSWORD_HASH_SHA);
        $encoded  = substr($sha, strpos($sha, '}'));
        $binary   = base64_decode($encoded);
        $this->assertEquals(20, strlen($binary));
        $this->assertEquals(sha1($password, true), $binary);
    }

    public function testPasswordGenerationSMD5()
    {
        $password = 'pa$$w0rd';
        $smd5     = Attribute::createPassword($password, Attribute::PASSWORD_HASH_SMD5);
        $encoded  = substr($smd5, strpos($smd5, '}'));
        $binary   = base64_decode($encoded);
        $this->assertEquals(20, strlen($binary));
        $hash = substr($binary, 0, 16);
        $salt = substr($binary, 16);
        $this->assertEquals(4, strlen($salt));
        $this->assertEquals(md5($password . $salt, true), $hash);
    }

    public function testPasswordGenerationMD5()
    {
        $password = 'pa$$w0rd';
        $md5      = Attribute::createPassword($password, Attribute::PASSWORD_HASH_MD5);
        $encoded  = substr($md5, strpos($md5, '}'));
        $binary   = base64_decode($encoded);
        $this->assertEquals(16, strlen($binary));
        $this->assertEquals(md5($password, true), $binary);
    }

    public function testPasswordGenerationUnicodePwd()
    {
        $password   = 'new';
        $unicodePwd = Attribute::createPassword($password, Attribute::PASSWORD_UNICODEPWD);
        $this->assertEquals(10, strlen($unicodePwd));
        $this->assertEquals("\x22\x00\x6E\x00\x65\x00\x77\x00\x22\x00", $unicodePwd);
    }
}
