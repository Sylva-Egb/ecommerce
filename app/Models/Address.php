<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'guest_id', 'full_name', 'address_line', 'city', 'zip_code', 'country', 'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class); // une commande par adresse
    }
}

