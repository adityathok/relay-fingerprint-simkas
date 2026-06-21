<?php

namespace App\Repositories;

use App\Models\FingerprintRawLog;
use Illuminate\Database\Eloquent\Collection;

interface FingerprintRawLogRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?FingerprintRawLog;

    public function findByDeviceSn(string $deviceSn): Collection;

    public function create(array $data): FingerprintRawLog;

    public function incrementRetryCount(FingerprintRawLog $log): FingerprintRawLog;

    public function delete(FingerprintRawLog $log): void;
}
