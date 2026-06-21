<?php

use App\Models\Client;
use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Clients')] class extends Component
{
    public $clients;
    public ?int $editingClientId = null;
    public string $name = '';
    public string $code = '';
    public ?string $domain = null;

    public function mount(): void
    {
        $this->clients = Client::all();
    }

    public function deleteClient(int $id): void
    {
        $client = Client::find($id);

        if ($client) {
            $client->delete();
            $this->clients = Client::all();
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingClientId = null;
        Flux::modal('client-form')->show();
    }

    public function openEditModal(int $id): void
    {
        $client = Client::findOrFail($id);
        $this->editingClientId = $client->id;
        $this->name = $client->name;
        $this->code = $client->code;
        $this->domain = $client->domain;
        Flux::modal('client-form')->show();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:clients,code,' . $this->editingClientId,
            'domain' => 'nullable|string|max:255',
        ]);

        if ($this->editingClientId) {
            $client = Client::findOrFail($this->editingClientId);
            $client->update($data);
            Flux::toast(variant: 'success', text: __('Client updated.'));
        } else {
            Client::create($data + ['token' => \Illuminate\Support\Str::random(64)]);
            Flux::toast(variant: 'success', text: __('Client created.'));
        }

        $this->clients = Client::all();
        $this->resetForm();
        Flux::modal('client-form')->close();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'code', 'domain', 'editingClientId']);
        $this->resetValidation();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Clients') }}</flux:heading>
        <flux:button variant="primary" wire:click="openCreateModal">
            {{ __('Add Client') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Code') }}</flux:table.column>
            <flux:table.column>{{ __('Domain') }}</flux:table.column>
            <flux:table.column>{{ __('Token') }}</flux:table.column>
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($clients as $client)
            <flux:table.row>
                <flux:table.cell>{{ $loop->iteration }}</flux:table.cell>
                <flux:table.cell class="font-medium">{{ $client->name }}</flux:table.cell>
                <flux:table.cell>
                    <flux:badge size="sm">{{ $client->code }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell>{{ $client->domain ?? '-' }}</flux:table.cell>
                <flux:table.cell class="max-w-50 truncate font-mono text-xs">
                    {{ $client->token }}
                </flux:table.cell>
                <flux:table.cell class="flex gap-1">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        wire:click="openEditModal({{ $client->id }})">
                        {{ __('Edit') }}
                    </flux:button>
                    <flux:button
                        variant="danger"
                        size="sm"
                        wire:click="deleteClient({{ $client->id }})"
                        wire:confirm="{{ __('Are you sure you want to delete this client?') }}">
                        {{ __('Delete') }}
                    </flux:button>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="text-center text-neutral-500">
                    {{ __('No clients found.') }}
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="client-form" class="w-full max-w-md">
        <flux:heading size="lg">
            {{ $editingClientId ? __('Edit Client') : __('Add Client') }}
        </flux:heading>

        <form wire:submit="save" class="mt-6 space-y-4">
            <flux:input wire:model="name" :label="__('Name')" required autofocus />
            <flux:input wire:model="code" :label="__('Code')" required />
            <flux:input wire:model="domain" :label="__('Domain')" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $editingClientId ? __('Update') : __('Save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>