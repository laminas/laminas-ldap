<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('ErrorHandler', ['src/ErrorHandler.php', 'src/ErrorHandlerInterface.php', 'src/Handler.php'])
    ->layer('LdapException', 'src/Exception/LdapException.php')
    ->layer('Converter', 'src/Converter')
    ->layer('Filter', 'src/Filter')
    ->layer('Collection', 'src/Collection')
    ->layer('Ldif', 'src/Ldif')
    ->layer('NodeRootDse', 'src/Node/RootDse')
    ->layer('NodeSchema', 'src/Node/Schema')
    ->layerPattern(
        'Exception',
        '/^Laminas\\\\Ldap\\\\Exception\\\\.*$/',
        '/^Laminas\\\\Ldap\\\\Exception\\\\LdapException$/'
    )
    ->layerPattern(
        'Node',
        '/^Laminas\\\\Ldap\\\\Node\\\\.*$/',
        '/^Laminas\\\\Ldap\\\\Node\\\\(RootDse|Schema)\\\\.*$/'
    )
    ->layerPattern(
        'Ldap',
        '/^Laminas\\\\Ldap\\\\.*$/',
        [
            '/^Laminas\\\\Ldap\\\\.+\\\\.*$/',
            '/^Laminas\\\\Ldap\\\\(ErrorHandler|ErrorHandlerInterface|Handler)$/',
        ]
    )
    ->ruleset([
        'ErrorHandler'  => [],
        'Exception'     => [],
        'LdapException' => ['Exception', 'Ldap'],
        'Converter'     => ['ErrorHandler'],
        'Filter'        => ['Converter', 'Exception'],
        'Collection'    => ['+LdapException', 'ErrorHandler'],
        'Ldif'          => ['Ldap'],
        'NodeRootDse'   => ['+LdapException', 'Node'],
        'NodeSchema'    => ['+LdapException', 'Converter', 'Node'],
        'Node'          => ['+LdapException', 'Collection', 'NodeRootDse', 'NodeSchema'],
        'Ldap'          => ['+Filter', 'LdapException', 'ErrorHandler', 'Collection', 'Ldif', 'Node'],
    ]);
