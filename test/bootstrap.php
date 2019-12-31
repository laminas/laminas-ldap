<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

/*
 * Set error reporting to the level to which Laminas code must comply.
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Setup autoloading
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Setting the LaminasTest\Ldap\ErrorHandler as default ErrorHandler
 */
Laminas\Ldap\ErrorHandler::setErrorHandler(new LaminasTest\Ldap\ErrorHandler());

/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_LAMINAS_OB_ENABLED') && constant('TESTS_LAMINAS_OB_ENABLED')) {
    ob_start();
}
