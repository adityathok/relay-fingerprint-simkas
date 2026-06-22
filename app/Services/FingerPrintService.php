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
     * Handle CData — the main communication endpoint for iClock devices.
     *
     * - GET:  Device registration / handshake. Returns server configuration.
     * - POST: Receive attendance logs (tab-separated body), OPERLOG, USERINFO, etc.
     *
     * @param  string  $deviceSn  Serial number from query param.
     * @param  string|null  $table  Table type (ATTLOG, OPERLOG, USERINFO, etc.).
     * @param  string|null  $body  Raw request body (tab-separated lines for POST).
     * @return string Plain text response for the device.
     */
    public function cdata(string $deviceSn, ?string $table = null, ?string $body = null): string
    {
        $device = $this->deviceRepository->findBySerialNumber($deviceSn);

        // Register unknown device or update last seen
        if (! $device) {
            // TODO: optionally auto-register unknown devices
            return 'OK';
        }

        // POST: store the raw data
        if ($body !== null && $table !== null) {
            $this->logRepository->create([
                'device_sn' => $deviceSn,
                'raw_payload' => json_encode([
                    'table' => $table,
                    'data' => $body,
                ]),
            ]);
        }

        return 'OK';
    }

    /**
     * Handle GetRequest — device polls for pending commands.
     *
     * Returns "OK" if no commands, or one or more "C:<id>:<COMMAND>" lines.
     *
     * @param  string  $deviceSn  Serial number of the device.
     * @return string Plain text response for the device.
     */
    public function getrequest(string $deviceSn): string
    {
        $device = $this->deviceRepository->findBySerialNumber($deviceSn);

        if (! $device) {
            return 'OK';
        }

        // TODO: Query pending commands from a device_commands table.
        // Example return when commands exist:
        // return "C:1:CHECK\nC:2:REBOOT";

        return 'OK';
    }

    /**
     * Handle DeviceCMD — alternative command polling endpoint.
     *
     * Returns command details in "ID:<n>\nCOMMAND:<CMD>" format.
     *
     * @param  string  $deviceSn  Serial number of the device.
     * @return string Plain text response for the device.
     */
    public function devicecmd(string $deviceSn): string
    {
        $device = $this->deviceRepository->findBySerialNumber($deviceSn);

        if (! $device) {
            return 'OK';
        }

        // TODO: Query pending commands from a device_commands table.
        // Example return when a command exists:
        // return "ID:1\nCOMMAND:REBOOT";

        return 'OK';
    }
}
