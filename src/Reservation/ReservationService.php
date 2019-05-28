<?php

/**
 * Handles reservations.
 */

namespace App\Reservation;

use DateTime;

/**
 * Class ReservationService
 *
 * @package App\Reservation
 */
class ReservationService
{
    /**
     * @var ReservationRepository $reservationRepository repository for saving and getting reservations
     */
    private $reservationRepository;

    /**
     * ReservationService constructor.
     *
     * @param ReservationRepository $reservationRepository
     */
    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    /**
     * Creates and saves reservation in respository.
     * Sets id from repository.
     *
     * @param DateTime $dateTime when to reserve machine
     * @param string $phone user's cell phone number
     * @param string $email user's email address
     *
     * @return Reservation newly created and saved reservation
     */
    public function create(DateTime $dateTime, string $phone, string $email): Reservation
    {
        $reservation = new Reservation($dateTime, $phone, $email);
        $this->reservationRepository->insert($reservation);
        $reservation->setId($this->reservationRepository->getLastInsertedId());

        return $reservation;
    }
}
