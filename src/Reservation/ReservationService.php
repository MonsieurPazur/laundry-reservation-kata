<?php

/**
 * Handles reservations.
 */

namespace App\Reservation;

use App\Email\EmailService;
use App\Machine\MachineService;
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
     * @var MachineService $machineService service for getting machine data
     */
    private $machineService;

    /**
     * ReservationService constructor.
     *
     * @param ReservationRepository $reservationRepository
     * @param EmailService $emailService
     * @param MachineService $machineService
     */
    public function __construct(
        ReservationRepository $reservationRepository,
        EmailService $emailService,
        MachineService $machineService
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->emailService = $emailService;
        $this->machineService = $machineService;
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

        $this->emailService->send(
            EmailService::EVENT_CONFIRM,
            $email,
            [
                'reservation_id' => $reservationId,
                'machine_id' => $this->machineService->getFirstAvailableMachineId(),
                'pin' => $this->machineService->generatePIN()
            ]
        );

        return $reservation;
    }
}
