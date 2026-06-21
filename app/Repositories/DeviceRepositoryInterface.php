<?php

namespace App\Repositories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

interface DeviceRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Device;

    public function findByCode(string $code): ?Device;

    public function findBySerialNumber(string $serialNumber): ?Device;

    public function findByClientId(int $clientId): Collection;

    public function create(array $data): Device;

    public function update(Device $device, array $data): Device;

    public function delete(Device $device): void;
}
