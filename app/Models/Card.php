<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'has_name' => 'boolean',
        'has_address' => 'boolean',
        'has_zip' => 'boolean',
        'has_phone' => 'boolean',
        'has_mail' => 'boolean',
        'has_ssn' => 'boolean',
        'has_dob' => 'boolean',
        'has_user_agent' => 'boolean',
        'has_email_password' => 'boolean',
        'refundable' => 'boolean',
        'price_c' => 'float',
        'price_unc' => 'float',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
