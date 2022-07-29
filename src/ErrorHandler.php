<?php

namespace Laminas\Ldap;

use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

/**
 * Handle Errors that might occur during execution of ldap_*-functions
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /** @var ErrorHandlerInterface The Errror-Handler instance */
    protected static $errorHandler;

    /**
     * Start the Error-Handling
     *
     * You can specify which errors to handle by passing a combination of PHPs
     * Error-constants like E_WARNING or E_NOTICE or E_WARNING ^ E_DEPRECATED
     *
     * @param int $level The Error-level(s) to handle by this ErrorHandler
     * @return void
     */
    public static function start($level = E_WARNING)
    {
        self::getErrorHandler()->startErrorHandling($level);
    }

    /**
     * @param bool|false $throw
     * @return mixed
     */
    public static function stop($throw = false)
    {
        return self::getErrorHandler()->stopErrorHandling($throw);
    }

    /**
     * Get an error handler
     *
     * @return ErrorHandlerInterface
     */
    protected static function getErrorHandler()
    {
        if (! self::$errorHandler && ! self::$errorHandler instanceof ErrorHandlerInterface) {
            self::$errorHandler = new self();
        }

        return self::$errorHandler;
    }

    /**
     * This method does nothing on purpose.
     *
     * @see ErrorHandlerInterface::startErrorHandling()
     *
     * @param int $level
     * @return void
     */
    public function startErrorHandling($level = E_WARNING)
    {
        set_error_handler(static function ($errNo, $errString): void {
        });
    }

    /**
     * This method does nothing on purpose.
     *
     * @see ErrorHandlerInterface::stopErrorHandling()
     *
     * @param bool|false $throw
     * @return void
     */
    public function stopErrorHandling($throw = false)
    {
        restore_error_handler();
    }

    /**
     * Set the error handler to be used
     *
     * @return void
     */
    public static function setErrorHandler(ErrorHandlerInterface $errorHandler)
    {
        self::$errorHandler = $errorHandler;
    }
}
