<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profiles';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'user_id', 'display_name', 'bio', 'avatar_url',
        'gender', 'age', 'city', 'state', 'country', 'nationality',
        'interests', 'marital_status', 'orientation', 'public',
        'verified_profile', 'last_active_at', 'nickname', 'profile_type',
        'looking_for', 'partner_name', 'partner_age', 'partner_gender',
        'partner_bio', 'privacy_settings', 'notifications',
        'profile_completed', 'profile_completed_at',
        'height', 'weight', 'ethnicity', 'penis_size', 'breast_size',
        'tattoos', 'piercings', 'smokes', 'drinks', 'languages', 'show_name',
        'partner_height', 'partner_weight', 'partner_ethnicity',
        'partner_nationality', 'partner_penis_size', 'partner_breast_size',
        'partner_tattoos', 'partner_piercings', 'partner_smokes',
        'partner_drinks', 'partner_languages', 'partner_orientation',
        'partner_looking_for', 'show_partner_name',
    ];

    protected $casts = [
        'id'                  => 'string',
        'user_id'             => 'string',
        'age'                 => 'integer',
        'public'              => 'boolean',
        'verified_profile'    => 'boolean',
        'profile_completed'   => 'boolean',
        'show_name'           => 'boolean',
        'show_partner_name'   => 'boolean',
        'interests'           => 'array',
        'looking_for'         => 'array',
        'languages'           => 'array',
        'privacy_settings'    => 'array',
        'notifications'       => 'array',
        'partner_looking_for' => 'array',
        'partner_languages'   => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
