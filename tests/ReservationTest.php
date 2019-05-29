<?php

/**
 * Test suite for Reservation functionalities.
 */

namespace Test;

use App\Email\EmailService;
use App\Machine\MachineAPI;
use App\Machine\MachineService;
use App\Reservation\Reservation;
use App\Reservation\ReservationRepository;
use App\Reservation\ReservationService;
use App\SMS\SMSService;
use DateTime;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Class ReservationTest
 *
 * @package Test
 */
class ReservationTest extends TestCase
{
    /**
     * @var MockObject|ReservationRepository mock for getting and persisting reservations
     */
    private $reservationRepository;

    /**
     * @var ReservationService $reservationService service for handling reservations
     */
    private $reservationService;

    /**
     * @var MockObject|MachineAPI $machineApi mock for machine API
     */
    private $machineApi;

    /**
     * @var MockObject|MachineService $machineService mock for generating machine data
     */
    private $machineService;

    /**
     * @var MockObject|EmailService $emailService mock for sending emails
     */
    private $emailService;

    /**
     * @var MockObject|SMSService $smsService mock for sending SMS
     */
    private $smsService;

    /**
     * @var Reservation $sampleReservation predefined reservation
     */
    private $sampleReservation;

    /**
     * Sets up mocks and services.
     *
     * @throws ReflectionException
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->setMethods([
                'insert',
                'getLastInsertedId',
                'getByMachineId',
                'getFailedAttempts',
                'updateAsUsed',
                'updateFailedAttempts',
                'updatePIN'
            ])
            ->getMock();

        $this->emailService = $this->getMockBuilder(EmailService::class)
            ->setMethods(['send'])
            ->getMock();

        $this->smsService = $this->getMockBuilder(SMSService::class)
            ->setMethods(['send'])
            ->getMock();

        $this->machineApi = $this->getMockBuilder(MachineAPI::class)
            ->setMethods(['lock', 'unlock'])
            ->getMock();

        $this->machineService = $this->getMockBuilder(MachineService::class)
            ->setConstructorArgs([$this->machineApi])
            ->setMethods(['getFirstAvailableMachineId', 'generatePIN'])
            ->getMock();

        $this->reservationService = new ReservationService(
            $this->reservationRepository,
            $this->emailService,
            $this->smsService,
            $this->machineService
        );

        $this->setSampleReservation();
    }

    /**
     * Tests creating reservation, including saving to database, sending email and locking machine.
     *
     * @throws Exception
     */
    public function testCreateReservation(): void
    {
        // Creating fresh, empty (no id) reservation for repository to insert.
        $emptyReservation = new Reservation(
            $this->sampleReservation->getDateTime(),
            $this->sampleReservation->getPhone(),
            $this->sampleReservation->getEmail(),
            $this->sampleReservation->getMachineId(),
            $this->sampleReservation->getPIN()
        );

        // Mock inserting into repository.
        $this->reservationRepository->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($emptyReservation));
        $this->reservationRepository->expects($this->once())
            ->method('getLastInsertedId')
            ->willReturn($this->sampleReservation->getId());

        // Mock getting machine data.
        $this->machineService->expects($this->once())
            ->method('getFirstAvailableMachineId')
            ->willReturn($this->sampleReservation->getMachineId());
        $this->machineService->expects($this->once())
            ->method('generatePIN')
            ->willReturn($this->sampleReservation->getPIN());

