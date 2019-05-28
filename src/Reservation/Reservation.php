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
     * Reservation constructor.
     *
     * @param DateTime $dateTime when to reserve machine
     * @param string $phone user's cell phone number
     * @param string $email user's email address
     */
    public function __construct(DateTime $dateTime, string $phone, string $email)
    {
    }
}
