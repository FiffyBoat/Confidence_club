<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTreasurer()
    {
        return $this->role === 'treasurer';
    }

    public function isViewer()
    {
        return $this->role === 'viewer';
    }

    public function contributionsRecorded(): HasMany
    {
        return $this->hasMany(Contribution::class, 'recorded_by');
    }

    public function incomesRecorded(): HasMany
    {
        return $this->hasMany(Income::class, 'recorded_by');
    }

    public function expensesRecorded(): HasMany
    {
        return $this->hasMany(Expense::class, 'recorded_by');
    }

    public function loansRecorded(): HasMany
    {
        return $this->hasMany(Loan::class, 'recorded_by');
    }

    public function repaymentsRecorded(): HasMany
    {
        return $this->hasMany(LoanRepayment::class, 'recorded_by');
    }

    public function receiptsGenerated(): HasMany
    {
        return $this->hasMany(Receipt::class, 'generated_by');
    }
}
