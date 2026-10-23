<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
