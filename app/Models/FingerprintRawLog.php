<?php

namespace App\Models;

use Database\Factories\FingerprintRawLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerprintRawLog extends Model
{
    /** @use HasFactory<FingerprintRawLogFactory> */
    use HasFactory;

    protected $fillable = [
        'device_sn',
        'raw_payload',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'retry_count' => 'integer',
        ];
    }
}
