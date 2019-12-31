<?php

/**
 * @see       https://github.com/laminas/laminas-ldap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-ldap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-ldap/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Ldap;

use Laminas\Ldap\ErrorHandler;

/**
 * @group      Laminas_Ldap
 */
class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $dummyErrorHandler;

    protected $currentErrorHandler = [
        'PHPUnit_Util_ErrorHandler',
        'handleError',
    ];

    public function setup()
    {
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
