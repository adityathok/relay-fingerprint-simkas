<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository implements ClientRepositoryInterface
{
    public function all(): Collection
    {
        return Client::all();
    }

    public function findById(int $id): ?Client
    {
        return Client::find($id);
    }

    public function findByCode(string $code): ?Client
    {
        return Client::where('code', $code)->first();
    }

    public function findByToken(string $token): ?Client
    {
        return Client::where('token', $token)->first();
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
