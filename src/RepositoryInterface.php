<?php

/**
 * Handles persisting and getting Enities in and out of some external repository.
 */

namespace App;

/**
 * Interface RepositoryInterface
 *
 * @package App
 */
interface RepositoryInterface
{
    /**
     * Persists Entity in repository.
     *
     * @param Entity $entity object to be persisted
     */
    public function insert(Entity $entity): void;

    /**
     * Gets last inserted Entity's id.
     *
     * @return int id of Entity that was lastly inserted
     */
    public function getLastInsertedId(): int;
}
