<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasPushSubscriptions, HasRoles, Notifiable;

    protected $fillable = [
        'nip',
        'nip_baru',
        'username',
        'nama',
        'email_bps',
        'email_gmail',
        'status_pegawai',
        'golongan',
        'jabatan',
        'url_foto',
        'password',
        'quick_menu_keys',
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'quick_menu_keys' => 'array',
        ];
    }
}
