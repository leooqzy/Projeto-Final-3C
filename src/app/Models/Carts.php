<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carts extends Model
{
    use HasFactory;

    protected $fillable = [
        'createdAt',
        'user_id',
    ];

    public function cartitems()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
