<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveMetadata extends Model
{
    protected $fillable = [
        'archive_id',
        'meta_key',
        'meta_value',
        'value_type',
    ];

    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }
}
