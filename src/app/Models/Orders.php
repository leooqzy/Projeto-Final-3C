<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_date',
        'coupon_id',
        'status',
        'totalAmount',
    ];
    public function products()
    {
        return $this->belongsToMany(Products::class, 'orders_items', 'order_id', 'product_id');
    }
}
