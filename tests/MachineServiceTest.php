<?php

/**
 * Test suite for MachineService.
 */

namespace Test;

use App\Machine\MachineAPI;
use App\Machine\MachineService;
use DateTime;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Class MachineServiceTest
 *
 * @package Test
 */
class MachineServiceTest extends TestCase
{
    /**
     * @var MockObject|MachineAPI $machineApi mock for machine API
     */
    private $machineApi;

    /**
     * @var MachineService $machineService service that's being tested
     */
    private $machineService;

    /**
     * Sets up mock and service.
     *
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->machineApi = $this->getMockBuilder(MachineAPI::class)
            ->setMethods(['lock', 'unlock'])
            ->getMock();
        $this->machineService = new MachineService($this->machineApi);
    }

    /**
     * Tests lock functionality.
     *
     * @throws Exception
     */
    public function testLock(): void
    {
        $this->machineApi->expects($this->once())
            ->method('lock')
            ->willReturn(true);
        $locked = $this->machineService->lock(1, 1, new DateTime(), '00000');
        $this->assertTrue($locked);
    }

    /**
     * Tests unlocking machine.
     */
    public function testUnlock(): void
    {
        $this->machineApi->expects($this->once())
            ->method('unlock');
        $this->machineService->unlock(1, 1);
    }

    /**
     * Tests getting first available machine id.
     *
     * @throws Exception
     */
    public function testGetFirstAvailableMachineId(): void
    {
        $machineId = $this->machineService->getFirstAvailableMachineId();
        $this->assertGreaterThanOrEqual(1, $machineId);
        $this->assertLessThanOrEqual(25, $machineId);
    }

    /**
     * Tests generating PINs for unlocking machines.
     *
     * @throws Exception
     */
    public function testGeneratePIN(): void
    {
        $pin = $this->machineService->generatePIN();
        $this->assertGreaterThanOrEqual(0, (int)$pin);
        $this->assertLessThanOrEqual(99999, (int)$pin);
    }
}
