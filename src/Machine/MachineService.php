<?php

/**
 * Handles machine data.
 */

namespace App\Machine;

use DateTime;

/**
 * Class MachineService
 *
 * @package App\Machine
 */
class MachineService
{
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
     * @param int $pin access code
     *
     * @return bool
     */
    public function lock(int $machineId, int $reservationId, DateTime $reservationDateTime, int $pin): bool
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
     */
    public function getFirstAvailableMachineId(): int
    {
    }

    /**
     * Generates 5-digit PIN for unlocking machines.
     *
     * @return int 5-digit PIN
     */
    public function generatePIN(): int
    {
    }
}
