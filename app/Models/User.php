<?php

namespace App\Models;
use App\Models\Donation;
use App\Models\Adoption;
use App\Models\Sponcership;
use App\Models\ResetCodePassword;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Contracts\Auth\MustVerifyEmail;

 
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable ,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'gender',
        'photo',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'password' => 'hashed',
        ];
    }
    public function donations()
{
    return $this->hasMany(Donation::class);
}
public function sponcerships()
{
    return $this->hasMany(Sponcership::class);
}
public function adoptions()
{
    return $this->hasMany(Adoption::class);
}

public function employee()
{
    return $this->hasOne(Employee::class, 'user_id', 'id');
}
}
