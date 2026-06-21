<?php

use Livewire\Component;
use App\Models\Client;

new class extends Component
{
    public $clients;

    public function mount()
    {
        $this->clients = Client::all();
    }
};
?>

<div>

    <ul>
        @foreach($clients as $client)
        <li class="flex justify-between py-2">
            <span>{{ $client->name }}</span>
            <!-- Memanggil fungsi hapus langsung dari HTML -->
            <button wire:click="deleteClient({{ $client->id }})" class="text-red-500">Hapus</button>
        </li>
        @endforeach
    </ul>

</div>