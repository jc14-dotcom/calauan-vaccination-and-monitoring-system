<?php
namespace App\Models;

use App\Traits\CapturesActivityCauser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HealthWorker extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity, CapturesActivityCauser;

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'email', 'barangay_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Health worker {$eventName}")
            ->useLogName('health_worker');
    }

    protected $table = 'health_workers'; // Specify the table name

    protected $fillable = [
        'username',
        'name',
        'password',
        'email',
        'contact_number',
        'barangay_id',
        // Session security fields (Option C)
        'session_token',
        'session_created_at',
        // Account management fields
        'is_active',
        'created_by',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'session_created_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Set the name attribute with proper capitalization.
     * Example: "maria santos" -> "Maria Santos"
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = formatProperName($value);
    }

    /**
     * Get display name for activity log and UI.
     * Falls back to username if name is not set.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }
        return $this->username;
    }

    /**
     * Get the barangay this health worker is assigned to.
     * NULL means RHU admin with access to all barangays.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    /**
     * Check if this health worker is an RHU admin (has access to all barangays).
     * RHU admins have barangay_id = NULL.
     */
    public function isRHU(): bool
    {
        return $this->barangay_id === null;
    }

    /**
     * Check if this health worker is a barangay worker (limited to one barangay).
     */
    public function isBarangayWorker(): bool
    {
        return $this->barangay_id !== null;
    }

    /**
     * Check if this health worker can access a specific barangay.
     * RHU admins can access all barangays.
     * Barangay workers can only access their assigned barangay.
     *
     * @param string|null $barangayName The barangay name to check
     * @return bool
     */
    public function canAccessBarangay(?string $barangayName): bool
    {
        // RHU admin can access everything
        if ($this->isRHU()) {
            return true;
        }

        // No barangay specified = allow (for general queries)
        if (empty($barangayName)) {
            return true;
        }

        // Barangay worker can only access their assigned barangay
        return $this->barangay && 
               strtolower($this->barangay->name) === strtolower($barangayName);
    }

    /**
     * Get the barangay name this worker is assigned to.
     * Returns null for RHU admins.
     */
    public function getAssignedBarangayName(): ?string
    {
        return $this->barangay?->name;
    }

    /**
     * Get list of barangay names this health worker can access.
     * RHU admins get all active barangays.
     * Barangay workers get only their assigned barangay.
     */
    public function getAccessibleBarangays(): array
    {
        if ($this->isRHU()) {
            return Barangay::getActiveNames();
        }

        return $this->barangay ? [$this->barangay->name] : [];
    }

    /**
     * Get list of barangays for vaccination schedule dropdown.
     * RHU admins get schedulable barangays + "RHU (Health Center)" option.
     * Barangay workers get only their assigned barangay (if schedulable).
     */
    public function getSchedulableBarangays(): array
    {
        if ($this->isRHU()) {
            // Get all schedulable barangays and prepend RHU option
            $barangays = Barangay::getSchedulableNames();
            array_unshift($barangays, 'RHU (Health Center)');
            return $barangays;
        }

        // Barangay worker - return their barangay only if it's schedulable
        if ($this->barangay && $this->barangay->has_scheduled_vaccination) {
            return [$this->barangay->name];
        }

        return [];
    }

    /**
     * Get the health worker who created this account.
     */
    public function creator()
    {
        return $this->belongsTo(HealthWorker::class, 'created_by');
    }

    /**
     * Get accounts created by this health worker.
     */
    public function createdAccounts()
    {
        return $this->hasMany(HealthWorker::class, 'created_by');
    }
}
