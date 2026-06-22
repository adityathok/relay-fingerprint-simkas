<?php

namespace App\Services;

use App\Repositories\DeviceRepositoryInterface;
use App\Repositories\FingerprintRawLogRepositoryInterface;

class FingerPrintService
{
    public function __construct(
        private readonly DeviceRepositoryInterface $deviceRepository,
        private readonly FingerprintRawLogRepositoryInterface $logRepository,
    ) {
        //
    }

    /**
     * Handle real-time attendance data push from the fingerprint machine.
     *
     * Validates the device serial number, stores the raw payload as a pending
     * log, and returns an acknowledgment for the machine.
     *
     * @param  string  $deviceSn  Serial number of the fingerprint machine.
     * @param  array<string, mixed>  $payload  Attendance data sent by the machine.
     * @return array<string, mixed>
     */
    public function rtdata(string $deviceSn, array $payload): array
    {
        $device = $this->deviceRepository->findBySerialNumber($deviceSn);

        if (! $device) {
            return [
                'success' => false,
                'message' => 'Device not recognized.',
            ];
        }

        $this->logRepository->create([
            'device_sn' => $deviceSn,
            'raw_payload' => json_encode($payload),
        ]);

        return [
            'success' => true,
            'message' => 'Data received.',
        ];
    }

    /**
     * Handle device polling for pending commands (GetRequest).
     *
     * Fingerprint machines periodically poll the server to check if there
     * are any pending commands (e.g. delete user, restart, set datetime).
     *
     * @param  string  $deviceSn  Serial number of the fingerprint machine.
     * @return array<string, mixed>
     */
    public function getrequest(string $deviceSn): array
    {
        $device = $this->deviceRepository->findBySerialNumber($deviceSn);

        if (! $device) {
            return [
                'success' => false,
                'message' => 'Device not recognized.',
            ];
        }

        // TODO: Query a device_commands table for pending commands
        // and return them here so the device can execute them.

        return [
            'success' => true,
            'commands' => [],
        ];
    }

    /**
     * Queue a command for a fingerprint machine (DeviceCmd).
     *
     * Prepares a command (e.g. DeleteRecord, Restart, ClearData, SetTime)
     * to be picked up by the device on its next GetRequest poll.
     *
     * @param  string  $deviceSn  Serial number of the target fingerprint machine.
     * @param  string  $command  Command name (e.g. 'DeleteRecord', 'Restart').
     * @param  array<string, mixed>|null  $parameters  Optional parameters for the command.
     * @return array<string, mixed>
     */
    public function devicecmd(string $deviceSn, string $command, ?array $parameters = []): array
    {
        $device = $this->deviceRepository->findBySerialNumber($deviceSn);

        if (! $device) {
            return [
                'success' => false,
                'message' => 'Device not recognized.',
            ];
        }

        // TODO: Store the command in a device_commands table so that
        // getrequest() can return it on the next device poll.

        return [
            'success' => true,
            'message' => "Command '{$command}' queued for device {$deviceSn}.",
        ];
    }
}
