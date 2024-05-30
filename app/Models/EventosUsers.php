<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EventosUsers extends Pivot
{

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    
    protected $fillable = [
        'user_id',
        'eventos_id'
    ];

    public function users() {
        return $this->belongsTo(User::class);
    }
}
