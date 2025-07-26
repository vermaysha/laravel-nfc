# Claude AI Instructions - Laravel NFC Employee Attendance System

## Project Context & Understanding

This is a production-ready Laravel 12 employee attendance system that uses NFC cards for secure check-in/check-out operations. The system is designed with enterprise-level security features and is optimized for mobile devices using the Web NFC API.

### Core Mission
Provide a secure, user-friendly, and mobile-optimized attendance tracking solution that prevents card cloning, unauthorized access, and fraudulent time tracking through advanced cryptographic security and device fingerprinting.

## System Architecture Deep Dive

### Security-First Design Philosophy
The system implements multiple layers of security:

1. **Cryptographic Security**: RSA-2048 encryption with SHA-256 signatures
2. **Device Authentication**: Multi-factor device fingerprinting
3. **Anti-Cloning**: Advanced pattern detection and behavioral analysis
4. **Audit Trail**: Comprehensive logging of all security events
5. **Access Control**: Progressive risk assessment and device blocking

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.3+)
- **Database**: SQLite (development) / MySQL/PostgreSQL (production)
- **Frontend**: Tailwind CSS + Vanilla JavaScript
- **NFC**: Web NFC API (Chrome/Opera Android)
- **Security**: OpenSSL, custom fingerprinting algorithms

## Key Business Logic

### Employee Lifecycle
1. **Registration**: Employee created with unique ID and profile
2. **Card Issuance**: NFC card generated with RSA key pair and encrypted data
3. **Attendance Tracking**: Real-time scan processing with security validation
4. **Status Management**: Active/inactive states with audit logging

### Attendance Flow
1. **Device Validation**: Generate and validate device fingerprint
2. **Card Reading**: Extract and parse NDEF payload from NFC card
3. **Security Verification**: Validate RSA signatures and detect cloning attempts
4. **Business Logic**: Check attendance rules (duplicate scans, timing, etc.)
5. **Recording**: Store attendance with full metadata and security context

### Security Event Handling
- **Clone Detection**: Multiple cards with same data patterns
- **Device Anomalies**: Unusual fingerprint changes or risk patterns
- **Suspicious Behavior**: Rapid scanning, timing anomalies, location inconsistencies
- **Access Violations**: Blocked devices, expired cards, inactive employees

## Data Models & Relationships

### Employee Model
```php
// Core employee information
id, employee_id (unique), first_name, last_name, email, phone
department, position, hire_date, status (active/inactive)
created_at, updated_at

// Relationships
hasOne(NfcCard), hasMany(Attendance)
```

### NfcCard Model
```php
// Card security data
id, employee_id, card_uid (unique), public_key, encrypted_data, signature
issued_at, expires_at, status (active/blocked/expired), ndef_data
created_at, updated_at

// Relationships
belongsTo(Employee), hasMany(Attendance)
```

### Attendance Model
```php
// Attendance records
id, employee_id, nfc_card_id, device_fingerprint_id
type (check_in/check_out), scanned_at, location, scan_metadata (JSON)
status (valid/suspicious), created_at, updated_at

// Relationships
belongsTo(Employee), belongsTo(NfcCard), belongsTo(DeviceFingerprint)
```

### DeviceFingerprint Model
```php
// Device tracking
id, fingerprint_hash (unique), device_info (JSON), risk_level (low/medium/high)
status (trusted/suspicious/blocked), first_seen_at, last_used_at
usage_count, created_at, updated_at

// Relationships
hasMany(Attendance)
```

## Service Layer Architecture

### NfcSecurityService
**Purpose**: Handle all cryptographic operations and security validations

**Key Methods**:
- `createSecureCardData()`: Generate RSA keys and encrypt employee data
- `verifyCardData()`: Decrypt and validate card authenticity
- `createNdefPayload()`: Generate NTAG215-optimized payload (max 400 bytes)
- `parseNdefPayload()`: Extract and validate NDEF data
- `detectClonedCard()`: Implement clone detection algorithms
- `isCardDataValid()`: Validate card against business rules

**Security Features**:
- RSA-2048 key generation with secure random seeds
- AES-256 encryption for sensitive data
- SHA-256 HMAC for integrity verification
- Nonce-based replay attack prevention
- Time-based card expiration validation

### DeviceFingerprintService
**Purpose**: Generate, validate, and manage device identities

**Key Methods**:
- `generateFingerprint()`: Create comprehensive device signature
- `validateDevice()`: Check device against known patterns
- `calculateRiskLevel()`: Assess device trustworthiness
- `markAsSuspicious()`: Flag devices for security review
- `isBlocked()`: Check device blocking status

**Fingerprinting Elements**:
- Canvas fingerprinting (unique rendering patterns)
- WebGL fingerprinting (graphics capabilities)
- User-Agent analysis (browser and OS details)
- Screen resolution and timezone data
- Behavioral patterns and usage history

## API Design Patterns

### Request/Response Format
```json
// Standard success response
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response payload
    }
}

// Standard error response
{
    "success": false,
    "message": "Detailed error description",
    "errors": {
        // Validation errors if applicable
    }
}
```

### Security Headers
All API responses include:
- CSRF token validation
- Content-Type: application/json
- Proper HTTP status codes
- Security event logging

## Frontend Architecture

