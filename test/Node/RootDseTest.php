<?php

declare(strict_types=1);

namespace LaminasTest\Ldap\Node;

use BadMethodCallException;
use Laminas\Ldap\Node;
use LaminasTest\Ldap as TestLdap;

/**
 * @group      Laminas_Ldap
 * @group      Laminas_Ldap_Node
 */
class RootDseTest extends TestLdap\AbstractOnlineTestCase
{
    public function testLoadRootDseNode()
    {
        $root1 = $this->getLDAP()->getRootDse();
        $root2 = $this->getLDAP()->getRootDse();

        $this->assertEquals($root1, $root2);
        $this->assertSame($root1, $root2);
    }

    public function testSupportCheckMethods()
    {
        $root = $this->getLDAP()->getRootDse();

        $this->assertIsBool($root->supportsSaslMechanism('GSSAPI'));
        $this->assertIsBool($root->supportsSaslMechanism(['GSSAPI', 'DIGEST-MD5']));
        $this->assertIsBool($root->supportsVersion('3'));
        $this->assertIsBool($root->supportsVersion(3));
        $this->assertIsBool($root->supportsVersion(['3', '2']));
        $this->assertIsBool($root->supportsVersion([3, 2]));

        switch ($root->getServerType()) {
            case Node\RootDse::SERVER_TYPE_ACTIVEDIRECTORY:
                $this->assertIsBool($root->supportsControl('1.2.840.113556.1.4.319'));
                $this->assertIsBool($root->supportsControl([
                    '1.2.840.113556.1.4.319',
                    '1.2.840.113556.1.4.473',
                ]));
                $this->assertIsBool($root->supportsCapability('1.3.6.1.4.1.4203.1.9.1.1'));
                $this->assertIsBool($root->supportsCapability([
                    '1.3.6.1.4.1.4203.1.9.1.1',
                    '2.16.840.1.113730.3.4.18',
                ]));
                $this->assertIsBool($root->supportsPolicy('unknown'));
                $this->assertIsBool($root->supportsPolicy(['unknown', 'unknown']));
                break;
            case Node\RootDse::SERVER_TYPE_EDIRECTORY:
                $this->assertIsBool($root->supportsExtension('1.3.6.1.4.1.1466.20037'));
                $this->assertIsBool($root->supportsExtension([
                    '1.3.6.1.4.1.1466.20037',
                    '1.3.6.1.4.1.4203.1.11.1',
                ]));
                break;
            case Node\RootDse::SERVER_TYPE_OPENLDAP:
                $this->assertIsBool($root->supportsControl('1.3.6.1.4.1.4203.1.9.1.1'));
                $this->assertIsBool($root->supportsControl([
                    '1.3.6.1.4.1.4203.1.9.1.1',
                    '2.16.840.1.113730.3.4.18',
                ]));
                $this->assertIsBool($root->supportsExtension('1.3.6.1.4.1.1466.20037'));
                $this->assertIsBool($root->supportsExtension([
                    '1.3.6.1.4.1.1466.20037',
                    '1.3.6.1.4.1.4203.1.11.1',
                ]));
                $this->assertIsBool($root->supportsFeature('1.3.6.1.1.14'));
                $this->assertIsBool($root->supportsFeature([
                    '1.3.6.1.1.14',
                    '1.3.6.1.4.1.4203.1.5.1',
                ]));
                break;
        }
    }

    public function testGetters()
    {
        $root = $this->getLDAP()->getRootDse();

        $this->assertIsArray($root->getNamingContexts());
        $this->assertIsString($root->getSubschemaSubentry());

        switch ($root->getServerType()) {
            case Node\RootDse::SERVER_TYPE_ACTIVEDIRECTORY:
                $this->assertIsString($root->getConfigurationNamingContext());
                $this->assertIsString($root->getCurrentTime());
                $this->assertIsString($root->getDefaultNamingContext());
                $this->assertIsString($root->getDnsHostName());
                $this->assertIsString($root->getDomainControllerFunctionality());
                $this->assertIsString($root->getDomainFunctionality());
                $this->assertIsString($root->getDsServiceName());
                $this->assertIsString($root->getForestFunctionality());
                $this->assertIsString($root->getHighestCommittedUSN());
                $this->assertIsBool($root->getIsGlobalCatalogReady());
                $this->assertIsBool($root->getIsSynchronized());
                $this->assertIsString($root->getLDAPServiceName());
                $this->assertIsString($root->getRootDomainNamingContext());
                $this->assertIsString($root->getSchemaNamingContext());
                $this->assertIsString($root->getServerName());
                break;
            case Node\RootDse::SERVER_TYPE_EDIRECTORY:
                $this->assertIsString($root->getVendorName());
                $this->assertIsString($root->getVendorVersion());
                $this->assertIsString($root->getDsaName());
                $this->assertIsString($root->getStatisticsErrors());
                $this->assertIsString($root->getStatisticsSecurityErrors());
                $this->assertIsString($root->getStatisticsChainings());
                $this->assertIsString($root->getStatisticsReferralsReturned());
                $this->assertIsString($root->getStatisticsExtendedOps());
                $this->assertIsString($root->getStatisticsAbandonOps());
                $this->assertIsString($root->getStatisticsWholeSubtreeSearchOps());
                break;
            case Node\RootDse::SERVER_TYPE_OPENLDAP:
                $this->assertNullOrString($root->getConfigContext());
                $this->assertNullOrString($root->getMonitorContext());
                break;
        }
    }

    /** @param mixed $value */
    protected function assertNullOrString($value): void
    {
        if ($value === null) {
            $this->assertNull($value);
        } else {
            $this->assertIsString($value);
        }
    }

    public function testSetterWillThrowException()
    {
        $root = $this->getLDAP()->getRootDse();
        $this->expectException(BadMethodCallException::class);
        $root->objectClass = 'illegal';
    }

    public function testOffsetSetWillThrowException()
    {
        $root = $this->getLDAP()->getRootDse();
        $this->expectException(BadMethodCallException::class);
        $root['objectClass'] = 'illegal';
    }

    public function testUnsetterWillThrowException()
    {
        $root = $this->getLDAP()->getRootDse();
        $this->expectException(BadMethodCallException::class);
        unset($root->objectClass);
    }

    public function testOffsetUnsetWillThrowException()
    {
        $root = $this->getLDAP()->getRootDse();
        $this->expectException(BadMethodCallException::class);
        unset($root['objectClass']);
    }
}
