<?php

namespace App\Services;

use App\Contracts\AnimalServiceInterface;
use Illuminate\Support\Facades\Storage;

class FileAnimalService implements AnimalServiceInterface
{
    private function getAnimals(): array
    {
        if (!Storage::disk('local')->exists('animals.json')) {
            return [];
        }

        $json = Storage::disk('local')->get('animals.json');

        return json_decode($json, true);
    }

    private function saveAnimals(array $animals): void
    {
        $jsonContent = json_encode($animals, JSON_PRETTY_PRINT);

        Storage::disk('local')->put('animals.json', $jsonContent);
    }

    public function all(): array
    {
        return $this->getAnimals();
    }

    public function find(string $id): ?array
    {
        $animals = $this->getAnimals();

        foreach ($animals as $animal) {
            if ((string) $animal['id'] === $id) {
                return $animal;
            }
        }

        throw new \Exception("El animal con ID {$id} no fue encontrado.");
    }

    public function create(array $data): array
    {
        $animals = $this->getAnimals();

        $newId = 1;

        foreach ($animals as $animal) {
            if ($animal['id'] >= $newId) {
                $newId = $animal['id'] + 1;
            }
        }

        $newAnimal = ['id' => $newId] + $data;

        $animals[] = $newAnimal;

        $this->saveAnimals($animals);

        return $newAnimal;
    }

    public function update(string $id, array $data): ?array
    {
        $animals = $this->getAnimals();

        foreach ($animals as $index => $animal) {
            if ((string) $animal['id'] === $id) {
                $updatedAnimal = ['id' => $animal['id']] + $data;

                $animals[$index] = $updatedAnimal;

                $this->saveAnimals($animals);

                return $updatedAnimal;
            }
        }

        throw new \Exception("El animal con ID {$id} no fue encontrado.");
    }

    public function delete(string $id): bool
    {
        $animals = $this->getAnimals();

        foreach ($animals as $index => $animal) {
            if ((string) $animal['id'] === $id) {
                unset($animals[$index]);

                $animals = array_values($animals);

                $this->saveAnimals($animals);

                return true;
            }
        }

        throw new \Exception("El animal con ID {$id} no fue encontrado.");
    }

    public function reset(): void
    {
        $this->saveAnimals([]);
    }
}