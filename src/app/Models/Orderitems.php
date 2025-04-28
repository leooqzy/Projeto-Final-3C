<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderitems extends Model
{
    protected $table = 'orders_items';
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unitPrice',
    ];
}
