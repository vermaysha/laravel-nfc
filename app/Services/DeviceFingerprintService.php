<?php

namespace App\Services;

use App\Models\DeviceFingerprint;
use Illuminate\Support\Facades\Request;

class DeviceFingerprintService
{
    public function generateFingerprint(array $fingerprintData): string
    {
        // Create a hash from the device fingerprint data
        $data = [
            'user_agent' => $fingerprintData['userAgent'] ?? '',
            'screen_resolution' => $fingerprintData['screenResolution'] ?? '',
            'timezone' => $fingerprintData['timezone'] ?? '',
            'language' => $fingerprintData['language'] ?? '',
            'platform' => $fingerprintData['platform'] ?? '',
            'canvas_fingerprint' => $fingerprintData['canvasFingerprint'] ?? '',
            'webgl_fingerprint' => $fingerprintData['webglFingerprint'] ?? '',
        ];

        return hash('sha256', json_encode($data));
    }

    public function getOrCreateFingerprint(array $fingerprintData): DeviceFingerprint
    {
        $fingerprintHash = $this->generateFingerprint($fingerprintData);
        $ipAddress = Request::ip();
        $userAgent = Request::userAgent();

        $fingerprint = DeviceFingerprint::where('fingerprint_hash', $fingerprintHash)->first();

        if ($fingerprint) {
            $fingerprint->updateUsage();
            return $fingerprint;
        }

        return DeviceFingerprint::create([
            'fingerprint_hash' => $fingerprintHash,
            'browser_info' => [
                'name' => $fingerprintData['browserName'] ?? '',
                'version' => $fingerprintData['browserVersion'] ?? '',
                'user_agent' => $userAgent,
            ],
            'device_info' => [
                'screen_resolution' => $fingerprintData['screenResolution'] ?? '',
                'timezone' => $fingerprintData['timezone'] ?? '',
                'language' => $fingerprintData['language'] ?? '',
                'platform' => $fingerprintData['platform'] ?? '',
                'canvas_fingerprint' => $fingerprintData['canvasFingerprint'] ?? '',
                'webgl_fingerprint' => $fingerprintData['webglFingerprint'] ?? '',
            ],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);
    }

    public function isDeviceTrusted(DeviceFingerprint $fingerprint): bool
    {
        return $fingerprint->isTrusted();
    }

    public function validateDevice(array $fingerprintData): array
    {
        $fingerprint = $this->getOrCreateFingerprint($fingerprintData);

        return [
            'is_trusted' => $this->isDeviceTrusted($fingerprint),
            'fingerprint' => $fingerprint,
            'risk_level' => $this->calculateRiskLevel($fingerprint),
        ];
    }

    private function calculateRiskLevel(DeviceFingerprint $fingerprint): string
    {
        if ($fingerprint->isBlocked()) {
            return 'high';
        }

        if ($fingerprint->isSuspicious()) {
            return 'medium';
        }

        // New device with low usage
        if ($fingerprint->usage_count < 5) {
            return 'medium';
        }

        return 'low';
    }
}
