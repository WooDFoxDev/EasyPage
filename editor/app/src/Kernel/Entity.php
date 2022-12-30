<?php

namespace Easypage\Kernel;

class Entity
{
    private array $values;

    public function fill(array $values): void
    {
        $this->values = $values;
    }

    public function fillFromJSON(string $json): void
    {
        if (!$values = json_decode($json, true)) {
            throw new \InvalidArgumentException('Please, provide correct JSON string');
        }

        $this->fill($values);
    }

    public function fillFromString(string $string): void
    {
        if (!$values = unserialize($string)) {
            throw new \InvalidArgumentException('Please, provide correct serialized string');
        }

        $this->fill($values);
    }

    public function getValues(): array
    {
        if (is_null($this->values)) {
            throw new \RuntimeException('Initialize DTO first');
        }

        return $this->values;
    }
    public function getValuesJSON(): string
    {
        return json_encode($this->getValues());
    }
    public function getValuesString(): string
    {
        return serialize($this->getValues());
    }
}
