<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function archives()
    {
        return $this->hasMany(Archive::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function shoppingItems()
    {
        return $this->hasMany(ShoppingItem::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }
}
