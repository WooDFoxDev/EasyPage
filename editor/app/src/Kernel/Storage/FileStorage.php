<?php

namespace Easypage\Kernel\Storage;

use Easypage\Kernel\Abstractions\Storage;
use Easypage\Kernel\Entity;
use Easypage\Kernel\EntitiesArray;

/**
 * FileStorage
 */
class FileStorage extends Storage
{
    protected $root_path = '';

    public function __construct(?string $path = null)
    {
        $this->setStorage($path);
    }

    public function setStorage(?string $path = null): bool
    {
        $this->root_path = ROOT_PATH . ($path ?? $_ENV['STORAGE_PATH']);

        return $this->ensureStorage();
    }

    private function ensureStorage(): bool
    {
        if (!file_exists($this->root_path)) {
            if (!mkdir($this->root_path, recursive: true)) {
                throw new \RuntimeException('Cannot create storage');
            }
        }

        if (!is_readable($this->root_path)) {
            throw new \RuntimeException('Cannot read storage');
        }

        return true;
    }

    private function ensureRepository(string $repository): void
    {
        if (!file_exists($repository)) {
            if (!touch($repository)) {
                throw new \RuntimeException('Cannot create repository');
            }
        }

        if (!is_readable($repository)) {
            throw new \RuntimeException('Cannot read repository');
        }
    }

    private function readRepository(string $name): array
    {
        $repository_path = $this->getRepositoryPath($name);

        $this->ensureRepository($repository_path);

        $contents = file_get_contents($repository_path);

        if (empty($contents)) {
            return $this->repositoryDefaults();
        }

        if (!$data_array = json_decode($contents, true)) {
            throw new \JsonException('Repository is corrupted');
        }

        $this->checkUpdateRepository($data_array);

        return $data_array;
    }

    private function checkUpdateRepository(array &$data)
    {
        $reference = $this->repositoryDefaults();

        // Very first version
        if (!isset($data['stored'])) {
            $temp = $data;

            $data = $reference;
            $data['stored'] = $temp;
        }

        if (!isset($data['config'])) {
            $data['config'] = $reference['config'];
        }

        foreach (array_keys($reference['config']) as $key) {
            if (!isset($data['config'][$key])) {
                $data['config'][$key] = $reference['config'][$key];
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

    private function writeRepository(string $name, array $data): bool
    {
        $repository_path = $this->getRepositoryPath($name);

        $this->ensureRepository($repository_path);

        if (!empty($data)) {
            if (!$data_string = json_encode($data)) {
                throw new \JsonException('Data is corrupted');
            }
        } else {
            $data_string = '';
        }

        file_put_contents($repository_path, $data_string);

        return true;
    }

    private function getRepositoryPath(string $name): string
    {
        return $this->root_path . '/' . $name . '.json';
    }

    public function getEntities(string $repository, array $query): EntitiesArray|false
    {
        $data_array = $this->readRepository($repository);

        $entities = new EntitiesArray;

        foreach ($data_array['stored'] as $key => $data_item) {
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
        $data_array = $this->readRepository($repository);

        $entity_data = $entity->getValues();

        if (!isset($entity_data['id']) || is_null($entity_data['id'])) {
            $entity_id = ++$data_array['config']['auto_increment'];
        } else {
            $entity_id = $entity_data['id'];
        }

        $data_array['stored'][$entity_id] = $entity_data;

        $this->writeRepository($repository, $data_array);

        return $entity_id;
    }

    public function removeEntity(string $repository, int $id): bool
    {
        $data_array = $this->readRepository($repository);

        unset($data_array['stored'][$id]);

        $this->writeRepository($repository, $data_array);

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
