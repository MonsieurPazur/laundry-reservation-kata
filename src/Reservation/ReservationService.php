<?php

/**
 * Handles reservations.
 */

namespace App\Reservation;

use App\Email\EmailService;
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
     * @var EmailService $emailService service for sending emails
     */
    private $emailService;

    /**
     * ReservationService constructor.
     *
     * @param ReservationRepository $reservationRepository
     * @param EmailService $emailService
     */
    public function __construct(ReservationRepository $reservationRepository, EmailService $emailService)
    {
        $this->reservationRepository = $reservationRepository;
        $this->emailService = $emailService;
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
        $reservationId = $this->reservationRepository->getLastInsertedId();
        $reservation->setId($reservationId);

        $this->emailService->send(EmailService::EVENT_CONFIRM, $email, [
            'reservation_id' => $reservationId,
            'machine_number' => 1,
            'pin' => 49971
        ]);

        return $reservation;
    }
}
