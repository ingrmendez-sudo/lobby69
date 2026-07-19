<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class PhotoComment extends Model
{
    use HasUuids;
    public $timestamps = false;
    protected $table = 'photo_comments';
    protected $fillable = ['photo_id', 'user_id', 'body', 'status', 'parent_id', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];

    public function user()  { return $this->belongsTo(User::class); }
    public function photo() { return $this->belongsTo(Photo::class); }

    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopeUnread($q)   { return $q->whereNull('read_at'); }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->read_at = Carbon::now();
            $this->save();
        }
    }
}
