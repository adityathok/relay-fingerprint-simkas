<?php

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Clients')] class extends Component
{
    public $clients;

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
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Clients') }}</flux:heading>
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
                <flux:table.cell>
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
</div>