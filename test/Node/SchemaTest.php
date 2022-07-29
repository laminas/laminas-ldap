<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Node;

use BadMethodCallException;
use Laminas\Ldap\Node;
use Laminas\Ldap\Node\Schema;
use Laminas\Ldap\Node\Schema\ObjectClass\OpenLdap;
use LaminasTest\Ldap as TestLdap;

use function array_key_exists;
use function serialize;
use function unserialize;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Node
 */
class SchemaTest extends TestLdap\AbstractOnlineTestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schema = $this->getLDAP()->getSchema();
    }

    public function testSchemaNode()
    {
        $schema = $this->getLDAP()->getSchema();

        $this->assertEquals($this->schema, $schema);
        $this->assertSame($this->schema, $schema);

        $serial   = serialize($this->schema);
        $schemaUn = unserialize($serial);
        $this->assertEquals($this->schema, $schemaUn);
        $this->assertNotSame($this->schema, $schemaUn);
    }

    public function testGetters()
    {
        $this->assertIsArray($this->schema->getAttributeTypes());
        $this->assertIsArray($this->schema->getObjectClasses());

        switch ($this->getLDAP()->getRootDse()->getServerType()) {
            case Node\RootDse::SERVER_TYPE_ACTIVEDIRECTORY:
                break;
            case Node\RootDse::SERVER_TYPE_EDIRECTORY:
                break;
            case Node\RootDse::SERVER_TYPE_OPENLDAP:
                $this->assertIsArray($this->schema->getLDAPSyntaxes());
                $this->assertIsArray($this->schema->getMatchingRules());
                $this->assertIsArray($this->schema->getMatchingRuleUse());
                break;
        }
    }

    public function testSetterWillThrowException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->schema->objectClass = 'illegal';
    }

    public function testOffsetSetWillThrowException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->schema['objectClass'] = 'illegal';
    }

    public function testUnsetterWillThrowException()
    {
        $this->expectException(BadMethodCallException::class);
        unset($this->schema->objectClass);
    }

    public function testOffsetUnsetWillThrowException()
    {
        $this->expectException(BadMethodCallException::class);
        unset($this->schema['objectClass']);
    }

    public function testOpenLDAPSchema()
    {
        if (
            $this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_OPENLDAP
        ) {
            $this->markTestSkipped('Test can only be run on an OpenLDAP server');
        }

        $objectClasses  = $this->schema->getObjectClasses();
        $attributeTypes = $this->schema->getAttributeTypes();

        $this->assertArrayHasKey('organizationalUnit', $objectClasses);
        $ou = $objectClasses['organizationalUnit'];
        $this->assertInstanceOf(OpenLdap::class, $ou);
        $this->assertEquals('organizationalUnit', $ou->getName());
        $this->assertEquals('2.5.6.5', $ou->getOid());
        $this->assertEquals(['objectClass', 'ou'], $ou->getMustContain());
        $this->assertEquals([
            'businessCategory',
            'description',
            'destinationIndicator',
            'facsimileTelephoneNumber',
            'internationaliSDNNumber',
            'l',
            'physicalDeliveryOfficeName',
            'postOfficeBox',
            'postalAddress',
            'postalCode',
            'preferredDeliveryMethod',
            'registeredAddress',
            'searchGuide',
            'seeAlso',
            'st',
            'street',
            'telephoneNumber',
            'teletexTerminalIdentifier',
            'telexNumber',
            'userPassword',
            'x121Address',
        ], $ou->getMayContain());
        $this->assertEquals('RFC2256: an organizational unit', $ou->getDescription());
        $this->assertEquals(Schema::OBJECTCLASS_TYPE_STRUCTURAL, $ou->getType());
        $this->assertEquals(['top'], $ou->getParentClasses());

        $this->assertEquals('2.5.6.5', $ou->oid);
        $this->assertEquals('organizationalUnit', $ou->name);
        $this->assertEquals('RFC2256: an organizational unit', $ou->desc);
        $this->assertFalse($ou->obsolete);
        $this->assertEquals(['top'], $ou->sup);
        $this->assertFalse($ou->abstract);
        $this->assertTrue($ou->structural);
        $this->assertFalse($ou->auxiliary);
        $this->assertEquals(['ou'], $ou->must);
        $this->assertEquals([
            'userPassword',
            'searchGuide',
            'seeAlso',
            'businessCategory',
            'x121Address',
            'registeredAddress',
            'destinationIndicator',
            'preferredDeliveryMethod',
            'telexNumber',
            'teletexTerminalIdentifier',
            'telephoneNumber',
            'internationaliSDNNumber',
            'facsimileTelephoneNumber',
            'street',
            'postOfficeBox',
            'postalCode',
            'postalAddress',
            'physicalDeliveryOfficeName',
            'st',
            'l',
            'description',
        ], $ou->may);
        $this->assertEquals("( 2.5.6.5 NAME 'organizationalUnit' "
                . "DESC 'RFC2256: an organizational unit' SUP top STRUCTURAL MUST ou "
                . "MAY ( userPassword $ searchGuide $ seeAlso $ businessCategory $ x121Address $ "
                . "registeredAddress $ destinationIndicator $ preferredDeliveryMethod $ telexNumber $ "
                . "teletexTerminalIdentifier $ telephoneNumber $ internationaliSDNNumber $ "
                . "facsimileTelephoneNumber $ street $ postOfficeBox $ postalCode $ postalAddress $ "
                . "physicalDeliveryOfficeName $ st $ l $ description ) )", $ou->_string);

        $this->assertEquals([], $ou->aliases);
        $this->assertSame($objectClasses['top'], $ou->_parents[0]);

        $this->assertArrayHasKey('ou', $attributeTypes);
        $ou = $attributeTypes['ou'];
        $this->assertInstanceOf(\Laminas\Ldap\Node\Schema\AttributeType\OpenLdap::class, $ou);
        $this->assertEquals('ou', $ou->getName());
        $this->assertEquals('2.5.4.11', $ou->getOid());
        $this->assertEquals('1.3.6.1.4.1.1466.115.121.1.15', $ou->getSyntax());
        $this->assertEquals(32768, $ou->getMaxLength());
        $this->assertFalse($ou->isSingleValued());
        $this->assertEquals('RFC2256: organizational unit this object belongs to', $ou->getDescription());

        $this->assertEquals('2.5.4.11', $ou->oid);
        $this->assertEquals('ou', $ou->name);
        $this->assertEquals('RFC2256: organizational unit this object belongs to', $ou->desc);
        $this->assertFalse($ou->obsolete);
        $this->assertEquals(['name'], $ou->sup);
        $this->assertNull($ou->equality);
        $this->assertNull($ou->ordering);
        $this->assertNull($ou->substr);
        $this->assertNull($ou->syntax);
        $this->assertNull($ou->{'max-length'});
        $this->assertFalse($ou->{'single-value'});
        $this->assertFalse($ou->collective);
        $this->assertFalse($ou->{'no-user-modification'});
        $this->assertEquals('userApplications', $ou->usage);
        $this->assertEquals("( 2.5.4.11 NAME ( 'ou' 'organizationalUnitName' ) "
                . "DESC 'RFC2256: organizational unit this object belongs to' SUP name )", $ou->_string);
        $this->assertEquals(['organizationalUnitName'], $ou->aliases);
        $this->assertSame($attributeTypes['name'], $ou->_parents[0]);
    }

    public function testActiveDirectorySchema()
    {
        if (
            $this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_ACTIVEDIRECTORY
        ) {
            $this->markTestSkipped('Test can only be run on an Active Directory server');
        }

        $objectClasses  = $this->schema->getObjectClasses();
        $attributeTypes = $this->schema->getAttributeTypes();
    }

    public function testeDirectorySchema()
    {
        if (
            $this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_EDIRECTORY
        ) {
            $this->markTestSkipped('Test can only be run on an eDirectory server');
        }
        $this->markTestIncomplete("Novell eDirectory schema parsing is incomplete");
    }

    public function testOpenLDAPSchemaAttributeTypeInheritance()
    {
        if (
            $this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_OPENLDAP
        ) {
            $this->markTestSkipped('Test can only be run on an OpenLDAP server');
        }

        $attributeTypes = $this->schema->getAttributeTypes();

        $name = $attributeTypes['name'];
        $cn   = $attributeTypes['cn'];

        $this->assertEquals('2.5.4.41', $name->getOid());
        $this->assertEquals('2.5.4.3', $cn->getOid());
        $this->assertNull($name->sup);
        $this->assertEquals(['name'], $cn->sup);

        $this->assertEquals('caseIgnoreMatch', $name->equality);
        $this->assertNull($name->ordering);
        $this->assertEquals('caseIgnoreSubstringsMatch', $name->substr);
        $this->assertEquals('1.3.6.1.4.1.1466.115.121.1.15', $name->syntax);
        $this->assertEquals('1.3.6.1.4.1.1466.115.121.1.15', $name->getSyntax());
        $this->assertEquals(32768, $name->{'max-length'});
        $this->assertEquals(32768, $name->getMaxLength());

        $this->assertNull($cn->equality);
        $this->assertNull($cn->ordering);
        $this->assertNull($cn->substr);
        $this->assertNull($cn->syntax);
        $this->assertEquals('1.3.6.1.4.1.1466.115.121.1.15', $cn->getSyntax());
        $this->assertNull($cn->{'max-length'});
        $this->assertEquals(32768, $cn->getMaxLength());
    }

    public function testOpenLDAPSchemaObjectClassInheritance()
    {
        if (
            $this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_OPENLDAP
        ) {
            $this->markTestSkipped('Test can only be run on an OpenLDAP server');
        }

        $objectClasses = $this->schema->getObjectClasses();

        if (
            ! array_key_exists('certificationAuthority', $objectClasses)
            || ! array_key_exists('certificationAuthority-V2', $objectClasses)
        ) {
            $this->markTestSkipped('This requires OpenLDAP core schema');
        }

        $ca  = $objectClasses['certificationAuthority'];
        $ca2 = $objectClasses['certificationAuthority-V2'];

        $this->assertEquals('2.5.6.16', $ca->getOid());
        $this->assertEquals('2.5.6.16.2', $ca2->getOid());
        $this->assertEquals(['top'], $ca->sup);
        $this->assertEquals(['certificationAuthority'], $ca2->sup);

        $this->assertEquals([
            'authorityRevocationList',
            'certificateRevocationList',
            'cACertificate',
        ], $ca->must);
        $this->assertEquals([
            'authorityRevocationList',
            'cACertificate',
            'certificateRevocationList',
            'objectClass',
        ], $ca->getMustContain());
        $this->assertEquals(['crossCertificatePair'], $ca->may);
        $this->assertEquals(['crossCertificatePair'], $ca->getMayContain());

        $this->assertEquals([], $ca2->must);
        $this->assertEquals([
            'authorityRevocationList',
            'cACertificate',
            'certificateRevocationList',
            'objectClass',
        ], $ca2->getMustContain());
        $this->assertEquals(['deltaRevocationList'], $ca2->may);
        $this->assertEquals(
            ['crossCertificatePair', 'deltaRevocationList'],
            $ca2->getMayContain()
        );
    }

    public function testOpenLDAPSchemaAttributeTypeAliases()
    {
        if (
            $this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_OPENLDAP
        ) {
            $this->markTestSkipped('Test can only be run on an OpenLDAP server');
        }

        $attributeTypes = $this->schema->getAttributeTypes();
        $this->assertArrayHasKey('cn', $attributeTypes);
        $this->assertArrayHasKey('commonName', $attributeTypes);
        $ob1 = $attributeTypes['cn'];
        $ob2 = $attributeTypes['commonName'];
        $this->assertSame($ob1, $ob2);
    }

    public function testOpenLDAPSchemaObjectClassAliases()
    {
        if (
            $this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_OPENLDAP
        ) {
            $this->markTestSkipped('Test can only be run on an OpenLDAP server');
        }

        $objectClasses = $this->schema->getObjectClasses();
        $this->assertArrayHasKey('OpenLDAProotDSE', $objectClasses);
        $this->assertArrayHasKey('LDAProotDSE', $objectClasses);
        $ob1 = $objectClasses['OpenLDAProotDSE'];
        $ob2 = $objectClasses['LDAProotDSE'];
        $this->assertSame($ob1, $ob2);
    }
}
