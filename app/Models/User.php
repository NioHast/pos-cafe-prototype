<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nim',
        'phone',
        'is_student_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'is_student_verified' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cashierOrders()
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }
}
