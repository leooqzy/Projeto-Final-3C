<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stock',
        'price',
        'category_id',
        'user_id',
        'image',
        'discount_percentage'
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function discounts()
    {
        return $this->hasMany(Discounts::class, 'product_id');
    }

    public function getDiscountPercentageAttribute($value)
    {
        return $value ?? 0;
    }
}
