<?php

use App\Models\FingerprintRawLog;
use App\Services\FingerprintRawLogService;
use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Fingerprint Raw Logs')] class extends Component
{
    public $logs;
    public ?int $editingLogId = null;
    public string $device_sn = '';
    public string $raw_payload = '';
    public int $retry_count = 0;

    public function mount(FingerprintRawLogService $logService): void
    {
        $this->logs = $logService->getAll();
    }

    public function deleteLog(int $id, FingerprintRawLogService $logService): void
    {
        try {
            $logService->delete($id);
            $this->logs = $logService->getAll();
            Flux::toast(variant: 'success', text: __('Log deleted.'));
        } catch (\RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingLogId = null;
        Flux::modal('log-form')->show();
    }

    public function openEditModal(int $id, FingerprintRawLogService $logService): void
    {
        $log = $logService->getById($id);

        if (! $log) {
            Flux::toast(variant: 'danger', text: __('Log not found.'));

            return;
        }

        $this->editingLogId = $log->id;
        $this->device_sn = $log->device_sn;
        $this->raw_payload = $log->raw_payload;
        $this->retry_count = $log->retry_count;
        Flux::modal('log-form')->show();
    }

    public function save(FingerprintRawLogService $logService): void
    {
        $data = $this->validate([
            'device_sn' => 'required|string|max:255',
            'raw_payload' => 'required|string',
            'retry_count' => 'required|integer|min:0',
        ]);

        if ($this->editingLogId) {
            try {
                $log = $logService->getById($this->editingLogId);
                if (! $log) {
                    throw new \RuntimeException('Log not found.');
                }
                $log->update($data);
                Flux::toast(variant: 'success', text: __('Log updated.'));
            } catch (\RuntimeException $e) {
                Flux::toast(variant: 'danger', text: $e->getMessage());

                return;
            }
        } else {
            $logService->create($data);
            Flux::toast(variant: 'success', text: __('Log created.'));
        }

        $this->logs = $logService->getAll();
        $this->resetForm();
        Flux::modal('log-form')->close();
    }

    public function incrementRetry(int $id, FingerprintRawLogService $logService): void
    {
        try {
            $logService->incrementRetryCount($id);
            $this->logs = $logService->getAll();
            Flux::toast(variant: 'success', text: __('Retry count incremented.'));
        } catch (\RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->reset(['device_sn', 'raw_payload', 'retry_count', 'editingLogId']);
        $this->resetValidation();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Fingerprint Raw Logs') }}</flux:heading>
        <flux:button variant="primary" wire:click="openCreateModal">
            {{ __('Add Log') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>{{ __('Device SN') }}</flux:table.column>
            <flux:table.column>{{ __('Raw Payload') }}</flux:table.column>
            <flux:table.column>{{ __('Retry Count') }}</flux:table.column>
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($logs as $log)
            <flux:table.row>
                <flux:table.cell>{{ $loop->iteration }}</flux:table.cell>
                <flux:table.cell class="font-mono text-xs">{{ $log->device_sn }}</flux:table.cell>
                <flux:table.cell class="max-w-xs truncate font-mono text-xs">
                    <code>{{ $log->raw_payload }}</code>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge size="sm" :variant="$log->retry_count > 0 ? 'warning' : 'success'">
                        {{ $log->retry_count }}
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell class="flex gap-1">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        wire:click="openEditModal({{ $log->id }})">
                        {{ __('Edit') }}
                    </flux:button>
                    <flux:button
                        variant="ghost"
                        size="sm"
                        wire:click="incrementRetry({{ $log->id }})">
                        {{ __('Retry') }}
                    </flux:button>
                    <flux:button
                        variant="danger"
                        size="sm"
                        wire:click="deleteLog({{ $log->id }})"
                        wire:confirm="{{ __('Are you sure you want to delete this log?') }}">
                        {{ __('Delete') }}
                    </flux:button>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="5" class="text-center text-neutral-500">
                    {{ __('No logs found.') }}
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="log-form" class="w-full max-w-lg">
        <flux:heading size="lg">
            {{ $editingLogId ? __('Edit Log') : __('Add Log') }}
        </flux:heading>

        <form wire:submit="save" class="mt-6 space-y-4">
            <flux:input wire:model="device_sn" :label="__('Device SN')" required autofocus />
            <flux:textarea wire:model="raw_payload" :label="__('Raw Payload')" required rows="4" />
            <flux:input wire:model="retry_count" :label="__('Retry Count')" type="number" min="0" required />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $editingLogId ? __('Update') : __('Save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>