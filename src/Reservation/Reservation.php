<?php

/**
 * Handles reservation logic.
 */

namespace App\Reservation;

use App\Entity;
use DateTime;

/**
 * Class Reservation
 *
 * @package App
 */
class Reservation extends Entity
{
    /**
     * @var int $machineId id of associated machine
     */
    private $machineId;

    /**
     * @var string $pin code that allows access to machine
     */
    private $pin;

    /**
     * Reservation constructor.
     *
     * @param DateTime $dateTime when to reserve machine
     * @param string $phone user's cell phone number
     * @param string $email user's email address
     * @param int $machineId associated machine's id
     * @param string $pin code to access machine
     */
    public function __construct(DateTime $dateTime, string $phone, string $email, int $machineId, string $pin)
    {
        $this->machineId = $machineId;
        $this->pin = $pin;
    }

    /**
     * Gets associated machine's id.
     *
     * @return int id of associated machine
     */
    public function getMachineId(): int
    {
        return $this->machineId;
    }

    /**
     * Gets PIN for accessing machine.
     *
     * @return string code that allows access to machine
     */
    public function getPIN(): string
    {
        return $this->pin;
    }
}
