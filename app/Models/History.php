<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'device_id',
        'url',
        'title',
        'domain',
        'visited_at',
        'duration_ms',
        'source_data',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'source_data' => 'array',
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

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
