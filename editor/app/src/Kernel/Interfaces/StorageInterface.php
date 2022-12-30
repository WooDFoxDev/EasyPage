<?php

namespace Easypage\Kernel\Interfaces;

use Easypage\Kernel\Abstractions\Storage;
use Easypage\Kernel\EntitiesArray;
use Easypage\Kernel\Entity;

/**
 * StorageInterface
 */
interface StorageInterface
{
    public function setStorage(string $path): bool;
    public function getEntities(string $repository, array $query): EntitiesArray|false;
    public function saveEntity(string $repository, Entity $entity): int|string|false;
    public function removeEntity(string $repository, int $id): bool;
}
