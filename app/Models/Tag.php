<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function archives()
    {
        return $this->morphedByMany(Archive::class, 'taggable');
    }

    public function shoppingItems()
    {
        return $this->morphedByMany(ShoppingItem::class, 'taggable');
    }
}
