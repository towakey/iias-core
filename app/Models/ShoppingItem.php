<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoppingItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'name',
        'image_path',
        'price',
        'memo',
        'status',
        'purchased_at',
        'sort_order',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
