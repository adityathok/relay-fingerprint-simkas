<?php

namespace App\Services;

use App\Models\Device;
use App\Repositories\DeviceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DeviceService
{
    public function __construct(
        private readonly DeviceRepositoryInterface $deviceRepository,
    ) {
        //
    }

    public function getAll(): Collection
    {
        return $this->deviceRepository->all();
    }

    public function getById(int $id): ?Device
    {
        return $this->deviceRepository->findById($id);
    }

    public function getByCode(string $code): ?Device
    {
        return $this->deviceRepository->findByCode($code);
    }

    public function getBySerialNumber(string $serialNumber): ?Device
    {
        return $this->deviceRepository->findBySerialNumber($serialNumber);
    }

    public function getByClientId(int $clientId): Collection
    {
        return $this->deviceRepository->findByClientId($clientId);
    }

    public function create(array $data): Device
    {
        return $this->deviceRepository->create($data);
    }

    public function update(int $id, array $data): Device
    {
        $device = $this->deviceRepository->findById($id);

        if (! $device) {
            throw new \RuntimeException("Device with ID {$id} not found.");
        }

        return $this->deviceRepository->update($device, $data);
    }

    public function delete(int $id): void
    {
        $device = $this->deviceRepository->findById($id);

        if (! $device) {
            throw new \RuntimeException("Device with ID {$id} not found.");
        }

        $this->deviceRepository->delete($device);
    }
}
