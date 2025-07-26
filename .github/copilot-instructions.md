# GitHub Copilot Instructions - Laravel NFC Employee Attendance System

## Project Overview
This is a secure Laravel 12 employee attendance system using NFC cards with advanced security features including RSA encryption, device fingerprinting, and clone detection. The system is optimized for NTAG215 NFC cards and uses the Web NFC API for mobile browser scanning.

## Code Style & Conventions

### PHP/Laravel Standards
- Follow PSR-12 coding standards
- Use Laravel naming conventions (PascalCase for classes, camelCase for methods/properties)
- Prefer explicit return types and parameter types
- Use Laravel's built-in helpers and facades
- Follow Repository pattern for data access where appropriate

### Frontend Standards
- Use Tailwind CSS for styling (mobile-first approach)
- Vanilla JavaScript with ES6+ features
- Responsive design patterns for mobile NFC scanning
- Consistent icon usage with Font Awesome

### Security Patterns
- Always validate input data with Laravel's validation rules
- Use RSA encryption for sensitive NFC card data
- Implement device fingerprinting for access control
- Log security events for audit trails
- Never expose private keys or sensitive data in responses

## Architecture Patterns

### Controllers
```php
// Always inject services via constructor
public function __construct(
    NfcSecurityService $nfcSecurityService,
    DeviceFingerprintService $deviceFingerprintService
) {
    $this->nfcSecurityService = $nfcSecurityService;
    $this->deviceFingerprintService = $deviceFingerprintService;
}

// Use database transactions for complex operations
DB::beginTransaction();
try {
    // operations
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Operation failed', ['error' => $e->getMessage()]);
    throw $e;
}

// Return consistent JSON responses
return response()->json([
    'success' => true,
    'message' => 'Operation completed successfully',
    'data' => $data
]);
```

### Models
```php
// Use proper relationships
public function employee(): BelongsTo
{
    return $this->belongsTo(Employee::class);
}

// Add status helper methods
public function isActive(): bool
{
    return $this->status === 'active';
}

// Use proper casting for complex data
protected $casts = [
    'scan_metadata' => 'array',
    'scanned_at' => 'datetime',
];
```

### Services
```php
// Use dependency injection
class NfcSecurityService
{
    public function __construct(
        private string $privateKeyPath,
        private string $publicKeyPath
    ) {}

    // Always handle exceptions properly
    public function verifyCardData(string $encryptedData, string $signature, string $publicKey): array
    {
        try {
            // verification logic
        } catch (\Exception $e) {
            Log::error('Card verification failed', ['error' => $e->getMessage()]);
            throw new \Exception('Card verification failed: ' . $e->getMessage());
        }
    }
}
```

### Frontend JavaScript
```javascript
// Use classes for complex functionality
class NFCScanner {
    constructor() {
        this.ndef = null;
        this.isScanning = false;
        // Bind methods to preserve context
        this.handleNFCRead = this.handleNFCRead.bind(this);
    }

    // Always handle errors gracefully
    async startScanning() {
        try {
            // scanning logic
        } catch (error) {
            console.error('NFC scan failed:', error);
            this.showStatus('Failed to start scanning: ' + error.message, 'error');
        }
    }

    // Use proper event cleanup
    cleanup() {
        if (this.ndef) {
            this.ndef.removeEventListener('read', this.handleNFCRead);
        }
    }
}
```

## Key Components

### 1. NfcSecurityService
- Handles RSA key generation and management
- Encrypts/decrypts card data using AES-256
- Creates NTAG215-optimized payloads (max 400 bytes)
- Implements clone detection algorithms
- Validates card authenticity and expiration

### 2. DeviceFingerprintService
- Generates unique device signatures using Canvas, WebGL, UserAgent
- Implements risk assessment (low/medium/high)
- Tracks device usage patterns
- Manages device blocking and suspicious activity detection

### 3. AttendanceController
- Processes NFC scan requests with security validation
- Prevents duplicate scans within 1-minute window
- Records attendance with metadata and device information
- Handles check-in/check-out logic validation

