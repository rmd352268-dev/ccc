<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'secondary_password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function getSecurityCodes(): array
    {
        if (empty($this->security_codes)) {
            return [];
        }
        $decoded = json_decode($this->security_codes, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function generateFiveSecurityCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 5; $i++) {
            $num = rand(1000, 9999);
            $char = chr(rand(65, 90));
            $codes[] = "SEC-{$num}-{$char}";
        }
        return $codes;
    }
}
