<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Profile extends Model
{
    protected $table = 'profiles';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'account_id', 'profile_type', 'display_name', 'user_nick', 'nick',
        'bio', 'avatar_url', 'city', 'state', 'country', 'country_code',
        'age', 'gender', 'visibility', 'is_active', 'is_complete',
        'email', 'buscas', 'para_que', 'privacidad', 'notificaciones',
    ];

    protected $casts = [
        'id' => 'string',
        'age' => 'integer',
        'is_active' => 'boolean',
        'is_complete' => 'boolean',
        'buscas' => 'array',
        'para_que' => 'array',
        'privacidad' => 'array',
        'notificaciones' => 'array',
        'interests' => 'array',
        'looking_for' => 'array',
        'activities' => 'array',
        'preferences' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
