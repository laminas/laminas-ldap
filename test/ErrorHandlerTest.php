<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Ldap\ErrorHandler;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Ldap
 */
class ErrorHandlerTest extends TestCase
{
    protected $dummyErrorHandler;

    protected $currentErrorHandler = [
        \PHPUnit\Util\ErrorHandler::class,
        'handleError',
    ];

    protected function setUp()
    {
        /** @todo: remove when migrate to PHP 7.1+ and PHPUnit 7+ only */
        if (class_exists(\PHPUnit_Util_ErrorHandler::class)) {
            $this->currentErrorHandler[0] = \PHPUnit_Util_ErrorHandler::class;
        }

        $this->dummyErrorHandler = function ($errno, $error) {
        };
    }
    public function testErrorHandlerSettingWorks()
    {
        $errorHandler = new ErrorHandler();

        $this->assertEquals($this->currentErrorHandler, set_error_handler($this->dummyErrorHandler));
        $errorHandler->startErrorHandling();
        $this->assertEquals($this->dummyErrorHandler, set_error_handler($this->dummyErrorHandler));

        restore_error_handler();
        restore_error_handler();
    }

    public function testErrorHandlerREmovalWorks()
    {
        $errorHandler = new ErrorHandler();

        $this->assertEquals($this->currentErrorHandler, set_error_handler($this->dummyErrorHandler));
        $errorHandler->stopErrorHandling();
        $this->assertEquals($this->currentErrorHandler, set_error_handler($this->dummyErrorHandler));

        restore_error_handler();
    }
}
