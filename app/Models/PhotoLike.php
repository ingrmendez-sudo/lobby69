<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PhotoLike extends Model
{
    use HasUuids;
    public $timestamps = false;
    protected $table = 'photo_likes';
    protected $fillable = ['user_id', 'photo_id'];
    protected $casts = ['created_at' => 'datetime'];

    public function user()  { return $this->belongsTo(User::class); }
    public function photo() { return $this->belongsTo(Photo::class); }
}