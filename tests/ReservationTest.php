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
     * Sets up mocks and services.
     *
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->setMethods(['insert', 'getLastInsertedId', 'getByMachineId'])
            ->getMock();

        $this->emailService = $this->getMockBuilder(EmailService::class)
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
            $this->machineService
        );
    }

    /**
     * Tests creating Reservation object "just like that".
     *
     * @throws Exception
     */
    public function testSimpleCreateReservation(): void
    {
        $reservation = $this->getSampleRawReservation();
        $this->assertEquals(69, $reservation->getId());
    }

    /**
     * Tests creating reservation, including saving to database, sending email and locking machine.
     *
     * @throws Exception
     */
    public function testCreateReservation(): void
    {
        $this->getSampleReservation();
    }

    /**
     * Tests claiming reservation.
     *
     * @throws Exception
     */
    public function testClaimReservation(): void
    {
        $this->reservationRepository->expects($this->once())
            ->method('getByMachineId')
            ->with($this->equalTo(1))
            ->willReturn($this->getSampleRawReservation());
        $this->reservationService->claim(1, 49971);
    }

    /**
     * Helper; gets predefined reservation.
     *
     * @return Reservation sample reservation with predefined values
     *
     * @throws Exception
     */
    private function getSampleReservation() : Reservation
    {
        // Predefined data.
        $dateTime = '2019-05-28 11:29:00';
        $phone = '+48778398445';
        $email = 'example@example.com';
        $reservationId = 1;
        $machineId = 1;
        $pin = '49971';

        // Mock inserting into repository.
        $this->reservationRepository->expects($this->once())
            ->method('insert')
            ->with($this->equalTo(new Reservation(new DateTime($dateTime), $phone, $email)));
        $this->reservationRepository->expects($this->once())
            ->method('getLastInsertedId')
            ->willReturn($reservationId);

        // Mock getting machine data.
        $this->machineService->expects($this->once())
            ->method('getFirstAvailableMachineId')
            ->willReturn($machineId);
        $this->machineService->expects($this->once())
            ->method('generatePIN')
            ->willReturn($pin);

        // Mock sending email.
        $this->emailService->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo(EmailService::EVENT_CONFIRM),
                $this->equalTo($email),
                $this->equalTo([
                    'reservation_id' => $reservationId,
                    'machine_id' => $machineId,
                    'pin' => $pin
                ])
            );

        // Mock locking machine.
        $this->machineApi->expects($this->once())
            ->method('lock')
            ->with(
                $this->equalTo($machineId),
                $this->equalTo($reservationId),
                $this->equalTo(new DateTime($dateTime)),
                $this->equalTo($pin)
            )
            ->willReturn(true);

        return $this->reservationService->create(new DateTime($dateTime), $phone, $email);
    }

    /**
     * Helper; creates sample reservation (without any associated logic).
     *
     * @return Reservation predefined raw Reservation object
     *
     * @throws Exception
     */
    private function getSampleRawReservation(): Reservation
    {
        // Predefined data.
        $dateTime = '2020-01-19 23:59:00';
        $phone = '+48564777597';
        $email = 'some_email@some_domain.com';
        $id = 69;

        $reservation = new Reservation(new DateTime($dateTime), $phone, $email);
        $reservation->setId($id);

        return $reservation;
    }
}
