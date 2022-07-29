<?php

declare(strict_types=1);

use LaminasTest\Ldap\TestAsset\BuiltinFunctionMocks;

/*
 * Set error reporting to the level to which Laminas code must comply.
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Setup autoloading
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_LAMINAS_OB_ENABLED') && constant('TESTS_LAMINAS_OB_ENABLED')) {
    ob_start();
}

/**
 * A limitation in the OpenLDAP libraries linked to PHP requires that if a
 * client certificate/key will be used in any ldap bind, the environment must
 * point to them before the first bind made by the process, even if that first
 * bind is not client certificate-based.
 *
 * Therefore, configure this aspect of the environment here in bootstrap.
 * Applications using a client cert with laminas-ldap should similarly ensure their
 * environment variables are set before the first ldap connect/bind.
 */
putenv(sprintf("LDAPTLS_CERT=%s", getenv('TESTS_LAMINAS_LDAP_SASL_CERTIFICATE')));
putenv(sprintf("LDAPTLS_KEY=%s", getenv('TESTS_LAMINAS_LDAP_SASL_KEY')));

/**
 * Work around https://bugs.php.net/bug.php?id=68541 by defining function
 * mocks early.
 *
 * The Mock instances need to be defined now, but accessible for enabling/
 * inspection by OfflineTest.
 * They are wrapped in a class because if they were simply declared globally,
 * phpunit would find them and error while attempting to serialize global
 * variables.
 */
BuiltinFunctionMocks::createMocks();