        // Mock sending email.
        $this->emailService->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo(EmailService::EVENT_CONFIRM),
                $this->equalTo($this->sampleReservation->getEmail()),
                $this->equalTo([
                    'reservation_id' => $this->sampleReservation->getId(),
                    'machine_id' => $this->sampleReservation->getMachineId(),
                    'pin' => $this->sampleReservation->getPIN()
                ])
            );

        // Mock locking machine.
        $this->machineApi->expects($this->once())
            ->method('lock')
            ->with(
                $this->equalTo($this->sampleReservation->getMachineId()),
                $this->equalTo($this->sampleReservation->getId()),
                $this->equalTo($this->sampleReservation->getDateTime()),
                $this->equalTo($this->sampleReservation->getPIN())
            )
            ->willReturn(true);

        $this->reservationService->create(
            $this->sampleReservation->getDateTime(),
            $this->sampleReservation->getPhone(),
            $this->sampleReservation->getEmail()
        );
    }

    /**
     * Tests claiming reservation.
     *
     * @throws Exception
     */
    public function testClaimReservation(): void
    {
        // Getting reservation from repository.
        $this->reservationRepository->expects($this->once())
            ->method('getByMachineId')
            ->with($this->equalTo($this->sampleReservation->getMachineId()))
            ->willReturn($this->sampleReservation);

        // Updating after successful claim.
        $this->reservationRepository->expects($this->once())
            ->method('updateAsUsed')
            ->with($this->equalTo($this->sampleReservation->getId()));

        // Unlocking machine.
        $this->machineApi->expects($this->once())
            ->method('unlock')
            ->with(
                $this->equalTo($this->sampleReservation->getMachineId()),
                $this->equalTo($this->sampleReservation->getId())
            );

        $this->reservationService->claim(
            $this->sampleReservation->getMachineId(),
            $this->sampleReservation->getPIN()
        );
    }

    /**
     * Tests claiming reservation with wrong PIN.
     *
     * @throws Exception
     */
    public function testFailedClaimReservation(): void
    {
        // Predefined data.
        $wrongPin = '17994';
        $newPin = '77627';

        $this->prepareFailedClaimReservation();

        // Updating reservation's failed attempt counter.
        $this->reservationRepository->expects($this->exactly(ReservationService::MAX_FAILED_ATTEMPTS))
            ->method('updateFailedAttempts')
            ->with($this->equalTo($this->sampleReservation->getId()));

        // Generating new PIN.
        $this->machineService->expects($this->once())
            ->method('generatePIN')
            ->willReturn($newPin);

        // Updating new PIN.
        $this->reservationRepository->expects($this->once())
            ->method('updatePIN')
            ->with($this->equalTo($this->sampleReservation->getId()), $this->equalTo($newPin));

        // Locking machine with new values.
        $this->machineApi->expects($this->once())
            ->method('lock')
            ->with(
                $this->equalTo($this->sampleReservation->getMachineId()),
                $this->equalTo($this->sampleReservation->getId()),
                $this->equalTo($this->sampleReservation->getDateTime()),
                $this->equalTo($newPin)
            );

        for ($i = 0; $i < 5; $i++) {
            $this->reservationService->claim(1, $wrongPin);
        }
    }

    /**
     * @throws Exception
     */
    public function testSendSMSAfterFailedClaimReservation(): void
    {
        // Predefined data.
        $wrongPin = '17994';
        $newPin = '77627';

        $this->prepareFailedClaimReservation();

        // Generating new PIN.
        $this->machineService->expects($this->once())
            ->method('generatePIN')
            ->willReturn($newPin);

        $this->smsService->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo(SMSService::EVENT_RESET_PIN),
                $this->equalTo($this->sampleReservation->getPhone()),
                $this->equalTo([
                    'pin' => $newPin
                ])
            );

        for ($i = 0; $i < 5; $i++) {
            $this->reservationService->claim($this->sampleReservation->getMachineId(), $wrongPin);
        }
    }

    /**
     * Helper; creates sample reservation (without any associated logic).
     *
     * @throws Exception
     */
    private function setSampleReservation(): void
    {
        // Predefined data.
        $dateTime = '2020-01-19 23:59:00';
        $phone = '+48564777597';
        $email = 'some_email@some_domain.com';
        $id = 69;
        $machineId = 1;
        $pin = '49971';

        $reservation = new Reservation(new DateTime($dateTime), $phone, $email, $machineId, $pin);
        $reservation->setId($id);

        $this->sampleReservation = $reservation;
    }

    /**
     * Prepares data to be returned upon failed reservation claim.
     *
     * @throws Exception
     */
    private function prepareFailedClaimReservation(): void
    {
        // Getting reservation from repository.
        $this->reservationRepository->expects($this->exactly(ReservationService::MAX_FAILED_ATTEMPTS))
            ->method('getByMachineId')
            ->with($this->equalTo($this->sampleReservation->getMachineId()))
            ->willReturn($this->sampleReservation);

        // Getting reservation's failed attempt counter.
        $this->reservationRepository->expects($this->exactly(ReservationService::MAX_FAILED_ATTEMPTS))
            ->method('getFailedAttempts')
            ->with($this->equalTo($this->sampleReservation->getId()))
            ->willReturnOnConsecutiveCalls(0, 1, 2, 3, 4);
    }
}
