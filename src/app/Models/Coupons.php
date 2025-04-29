<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupons extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'startDate',
        'endDate',
        'discountPercentage',
    ];

    public function orders()
    {
        return $this->hasMany(Orders::class);
    }
}
