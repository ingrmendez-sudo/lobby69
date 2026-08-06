<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoLike extends Model
{
    public $timestamps = false;

    protected $table = 'video_likes';

    protected $fillable = ['video_id', 'user_id'];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}