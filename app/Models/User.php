<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\CausesActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, CausesActivity, Concerns\RecordsActivity;

    protected $activityLogName = 'pengguna';
    protected $activityModelLabel = 'akun';
    protected $activityLogAttributes = ['name', 'email', 'phone', 'role', 'is_blocked'];

    protected $fillable = [
        'name',
        'role',
        'email',
        'phone',
        'avatar',
        'password',
        'is_blocked',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_blocked'        => 'boolean',
            'rpay_balance'      => 'decimal:2',
            'referral_issued_at' => 'datetime',
        ];
    }

    /*
     * Saldo R_Pay sengaja TIDAK dimasukkan ke $fillable. Satu-satunya jalan
     * mengubahnya adalah lewat App\Services\RpayService, supaya setiap
     * pergerakan dana selalu punya baris buku besarnya.
     */

    public function rpayTransactions()
    {
        return $this->hasMany(RpayTransaction::class)->latest('id');
    }

    public function rpayWithdrawals()
    {
        return $this->hasMany(RpayWithdrawal::class)->latest('id');
    }

    public function orderReturns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
}
