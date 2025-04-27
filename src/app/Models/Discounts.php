<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discounts extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'startDate',
        'endDate',
        'discountPercentage',
        'product_id'
    ];
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
