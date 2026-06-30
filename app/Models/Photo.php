<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\DB;


class Photo extends Model
{
    use HasUuids;
    protected $table = 'photos';
    protected $fillable = [
        'user_id','album_type','file_path','thumbnail_path',
        'is_profile_photo','status','admin_note',
        'reviewed_by','reviewed_at','sort_order','caption',
    ];
    protected $casts = [
        'is_profile_photo' => 'boolean',
        'reviewed_at'      => 'datetime',
        'sort_order'       => 'integer',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function likes()    { return $this->hasMany(PhotoLike::class, 'photo_id', 'id'); }
public function comments() { return $this->hasMany(PhotoComment::class, 'photo_id', 'id')->where('status','approved'); }

    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopePublic($q)   { return $q->where('album_type', 'public')->where('status', 'approved'); }
    public function scopePending($q)  { return $q->where('status', 'pending'); }

    public function isLikedBy(string $userId): bool {
    return \Illuminate\Support\Facades\DB::table('photo_likes')
        ->where(DB::raw('photo_id::text'), (string) $this->id)
        ->where(DB::raw('user_id::text'), (string) $userId)
        ->exists();
  }

}