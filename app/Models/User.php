<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip_nim',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function thesis()
    {
        return $this->hasOne(Thesis::class, 'student_id');
    }

    public function mentoredThesesAsFirst()
    {
        return $this->hasMany(Thesis::class, 'lecturer_1_id');
    }

    public function mentoredThesesAsSecond()
    {
        return $this->hasMany(Thesis::class, 'lecturer_2_id');
    }

    public function allMentoredTheses()
    {
        return $this->mentoredThesesAsFirst()->union($this->mentoredThesesAsSecond());
    }

    
    
    
    
    public function isStudent() { return $this->role === 'student'; }
    public function isLecturer() { return $this->role === 'lecturer'; }
    public function isAdmin() { return $this->role === 'admin'; }
}