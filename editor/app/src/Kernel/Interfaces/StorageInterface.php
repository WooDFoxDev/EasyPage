<?php

namespace Easypage\Kernel\Interfaces;

use Easypage\Kernel\EntitiesArray;
use Easypage\Kernel\Entity;

/**
 * StorageInterface
 */
interface StorageInterface
{
    /**
     * Sets storage location
     * Should return true on success
     *
     * @param  string $path
     * @return bool
     */
    public function setStorage(string $path): bool;

    /**
     * Returns entities from a given repository
     * or false if nothing found
     *
     * @param  mixed $repository
     * @param  mixed $query
     * @return EntitiesArray|false
     */
    public function getEntities(string $repository, array $query): EntitiesArray|false;

    /**
     * Saves a given entity to a given repository
     * returns entity ID on success
     * or false on fail
     *
     * @param  string $repository
     * @param  Entity $entity
     * @return int|string|false
     */
    public function saveEntity(string $repository, Entity $entity): int|string|false;

    /**
     * Removes entity by ID from a given repository
     * returns true on success
     *
     * @param  string $repository
     * @param  int $id
     * @return bool
     */
    public function removeEntity(string $repository, int|string $id): bool;
}
