<?php

namespace App\Services;

use App\Models\Client;
use App\Repositories\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ClientService
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
    ) {
        //
    }

    public function getAll(): Collection
    {
        return $this->clientRepository->all();
    }

    public function getById(int $id): ?Client
    {
        return $this->clientRepository->findById($id);
    }

    public function getByCode(string $code): ?Client
    {
        return $this->clientRepository->findByCode($code);
    }

    public function getByToken(string $token): ?Client
    {
        return $this->clientRepository->findByToken($token);
    }

    public function create(array $data): Client
    {
        $data['token'] = $data['token'] ?? Str::random(64);

        return $this->clientRepository->create($data);
    }

    public function update(int $id, array $data): Client
    {
        $client = $this->clientRepository->findById($id);

        if (! $client) {
            throw new \RuntimeException("Client with ID {$id} not found.");
        }

        return $this->clientRepository->update($client, $data);
    }

    public function regenerateToken(int $id): Client
    {
        $client = $this->clientRepository->findById($id);

        if (! $client) {
            throw new \RuntimeException("Client with ID {$id} not found.");
        }

        return $this->clientRepository->update($client, [
            'token' => Str::random(64),
        ]);
    }

    public function delete(int $id): void
    {
        $client = $this->clientRepository->findById($id);

        if (! $client) {
            throw new \RuntimeException("Client with ID {$id} not found.");
        }

        $this->clientRepository->delete($client);
    }
}
