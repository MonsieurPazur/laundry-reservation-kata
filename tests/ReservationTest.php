<?php

/**
 * Test suite for Reservation functionalities.
 */

namespace Test;

use App\Reservation;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class ReservationTest
 *
 * @package Test
 */
class ReservationTest extends TestCase
{
    /**
     * Tests creating reservation.
     *
     * @throws Exception
     */
    public function testCreateReservation(): void
    {
        $reservation = new Reservation(
            new DateTime('2019-05-28 11:26:00'),
            '+48778342655',
            'example@example.com'
        );
        $this->assertInstanceOf(Reservation::class, $reservation);
    }
}
