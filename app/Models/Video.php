<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'videos';

    protected $fillable = [
        'video_uuid', 'user_id', 'album_type', 'file_path',
        'thumbnail_path', 'duration_seconds', 'file_size_bytes',
        'caption', 'status', 'admin_note', 'reviewed_by',
        'reviewed_at', 'sort_order', 'views_count',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function likes()
    {
        return $this->hasMany(VideoLike::class);
    }

    public function comments()
    {
        return $this->hasMany(VideoComment::class)->whereNull('parent_id')->orderBy('created_at');
    }

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function isLikedBy($userId): bool
    {
        return $this->likes()->where('user_id', (string) $userId)->exists();
    }
}