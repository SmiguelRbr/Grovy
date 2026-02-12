<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
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
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->hasOne(Role::class);
    }

    public function professional_profile()
    {
        return $this->hasOne(ProfessionalProfile::class);
    }

    public function patient_details(){
        return $this->hasOne(PatientDetail::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'user_id');
    }

    public function contracts_as_student()
    {
        return $this->hasMany(Contract::class, 'student_id');
    }

    public function contracts_as_professional()
    {
        return $this->hasMany(Contract::class, 'professional_id');
    }

    public function plans_as_student()
    {
        return $this->hasMany(Plan::class, 'student_id');
    }

    public function plans_as_professional()
    {
        return $this->hasMany(Plan::class, 'professional_id');
    }
}
