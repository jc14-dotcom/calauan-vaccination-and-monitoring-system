<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Parents extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'parents'; 

    protected $fillable = [
        'username',
        'password',
        'password_changed',
        'privacy_policy_accepted',
        'privacy_policy_accepted_at',
        'privacy_policy_version',
        'email',
        'barangay',
        'address',
        'contact_number',
        // Session security fields (Option C)
        'session_token',
        'session_created_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        // 'email' => 'encrypted',     // Encrypt email
        'password_changed' => 'boolean',
        'privacy_policy_accepted' => 'boolean',
        'privacy_policy_accepted_at' => 'datetime',
        'session_created_at' => 'datetime',
    ];
    
    // In Parents model
    public function patients()
    {
        return $this->hasMany(Patient::class, 'parent_id');
    }
    
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'parent_id');
    }
}