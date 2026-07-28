<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegularItem extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'price',
        'memo',
        'image_path',
        'sort_order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
