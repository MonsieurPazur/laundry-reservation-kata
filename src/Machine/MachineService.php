<?php

/**
 * Handles machine data.
 */

namespace App\Machine;

use DateTime;
use Exception;

/**
 * Class MachineService
 *
 * @package App\Machine
 */
class MachineService
{
    /**
     * @var int how many machines are available
     */
    public const MACHINES_COUNT = 25;

    /**
     * @var int how long should PIN be
     */
    public const PIN_DIGITS = 5;

    /**
     * @var MachineAPI $machineApi API to communicate with machines
     */
    private $machineApi;

    /**
     * MachineService constructor.
     *
     * @param MachineAPI $machineApi
     */
    public function __construct(MachineAPI $machineApi)
    {
        $this->machineApi = $machineApi;
    }

    /**
     * Sends request via machineApi to lock specific machine.
     *
     * @param int $machineId which machine to lock
     * @param int $reservationId id of this machine's reservation
     * @param DateTime $reservationDateTime date and time of reservation
     * @param string $pin access code
     *
     * @return bool
     */
    public function lock(int $machineId, int $reservationId, DateTime $reservationDateTime, string $pin): bool
    {
        return $this->machineApi->lock($machineId, $reservationId, $reservationDateTime, $pin);
    }

    /**
     * Sends request via machineApi to unlock specific machine.
     *
     * @param int $machineId which machine to unlock
     * @param int $reservationId id of this machine's reservation
     */
    public function unlock(int $machineId, int $reservationId): void
    {
        $this->machineApi->unlock($machineId, $reservationId);
    }

    /**
     * Gets id of the first unlocked machine.
     *
     * @return int id of machine
     *
     * @throws Exception
     */
    public function getFirstAvailableMachineId(): int
    {
        // This is to simplify domain.
        return random_int(1, self::MACHINES_COUNT);
    }

    /**
     * Generates 5-digit PIN for unlocking machines.
     *
     * This can be also done by generating whole number all at once
     * and by str_pad() result with 0's. Though this metod can't be
     * simply made variable by only one const like the one below.
     *
     * @return string 5-digit PIN
     *
     * @throws Exception
     */
    public function generatePIN(): string
    {
        $pin = '';
        for ($i = 0; $i < self::PIN_DIGITS; $i++) {
            $pin .= random_int(0, 9);
        }
        return $pin;
    }
}
