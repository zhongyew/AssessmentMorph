<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    //
    protected $fillable = [
        'input_text', 
        'output_result', 
        'tokens_used', 
        'fee'
    ];
}
