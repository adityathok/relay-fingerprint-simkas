<?php

use App\Models\Client;
use App\Models\Device;
use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Devices')] class extends Component
{
    public $devices;
    public ?int $editingDeviceId = null;
    public string $code = '';
    public string $serial_number = '';
    public string $device_name = '';
    public ?int $client_id = null;

    public function mount(): void
    {
        $this->devices = Device::with('client')->get();
    }

    public function deleteDevice(int $id): void
    {
        $device = Device::find($id);

        if ($device) {
            $device->delete();
            $this->devices = Device::with('client')->get();
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingDeviceId = null;
        Flux::modal('device-form')->show();
    }

    public function openEditModal(int $id): void
    {
        $device = Device::with('client')->findOrFail($id);
        $this->editingDeviceId = $device->id;
        $this->code = $device->code;
        $this->serial_number = $device->serial_number;
        $this->device_name = $device->device_name;
        $this->client_id = $device->client_id;
        Flux::modal('device-form')->show();
    }

    public function save(): void
    {
        $data = $this->validate([
            'code' => 'required|string|max:255|unique:devices,code,' . $this->editingDeviceId,
            'serial_number' => 'required|string|max:255|unique:devices,serial_number,' . $this->editingDeviceId,
            'device_name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
        ]);

        if ($this->editingDeviceId) {
            $device = Device::findOrFail($this->editingDeviceId);
            $device->update($data);
            Flux::toast(variant: 'success', text: __('Device updated.'));
        } else {
            Device::create($data);
            Flux::toast(variant: 'success', text: __('Device created.'));
        }

        $this->devices = Device::with('client')->get();
        $this->resetForm();
        Flux::modal('device-form')->close();
    }

    private function resetForm(): void
    {
        $this->reset(['code', 'serial_number', 'device_name', 'client_id', 'editingDeviceId']);
        $this->resetValidation();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Devices') }}</flux:heading>
        <flux:button variant="primary" wire:click="openCreateModal">
            {{ __('Add Device') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>{{ __('Code') }}</flux:table.column>
            <flux:table.column>{{ __('Serial Number') }}</flux:table.column>
            <flux:table.column>{{ __('Device Name') }}</flux:table.column>
            <flux:table.column>{{ __('Client') }}</flux:table.column>
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($devices as $device)
            <flux:table.row>
                <flux:table.cell>{{ $loop->iteration }}</flux:table.cell>
                <flux:table.cell class="font-medium">
                    <flux:badge size="sm">{{ $device->code }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="font-mono text-xs">{{ $device->serial_number }}</flux:table.cell>
                <flux:table.cell>{{ $device->device_name }}</flux:table.cell>
                <flux:table.cell>{{ $device->client?->name ?? '-' }}</flux:table.cell>
                <flux:table.cell class="flex gap-1">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        wire:click="openEditModal({{ $device->id }})">
                        {{ __('Edit') }}
                    </flux:button>
                    <flux:button
                        variant="danger"
                        size="sm"
                        wire:click="deleteDevice({{ $device->id }})"
                        wire:confirm="{{ __('Are you sure you want to delete this device?') }}">
                        {{ __('Delete') }}
                    </flux:button>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="text-center text-neutral-500">
                    {{ __('No devices found.') }}
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="device-form" class="w-full max-w-md">
        <flux:heading size="lg">
            {{ $editingDeviceId ? __('Edit Device') : __('Add Device') }}
        </flux:heading>

        <form wire:submit="save" class="mt-6 space-y-4">
            <flux:input wire:model="code" :label="__('Code')" required autofocus />
            <flux:input wire:model="serial_number" :label="__('Serial Number')" required />
            <flux:input wire:model="device_name" :label="__('Device Name')" required />

            <flux:select wire:model="client_id" :label="__('Client')" required>
                @foreach (App\Models\Client::all() as $client)
                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->code }})</option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $editingDeviceId ? __('Update') : __('Save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>