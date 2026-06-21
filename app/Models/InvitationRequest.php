<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvitationRequest extends Model
{
    protected $table = 'invitation_requests';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'nombre_completo', 'email', 'edad', 'pais', 'estado',
        'municipio', 'tipo_perfil', 'motivo', 'status',
        'terminos_aceptados', 'privacidad_aceptada',
        'activation_token', 'activation_used',
    ];

    protected $casts = [
        'id' => 'string',
        'edad' => 'integer',
        'terminos_aceptados' => 'boolean',
        'privacidad_aceptada' => 'boolean',
        'activation_used' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->status)) {
                $model->status = 'pending';
            }
            if (empty($model->activation_token)) {
                $model->activation_token = Str::random(64);
            }
        });
    }
}
