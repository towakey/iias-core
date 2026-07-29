<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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

    public function regularItems()
    {
        return $this->hasMany(RegularItem::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function tagRules()
    {
        return $this->hasMany(TagRule::class);
    }

    public function qrLoginCode()
    {
        return $this->hasOne(QrLoginCode::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }

    public function settings()
    {
        return $this->hasMany(UserSetting::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
