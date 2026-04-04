<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneTimeCode extends Model
{
    use HasFactory;

    public const PURPOSE_LOGIN = 'login';
    public const PURPOSE_REGISTER = 'register';
    public const PURPOSE_EMAIL_CHANGE = 'email_change';
    public const PURPOSE_PASSWORD_RESET = 'password_reset';

    protected $fillable = [
        'email',
        'purpose',
        'code_hash',
        'expires_at',
        'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'meta' => 'array',
    ];
}
