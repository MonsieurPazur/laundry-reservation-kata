<?php

/**
 * Basic entity class.
 */

namespace App;

/**
 * Class Entity
 *
 * @package App
 */
abstract class Entity
{
    /**
     * @var int $id unique identification across Entities of the same class
     */
    protected $id;

    /**
     * Gets id of this Entity.
     *
     * @return int this Entity's id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets id from repository.
     *
     * @param int $id id from repository
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
