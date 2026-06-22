<?php

namespace App\Http\Controllers;

use App\Services\FingerPrintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FingerPrintController extends Controller
{
    public function __construct(
        private readonly FingerPrintService $fingerPrintService,
    ) {
        //
    }

    /**
     * Handle real-time attendance data push from fingerprint machines.
     */
    public function rtdata(Request $request): JsonResponse
    {
        $deviceSn = $request->input('SN', $request->input('SerialNumber', ''));
        $payload = $request->except(['SN', 'SerialNumber']);

        if (empty($deviceSn)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing device serial number.',
            ], 400);
        }

        $result = $this->fingerPrintService->rtdata($deviceSn, $payload);

        if (! $result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Handle device polling for pending commands.
     */
    public function getrequest(Request $request): JsonResponse
    {
        $deviceSn = $request->input('SN', $request->input('SerialNumber', ''));

        if (empty($deviceSn)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing device serial number.',
            ], 400);
        }

        $result = $this->fingerPrintService->getrequest($deviceSn);

        if (! $result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Queue a command for a fingerprint machine.
     */
    public function devicecmd(Request $request): JsonResponse
    {
        $deviceSn = $request->input('SN', $request->input('SerialNumber', ''));
        $command = $request->input('command', '');
        $parameters = $request->except(['SN', 'SerialNumber', 'command']);

        if (empty($deviceSn) || empty($command)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing device serial number or command.',
            ], 400);
        }

        $result = $this->fingerPrintService->devicecmd($deviceSn, $command, $parameters);

        if (! $result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }
}
