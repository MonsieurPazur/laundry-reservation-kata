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
        // TODO: Implement insert() method.
    }

    /**
     * Gets last inserted Entity's id.
     *
     * @return int id of Entity that was lastly inserted
     */
    public function getLastInsertedId(): int
    {
        // TODO: Implement getLastInsertedId() method.
    }
}
