<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PhotoComment extends Model
{
    use HasUuids;
    public $timestamps = false;
    protected $table = 'photo_comments';
    protected $fillable = ['photo_id', 'user_id', 'body', 'status'];

    public function user()  { return $this->belongsTo(User::class); }
    public function photo() { return $this->belongsTo(Photo::class); }

    public function scopeApproved($q) { return $q->where('status', 'approved'); }
}