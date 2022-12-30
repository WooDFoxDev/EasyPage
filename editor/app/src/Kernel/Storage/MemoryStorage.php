<?php

namespace Easypage\Kernel\Storage;

use Easypage\Kernel\Abstractions\Storage;
use Easypage\Kernel\Entity;
use Easypage\Kernel\EntitiesArray;

/**
 * FileStorage
 */
class MemoryStorage extends Storage
{
    static array $data = [];

    public function setStorage(?string $path = null): bool
    {
        return true;
    }

    private function checkUpdateRepository(string $repository)
    {
        $reference = $this->repositoryDefaults();

        if (!isset(static::$data[$repository]['config'])) {
            static::$data[$repository]['config'] = $reference['config'];
        }

        foreach (array_keys($reference['config']) as $key) {
            if (!isset(static::$data[$repository]['config'][$key])) {
                static::$data[$repository]['config'][$key] = $reference['config'][$key];
            }
        }
    }

    private function repositoryDefaults(): array
    {
        return [
            'config' => [
                'auto_increment' => 0,
            ],
            'stored' => []
        ];
    }

    public function getEntities(string $repository, array $query): EntitiesArray|false
    {
        $this->checkUpdateRepository($repository);

        $entities = new EntitiesArray;

        foreach (static::$data[$repository]['stored'] as $key => $data_item) {
            $data_item['id'] = $key;

            if (empty($query)) {
                $entity = new Entity();
                $entity->fill($data_item);

                $entities[] = $entity;
            } else {
                foreach ($query as $key => $value) {
                    if ($data_item[$key] == $value) {
                        $entity = new Entity();
                        $entity->fill($data_item);

                        $entities[] = $entity;
                    }
                }
            }
        }

        return $entities;
    }

    public function saveEntity(string $repository, Entity $entity): int|string|false
    {
        $this->checkUpdateRepository($repository);

        $entity_data = $entity->getValues();

        if (!isset($entity_data['id']) || is_null($entity_data['id'])) {
            $entity_id = ++static::$data[$repository]['config']['auto_increment'];
        } else {
            $entity_id = $entity_data['id'];
        }

        static::$data[$repository]['stored'][$entity_id] = $entity_data;

        return $entity_id;
    }

    public function removeEntity(string $repository, int $id): bool
    {
        $this->checkUpdateRepository($repository);

        unset(static::$data[$repository]['stored'][$id]);

        return true;
    }

    public function saveEntities(string $repository, EntitiesArray $entities): bool
    {
        foreach ($entities as $entity) {
            $this->saveEntity($repository, $entity);
        }

        return true;
    }
}
