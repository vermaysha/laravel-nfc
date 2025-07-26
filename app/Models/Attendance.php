<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'nfc_card_id',
        'device_fingerprint_id',
        'type',
        'scanned_at',
        'location',
        'scan_metadata',
        'status',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'scan_metadata' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function nfcCard(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class);
    }

    public function deviceFingerprint(): BelongsTo
    {
        return $this->belongsTo(DeviceFingerprint::class);
    }

    public function isCheckIn(): bool
    {
        return $this->type === 'check_in';
    }

    public function isCheckOut(): bool
    {
        return $this->type === 'check_out';
    }

    public function markAsSuspicious(string $reason = ''): bool
    {
        return $this->update([
            'status' => 'suspicious',
            'notes' => $reason
        ]);
    }
}
