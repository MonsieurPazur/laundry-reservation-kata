<?php

/**
 * API for communicating with machines.
 */

namespace App\Machine;

use DateTime;

/**
 * Class MachineAPI
 *
 * @package App\Machine
 */
class MachineAPI
{
    /**
     * Locks certain machine due to reservation.
     *
     * @param int $machineId which machine to lock
     * @param int $reservationId id of this machine's reservation
     * @param DateTime $reservationDateTime date and time of reservation
     * @param string $pin access code
     *
     * @return bool true if locking was successful
     */
    public function lock(int $machineId, int $reservationId, DateTime $reservationDateTime, string $pin): bool
    {
    }

    /**
     * Unlocks certain machine.
     *
     * @param int $machineId which machine to unlock
     * @param int $reservationId id of this machine's reservation
     */
    public function unlock(int $machineId, int $reservationId): void
    {
    }
}
