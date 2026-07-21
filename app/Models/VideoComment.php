<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoComment extends Model
{
    protected $table = 'video_comments';

    protected $fillable = ['video_id', 'user_id', 'parent_id', 'body'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(VideoComment::class, 'parent_id')->orderBy('created_at');
    }

    public function parent()
    {
        return $this->belongsTo(VideoComment::class, 'parent_id');
    }
}