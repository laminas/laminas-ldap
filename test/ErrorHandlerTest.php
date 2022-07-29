<?php

declare(strict_types=1);

namespace LaminasTest\Ldap;

use Closure;
use Laminas\Ldap\ErrorHandler;
use PHPUnit\Framework\TestCase;

use function restore_error_handler;
use function set_error_handler;

/**
 * @group      Laminas_Ldap
 */
class ErrorHandlerTest extends TestCase
{
    /** @var callable */
    protected $dummyErrorHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dummyErrorHandler = static function ($errno, $error): void {
        };
    }

    public function testErrorHandlerSettingWorks(): void
    {
        $errorHandler = new ErrorHandler();

        $returnValue1 = set_error_handler($this->dummyErrorHandler);
        $this->assertIsObject($returnValue1);
        $this->assertInstanceOf(\PHPUnit\Util\ErrorHandler::class, $returnValue1);
        $errorHandler->startErrorHandling();
        $returnValue2 = set_error_handler($this->dummyErrorHandler);
        $this->assertIsObject($returnValue2);
        $this->assertInstanceOf(Closure::class, $returnValue2);

        restore_error_handler();
        restore_error_handler();
    }

    public function testErrorHandlerRemovalWorks(): void
    {
        $errorHandler = new ErrorHandler();

        $returnValue1 = set_error_handler($this->dummyErrorHandler);
        $this->assertIsObject($returnValue1);
        $this->assertInstanceOf(\PHPUnit\Util\ErrorHandler::class, $returnValue1);
        $errorHandler->stopErrorHandling();
        $returnValue2 = set_error_handler($this->dummyErrorHandler);
        $this->assertIsObject($returnValue2);
        $this->assertInstanceOf(\PHPUnit\Util\ErrorHandler::class, $returnValue2);

        restore_error_handler();
    }
}
