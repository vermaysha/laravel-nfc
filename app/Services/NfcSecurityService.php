<?php

namespace App\Services;

use App\Models\NfcCard;
use App\Models\Employee;
use Illuminate\Support\Facades\Crypt;

class NfcSecurityService
{
    private const RSA_KEY_SIZE = 2048;
    private const CARD_DATA_VERSION = 1;

    public function generateKeyPair(): array
    {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => self::RSA_KEY_SIZE,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKeyDetails = openssl_pkey_get_details($res);
        $publicKey = $publicKeyDetails['key'];

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
        ];
    }

    public function createSecureCardData(Employee $employee, string $cardUid): array
    {
        $keyPair = $this->generateKeyPair();

        // Create card data with timestamp and version
        $cardData = [
            'version' => self::CARD_DATA_VERSION,
            'employee_id' => $employee->id,
            'employee_code' => $employee->employee_id,
            'issued_at' => now()->timestamp,
            'card_uid' => $cardUid,
            'nonce' => bin2hex(random_bytes(16)),
        ];

        // Encrypt the data
        $encryptedData = Crypt::encrypt(json_encode($cardData));

        // Create signature
        $signature = $this->createSignature($encryptedData, $keyPair['private_key']);

        return [
            'public_key' => $keyPair['public_key'],
            'encrypted_data' => $encryptedData,
            'signature' => $signature,
            'card_data' => $cardData,
        ];
    }

    public function verifyCardFromNdef(array $ndefData): ?NfcCard
    {
        // Find the NFC card by employee ID and card UID
        $nfcCard = NfcCard::where('employee_id', $ndefData['employee_id'])
                          ->where('card_uid', $ndefData['card_uid'])
                          ->where('status', 'active')
                          ->first();

        if (!$nfcCard) {
            throw new \Exception('NFC card not found or inactive');
        }

        // Verify the key hash matches
        if (isset($ndefData['key_hash'])) {
            $storedKeyHash = substr(hash('sha256', $nfcCard->public_key), 0, 16);
            if ($storedKeyHash !== $ndefData['key_hash']) {
                throw new \Exception('Card key hash mismatch - possible cloned card');
            }
        }

        // Verify the card data using stored encrypted data and signature
        $this->verifyCardData($nfcCard->encrypted_data, $nfcCard->signature, $nfcCard->public_key);

        // Additional security checks
        if ($this->detectClonedCard($nfcCard, $ndefData)) {
            throw new \Exception('Cloned card detected');
        }

        return $nfcCard;
    }

    public function verifyCardData(string $encryptedData, string $signature, string $publicKey): array
    {
        // Verify signature first
        if (!$this->verifySignature($encryptedData, $signature, $publicKey)) {
            throw new \Exception('Invalid card signature - possible cloned card');
        }

        try {
            // Decrypt the data
            $decryptedData = Crypt::decrypt($encryptedData);
            $cardData = json_decode($decryptedData, true);

            if (!$cardData) {
                throw new \Exception('Invalid card data format');
            }

            // Validate data structure
            $this->validateCardDataStructure($cardData);

            return $cardData;
        } catch (\Exception $e) {
            throw new \Exception('Failed to decrypt card data: ' . $e->getMessage());
        }
    }

    public function isCardDataValid(array $cardData, string $scannedCardUid): bool
    {
        logger('Validating NFC card data', [
            'card_data' => $cardData,
            'scanned_card_uid' => $scannedCardUid,
        ]);
        // Check version compatibility
        if ($cardData['version'] !== self::CARD_DATA_VERSION) {
            return false;
        }

        // // Verify card UID matches
        // if ($cardData['card_uid'] !== $scannedCardUid) {
        //     return false;
        // }

        // Check if card is not too old (prevent replay attacks)
        $maxAge = 365 * 24 * 60 * 60; // 1 year in seconds
        if ((time() - $cardData['issued_at']) > $maxAge) {
            return false;
        }

        return true;
    }

    public function createNdefPayload(array $secureData): string
    {
        // For NTAG215, we'll store only essential data on the card
        // The public key will be stored in the database, not on the card
        $payload = [
            'v' => self::CARD_DATA_VERSION,
            'id' => $secureData['card_data']['employee_id'],
            'uid' => $secureData['card_data']['card_uid'],
            'n' => $secureData['card_data']['nonce'],
            't' => $secureData['card_data']['issued_at'],
            // Store a hash of the public key instead of the full key
            'kh' => substr(hash('sha256', $secureData['public_key']), 0, 16),
        ];

        $jsonPayload = json_encode($payload);

        // Check size limit (leaving space for NDEF headers - NTAG215 has 496 usable bytes)
        if (strlen($jsonPayload) > 400) {
            throw new \Exception('Card data too large for NTAG215');
        }

        return $jsonPayload;
    }

    public function parseNdefPayload(string $payload): array
    {
        $data = json_decode($payload, true);

        if (!$data || !isset($data['v'], $data['id'], $data['uid'], $data['n'], $data['t'])) {
            throw new \Exception('Invalid NDEF payload format');
        }

        return [
            'version' => $data['v'],
            'employee_id' => $data['id'],
            'card_uid' => $data['uid'],
            'nonce' => $data['n'],
            'issued_at' => $data['t'],
            'key_hash' => $data['kh'] ?? null,
        ];
    }

    private function createSignature(string $data, string $privateKey): string
    {
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    private function verifySignature(string $data, string $signature, string $publicKey): bool
    {
        $binarySignature = base64_decode($signature);
        return openssl_verify($data, $binarySignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    private function validateCardDataStructure(array $cardData): void
    {
        $requiredFields = ['version', 'employee_id', 'employee_code', 'issued_at', 'card_uid', 'nonce'];

        foreach ($requiredFields as $field) {
            if (!isset($cardData[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
    }

    public function detectClonedCard(NfcCard $nfcCard, array $scannedData): bool
    {
        // Check for timing attacks
        $lastScan = $nfcCard->attendances()->latest('scanned_at')->first();
        if ($lastScan && $lastScan->scanned_at->diffInSeconds(now()) < 5) {
            return true; // Too frequent scans
        }

        // Check for simultaneous usage from different locations
        $recentScans = $nfcCard->attendances()
            ->where('scanned_at', '>=', now()->subMinutes(5))
            ->with('deviceFingerprint')
            ->get();

        if ($recentScans->count() > 1) {
            $uniqueFingerprints = $recentScans->pluck('deviceFingerprint.fingerprint_hash')->unique();
            if ($uniqueFingerprints->count() > 1) {
                return true; // Multiple devices used simultaneously
            }
        }

        return false;
    }
}
