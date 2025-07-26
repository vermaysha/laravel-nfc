<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceFingerprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'fingerprint_hash',
        'browser_info',
        'device_info',
        'ip_address',
        'user_agent',
        'status',
        'first_seen_at',
        'last_seen_at',
        'usage_count',
    ];

    protected $casts = [
        'browser_info' => 'array',
        'device_info' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isTrusted(): bool
    {
        return $this->status === 'trusted';
    }

    public function isSuspicious(): bool
    {
        return $this->status === 'suspicious';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function markAsSuspicious(): bool
    {
        return $this->update(['status' => 'suspicious']);
    }

    public function markAsBlocked(): bool
    {
        return $this->update(['status' => 'blocked']);
    }

    public function updateUsage(): bool
    {
        return $this->update([
            'last_seen_at' => now(),
            'usage_count' => $this->usage_count + 1,
        ]);
    }
}
