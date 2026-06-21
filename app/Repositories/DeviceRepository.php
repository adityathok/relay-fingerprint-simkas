<?php

namespace App\Repositories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

class DeviceRepository implements DeviceRepositoryInterface
{
    public function all(): Collection
    {
        return Device::with('client')->get();
    }

    public function findById(int $id): ?Device
    {
        return Device::with('client')->find($id);
    }

    public function findByCode(string $code): ?Device
    {
        return Device::with('client')->where('code', $code)->first();
    }

    public function findBySerialNumber(string $serialNumber): ?Device
    {
        return Device::with('client')->where('serial_number', $serialNumber)->first();
    }

    public function findByClientId(int $clientId): Collection
    {
        return Device::with('client')->where('client_id', $clientId)->get();
    }

    public function create(array $data): Device
    {
        return Device::create($data);
    }

    public function update(Device $device, array $data): Device
    {
        $device->update($data);

        return $device;
    }

    public function delete(Device $device): void
    {
        $device->delete();
    }
}
