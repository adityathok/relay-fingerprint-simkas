<?php

namespace App\Http\Controllers;

use App\Services\FingerPrintService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FingerPrintController extends Controller
{
    public function __construct(
        private readonly FingerPrintService $fingerPrintService,
    ) {
        //
    }

    /**
     * Handle CData — main communication endpoint for iClock devices.
     *
     * GET:  Device registration / handshake.
     * POST: Receive attendance logs (tab-separated body) and other table data.
     */
    public function cdata(Request $request): Response
    {
        $deviceSn = $request->query('SN', '');
        $table = $request->query('table');

        if (empty($deviceSn)) {
            return response('OK');
        }

        $body = null;
        if ($request->isMethod('POST')) {
            $body = $request->getContent();
        }

        $result = $this->fingerPrintService->cdata($deviceSn, $table, $body);

        return response($result);
    }

    /**
     * Handle GetRequest — device polls for pending commands.
     *
     * Returns "OK" if no commands, or "C:<id>:<COMMAND>" lines.
     */
    public function getrequest(Request $request): Response
    {
        $deviceSn = $request->query('SN', '');

        if (empty($deviceSn)) {
            return response('OK');
        }

        $result = $this->fingerPrintService->getrequest($deviceSn);

        return response($result);
    }

    /**
     * Handle DeviceCMD — alternative command polling endpoint.
     *
     * Returns "ID:<n>\nCOMMAND:<CMD>" format.
     */
    public function devicecmd(Request $request): Response
    {
        $deviceSn = $request->query('SN', '');

        if (empty($deviceSn)) {
            return response('OK');
        }

        $result = $this->fingerPrintService->devicecmd($deviceSn);

        return response($result);
    }
}