### 4. Database Schema
```sql
-- Core tables with proper indexing
employees: id, employee_id (unique), first_name, last_name, email, status
nfc_cards: id, employee_id, card_uid (unique), public_key, encrypted_data, signature, status
attendances: id, employee_id, nfc_card_id, device_fingerprint_id, type, scanned_at, status
device_fingerprints: id, fingerprint_hash (unique), risk_level, status, last_used_at
```

## Security Guidelines

### NFC Card Security
- Generate unique RSA key pairs per card (2048-bit)
- Store only public keys and encrypted data on cards
- Use nonces to prevent replay attacks
- Implement card expiration (default: 1 year)
- Log all card operations for audit trails

### Device Security
- Generate comprehensive device fingerprints
- Implement progressive risk assessment
- Block devices with high-risk patterns
- Monitor for suspicious scanning behavior
- Rate limit scan attempts per device

### Data Protection
- Never log sensitive card data
- Encrypt all stored card information
- Use secure random number generation
- Implement proper session management
- Validate all input parameters

## Common Patterns

### API Response Format
```php
// Success response
return response()->json([
    'success' => true,
    'message' => 'Operation completed successfully',
    'data' => $responseData
]);

// Error response
return response()->json([
    'success' => false,
    'message' => 'Operation failed: ' . $errorMessage
], $httpStatusCode);
```

### Validation Rules
```php
// Always validate NFC-related requests
$validated = $request->validate([
    'card_uid' => 'required|string|max:255',
    'ndef_payload' => 'required|string',
    'device_fingerprint' => 'required|array',
    'type' => 'required|in:check_in,check_out',
]);
```

### Logging Patterns
```php
// Security events
Log::warning('Security violation detected', [
    'event' => 'cloned_card_detected',
    'card_uid' => $cardUid,
    'employee_id' => $employeeId,
    'device_fingerprint' => $deviceHash
]);

// Attendance events
Log::info('Attendance recorded', [
    'attendance_id' => $attendance->id,
    'employee_id' => $employee->id,
    'type' => $type,
    'device_fingerprint' => $deviceHash
]);
```

### Error Handling
```php
// Service layer error handling
try {
    $result = $this->performOperation();
    return $result;
} catch (SecurityException $e) {
    Log::warning('Security exception', ['error' => $e->getMessage()]);
    throw $e;
} catch (\Exception $e) {
    Log::error('Unexpected error', ['error' => $e->getMessage()]);
    throw new \Exception('Operation failed: ' . $e->getMessage());
}
```

## Mobile NFC Considerations

### Browser Compatibility
- Target Chrome for Android 89+ and Opera for Android 63+
- Always check NDEFReader availability
- Handle permission requests gracefully
- Provide clear error messages for unsupported browsers

### NFC Scanning Best Practices
- Use AbortController for proper scan lifecycle management
- Implement proper event listener cleanup
- Handle scan interruptions gracefully
- Provide visual feedback during scanning

### Responsive Design
- Mobile-first approach with Tailwind CSS
- Touch-friendly interface elements
- Optimized scanning area for mobile devices
- Progressive enhancement for desktop testing

## Testing Considerations

### Test Mode Implementation
- Provide desktop simulation for development
- Use realistic test data matching production format
- Implement proper mocking for NFC operations
- Ensure test mode is clearly marked and disabled in production

### Security Testing
- Test clone detection algorithms
- Validate encryption/decryption cycles
- Test device fingerprinting accuracy
- Verify rate limiting and blocking mechanisms

## Performance Optimizations

### Database
- Index frequently queried columns (card_uid, employee_id, scanned_at)
- Use eager loading for relationships
- Implement pagination for large datasets
- Regular cleanup of old records

### Frontend
- Minimize JavaScript bundle size
- Cache API responses where appropriate
- Optimize NFC scanning loops
- Use event delegation for dynamic content

## Maintenance Tasks

### Regular Maintenance
- Rotate RSA keys periodically
- Clean up expired cards and old attendance records
- Review and update device fingerprint patterns
- Monitor system performance and security metrics

### Security Audits
- Review suspicious activity reports
- Validate clone detection effectiveness
- Update security thresholds based on usage patterns
- Ensure compliance with data protection requirements

Remember: This system handles sensitive employee data and security credentials. Always prioritize security over convenience and follow the principle of least privilege.
