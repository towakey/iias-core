<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archive extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'device_id',
        'archive_type',
        'title',
        'url',
        'body',
        'memo',
        'image_path',
        'source_data',
        'recorded_at',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'source_data' => 'array',
            'recorded_at' => 'datetime',
            'visited_at' => 'datetime',
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

    public function metadata()
    {
        return $this->hasMany(ArchiveMetadata::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
