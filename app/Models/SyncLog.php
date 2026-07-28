<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'service_id',
        'sync_type',
        'status',
        'started_at',
        'finished_at',
        'payload_size',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
