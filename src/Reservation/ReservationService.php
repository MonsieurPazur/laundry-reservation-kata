<?php

/**
 * Handles reservations.
 */

namespace App\Reservation;

use App\Email\EmailService;
use App\Machine\MachineService;
use App\SMS\SMSService;
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
     * @var int max amount of failed reservation claim attempts before PIN reset
     */
    public const MAX_FAILED_ATTEMPTS = 5;

    /**
     * @var ReservationRepository $reservationRepository repository for saving and getting reservations
     */
    private $reservationRepository;

    /**
     * @var EmailService $emailService service for sending emails
     */
    private $emailService;

    /**
     * @var SMSService $emailService service for sending SMS
     */
    private $smsService;

    /**
     * @var MachineService $machineService service for getting machine data
     */
    private $machineService;

    /**
     * ReservationService constructor.
     *
     * @param ReservationRepository $reservationRepository
     * @param EmailService $emailService
     * @param SMSService $smsService
     * @param MachineService $machineService
     */
    public function __construct(
        ReservationRepository $reservationRepository,
        EmailService $emailService,
        SMSService $smsService,
        MachineService $machineService
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
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
        $this->machineService->lock($machineId, $reservationId, $dateTime, $pin);

        return $reservation;
    }

    /**
     * @param int $machineId
     * @param string $pin
     *
     * @throws Exception
     */
    public function claim(int $machineId, string $pin): void
    {
        $reservation = $this->reservationRepository->getByMachineId($machineId);
        if ($reservation->getPIN() === $pin) {
            $this->reservationRepository->updateAsUsed($reservation->getId());
            $this->machineService->unlock($machineId, $reservation->getId());
        } else {
            $this->failedAttempt($machineId, $reservation);
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
     * Handles reservation failed attempt.
     *
     * @param int $machineId which machine couldn't be unlocked
     * @param Reservation $reservation reservation to update
     *
     * @throws Exception
     */
    private function failedAttempt(int $machineId, Reservation $reservation): void
    {
        $failedAttempts = $this->reservationRepository->getFailedAttempts($reservation->getId());

        // If this is last allowed failed attempt (hence -1).
        if (self::MAX_FAILED_ATTEMPTS - 1 === $failedAttempts) {
            $newPin = $this->machineService->generatePIN();

            // Updating new PIN.
            $this->reservationRepository->updatePIN($reservation->getId(), $newPin);

            // Locking machine with new PIN.
            $this->machineService->lock($machineId, $reservation->getId(), $reservation->getDateTime(), $newPin);

            // Sending SMS with new PIN.
            $this->sendResetSMS($reservation->getPhone(), $newPin);

            // Resetting failed attempts counter.
            $this->reservationRepository->updateFailedAttempts($reservation->getId(), 0);
        } else {
            $this->reservationRepository->updateFailedAttempts($reservation->getId(), $failedAttempts + 1);
        }
    }

    /**
     * Helper; sends SMS with new PIN via SMSService.
     *
     * @param string $phone where to
     * @param string $pin new code
     */
    private function sendResetSMS(string $phone, string $pin): void
    {
        $this->smsService->send(
            SMSService::EVENT_RESET_PIN,
            $phone,
            [
                'pin' => $pin
            ]
        );
    }
}
