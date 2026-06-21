<?php

namespace App\Repositories;

use App\Models\FingerprintRawLog;
use Illuminate\Database\Eloquent\Collection;

class FingerprintRawLogRepository implements FingerprintRawLogRepositoryInterface
{
    public function all(): Collection
    {
        return FingerprintRawLog::latest()->get();
    }

    public function findById(int $id): ?FingerprintRawLog
    {
        return FingerprintRawLog::find($id);
    }

    public function findByDeviceSn(string $deviceSn): Collection
    {
        return FingerprintRawLog::where('device_sn', $deviceSn)
            ->latest()
            ->get();
    }

    public function create(array $data): FingerprintRawLog
    {
        return FingerprintRawLog::create($data);
    }

    public function incrementRetryCount(FingerprintRawLog $log): FingerprintRawLog
    {
        $log->increment('retry_count');

        return $log->fresh();
    }

    public function delete(FingerprintRawLog $log): void
    {
        $log->delete();
    }
}
