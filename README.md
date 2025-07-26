# Laravel NFC Employee Attendance System

A secure, modern employee attendance system using NFC cards with Laravel 12 backend and mobile-responsive frontend. Features advanced security measures including RSA encryption, device fingerprinting, and clone detection.

![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.3-blue.svg)
![NFC](https://img.shields.io/badge/NFC-Web%20API-green.svg)
![Security](https://img.shields.io/badge/Security-RSA%20Encryption-orange.svg)

## 🚀 Features

### 🔐 Security Features
- **RSA Asymmetric Encryption**: Secure card data with public/private key pairs
- **Device Fingerprinting**: Track and validate scanning devices
- **Clone Detection**: Advanced algorithms to detect cloned NFC cards
- **Anti-Replay Protection**: Nonce-based security to prevent replay attacks
- **Suspicious Activity Monitoring**: Real-time detection and flagging

### 📱 NFC Technology
- **NTAG215 Optimized**: Compact data structure for 500-byte NFC cards
- **Web NFC API**: Modern browser-based NFC scanning
- **Mobile Responsive**: Optimized for mobile Chrome and Opera browsers
- **Real-time Processing**: Instant attendance recording and validation

### 💼 Business Features
- **Employee Management**: Complete CRUD operations for employees
- **NFC Card Management**: Issue, activate, block, and track NFC cards
- **Attendance Tracking**: Check-in/check-out with timestamps and locations
- **Dashboard Analytics**: Real-time attendance statistics and reports
- **Responsive Design**: Mobile-first design with Tailwind CSS

## 📋 System Requirements

### Server Requirements
- **PHP**: 8.3 or higher
- **Laravel**: 12.x
- **Database**: SQLite (included) or MySQL/PostgreSQL
- **Extensions**: OpenSSL, JSON, PDO

### Client Requirements
- **Mobile Browser**: Chrome for Android 89+ or Opera for Android 63+
- **NFC Hardware**: Device with NFC capability
- **Permissions**: NFC access permissions

## 🛠️ Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd laravel-nfc
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (if using Vite)
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

### 5. Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## 📖 Usage Guide

### 🏢 Employee Management

#### Adding New Employees
1. Navigate to **Employees** section
2. Click **Add Employee**
3. Fill in employee details:
   - Employee ID (unique)
   - First Name, Last Name
   - Email, Phone
   - Department, Position
   - Hire Date

#### Employee Status Management
- **Active**: Employee can use attendance system
- **Inactive**: Employee access is disabled
- **Suspended**: Temporary access suspension

### 💳 NFC Card Management

#### Issuing NFC Cards
1. Go to **NFC Cards** section
2. Click **Write New Card**
3. Select employee from dropdown
4. Follow mobile browser prompts to write card data
5. Card automatically linked to employee

#### Card Security Features
- **RSA Encryption**: Each card has unique public/private key pair
- **Compact Payload**: Optimized for NTAG215 (98-byte payload)
- **Expiration**: Cards expire after 1 year (configurable)
- **Status Tracking**: Active, blocked, expired status

### 📊 Attendance Scanning

#### Mobile NFC Scanning
1. Open scanner on mobile device with NFC
2. Choose **Check In** or **Check Out** mode
3. Tap **Start Scanning**
4. Grant NFC permissions when prompted
5. Hold NFC card near device
6. View real-time attendance confirmation

#### Test Mode (Development)
1. Enable **Test Mode** checkbox (desktop only)
2. Click **Start Scanning**
3. Click scanner area to simulate NFC reads
4. Perfect for development and testing

### 📈 Dashboard & Analytics

#### Real-time Statistics
- Today's attendance summary
- Active employees count
- Recent scan activity
- Security alerts and suspicious activities

#### Attendance Reports
- Employee attendance history
- Check-in/check-out patterns
- Late arrivals and early departures
- Export capabilities for payroll integration

## 🔧 Configuration

### Security Settings

#### RSA Key Configuration
```php
// config/nfc.php
'rsa' => [
    'key_size' => 2048,
    'private_key_path' => storage_path('app/private/nfc_private.pem'),
    'public_key_path' => storage_path('app/private/nfc_public.pem'),
]
```

#### Device Fingerprinting
```php
'device_fingerprinting' => [
    'enabled' => true,
    'risk_threshold' => 'medium', // low, medium, high
    'block_suspicious_devices' => true,
]
```

#### Clone Detection
```php
'clone_detection' => [
    'enabled' => true,
    'sensitivity' => 'high', // low, medium, high
    'auto_block' => true,
]
```

### Database Configuration

#### SQLite (Default)
```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

#### MySQL
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_nfc
DB_USERNAME=root
DB_PASSWORD=
```

## 🔐 Security Architecture

### Data Flow Security
```
NFC Card (NTAG215) → Mobile Browser (Web NFC API) → Laravel Backend
     ↓                        ↓                           ↓
Encrypted Payload → Device Fingerprinting → RSA Verification
     ↓                        ↓                           ↓
Clone Detection → Database Storage → Attendance Record
```

### Encryption Details
- **Algorithm**: RSA-2048 with SHA-256 signatures
- **Key Management**: Separate public/private keys per card
- **Payload Security**: AES-256 encrypted employee data
- **Integrity**: SHA-256 HMAC for tamper detection

### Device Security
- **Fingerprinting**: Canvas, WebGL, UserAgent, Screen resolution
- **Risk Assessment**: Low, Medium, High risk levels
- **Device Blocking**: Automatic blocking of suspicious devices
- **Session Tracking**: Device usage patterns and anomalies

## 📱 Mobile Browser Setup

### Chrome for Android
1. Ensure Chrome version 89+
2. Enable NFC in device settings
3. Grant location permissions (required for NFC)
4. Allow NFC access when prompted

### Opera for Android
1. Ensure Opera version 63+
2. Enable NFC in device settings
3. Grant location permissions
4. Allow NFC access when prompted

### Troubleshooting NFC Issues
- **Permission Denied**: Check NFC permissions in browser settings
- **NFC Not Supported**: Verify device has NFC hardware
- **Scan Failures**: Ensure card is properly positioned (1-2cm from device)
- **Data Errors**: Check card isn't damaged or demagnetized

## 🛠️ Development

### Project Structure
```
laravel-nfc/
├── app/
│   ├── Http/Controllers/
│   │   ├── AttendanceController.php    # Attendance scan processing
│   │   ├── EmployeeController.php      # Employee management
│   │   └── NfcController.php          # NFC card operations
│   ├── Models/
│   │   ├── Employee.php               # Employee model
│   │   ├── NfcCard.php               # NFC card model
│   │   ├── Attendance.php            # Attendance record model
│   │   └── DeviceFingerprint.php     # Device tracking model
│   └── Services/
│       ├── NfcSecurityService.php    # NFC security & encryption
│       └── DeviceFingerprintService.php # Device fingerprinting
├── database/migrations/              # Database schema
├── resources/views/                  # Blade templates
└── routes/
    ├── web.php                      # Web routes
    └── api.php                      # API routes
```

### API Endpoints

#### Employee Management
```http
GET    /api/employees              # List employees
POST   /api/employees              # Create employee
GET    /api/employees/{id}         # Get employee
PUT    /api/employees/{id}         # Update employee
DELETE /api/employees/{id}         # Delete employee
```

#### NFC Card Operations
```http
POST   /api/nfc/write             # Write new NFC card
POST   /api/nfc/read              # Read NFC card data
GET    /api/nfc                   # List NFC cards
PATCH  /api/nfc/{id}/block        # Block NFC card
```

#### Attendance Processing
```http
POST   /api/attendance/scan       # Process attendance scan
GET    /api/attendance            # List attendance records
GET    /api/attendance/today      # Today's summary
PATCH  /api/attendance/{id}/suspicious # Mark as suspicious
```

### Testing

#### Unit Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

#### Browser Testing
```bash
# Start development server
php artisan serve

# Open test scanner (desktop)
# Enable test mode for development
```

### Database Seeding
```bash
# Create sample data
php artisan db:seed

# Specific seeders
php artisan db:seed --class=EmployeeSeeder
php artisan db:seed --class=NfcCardSeeder
```

## 🔍 Troubleshooting

### Common Issues

#### NFC Scanner Not Working
- **Check Browser Support**: Only Chrome/Opera on Android
- **Verify NFC Hardware**: Test with other NFC apps
- **Permission Issues**: Grant NFC access in browser
- **Test Mode**: Use test mode for desktop development

#### Attendance Scan Failures
- **Card UID Mismatch**: Verify card is registered in system
- **Encryption Errors**: Check RSA key configuration
- **Device Blocked**: Review device fingerprint status
- **Clone Detection**: Card may be flagged as suspicious

#### Database Issues
- **Migration Errors**: Check database permissions
- **Seeding Failures**: Verify data format and constraints
- **Connection Issues**: Validate database configuration

### Logging and Debugging
```bash
# View application logs
tail -f storage/logs/laravel.log

# Enable debug mode
# Set APP_DEBUG=true in .env

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🏗️ System Architecture

### Core Components

#### 1. NFC Security Service
- Handles RSA encryption/decryption
- Manages NTAG215 payload optimization
- Implements clone detection algorithms
- Validates card authenticity

#### 2. Device Fingerprinting Service
- Generates unique device signatures
- Tracks device usage patterns
- Implements risk assessment
- Manages device blocking

#### 3. Attendance Controller
- Processes NFC scan requests
- Validates card and device security
- Records attendance with metadata
- Handles duplicate scan prevention

#### 4. Database Schema
```sql
-- Core tables
employees (id, employee_id, name, email, status, ...)
nfc_cards (id, employee_id, card_uid, public_key, encrypted_data, ...)
attendances (id, employee_id, nfc_card_id, type, scanned_at, ...)
device_fingerprints (id, fingerprint_hash, risk_level, status, ...)
```

### Security Flow

1. **Card Registration**:
   - Generate RSA key pair
   - Encrypt employee data
   - Create compact NDEF payload
   - Store security data in database

2. **Attendance Scan**:
   - Read NDEF payload from card
   - Generate device fingerprint
   - Validate card against database
   - Check for cloned card indicators
   - Record attendance with security metadata

3. **Security Validation**:
   - Verify RSA signatures
   - Check device fingerprint risk
   - Detect replay attacks
   - Monitor suspicious patterns

## 📊 Performance Considerations

### Database Optimization
- Indexed card_uid and employee_id columns
- Efficient queries with proper relationships
- Pagination for large datasets
- Regular cleanup of old records

### Mobile Performance
- Minimized JavaScript bundle size
- Optimized NFC scanning loops
- Responsive design with Tailwind CSS
- Cached API responses where appropriate

### Security Performance
- Efficient RSA operations
- Cached device fingerprints
- Optimized clone detection algorithms
- Background security monitoring

## 📚 Additional Resources

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [Web NFC API Specification](https://w3c.github.io/web-nfc/)
- [NTAG215 Technical Specifications](https://www.nxp.com/docs/en/data-sheet/NTAG213_215_216.pdf)

### Security Best Practices
- Regularly rotate RSA keys
- Monitor device fingerprint patterns
- Review suspicious activity reports
- Update browser requirements as needed
- Implement proper backup procedures

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📞 Support

For technical support or questions:
- Create an issue in the repository
- Review existing documentation
- Check troubleshooting guide above

---

**Built with ❤️ using Laravel 12 and modern web technologies**
