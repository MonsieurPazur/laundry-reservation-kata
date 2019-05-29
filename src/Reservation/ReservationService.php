<?php

/**
 * Handles reservations.
 */

namespace App\Reservation;

use App\Email\EmailService;
use App\Machine\MachineService;
use DateTime;
use Exception;

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
     *
     * @throws Exception
     */
    public function create(DateTime $dateTime, string $phone, string $email): Reservation
    {
        $machineId = $this->machineService->getFirstAvailableMachineId();
        $pin = $this->machineService->generatePIN();

        $reservation = new Reservation($dateTime, $phone, $email, $machineId, $pin);
        $this->reservationRepository->insert($reservation);
        $reservationId = $this->reservationRepository->getLastInsertedId();
        $reservation->setId($reservationId);

        $this->sendConfirmMail($email, $reservationId, $machineId, $pin);
        $this->lockMachine($machineId, $reservationId, $dateTime, $pin);

        return $reservation;
    }

    /**
     * @param int $machineId
     * @param string $pin
     */
    public function claim(int $machineId, string $pin): void
    {
        $reservation = $this->reservationRepository->getByMachineId($machineId);
        if ($reservation->getPIN() === $pin) {
            $this->reservationRepository->updateAsUsed($reservation->getId());
            $this->machineService->unlock($machineId, $reservation->getId());
        }
    }

    /**
     * Helper; sends reservation confirmation mail via emailService.
     *
     * @param string $email where to
     * @param int $reservationId id of a reservation
     * @param int $machineId specific machine's id
     * @param string $pin code to access machine
     */
    private function sendConfirmMail(string $email, int $reservationId, int $machineId, string $pin): void
    {
        $this->emailService->send(
            EmailService::EVENT_CONFIRM,
            $email,
            [
                'reservation_id' => $reservationId,
                'machine_id' => $machineId,
                'pin' => $pin
            ]
        );
    }

    /**
     * Locks specific machine via machineApi;
     *
     * @param int $machineId specific machine's id
     * @param int $reservationId id of a reservation
     * @param DateTime $dateTime date and time of reservation
     * @param string $pin code to access machine
     *
     * @return bool true if locking was successful
     */
    private function lockMachine(int $machineId, int $reservationId, DateTime $dateTime, string $pin): bool
    {
        return $this->machineService->lock($machineId, $reservationId, $dateTime, $pin);
    }
}
