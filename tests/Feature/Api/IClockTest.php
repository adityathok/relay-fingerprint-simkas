<?php

use App\Models\Device;
use App\Models\FingerprintRawLog;

use function Pest\Laravel\assertDatabaseCount;

describe('GET /api/iclock/cdata', function () {
    it('returns OK for device handshake without SN', function () {
        $response = $this->get('/api/iclock/cdata');

        $response->assertOk()->assertSee('OK');
    });

    it('returns OK for known device handshake', function () {
        $device = Device::factory()->create();

        $response = $this->get('/api/iclock/cdata?SN=' . $device->serial_number);

        $response->assertOk()->assertSee('OK');
    });

    it('returns OK for unknown device handshake', function () {
        $response = $this->get('/api/iclock/cdata?SN=UNKNOWN-DEVICE-001');

        $response->assertOk()->assertSee('OK');
    });

    it('does not store anything in fingerprint_raw_logs on GET', function () {
        $device = Device::factory()->create();

        $this->get('/api/iclock/cdata?SN=' . $device->serial_number);

        assertDatabaseCount('fingerprint_raw_logs', 0);
    });
});

describe('POST /api/iclock/cdata', function () {
    it('saves ATTLOG data with raw body content', function () {
        $device = Device::factory()->create();
        $body = "12345\t2026-06-22 07:01:12\t0\t1\t0";

        $response = $this->call('POST', '/api/iclock/cdata?SN=' . $device->serial_number . '&table=ATTLOG', [], [], [], ['CONTENT_TYPE' => 'text/plain'], $body);

        $response->assertOk()->assertSee('OK');

        assertDatabaseCount('fingerprint_raw_logs', 1);

        $log = FingerprintRawLog::first();
        expect($log->device_sn)->toBe($device->serial_number);
        expect($log->raw_payload)->toBe(json_encode([
            'table' => 'ATTLOG',
            'data' => $body,
        ]));
    });

    it('saves data from unknown device', function () {
        $body = "99999\t2026-06-22 08:00:00\t0\t1\t0";

        $response = $this->call('POST', '/api/iclock/cdata?SN=SN-UNKNOWN-999&table=ATTLOG', [], [], [], ['CONTENT_TYPE' => 'text/plain'], $body);

        $response->assertOk()->assertSee('OK');

        assertDatabaseCount('fingerprint_raw_logs', 1);

        $log = FingerprintRawLog::first();
        expect($log->device_sn)->toBe('SN-UNKNOWN-999');
    });

    it('saves OPERLOG data', function () {
        $device = Device::factory()->create();
        $body = "1\tadmin\t2026-06-22 09:00:00\t13\tSystem boot";

        $response = $this->call('POST', '/api/iclock/cdata?SN=' . $device->serial_number . '&table=OPERLOG', [], [], [], ['CONTENT_TYPE' => 'text/plain'], $body);

        $response->assertOk()->assertSee('OK');

        assertDatabaseCount('fingerprint_raw_logs', 1);
    });

    it('saves USERINFO data', function () {
        $device = Device::factory()->create();
        $body = "1\tJohn Doe\t1234\t\t0\t0\t0";

        $response = $this->call('POST', '/api/iclock/cdata?SN=' . $device->serial_number . '&table=USERINFO', [], [], [], ['CONTENT_TYPE' => 'text/plain'], $body);

        $response->assertOk()->assertSee('OK');

        assertDatabaseCount('fingerprint_raw_logs', 1);
    });

    it('does not save when table parameter is missing', function () {
        $device = Device::factory()->create();
        $body = "12345\t2026-06-22 07:01:12\t0\t1\t0";

        $response = $this->call('POST', '/api/iclock/cdata?SN=' . $device->serial_number, [], [], [], ['CONTENT_TYPE' => 'text/plain'], $body);

        $response->assertOk()->assertSee('OK');

        assertDatabaseCount('fingerprint_raw_logs', 0);
    });
});

describe('GET /api/iclock/getrequest', function () {
    it('returns OK without SN', function () {
        $response = $this->get('/api/iclock/getrequest');

        $response->assertOk()->assertSee('OK');
    });

    it('returns OK for known device', function () {
        $device = Device::factory()->create();

        $response = $this->get('/api/iclock/getrequest?SN=' . $device->serial_number);

        $response->assertOk()->assertSee('OK');
    });

    it('returns OK for unknown device', function () {
        $response = $this->get('/api/iclock/getrequest?SN=UNKNOWN-DEVICE-001');

        $response->assertOk()->assertSee('OK');
    });
});

describe('GET /api/iclock/devicecmd', function () {
    it('returns OK without SN', function () {
        $response = $this->get('/api/iclock/devicecmd');

        $response->assertOk()->assertSee('OK');
    });

    it('returns OK for known device', function () {
        $device = Device::factory()->create();

        $response = $this->get('/api/iclock/devicecmd?SN=' . $device->serial_number);

        $response->assertOk()->assertSee('OK');
    });

    it('returns OK for unknown device', function () {
        $response = $this->get('/api/iclock/devicecmd?SN=UNKNOWN-DEVICE-001');

        $response->assertOk()->assertSee('OK');
    });
});
