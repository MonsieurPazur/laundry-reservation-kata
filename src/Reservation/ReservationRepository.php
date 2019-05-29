<?php

/**
 * Handles persisting and getting reservations in and out of some external repository.
 */

namespace App\Reservation;

use App\Entity;
use App\RepositoryInterface;

/**
 * Class ReservationRepository
 *
 * @package App\Reservation
 */
class ReservationRepository implements RepositoryInterface
{
    /**
     * Persists Entity in repository.
     *
     * @param Entity $entity object to be persisted
     */
    public function insert(Entity $entity): void
    {
    }

    /**
     * Gets last inserted Entity's id.
     *
     * @return int id of Entity that was lastly inserted
     */
    public function getLastInsertedId(): int
    {
    }

    /**
     * Gets Reservation by specified machine id.
     *
     * @param int $machineId id of machine that this reservation is associated with.
     *
     * @return Reservation associated reservation
     */
    public function getByMachineId(int $machineId): Reservation
    {
    }

    /**
     * Updates reservation, marks it as claimed.
     *
     * @param int $reservationId reservation to update
     */
    public function updateAsUsed(int $reservationId): void
    {
    }

    /**
     * Updates reservation, increases failed attempts counter.
     *
     * @param int $reservationId reservation to update
     */
    public function updateFailedAttempts(int $reservationId): void
    {
    }
}
