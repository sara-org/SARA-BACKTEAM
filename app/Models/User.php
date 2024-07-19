<?php

namespace App\Models;
use App\Models\Donation;
use App\Models\Adoption;
use App\Models\Sponcership;
use App\Models\Vaccination;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\UserEmr;
use App\Models\UserSession;
use App\Models\ResetCodePassword;
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
        'wallet',
        'role'
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
public function doctor()
{
    return $this->hasOne(Doctor::class, 'user_id', 'id');
}
public function feedings()
{
    return $this->hasMany(Feeding::class);
}
public function vaccinations()
{
    return $this->hasMany(Vaccination::class);
}
public function emergencies()
{
    return $this->hasMany(Emergency::class);
}
public function userEmergencies()
    {
        return $this->hasMany(UserEmr::class);
    }
    public function requests()
    {
        return $this->hasMany(Req::class, 'request_id');
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function usersessions()
    {
        return $this->hasMany(UserSession::class);
    }
}
