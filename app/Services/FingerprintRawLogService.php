<?php

namespace App\Services;

use App\Models\FingerprintRawLog;
use App\Repositories\FingerprintRawLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FingerprintRawLogService
{
    public function __construct(
        private readonly FingerprintRawLogRepositoryInterface $logRepository,
    ) {
        //
    }

    public function getAll(): Collection
    {
        return $this->logRepository->all();
    }

    public function getById(int $id): ?FingerprintRawLog
    {
        return $this->logRepository->findById($id);
    }

    public function getByDeviceSn(string $deviceSn): Collection
    {
        return $this->logRepository->findByDeviceSn($deviceSn);
    }

    public function create(array $data): FingerprintRawLog
    {
        return $this->logRepository->create($data);
    }

    public function incrementRetryCount(int $id): FingerprintRawLog
    {
        $log = $this->logRepository->findById($id);

        if (! $log) {
            throw new \RuntimeException("FingerprintRawLog with ID {$id} not found.");
        }

        return $this->logRepository->incrementRetryCount($log);
    }

    public function delete(int $id): void
    {
        $log = $this->logRepository->findById($id);

        if (! $log) {
            throw new \RuntimeException("FingerprintRawLog with ID {$id} not found.");
        }

        $this->logRepository->delete($log);
    }
}
