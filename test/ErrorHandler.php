<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Ldap\ErrorHandlerInterface;
use Laminas\Stdlib\ErrorHandler as DefaultErrorHandler;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * Start the ErrorHandling-process
     *
     * @param int $level
     *
     * @return void
     */
    public function startErrorHandling($level = E_WARNING)
    {
        DefaultErrorHandler::start($level);
    }

    /**
     * Stop the error-handling process.
     * The parameter <var>$throw</var> handles whether the captured errors shall
     * be thrown as Exceptions or not
     *
     * @param bool|false $throw
     *
     * @return mixed
     */
    public function stopErrorHandling($throw = false)
    {
        return DefaultErrorHandler::stop($throw);
    }
}
