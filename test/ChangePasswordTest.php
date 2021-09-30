<?php

namespace LaminasTest\Ldap;

use Laminas\Ldap;
use Laminas\Ldap\Exception;
use Laminas\Ldap\Node;

/**
 * @group      Laminas_Ldap
 */

class ChangePasswordTest extends AbstractOnlineTestCase
{
    public function testAddNewUserWithPasswordOpenLDAP()
    {
        if ($this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_OPENLDAP
        ) {
            $this->markTestSkipped('Test can only be run on an OpenLDAP server');
        }

        $dn       = $this->createDn('uid=newuser,');
        $data     = [];
        $password = 'pa$$w0rd';
        Ldap\Attribute::setAttribute($data, 'uid', 'newuser', false);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'account', true);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'simpleSecurityObject', true);
        Ldap\Attribute::setPassword(
            $data,
            $password,
            Ldap\Attribute::PASSWORD_HASH_SSHA,
            'userPassword'
        );

        try {
            $this->getLDAP()->add($dn, $data);

            $this->assertInstanceOf('Laminas\Ldap\Ldap', $this->getLDAP()->bind($dn, $password));

            $this->getLDAP()->bind();
            $this->getLDAP()->delete($dn);
        } catch (Exception\LdapException $e) {
            $this->getLDAP()->bind();
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testChangePasswordWithUserAccountOpenLDAP()
    {
        if ($this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_OPENLDAP
        ) {
            $this->markTestSkipped('Test can only be run on an OpenLDAP server');
        }

        $dn       = $this->createDn('uid=newuser,');
        $data     = [];
        $password = 'pa$$w0rd';
        Ldap\Attribute::setAttribute($data, 'uid', 'newuser', false);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'account', true);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'simpleSecurityObject', true);
        Ldap\Attribute::setPassword(
            $data,
            $password,
            Ldap\Attribute::PASSWORD_HASH_SSHA,
            'userPassword'
        );

        try {
            $this->getLDAP()->add($dn, $data);

            $this->getLDAP()->bind($dn, $password);

            $newPasswd = 'newpasswd';
            $newData   = [];
            Ldap\Attribute::setPassword(
                $newData,
                $newPasswd,
                Ldap\Attribute::PASSWORD_HASH_SHA,
                'userPassword'
            );
            $this->getLDAP()->update($dn, $newData);

            try {
                $this->getLDAP()->bind($dn, $password);
                $this->fail('Expected exception not thrown');
            } catch (Exception\LdapException $zle) {
                $message = $zle->getMessage();
                $this->assertTrue(strstr($message, 'Invalid credentials')
                        || strstr($message, 'Server is unwilling to perform'));
            }

            $this->assertInstanceOf('Laminas\Ldap\Ldap', $this->getLDAP()->bind($dn, $newPasswd));

            $this->getLDAP()->bind();
            $this->getLDAP()->delete($dn);
        } catch (Exception\LdapException $e) {
            $this->getLDAP()->bind();
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testAddNewUserWithPasswordActiveDirectory()
    {
        if ($this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_ACTIVEDIRECTORY
        ) {
            $this->markTestSkipped('Test can only be run on an ActiveDirectory server');
        }
        $options = $this->getLDAP()->getOptions();
        if ($options['useSsl'] !== true && $options['useStartTls'] !== true) {
            $this->markTestSkipped('Test can only be run on an SSL or TLS secured connection');
        }

        $dn       = $this->createDn('cn=New User,');
        $data     = [];
        $password = 'pa$$w0rd';
        Ldap\Attribute::setAttribute($data, 'cn', 'New User', false);
        Ldap\Attribute::setAttribute($data, 'displayName', 'New User', false);
        Ldap\Attribute::setAttribute($data, 'sAMAccountName', 'newuser', false);
        Ldap\Attribute::setAttribute($data, 'userAccountControl', 512, false);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'person', true);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'organizationalPerson', true);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'user', true);
        Ldap\Attribute::setPassword(
            $data,
            $password,
            Ldap\Attribute::PASSWORD_UNICODEPWD,
            'unicodePwd'
        );

        try {
            $this->getLDAP()->add($dn, $data);

            $this->assertInstanceOf('Laminas\Ldap', $this->getLDAP()->bind($dn, $password));

            $this->getLDAP()->bind();
            $this->getLDAP()->delete($dn);
        } catch (Exception\LdapException $e) {
            $this->getLDAP()->bind();
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }

    public function testChangePasswordWithUserAccountActiveDirectory()
    {
        if ($this->getLDAP()->getRootDse()->getServerType() !==
            Node\RootDse::SERVER_TYPE_ACTIVEDIRECTORY
        ) {
            $this->markTestSkipped('Test can only be run on an ActiveDirectory server');
        }
        $options = $this->getLDAP()->getOptions();
        if ($options['useSsl'] !== true && $options['useStartTls'] !== true) {
            $this->markTestSkipped('Test can only be run on an SSL or TLS secured connection');
        }

        $dn       = $this->createDn('cn=New User,');
        $data     = [];
        $password = 'pa$$w0rd';
        Ldap\Attribute::setAttribute($data, 'cn', 'New User', false);
        Ldap\Attribute::setAttribute($data, 'displayName', 'New User', false);
        Ldap\Attribute::setAttribute($data, 'sAMAccountName', 'newuser', false);
        Ldap\Attribute::setAttribute($data, 'userAccountControl', 512, false);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'person', true);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'organizationalPerson', true);
        Ldap\Attribute::setAttribute($data, 'objectClass', 'user', true);
        Ldap\Attribute::setPassword(
            $data,
            $password,
            Ldap\Attribute::PASSWORD_UNICODEPWD,
            'unicodePwd'
        );

        try {
            $this->getLDAP()->add($dn, $data);

            $this->getLDAP()->bind($dn, $password);

            $newPasswd = 'newpasswd';
            $newData   = [];
            Ldap\Attribute::setPassword($newData, $newPasswd, Ldap\Attribute::PASSWORD_UNICODEPWD);
            $this->getLDAP()->update($dn, $newData);

            try {
                $this->getLDAP()->bind($dn, $password);
                $this->fail('Expected exception not thrown');
            } catch (Exception\LdapException $zle) {
                $message = $zle->getMessage();
                $this->assertTrue(strstr($message, 'Invalid credentials')
                        || strstr($message, 'Server is unwilling to perform'));
            }

            $this->assertInstanceOf('\Laminas\Ldap\Ldap', $this->getLDAP()->bind($dn, $newPasswd));

            $this->getLDAP()->bind();
            $this->getLDAP()->delete($dn);
        } catch (Exception\LdapException $e) {
            $this->getLDAP()->bind();
            if ($this->getLDAP()->exists($dn)) {
                $this->getLDAP()->delete($dn);
            }
            $this->fail($e->getMessage());
        }
    }
}
