<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'device_uuid',
        'device_name',
        'device_type',
        'os_version',
        'app_version',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_at' => 'datetime',
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

    public function archives()
    {
        return $this->hasMany(Archive::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }
}
