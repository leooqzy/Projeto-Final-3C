<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'street',
        'number',
        'zip',
        'city',
        'state',
        'country'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

