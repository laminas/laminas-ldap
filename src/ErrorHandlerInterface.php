<?php

namespace Laminas\Ldap;

/**
 * Handle Errors that might occur during execution of ldap_*-functions
 *
 * @package Laminas\Ldap\ErrorHandler
 */
interface ErrorHandlerInterface
{
    /**
     * Start the ErrorHandling-process
     *
     * @param int $level
     *
     * @return void
     */
    public function startErrorHandling($level = E_WARNING);

    /**
     * Stop the error-handling process.
     *
     * The parameter <var>$throw</var> handles whether the captured errors shall
     * be thrown as Exceptions or not
     *
     * @param bool|false $throw
     *
     * @return mixed
     */
    public function stopErrorHandling($throw = false);
}