### NFC Scanner Class
**Purpose**: Manage Web NFC API interactions and attendance processing

**Key Features**:
- AbortController for proper scan lifecycle management
- Device fingerprint generation using multiple techniques
- Test mode for development and debugging
- Real-time status updates and error handling
- Progressive enhancement for desktop vs mobile

**Security Considerations**:
- Validate all user inputs before API calls
- Implement proper event cleanup to prevent memory leaks
- Use HTTPS-only for all API communications
- Clear sensitive data from browser memory after use

### Mobile Optimization
- Touch-friendly interface with large scan areas
- Progressive Web App features for offline capability
- Responsive design optimized for portrait orientation
- Battery-efficient scanning with automatic timeouts

## Security Implementation Guidelines

### When Adding New Features

1. **Input Validation**: Always validate and sanitize all inputs
2. **Authorization**: Check user permissions and device trust levels
3. **Logging**: Log all security-relevant events with proper context
4. **Error Handling**: Provide secure error messages without exposing internals
5. **Testing**: Include security test cases for all new functionality

### When Modifying Security Logic

1. **Backward Compatibility**: Ensure existing cards remain valid
2. **Migration Strategy**: Plan for seamless security updates
3. **Performance Impact**: Monitor cryptographic operation performance
4. **Audit Trail**: Maintain complete security event history
5. **Emergency Procedures**: Implement card blocking and recovery mechanisms

### When Debugging Issues

1. **Log Analysis**: Use structured logging for security events
2. **Device Patterns**: Analyze fingerprint changes and risk patterns
3. **Performance Monitoring**: Track scan times and success rates
4. **User Experience**: Balance security with usability
5. **Compliance**: Ensure data protection and privacy requirements

## Common Development Scenarios

### Adding New Employee
1. Validate employee data and check for duplicates
2. Create employee record with proper status
3. Generate audit log entry
4. Optionally trigger card issuance workflow

### Issuing NFC Card
1. Validate employee eligibility and device trust
2. Generate RSA key pair with secure random numbers
3. Encrypt employee data with public key
4. Create NTAG215-optimized NDEF payload
5. Store security data in database
6. Log card issuance event with full context

### Processing Attendance Scan
1. Extract and validate NDEF payload format
2. Generate and validate device fingerprint
3. Lookup card in database and verify authenticity
4. Check for clone detection patterns
5. Validate business rules (duplicate scans, timing)
6. Record attendance with security metadata
7. Update device usage patterns
8. Return appropriate response to user

### Handling Security Violations
1. Identify violation type and severity
2. Block or flag suspicious devices/cards
3. Generate security alerts for administrators
4. Log detailed security event information
5. Implement progressive response (warning → blocking)
6. Provide user feedback without exposing security details

## Performance Considerations

### Database Optimization
- Use database indexes on frequently queried columns
- Implement efficient pagination for large datasets
- Archive old attendance records periodically
- Optimize join queries with proper relationships

### Security Performance
- Cache device fingerprints for repeated scans
- Optimize RSA operations with proper key management
- Implement background security monitoring
- Use efficient clone detection algorithms

### Mobile Performance
- Minimize JavaScript bundle size for faster loading
- Implement efficient NFC scanning loops
- Use progressive enhancement for better user experience
- Optimize network requests with proper caching

## Troubleshooting Guidelines

### Common Issues and Solutions

**NFC Scanning Problems**:
- Browser compatibility (Chrome/Opera Android only)
- NFC permissions and hardware availability
- Card positioning and reader sensitivity
- NDEF payload format validation

**Security Validation Errors**:
- RSA key corruption or expiration
- Device fingerprint changes or blocking
- Clone detection false positives
- Network connectivity and timeout issues

**Performance Issues**:
- Database query optimization
- Large dataset pagination
- Memory leaks in JavaScript
- Inefficient security calculations

### Debugging Approach
1. **Log Analysis**: Review application logs for error patterns
2. **Security Events**: Check for security violations and patterns
3. **Performance Metrics**: Monitor response times and success rates
4. **User Feedback**: Collect and analyze user experience reports
5. **System Health**: Monitor database and server performance

## Integration Considerations

### External Systems
- **Payroll Integration**: Export attendance data in standard formats
- **HR Systems**: Sync employee data and status changes
- **Access Control**: Integrate with building security systems
- **Reporting Tools**: Provide data feeds for analytics platforms

### API Extensions
- **Webhook Support**: Real-time attendance notifications
- **Bulk Operations**: Efficient batch processing for large datasets
- **Data Export**: Multiple format support (CSV, JSON, XML)
- **Analytics APIs**: Attendance patterns and security metrics

## Maintenance and Monitoring

### Regular Tasks
- **Security Audits**: Review and update security thresholds
- **Performance Monitoring**: Track system performance and user experience
- **Data Cleanup**: Archive old records and clean temporary data
- **Security Updates**: Apply security patches and updates

### Health Monitoring
- **System Metrics**: Response times, error rates, success ratios
- **Security Metrics**: Clone detection rates, device blocking frequency
- **User Metrics**: Scan success rates, user satisfaction
- **Business Metrics**: Attendance patterns, compliance rates

Remember: This system handles sensitive employee data and security credentials. Always prioritize security, privacy, and compliance in all development decisions. When in doubt, err on the side of security and user privacy.
