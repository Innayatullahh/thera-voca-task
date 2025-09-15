<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'description',
        'first_name',
        'last_name',
        'address',
        'height',
        'weight',
        'gender',
        'age',
        'raw_ai_response',
    ];

    protected $casts = [
        'raw_ai_response' => 'array',
        'age' => 'integer',
    ];
}
