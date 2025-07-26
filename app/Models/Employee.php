<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department',
        'position',
        'hire_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    public function nfcCard(): HasOne
    {
        return $this->hasOne(NfcCard::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getTodaysAttendance()
    {
        return $this->attendances()
            ->whereDate('scanned_at', today())
            ->orderBy('scanned_at', 'desc')
            ->get();
    }

    public function getLastCheckIn()
    {
        return $this->attendances()
            ->where('type', 'check_in')
            ->whereDate('scanned_at', today())
            ->orderBy('scanned_at', 'desc')
            ->first();
    }

    public function getLastCheckOut()
    {
        return $this->attendances()
            ->where('type', 'check_out')
            ->whereDate('scanned_at', today())
            ->orderBy('scanned_at', 'desc')
            ->first();
    }
}
