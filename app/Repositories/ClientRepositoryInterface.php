<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Client;

    public function findByCode(string $code): ?Client;

    public function findByToken(string $token): ?Client;

    public function create(array $data): Client;

    public function update(Client $client, array $data): Client;

    public function delete(Client $client): void;
}
