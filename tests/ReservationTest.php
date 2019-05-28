<?php

/**
 * Test suite for Reservation functionalities.
 */

namespace Test;

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
     * Sets up mocks and services.
     *
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->setMethods(['insert', 'getLastInsertedId'])
            ->getMock();
        $this->reservationService = new ReservationService($this->reservationRepository);
    }

    /**
     * Tests creating reservation.
     *
     * @throws Exception
     */
    public function testCreateReservation(): void
    {
        $this->getSampleReservation();
    }

    /**
     * Gets predefined reservation.
     *
     * @return Reservation sample reservation with predefined values
     *
     * @throws Exception
     */
    private function getSampleReservation(): Reservation
    {
        $this->reservationRepository->expects($this->once())
            ->method('insert')
            ->with(new Reservation(
                new DateTime('2019-05-28 11:26:00'),
                '+48778342655',
                'example@example.com'
            ));
        $this->reservationRepository->expects($this->once())
            ->method('getLastInsertedId')
            ->willReturn(1);
        return $this->reservationService->create(
            new DateTime('2019-05-28 11:26:00'),
            '+48778342655',
            'example@example.com'
        );
    }
}
