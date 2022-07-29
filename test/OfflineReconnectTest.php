<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Laminas\Ldap\Ldap;
use LaminasTest\Ldap\TestAsset\BuiltinFunctionMocks;
use phpmock\Mock;

class OfflineReconnectTest extends OfflineTest
{
    /**
     * Enables mocks for ldap_connect(), ldap_bind(), and ldap_set_option().
     * Not all tests need or are compatible with this, so it is called expliclty
     * by tests that do.
     */
    protected function activateBindableOfflineMocks(): void
    {
        BuiltinFunctionMocks::$ldap_connect_mock->enable();
        BuiltinFunctionMocks::$ldap_bind_mock->enable();
        BuiltinFunctionMocks::$ldap_set_option_mock->enable();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mock::disableAll();
    }

    protected function reportErrorsAsConnectionFailure(): void
    {
        $ldap_errno = $this->getFunctionMock('Laminas\\Ldap', 'ldap_errno');
        $ldap_errno->expects($this->atLeastOnce())
            ->willReturn(-1);
    }

    public function testAddingAttributesReconnect(): void
    {
        $this->activateBindableOfflineMocks();
        $this->reportErrorsAsConnectionFailure();

        $ldap_mod_add = $this->getFunctionMock('Laminas\\Ldap', 'ldap_mod_add');
        $ldap_mod_add->expects($this->exactly(2))
            ->willReturnOnConsecutiveCalls(false, true);

        $ldap = new Ldap([
            'host'              => 'offline phony',
            'reconnectAttempts' => 1,
        ]);
        $ldap->bind();
        $ldap->addAttributes('foo', ['bar']);
        $this->assertEquals(1, $ldap->getReconnectsAttempted());
    }

    public function testRemovingAttributesReconnect(): void
    {
        $this->activateBindableOfflineMocks();
        $this->reportErrorsAsConnectionFailure();

        $ldap_mod_del = $this->getFunctionMock('Laminas\\Ldap', 'ldap_mod_del');
        $ldap_mod_del->expects($this->exactly(2))
            ->willReturnOnConsecutiveCalls(false, true);

        $ldap = new Ldap([
            'host'              => 'offline phony',
            'reconnectAttempts' => 1,
        ]);
        $ldap->bind();
        $ldap->deleteAttributes('foo', ['bar']);
        $this->assertEquals(1, $ldap->getReconnectsAttempted());
    }
}
