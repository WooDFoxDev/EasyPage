<?php

namespace Easypage\Kernel\Abstractions;

use Easypage\Kernel\Core;
use Easypage\Kernel\ModelsArray;
use Easypage\Kernel\EntitiesArray;
use Easypage\Kernel\Entity;

abstract class Model
{
    protected static string $repository; // Repository name (for example: table in db, file in filestorage)
    protected array $persistent = []; // Parameters, that should be stored
    protected array $updateable = []; // Mass-assignment allowed (related to )
    protected ?int $id = null;
    protected bool $_is_valid = false;
    protected ?array $_validator_messages = null;

    public function __construct(?array $args = null)
    {
        if (!is_null($args)) {
            $this->create($args);
        }
    }

    public function create(array $args): void
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function fromRequest(array $args): void
    {
        foreach ($args as $key => $value) {
            if (in_array($key, $this->updateable)) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function save(): bool
    {
        if (!$values = $this->export()) {
            return false;
        }

        $entity = new Entity();
        $entity->fill($values);

        $entity_id = Core::getStorage()->saveEntity(static::$repository, $entity);

        if ($entity_id === false) {
            return false;
        }

        if (is_null($this->getId())) {
            $this->setId($entity_id);
        }

        return true;
    }

    public function remove(): bool
    {
        if (!is_null($this->getId())) {
            Core::getStorage()->removeEntity(static::$repository, $this->getId());

            $this->setId(null);
        }

        return true;
    }

    public function export(): array|false
    {
        if (!$this->isValid()) {
            return false;
        }

        if (!$this->onExport()) {
            return false;
        }

        $data = [];

        $data['id'] = $this->id;

        foreach ($this->getPersistent() as $key) {
            $data[$key] = $this->{$key} ?? null;
        }

        return $data;
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        if (!is_null($this->id) && !is_null($id)) {
            throw new \ErrorException('Model ID is already set');
        }

        $this->id = $id;
    }

    public function isValid(): bool
    {
        return $this->validate();
    }

    public function getValidationErrors(): ?array
    {
        return $this->_validator_messages;
    }

    protected function validate(): bool
    {
        return true;
    }

    protected function onExport(): bool
    {
        return true;
    }

    private function getPersistent(): array
    {
        return $this->persistent;
    }


    public static function countAll(): int
    {
        return count((array) static::findByValue());
    }

    public static function findAll(): ModelsArray
    {
        return static::findByValue();
    }

    public static function findById(string|int $id): object|false
    {
        return static::findSingular(['id' => $id]);
    }

    public static function findSingular(array $args): object|false
    {
        $models = static::findByValue($args);

        if (!isset($models[0])) {
            return false;
        }

        return $models[0];
    }

    public static function findByValue(array $args = []): ModelsArray
    {
        $entities = static::getFromStorage($args);

        $models = new ModelsArray();

        if ($entities !== false) {
            foreach ($entities as $entity) {
                $model = new static();

                $model->create($entity->getValues());

                $models[] = $model;
            }
        }

        return $models;
    }

    private static function getFromStorage(array $query = []): Entity|EntitiesArray|bool
    {
        return Core::getStorage()->getEntities(static::$repository, $query);
    }

    protected function validateProperty(string $property, string $callable, string $invalidMessage = 'Value is invalid', array $args = null): bool
    {
        if (is_null($args)) {
            $is_valid = $callable($this->$property);
        } else {
            $is_valid = $callable($this->$property, ...$args);
        }

        if (!$is_valid) {
            $this->_is_valid = false;
            $this->_validator_messages[$property][] = $invalidMessage;
        } else if (!isset($this->_validator_messages[$property])) {
            $this->_validator_messages[$property] = [];
        }

        return $is_valid;
    }
}
